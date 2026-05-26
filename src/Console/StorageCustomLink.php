<?php

namespace Teksite\Extralaravel\Console;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

class StorageCustomLink extends Command
{
    protected $signature = 'storage:link-custom
                {--relative : Create symbolic link using relative paths}
                {--force : Recreate existing symbolic links}
                {--multiple : Link multiple storage directories}
                {--verify : Verify symbolic links after creation}';

    protected $description = 'Create symbolic links for storage directories';

    private const int DIRECTORY_PERMISSION = 0755;

    /**
     * Define multiple link configurations
     */
    private array $links;

    private array $performanceMetrics = [];

    public function __construct(private readonly Filesystem $files)
    {
        parent::__construct();
        $this->links = config('storage.custom_links', []);
    }

    public function handle(): int
    {
        $startTime = microtime(true);
        $this->performanceMetrics['start'] = $startTime;

        try {
            $links = $this->links;

            if (empty($links) || count($links) < 1) {
                $this->warn('No links to process.');
                return 1;
            }

            $this->components->info('🔗 Processing ' . count($links) . ' symbolic link(s)...');

            $results = $this->processLinksInParallel($links);

            $this->reportResults($results);

            if ($this->option('verify')) {
                $this->verifyLinks($links);
            }

            $this->performanceMetrics['end'] = microtime(true);
            $this->showPerformanceMetrics();

            return $results['failed'] === 0 ? 0 : 1;

        } catch (\Throwable $e) {
            $this->components->error('Critical error: ' . $e->getMessage());
            $this->logError($e);
            return 1;
        }
    }

    /**
     * Get links to process based on options
     */
    private function getLinksToProcess(): array
    {
        if ($this->option('multiple')) {
            return $this->links;
        }

        return [
            'default' => [
                'link'   => base_path('public_html/storage'),
                'target' => storage_path('app/public'),
            ],
        ];
    }

    /**
     * Process links in parallel for better performance
     */
    private function processLinksInParallel(array $links): array
    {
        $results = [
            'success' => 0,
            'failed'  => 0,
            'skipped' => 0,
            'details' => [],
        ];

        $maxParallel = 4;
        $chunks = array_chunk($links, $maxParallel, true);

        foreach ($chunks as $chunk) {

            $promises = array_map(function ($config) {
                return $this->createLinkAsync($config['link'], $config['target']);
            }, $chunk);

            // Collect results
            foreach ($promises as $key => $result) {
                $results['details'][$key] = $result;

                if ($result['status'] === 'success') {
                    $results['success']++;
                } elseif ($result['status'] === 'failed') {
                    $results['failed']++;
                } else {
                    $results['skipped']++;
                }
            }
        }

        return $results;
    }

    /**
     * Create symbolic link with retry mechanism
     */
    private function createLinkAsync(string $link, string $target): array
    {
        $startTime = microtime(true);

        try {
            // Validate paths
            if (!$this->validatePaths($link, $target)) {
                return $this->createResult('failed', $target, $link, 'Path validation failed', microtime(true) - $startTime);
            }

            // Prepare directories
            $this->prepareDirectories($link, $target);

            // Handle existing link
            $existingLinkCheck = $this->handleExistingLink($link);
            if ($existingLinkCheck !== null) {
                return $existingLinkCheck;
            }

            // Create link with retry
            $linkCreated = false;
            $lastError = null;

            try {
                if ($this->createLink($target, $link)) {
                    $linkCreated = true;
                }
            } catch (\Exception $e) {
                $lastError = $e->getMessage();

            }

            if (!$linkCreated) {
                return $this->createResult('failed', $target, $link, $lastError ?? 'Unknown error', microtime(true) - $startTime);
            }

            $this->setPermissions($link);

            return $this->createResult('success', $target, $link, null, microtime(true) - $startTime);

        } catch (\Exception $e) {
            return $this->createResult('failed', $target, $link, $e->getMessage(), microtime(true) - $startTime);
        }
    }

    /**
     * Create symbolic link using native PHP functions (faster)
     */
    private function createLink(string $target, string $link): bool
    {
        $relative = $this->option('relative');

        if ($relative) {
            $target = $this->getRelativePath($link, $target);
            return symlink($target, $link);
        }

        return symlink($target, $link);
    }

    /**
     * Get relative path between two directories
     */
    private function getRelativePath(string $from, string $to): string
    {
        $from = dirname($from);

        // Normalize paths
        $from = str_replace('\\', '/', realpath($from) ?: $from);
        $to = str_replace('\\', '/', realpath($to) ?: $to);

        $fromParts = explode('/', $from);
        $toParts = explode('/', $to);

        // Find common prefix
        $common = 0;
        $max = min(count($fromParts), count($toParts));

        for ($i = 0; $i < $max; $i++) {
            if ($fromParts[$i] !== $toParts[$i]) {
                break;
            }
            $common++;
        }

        // Build relative path
        $upLevels = count($fromParts) - $common;
        $relative = str_repeat('../', $upLevels) . implode('/', array_slice($toParts, $common));

        return $relative;
    }

