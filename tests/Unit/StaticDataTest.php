<?php

namespace Teksite\Extralaravel\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Teksite\Extralaravel\Support\StaticData;

final class StaticDataTest extends TestCase
{
    private string $base;

    private bool $symlinkCreated = false;

    protected function setUp(): void
    {
        parent::setUp();

        unset($GLOBALS['extralaravel_executed']);

        $this->base = sys_get_temp_dir() . '/extralaravel_' . bin2hex(random_bytes(4));

        mkdir($this->base . '/app/data/times', 0777, true);
        mkdir($this->base . '/bootstrap', 0777, true);

        file_put_contents($this->base . '/app/data/times/iran.php', "<?php return ['tehran' => ['tz' => 'Asia/Tehran']];");
        file_put_contents($this->base . '/app/data/flat.php', "<?php return ['a' => ['b' => 'deep']];");
        file_put_contents($this->base . '/app/secret.php', "<?php \$GLOBALS['extralaravel_executed'] = 'secret'; return ['k' => 'v'];");
        file_put_contents($this->base . '/bootstrap/app.php', "<?php \$GLOBALS['extralaravel_executed'] = 'bootstrap'; return ['k' => 'v'];");

        // Creating symlinks needs elevated rights on Windows, so the related test skips itself when this fails.
        $this->symlinkCreated = @symlink($this->base . '/app/secret.php', $this->base . '/app/data/link.php');
    }

    protected function tearDown(): void
    {
        $this->deleteDirectory($this->base);

        parent::tearDown();
    }

    /**
     * Portable replacement for "rm -rf" (the shell command does not exist on Windows).
     */
    private function deleteDirectory(string $directory): void
    {
        if (!is_dir($directory)) {
            return;
        }

        $items = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($directory, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST,
        );

        foreach ($items as $item) {
            // Links are removed, never followed, so a link to a file outside the sandbox stays untouched.
            if ($item->isLink() || $item->isFile()) {
                @unlink($item->getPathname()) || @rmdir($item->getPathname());
            } else {
                rmdir($item->getPathname());
            }
        }

        rmdir($directory);
    }

    public function test_reads_values_through_nested_files_and_array_keys(): void
    {
        $data = new StaticData($this->base, 'app/data');

        $this->assertSame('Asia/Tehran', $data->get('app.data.times.iran.tehran.tz'));
        $this->assertSame('deep', $data->get('app.data.flat.a.b'));
        $this->assertSame('deep', $data->get('flat.a.b', null, 'app.data'));
        $this->assertSame('fallback', $data->get('app.data.flat.missing', 'fallback'));
    }

    public function test_files_outside_the_data_directory_are_never_executed(): void
    {
        $data = new StaticData($this->base, 'app/data');

        $this->assertSame('blocked', $data->get('app.secret', 'blocked'));
        $this->assertSame('blocked', $data->get('bootstrap.app', 'blocked'));
        $this->assertArrayNotHasKey('extralaravel_executed', $GLOBALS);
    }

    public function test_symlinks_escaping_the_data_directory_are_rejected(): void
    {
        if (!$this->symlinkCreated) {
            $this->markTestSkipped('Symlinks cannot be created in this environment.');
        }

        $data = new StaticData($this->base, 'app/data');

        $this->assertSame('blocked', $data->get('app.data.link.k', 'blocked'));
        $this->assertArrayNotHasKey('extralaravel_executed', $GLOBALS);
    }

    public function test_traversal_attempts_and_unsafe_roots_return_the_default(): void
    {
        $this->assertNull((new StaticData($this->base, 'app/data'))->get('app.data..flat'));
        $this->assertNull((new StaticData($this->base, 'app/data'))->get('app/data/flat'));
        $this->assertSame('d', (new StaticData($this->base, '../x'))->get('x.y', 'd'));
    }
}
