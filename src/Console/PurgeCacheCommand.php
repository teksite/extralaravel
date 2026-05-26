<?php

namespace Teksite\Extralaravel\Console;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Symfony\Component\Finder\Finder;

class PurgeCacheCommand extends Command
{
    protected $signature = 'cache:purge
                            {--connection= : Database connection for cache}
                            {--limit=1000 : Limit for deleting records per batch}
                            {--dry-run : Simulate without actually deleting}
                            {--force : Force delete even on production}';

    protected $description = 'Clear expired database cache, tokens, and filesystem cache';

    public function __construct(
        protected Filesystem $files
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        // Safety check for production
        if (!$this->option('force') && app()->environment('production')) {
            if (!$this->confirm('⚠️  You are in production environment. Do you want to continue?')) {
                $this->info('Operation cancelled.');
                return 1;
            }
        }

        $this->info('🧹 Starting cache cleanup...');
        $startTime = microtime(true);

        if ($this->option('dry-run')) {
            $this->warn('⚠️  DRY RUN MODE - No actual deletions will occur');
            $this->newLine();
        }

        $stats = [
            'database' => 0,
            'tokens' => 0,
            'files' => 0,
        ];

        // 1. Clean Database Cache
        $stats['database'] = $this->cleanDatabaseCache();

        // 2. Clean Personal Access Tokens
        $stats['tokens'] = $this->cleanPersonalAccessTokens();

        // 3. Clean File Cache
        $stats['files'] = $this->cleanFileCache();

        $duration = round((microtime(true) - $startTime) * 1000, 2);
        $memory =function_exists('memory_get_peak_usage') ?  round(memory_get_peak_usage(true) / 1024 / 1024, 2) : 'UNKNOWN';

        $this->newLine();
        $this->info("✅ Cleanup completed in {$duration}ms (Memory: {$memory}MB)");

        $this->table(
            ['Cache Type', 'Deleted Items'],
            [
                ['Database Cache', $stats['database']],
                ['Expired Tokens', $stats['tokens']],
                ['File Cache', $stats['files']],
                ['━━━━━━━━━━━━━━', '━━━━━━━━━━━━'],
                ['Total', array_sum($stats)],
            ]
        );

        // Log the operation
        if (!$this->option('dry-run')) {
            Log::channel('daily')->info('Cache cleanup completed', [
                'stats' => $stats,
                'duration_ms' => $duration,
                'memory_mb' => $memory
            ]);
        }

        return 0;
    }

    protected function cleanDatabaseCache(): int
    {
        $table = Config::get('cache.stores.database.table', 'cache');
        $connection = $this->option('connection') ?? Config::get('cache.stores.database.connection');

        if (!$this->isTableExists($table, $connection)) {
            $this->warn("Table '{$table}' not found, skipping...");
            return 0;
        }

        // First, check if table has the required columns
        $columns = $this->getTableColumns($table, $connection);

        if (!in_array('expiration', $columns)) {
            $this->error("Table '{$table}' doesn't have 'expiration' column");
            return 0;
        }

        $query = DB::connection($connection)
                   ->table($table)
                   ->where('expiration', '<', time());

        if ($this->option('dry-run')) {
            $count = $query->count();
            $this->line("📊 Would delete {$count} expired cache items from '{$table}'");
            return 0;
        }

        $deleted = 0;
        $limit = (int)$this->option('limit');

        // Method 1: Simple delete (works without ID column)
        do {
            // For tables without auto-increment ID, use limit without ordering
            $affected = DB::connection($connection)
                          ->table($table)
                          ->where('expiration', '<', time())
                          ->limit($limit)
                          ->delete();

            $deleted += $affected;

            if ($affected > 0) {
                $this->output->write('.');
            }

            // Avoid infinite loop if something goes wrong
            if ($affected === 0) {
                break;
            }

            // Small delay to avoid overloading the database
            if ($affected === $limit) {
                usleep(10000); // 10ms
            }

        } while ($affected === $limit);

        if ($deleted > 0) {
            $this->newLine();
            $this->info("🗑️  Deleted {$deleted} expired cache item(s) from database");
        } else {
            $this->line("✨ No expired cache items found in database");
        }

        return $deleted;
    }