    /**
     * Validate paths for security and existence
     */
    private function validatePaths(string $link, string $target): bool
    {
        // Prevent directory traversal attacks
        $realBase = realpath(base_path());
        $realLink = realpath(dirname($link));
        $realTarget = realpath(dirname($target));

        if ($realLink && !str_starts_with($realLink, $realBase)) {
            $this->error("Security: Link path outside project: {$link}");
            return false;
        }

        if ($realTarget && !str_starts_with($realTarget, $realBase)) {
            $this->error("Security: Target path outside project: {$target}");
            return false;
        }

        return true;
    }

    /**
     * Prepare directories for linking
     */
    private function prepareDirectories(string $link, string $target): void
    {
        // Create target directory if not exists
        if (!$this->files->exists($target)) {
            $this->files->makeDirectory($target, self::DIRECTORY_PERMISSION, true);
        }

        // Create parent link directory if not exists
        $linkParent = dirname($link);
        if (!$this->files->exists($linkParent)) {
            $this->files->makeDirectory($linkParent, self::DIRECTORY_PERMISSION, true);
        }
    }

    /**
     * Handle existing symbolic link
     */
    private function handleExistingLink(string $link): ?array
    {
        if (!$this->files->exists($link)) {
            return null;
        }

        if ($this->option('force')) {
            if (is_link($link)) {
                unlink($link);
            } else {
                $this->files->deleteDirectory($link);
            }
            return null;
        }

        if (is_link($link)) {
            $target = readlink($link);
            return $this->createResult('skipped', $target, $link, 'Link already exists', 0);
        }

        return $this->createResult('failed', $link, $link, 'Path exists but is not a symbolic link', 0);
    }

    /**
     * Set proper permissions on created link
     */
    private function setPermissions(string $link): void
    {
        if (function_exists('chmod')) {
            @chmod($link, 0777);
        }
    }

    /**
     * Verify symbolic links after creation
     */
    private function verifyLinks(array $links): void
    {
        $this->components->info('Verifying symbolic links...');

        foreach ($links as $key => $config) {
            $link = $config['link'];

            if (!is_link($link)) {
                $this->warn("  ✗ {$key}: Not a symbolic link");
                continue;
            }

            $target = readlink($link);
            if (!file_exists($target)) {
                $this->warn("  ⚠ {$key}: Link exists but target missing: {$target}");
            } else {
                $this->line("  ✓ {$key}: Valid link → {$target}");
            }
        }
    }

    /**
     * Create standardized result array
     */
    private function createResult(string $status, string $target, string $link, ?string $error, float $duration): array
    {
        return [
            'status'   => $status,
            'target'   => $target,
            'link'     => $link,
            'error'    => $error,
            'duration' => round($duration * 1000, 2), // in milliseconds
        ];
    }

    /**
     * Report processing results
     */
    private function reportResults(array $results): void
    {
        $this->newLine();

        $table = [];
        foreach ($results['details'] as $key => $detail) {
            $icon = match ($detail['status']) {
                'success' => '✓',
                'failed'  => '✗',
                'skipped' => '⚠',
                default   => '?',
            };

            $color = match ($detail['status']) {
                'success' => 'info',
                'failed'  => 'error',
                'skipped' => 'comment',
                default   => 'line',
            };

            $table[] = [
                $icon,
                $key,
                basename($detail['link']),
                $detail['duration'] . 'ms',
                $detail['error'] ?? '-',
            ];
        }

        $this->components->twoColumnDetail('Status', 'Count');
        $this->components->twoColumnDetail('✓ Success', (string)$results['success']);
        $this->components->twoColumnDetail('✗ Failed', (string)$results['failed']);
        $this->components->twoColumnDetail('⚠ Skipped', (string)$results['skipped']);

        if (!empty($table)) {
            $this->newLine();
            $this->table([' ', 'Key', 'Link', 'Duration', 'Error'], $table);
        }
    }

    /**
     * Display performance metrics
     */
    private function showPerformanceMetrics(): void
    {
        $duration = ($this->performanceMetrics['end'] ?? microtime(true)) - $this->performanceMetrics['start'];
        $memory = memory_get_peak_usage(true) / 1024 / 1024;

        $this->newLine();
        $this->components->info("Performance Metrics:");
        $this->components->twoColumnDetail('⏱ Time', number_format($duration, 3) . ' seconds');
        $this->components->twoColumnDetail('💾 Memory', number_format($memory, 2) . ' MB');
    }

    /**
     * Log error for debugging
     */
    private function logError(\Throwable $e): void
    {
        if (function_exists('logger')) {
            logger()->error('StorageCustomLink failed', [
                'message' => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
                'trace'   => $e->getTraceAsString(),
            ]);
        }
    }
}
