<?php

namespace Teksite\Extralaravel\Console;

use Illuminate\Console\Command;
use Illuminate\Console\ConfirmableTrait;
use Illuminate\Database\Connection;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Finder\Finder;
use Throwable;

class PurgeCacheCommand extends Command
{
    use ConfirmableTrait;

    protected $signature = 'cache:purge
        {--store=database : Name of the database cache store to purge}
        {--connection= : Database connection used by the database cache store}
        {--limit=1000 : Maximum records/files to process per batch}
        {--dry-run : Show what would be deleted without deleting anything}
        {--force : Allow destructive operation in production}
        {--skip-database : Skip database cache cleanup}
        {--skip-files : Skip file cache cleanup}
        {--skip-tokens : Skip Sanctum token cleanup}';


    protected $description = 'Purge expired database cache, file cache, and Sanctum tokens';

    private const int DEFAULT_LIMIT = 1000;

    /**
     * Laravel stores the expiration timestamp in the first 10 bytes of every cache file.
     */
    private const int FILE_EXPIRATION_LENGTH = 10;

    public function __construct(protected Filesystem $files)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');

        if (
            !$dryRun &&
            !$this->confirmToProceed(
                'Are you sure you want to run this command?',
                fn (): bool => $this->laravel->environment('production')
            )
        ) {
            return self::FAILURE;
        }


        $start = microtime(true);

        $this->info('Starting cache cleanup...');

        if ($dryRun) {
            $this->warn('⚠️ DRY RUN MODE - No data will be deleted.');
            $this->newLine();
        }

        $stats = ['database' => null, 'locks' => null, 'files' => null, 'tokens' => null];

        try {
            if (!$this->option('skip-database')) {
                ['database' => $stats['database'], 'locks' => $stats['locks']] = $this->purgeDatabaseStore();
            }

            if (!$this->option('skip-tokens')) {
                $this->purgeSanctumTokens();
            }

            if (!$this->option('skip-files')) {
                $stats['files'] = $this->purgeFileCache();
            }

        } catch (Throwable $e) {
            $this->error("Cache cleanup failed: {$e->getMessage()}");
            report($e);
            return self::FAILURE;
        }

        $duration = round((microtime(true) - $start) * 1000, 2);

        $this->displaySummary($stats, $duration, $dryRun);

        if (!$dryRun) $this->logSummary($stats, $duration);

