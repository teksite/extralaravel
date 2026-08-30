<?php

namespace Teksite\Extralaravel\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class StorageCustomLink extends Command
{
    protected $signature = 'storage:link-custom
                            {--relative : Create relative symbolic links}
                            {--verify : Verify links after creation}';

    protected $description = 'Create custom symbolic links from config(storage.custom_links)';

    private const int DIRECTORY_PERMISSION = 0755;

    public function handle(): int
    {
        $start = microtime(true);

        $links = config('storage.custom_links', []);

        if (!is_array($links) || $links === []) {
            $this->components->warn('No links configured.');
            return self::SUCCESS;
        }

        $success = 0;
        $failed = 0;
        $skipped = 0;

        $this->components->info('Processing symbolic links...');

        foreach ($links as $key => $config) {
            try {
                if (!is_array($config) || !isset($config['link'], $config['target'])) {
                    throw new \RuntimeException("Invalid configuration for [{$key}].");
                }

                $link = $config['link'];
                $target = $config['target'];

                $this->prepareDirectories($link, $target);
                $status = $this->handleExistingLink($link);


                if ($status === 'skipped') {
                    $this->components->twoColumnDetail("⚠ {$key}", '<fg=yellow;options=bold>Already exists</>');
                    $skipped++;
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
                    '<fg=red;options=bold>' . $e->getMessage() . '</>'
                );

                $failed++;
            }
        }

        if ($this->option('verify')) $this->verifyLinks($links);


        $duration = round(microtime(true) - $start, 3);

        $this->newLine();

        $this->components->info('Summary');

        $col1_msg = "✓ Success: $success | ✗ Failed: $failed | ⚠ Skipped: $skipped";
        $col2_msg = "⏱ Time {$duration}s";
        $this->components->twoColumnDetail($col1_msg, $col2_msg);

        return $failed > 0
            ? self::FAILURE
            : self::SUCCESS;
    }

    private function createLink(string $target, string $link): bool
    {
        if ($this->option('relative')) {
            $target = $this->relativePath(dirname($link), $target);
        }
        return symlink($target, $link);
    }

    private function relativePath(string $from, string $to): string
    {
        $from = str_replace('\\', '/', realpath($from) ?: $from);
        $to = str_replace('\\', '/', realpath($to) ?: $to);

        $from = explode('/', trim($from, '/'));
        $to = explode('/', trim($to, '/'));

        while ($from !== [] && $to !== [] && $from[0] === $to[0]) {
            array_shift($from);
            array_shift($to);
        }

        return str_repeat('../', count($from)) . implode('/', $to);
    }

    private function prepareDirectories(string $link, string $target): void
    {
        if (!File::exists($target)) {
            File::makeDirectory($target, self::DIRECTORY_PERMISSION, true);
        }

        $parent = dirname($link);

        if (!File::exists($parent)) {
            File::makeDirectory($parent, self::DIRECTORY_PERMISSION, true);
        }
    }

    private function handleExistingLink(string $link)
    {
        if (!file_exists($link) && !is_link($link)) return null;

        if (is_link($link)) throw new \RuntimeException("Path exists and is not a symbolic link: {$link}");

    }

    private function verifyLinks(array $links): void
    {
        $this->newLine();

        $this->components->info('Verifying links...');

        foreach ($links as $key => $config) {

            if (!is_array($config) || !isset($config['link'], $config['target'])) {
                $this->components->twoColumnDetail("✗ {$key}", 'Invalid configuration');
                continue;
            }


            $link = $config['link'];

            if (!is_link($link)) {
                $this->components->twoColumnDetail("✗ {$key}", 'Invalid symlink');
                continue;
            }


            $target = realpath($link);

            if ($target === false || !file_exists($target)) {
                $this->components->twoColumnDetail("⚠ {$key}", 'Broken link');
                continue;
            }

            $this->components->twoColumnDetail("✓ {$key}", $target);
        }
    }
}
