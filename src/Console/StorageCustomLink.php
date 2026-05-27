<?php

namespace Teksite\Extralaravel\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class StorageCustomLink extends Command
{
    protected $signature = 'storage:link-custom
                            {--relative : Create relative symbolic links}
                            {--force : Recreate existing links}
                            {--verify : Verify links after creation}';

    protected $description = 'Create custom symbolic links from config(storage.custom_links)';

    private const int DIRECTORY_PERMISSION = 0755;

    public function handle(): int
    {
        $start = microtime(true);

        $links = config('storage.custom_links', []);

        if (!is_array($links) || empty($links)) {
            $this->components->warn('No links configured.');

            return self::SUCCESS;
        }

        $success = 0;
        $failed = 0;
        $skipped = 0;

        $this->components->info('Processing symbolic links...');

        foreach ($links as $link => $target) {

            if (!isset($config['link'], $config['target'])) {
                $this->components->error("Invalid config for [{$key}]");
                $failed++;
                continue;
            }

            $link = $config['link'];
            $target = $config['target'];

            try {

                $this->prepareDirectories($link, $target);

                $status = $this->handleExistingLink($link);

                if ($status === 'skipped') {
                    $this->components->twoColumnDetail( "⚠ {$key}", 'Already exists' );
                    $skipped++;
                    continue;
                }

                if ($status === 'failed') {
                    $failed++;
                    continue;
                }

                $created = $this->createLink($target, $link);

                if (!$created) {
                    throw new \RuntimeException('Unable to create symbolic link.');
                }

                $this->components->twoColumnDetail(
                    "✓ {$key}",
                    "{$link} → {$target}"
                );

                $success++;

            } catch (\Throwable $e) {

                $this->components->twoColumnDetail(
                    "✗ {$key}",
                    $e->getMessage()
                );

                $failed++;
            }
        }

        if ($this->option('verify')) {
            $this->verifyLinks($links);
        }

        $duration = round(microtime(true) - $start, 3);

        $this->newLine();

        $this->components->info('Summary');

        $this->components->twoColumnDetail('✓ Success', (string) $success);
        $this->components->twoColumnDetail('✗ Failed', (string) $failed);
        $this->components->twoColumnDetail('⚠ Skipped', (string) $skipped);
        $this->components->twoColumnDetail('⏱ Time', "{$duration}s");

        return $failed > 0
            ? self::FAILURE
            : self::SUCCESS;
    }

    private function createLink(string $target, string $link): bool
    {
        if ($this->option('relative')) {

            $target = $this->relativePath(
                dirname($link),
                $target
            );
        }

        return symlink($target, $link);
    }

    private function relativePath(string $from, string $to): string
    {
        $from = str_replace('\\', '/', realpath($from) ?: $from);
        $to = str_replace('\\', '/', realpath($to) ?: $to);

        $from = explode('/', trim($from, '/'));
        $to = explode('/', trim($to, '/'));

        while (
            count($from) &&
            count($to) &&
            ($from[0] === $to[0])
        ) {
            array_shift($from);
            array_shift($to);
        }

        return str_repeat('../', count($from)) . implode('/', $to);
    }

    private function prepareDirectories(string $link, string $target): void
    {
        if (!File::exists($target)) {
            File::makeDirectory(
                $target,
                self::DIRECTORY_PERMISSION,
                true
            );
        }

        $parent = dirname($link);

        if (!File::exists($parent)) {
            File::makeDirectory(
                $parent,
                self::DIRECTORY_PERMISSION,
                true
            );
        }
    }

    private function handleExistingLink(string $link): ?string
    {
        if (!file_exists($link) && !is_link($link)) {
            return null;
        }

        if (!$this->option('force')) {

            if (is_link($link)) {
                return 'skipped';
            }

            throw new \RuntimeException(
                "Path exists and is not a symbolic link: {$link}"
            );
        }

        if (is_link($link)) {
            unlink($link);

            return null;
        }

        File::deleteDirectory($link);

        return null;
    }

    private function verifyLinks(array $links): void
    {
        $this->newLine();

        $this->components->info('Verifying links...');

        foreach ($links as $key => $config) {

            $link = $config['link'];

            if (!is_link($link)) {

                $this->components->twoColumnDetail(
                    "✗ {$key}",
                    'Invalid symlink'
                );

                continue;
            }

            $target = realpath($link);

            if (!$target || !file_exists($target)) {

                $this->components->twoColumnDetail(
                    "⚠ {$key}",
                    'Broken link'
                );

                continue;
            }

            $this->components->twoColumnDetail(
                "✓ {$key}",
                $target
            );
        }
    }
}