        return self::SUCCESS;
    }

    private function getLimit(): int
    {
        $limit = (int) $this->option('limit');

        return $limit > 0 ? $limit : self::DEFAULT_LIMIT;
    }


    /**
     * Purge expired rows from the cache table and the cache lock table of a database cache store.
     *
     * @return array{database: int|null, locks: int|null}
     */
    private function purgeDatabaseStore(): array
    {
        $store = (string) $this->option('store');
        $config = config("cache.stores.{$store}");

        if (!is_array($config) || ($config['driver'] ?? null) !== 'database') {
            $this->line("ℹ️ Cache store [{$store}] is not a database store. Skipping.");
            return ['database' => null, 'locks' => null];
        }

        $override = $this->option('connection') ?: null;

        return [
            'database' => $this->purgeExpiredRows(
                DB::connection($override ?? $config['connection'] ?? null),
                (string) ($config['table'] ?? 'cache'),
                'database cache',
            ),
            'locks' => $this->purgeExpiredRows(
                DB::connection($override ?? $config['lock_connection'] ?? $config['connection'] ?? null),
                (string) ($config['lock_table'] ?? 'cache_locks'),
                'cache lock',
            ),
        ];
    }


    /**
     * Purge expired rows from the cache table and the cache lock table of a database cache store.
     *
     * @param Connection $connection
     * @param string     $table
     * @param string     $label
     * @return int
     */
    private function purgeExpiredRows(Connection $connection, string $table, string $label): int
    {
        $schema = $connection->getSchemaBuilder();

        if (!$schema->hasTable($table) || !$schema->hasColumn($table, 'expiration')) {
            $this->warn("Table [{$table}] does not exist or has no [expiration] column. Skipping {$label} cleanup.");
            return 0;
        }

        // A single timestamp keeps the batches consistent and guarantees the loop terminates.
        $now = time();

        if ($this->option('dry-run')) {
            $count = $connection->table($table)->where('expiration', '<=', $now)->count();
            $this->line("📊 Would delete {$count} expired {$label} item(s).");

            return $count;
        }

        $limit = $this->getLimit();
        $deleted = 0;

        do {
            $affected = $connection->table($table)->where('expiration', '<=', $now)->limit($limit)->delete();
            $deleted += $affected;
        } while ($affected === $limit);

        $this->line($deleted > 0
            ? "🗑️ Deleted {$deleted} expired {$label} item(s)."
            : "✨ No expired {$label} items found.");

        return $deleted;
    }

    /**
     * Run Sanctum's own pruning command.
     *
     * Sanctum already implements this logic, so it is not duplicated here. The command is never run
     * in dry-run mode because it deletes tokens immediately.
     */
    private function purgeSanctumTokens(): void
    {
        if (!class_exists(\Laravel\Sanctum\Sanctum::class) || !$this->getApplication()?->has('sanctum:prune-expired')) {
            $this->line('ℹ️ Laravel Sanctum is not installed. Skipping tokens.');
            return;
        }

        if ($this->option('dry-run')) {
            $this->line('ℹ️ Skipping sanctum:prune-expired in dry-run mode.');
            return;
        }

        $this->call('sanctum:prune-expired');
    }



    private function purgeFileCache(): ?int
    {
        $cachePath = (string) (config('cache.stores.file.path') ?: storage_path('framework/cache/data'));

        if (!$this->files->isDirectory($cachePath)) {
            $this->line("ℹ️ File cache directory does not exist: {$cachePath}");
            return null;
        }

        $dryRun = (bool) $this->option('dry-run');
        $now = time();
        $count = 0;

        $finder = (new Finder())->files()->in($cachePath)->ignoreDotFiles(true)->depth('> 1');

        foreach ($finder as $file) {
            $path = $file->getPathname();

            if (!$this->isExpiredCacheFile($path, $now)) continue;


            if (!$dryRun) $this->files->delete($path);

            $count++;
        }

        if ($dryRun) {
            $this->line("📊 Would delete {$count} expired file cache item(s).");
        } elseif ($count > 0) {
            $this->removeEmptyDirectories($cachePath);
            $this->line("📁 Deleted {$count} expired file cache item(s).");
        } else {
            $this->line('✨ No expired file cache items found.');
        }

        return $count;
    }

    private function isExpiredCacheFile(string $path, int $now): bool
    {
        $handle = @fopen($path, 'rb');

        if ($handle === false)  return false;

        try {
            $expiration = fread($handle, self::FILE_EXPIRATION_LENGTH);

            if ($expiration === false || strlen($expiration) !== self::FILE_EXPIRATION_LENGTH || !ctype_digit($expiration)) return false;

            return $now >= (int) $expiration;
        } finally {
            fclose($handle);
        }
    }


    /**
     * Remove empty cache directories, deepest first.
     *
     * rmdir() refuses to remove a directory that is not empty, so a cache file written by another
     * process in the meantime is never deleted by accident (unlike a recursive directory delete).
     */
    private function removeEmptyDirectories(string $root): void
    {
        $directories = [];

        foreach ((new Finder())->directories()->in($root) as $directory) {
            $directories[] = $directory->getPathname();
        }

        usort($directories, static fn (string $a, string $b): int => strlen($b) <=> strlen($a));

        foreach ($directories as $directory) {
            @rmdir($directory);
        }
    }


    /**
     * @param array<string, int|null> $stats
     */
    private function logSummary(array $stats, float $duration): void
    {
        Log::info('Extralaravel cache cleanup completed.', [
            'database'    => $stats['database'] ?? 'N/A',
            'locks'       => $stats['locks'] ?? 'N/A',
            'files'       => $stats['files'] ?? 'N/A',
            'duration_ms' => $duration,
        ]);
    }

    /**
     * @param array<string, int|null> $stats
     */
    private function displaySummary(array $stats, float $duration, bool $dryRun): void
    {
        $memory = round(memory_get_peak_usage(true) / 1024 / 1024, 2);

        $this->newLine();
        $this->info("✅ Cleanup completed in {$duration}ms (Peak memory: {$memory} MB)");

        $show = static fn (?int $value): string => $value === null ? '-' : (string) $value;

        $this->table(
            ['Cache Type', $dryRun ? 'Would Delete' : 'Deleted Items'],
            [
                ['Database Cache', $show($stats['database'])],
                ['Database Locks', $show($stats['locks'])],
                ['File Cache', $show($stats['files'])],
                ['Total', (string) array_sum($stats)],
            ],
        );
    }


}
