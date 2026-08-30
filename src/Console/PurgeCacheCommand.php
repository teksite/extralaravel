<?php

namespace Teksite\Extralaravel\Console;

use Illuminate\Console\Command;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Finder\Finder;

class PurgeCacheCommand extends Command
{
    protected $signature = 'cache:purge
        {--connection= : Database connection used by the database cache store}
        {--limit=1000 : Maximum records/files to process per batch}
        {--dry-run : Show what would be deleted without deleting anything}
        {--force : Allow destructive operation in production}
        {--skip-database : Skip database cache cleanup}
        {--skip-files : Skip file cache cleanup}
        {--skip-tokens : Skip Sanctum token cleanup}';


    protected $description = 'Purge expired database cache, file cache, and Sanctum tokens';

    private const int DEFAULT_LIMIT = 1000;

    public function __construct(protected Filesystem $files)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        if (!$this->confirmProductionExecution()) return self::FAILURE;

        $start = microtime(true);
        $dryRun = (bool)$this->option('dry-run');
        $this->displayStartMessage($dryRun);

        $stats = ['database' => 0, 'files' => 0, 'tokens' => 0,];

        try {
            if (!$this->option('skip-database')) {
                $stats['database'] = $this->purgeDatabaseCache();
            }

            if (!$this->option('skip-tokens')) {
                $stats['tokens'] = $this->purgeSanctumTokens();
            }

            if (!$this->option('skip-files')) {
                $stats['files'] = $this->purgeFileCache();
            }
        } catch (\Throwable $e) {
            $this->error("Cache cleanup failed: {$e->getMessage()}");
            $this->logException($e);
            return self::FAILURE;
        }

        $duration = round((microtime(true) - $start) * 1000, 2);

        $this->displaySummary($stats, $duration);

        if (!$dryRun) $this->logSummary($stats, $duration);