    protected function cleanPersonalAccessTokens(): int
    {
        // Check if Sanctum is installed
        if (!class_exists('\Laravel\Sanctum\Sanctum')) {
            $this->line("ℹ️  Laravel Sanctum not installed, skipping tokens cleanup");
            return 0;
        }

        $table = Config::get('sanctum.table', 'personal_access_tokens');

        if (!$this->isTableExists($table)) {
            $this->warn("Table '{$table}' not found, skipping tokens cleanup");
            return 0;
        }

        $columns = $this->getTableColumns($table);

        if (!in_array('expires_at', $columns)) {
            $this->warn("Table '{$table}' doesn't have 'expires_at' column, skipping");
            return 0;
        }

        $query = DB::table($table)
                   ->where('expires_at', '<', Carbon::now());

        if ($this->option('dry-run')) {
            $count = $query->count();
            $this->line("📊 Would delete {$count} expired tokens");
            return 0;
        }

        // For tokens table, use chunk by ID if available
        if (in_array('id', $columns)) {
            $deleted = 0;
            $query->chunkById(500, function ($tokens) use (&$deleted) {
                $ids = $tokens->pluck('id')->toArray();
                $deleted += DB::table($tokens->first()->getTable())
                              ->whereIn('id', $ids)
                              ->delete();
                $this->output->write('.');
            });
        } else {
            // Fallback to simple delete
            $deleted = $query->delete();
        }

        if ($deleted > 0) {
            $this->newLine();
            $this->info("🔑 Deleted {$deleted} expired personal access token(s)");
        }

        return $deleted;
    }

    protected function cleanFileCache(): int
    {
        $cachePath = storage_path('framework/cache/data');

        if (!$this->files->exists($cachePath)) {
            $this->warn("Cache path not found: {$cachePath}");
            return 0;
        }

        $finder = new Finder();
        $deleted = 0;
        $now = time();

        try {
            $finder->files()
                   ->in($cachePath)
                   ->ignoreDotFiles(true)
                   ->depth('== 0'); // Only top level files to avoid recursion issues

            foreach ($finder as $file) {
                // Check if file is expired (older than cache lifetime)
                $cacheLifetime = Config::get('cache.stores.file.lifetime', 3600);
                $expirationTime = $file->getMTime() + $cacheLifetime;

                if ($expirationTime < $now) {
                    if (!$this->option('dry-run')) {
                        $this->files->delete($file->getRealPath());
                    }
                    $deleted++;

                    if ($deleted % 100 === 0) {
                        $this->output->write('.');
                    }
                }
            }

            // Clean empty directories after file deletion
            if (!$this->option('dry-run') && $deleted > 0) {
                $this->deleteEmptyDirectories($cachePath);
            }

            if ($deleted > 0 && !$this->option('dry-run')) {
                $this->newLine();
                $this->info("📁 Deleted {$deleted} expired cached file(s)");
            } elseif ($this->option('dry-run')) {
                $this->line("📊 Would delete {$deleted} expired cached file(s)");
            } else {
                $this->line("📁 No expired cache files found");
            }

        } catch (\Exception $e) {
            $this->error("Error cleaning file cache: " . $e->getMessage());
            Log::warning('File cache cleanup failed', ['error' => $e->getMessage()]);
        }

        return $deleted;
    }

    protected function deleteEmptyDirectories(string $path): void
    {
        if (!$this->files->isDirectory($path)) {
            return;
        }

        $directories = iterator_to_array($this->files->directories($path));

        foreach ($directories as $directory) {
            $this->deleteEmptyDirectories($directory);

            if (count($this->files->files($directory)) === 0 &&
                count($this->files->directories($directory)) === 0) {
                $this->files->deleteDirectory($directory);
            }
        }
    }

    protected function isTableExists(string $table, ?string $connection = null): bool
    {
        try {
            return DB::connection($connection)
                     ->getSchemaBuilder()
                     ->hasTable($table);
        } catch (\Exception $e) {
            return false;
        }
    }

    protected function getTableColumns(string $table, ?string $connection = null): array
    {
        try {
            return DB::connection($connection)
                     ->getSchemaBuilder()
                     ->getColumnListing($table);
        } catch (\Exception $e) {
            return [];
        }
    }
}