        return self::SUCCESS;
    }

    /**
     * Ask for confirmation before destructive production operations.
     */
    private function confirmProductionExecution(): bool
    {
        if (app()->environment('production') && !$this->option('force')) {
            return $this->confirm('⚠️ Application is running in production. Continue?');
        }
        return true;
    }

    /**
     * Display command start information.
     */
    private function displayStartMessage(bool $dryRun): void
    {
        $this->info('🧹 Starting cache cleanup...');

        if ($dryRun) {
            $this->warn('⚠️ DRY RUN MODE - No data will be deleted.');
            $this->newLine();
        }
    }

    /**
     * Get validated batch limit.
     */
    private function getLimit(): int
    {
        $limit = (int)$this->option('limit');

        return $limit > 0 ? $limit : self::DEFAULT_LIMIT;
    }

    /**
     * Purge expired database cache records.
     */
    private function purgeDatabaseCache(): null|int
    {
        $config = config('cache.stores.database');

        if (($config['driver'] ?? null) !== 'database') {
            $this->line('ℹ️ Database cache store is not configured. Skipping.');
            return null;
        }

        $table = $config['table'] ?? 'cache';

        $connectionName = $this->option('connection') ?: ($config['connection'] ?? null);

        $connection = DB::connection($connectionName);

        if (!$this->tableExists($connection, $table)) {
            $this->warn("Database cache table [{$table}] does not exist. Skipping...");
            return 0;
        }

        if (!$this->hasColumn($connection, $table, 'expiration')) {
            $this->warn("Database cache table [{$table}] does not contain " . "[expiration]. Skipping...");
            return 0;
        }

        $query = $connection->table($table)->where('expiration', '<', time());

        if ($this->option('dry-run')) {
            $count = $query->count();
            $this->line("📊 Would delete {$count} expired database cache item(s).");
            return 0;
        }

        $deleted = 0;
        $limit = $this->getLimit();

        while (true) {
            $affected = $connection
                ->table($table)
                ->where('expiration', '<', time())
                ->limit($limit)
                ->delete();

            $deleted += $affected;

            if ($affected === 0) break;

            $this->newLine(2);

            if ($affected < $limit) break;
        }

        if ($deleted > 0) {
            $this->newLine();

            $this->info("🗑️ Deleted {$deleted} expired database cache item(s).");
        } else {
            $this->line('✨ No expired database cache items found.');
        }

        return $deleted;
    }

    /**
     * Purge expired Sanctum tokens.
     *
     * Laravel Sanctum already provides its own pruning command.
     * This package intentionally does not duplicate that logic.
     */
    private function purgeSanctumTokens(): null|int
    {
        if (!class_exists(\Laravel\Sanctum\Sanctum::class)) {
            $this->line('ℹ️ Laravel Sanctum is not installed. Skipping tokens.');
            return null;
        }
        $this->call('sanctum:prune-expired');
        return 0;
    }

    /**
     * Purge expired Laravel file cache entries.
     *
     * Laravel stores the expiration timestamp in the first
     * 10 bytes of each cache file.
     */
    private function purgeFileCache(): null|int
    {
        $cachePath = storage_path('framework/cache/data');

        if (!$this->files->isDirectory($cachePath)) {
            $this->line("ℹ️ File cache directory does not exist: {$cachePath}");
            return null;
        }

        $finder = new Finder();
        try {
            $finder->files()
                   ->in($cachePath)
                   ->ignoreDotFiles(true)
                   ->depth('> 1');

            $now = time();
            $deleted = 0;
            $limit = $this->getLimit();

            foreach ($finder as $file) {
                if ($this->isExpiredCacheFile($file->getRealPath(), $now)) {
                    if (!$this->option('dry-run')) {
                        $this->files->delete($file->getRealPath());
                    }
                    $deleted++;

                    if ($deleted % $limit === 0) $this->output->write('.');
                }
            }

            if (!$this->option('dry-run') && $deleted > 0) {
                $this->deleteEmptyDirectories($cachePath);
            }

            if ($this->option('dry-run')) {
                $this->line("📊 Would delete {$deleted} expired file cache item(s).");
            } elseif ($deleted > 0) {
                $this->newLine();
                $this->info("📁 Deleted {$deleted} expired file cache item(s).");
            } else {
                $this->line('✨ No expired file cache items found.');
            }

            return $deleted;
        } catch (\Throwable $e) {
            $this->error("File cache cleanup failed: {$e->getMessage()}");
            $this->logException($e);
            return 0;
        }
    }

    /**
     * Determine whether a Laravel file cache entry is expired.
     */
    private function isExpiredCacheFile(string $path, int $now): bool
    {
        $handle = @fopen($path, 'rb');

        if ($handle === false) return false;

        try {
            $expiration = fread($handle, 10);

            if ($expiration === false || strlen($expiration) !== 10) return false;

            if (!ctype_digit($expiration))   return false;

            return $now >= (int)$expiration;
        } finally {
            fclose($handle);
        }
    }

    /**
     * Remove empty cache directories.
     */
    private function deleteEmptyDirectories(string $path): void
    {
        if (!$this->files->isDirectory($path)) return;

        foreach ($this->files->directories($path) as $directory) {
            $this->deleteEmptyDirectories($directory);

            if (count($this->files->files($directory)) === 0 && count($this->files->directories($directory)) === 0) {
                $this->files->deleteDirectory($directory);
            }
        }
    }

    /**
     * Determine whether a database table exists.
     */
    private function tableExists(ConnectionInterface $connection, string $table): bool
    {
        try {
            return $connection->getSchemaBuilder()->hasTable($table);
        } catch (\Throwable) {
            return false;
        }
    }

    /**
     * Determine whether a database table contains a column.
     */
    private function hasColumn(ConnectionInterface $connection, string $table, string $column): bool
    {
        try {
            return in_array(
                $column,
                $connection->getSchemaBuilder()->getColumnListing($table), true);
        } catch (\Throwable) {
            return false;
        }
    }

    /**
     * Log an exception without exposing sensitive command arguments.
     */
    private function logException(\Throwable $e): void
    {
        report($e);
    }

    /**
     * Log cleanup summary.
     */
    private function logSummary(array $stats, float $duration): void
    {
        Log::info('Extralaravel cache cleanup completed.', [
            'database'    => $stats['database'] ?? 'N/A',
            'files'       => $stats['files'] ?? 'N/A',
            'tokens'      => $stats['tokens'] ?? 'N/A',
            'duration_ms' => $duration,
        ]);
    }

    /**
     * Display cleanup summary.
     */
    private function displaySummary(array $stats, float $duration): void
    {
        $memory = round(memory_get_peak_usage(true) / 1024 / 1024, 2);

        $this->newLine();

        $this->info("✅ Cleanup completed in {$duration}ms " . "(Peak memory: {$memory} MB)");

        $this->table(
            ['Cache Type', 'Deleted Items'],
            [
                ['Database Cache', $stats['database']],
                ['File Cache', $stats['files']],
                ['Sanctum Tokens', $stats['tokens']],
                ['Total', array_sum($stats)],
            ]
        );
    }
}
