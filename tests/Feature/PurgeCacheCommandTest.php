<?php

namespace Teksite\Extralaravel\Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Teksite\Extralaravel\Tests\TestCase;

final class PurgeCacheCommandTest extends TestCase
{
    private string $fileCachePath;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'cache.stores.database' => [
                'driver'     => 'database',
                'connection' => 'testing',
                'table'      => 'cache',
                'lock_table' => 'cache_locks',
            ],
        ]);

        Schema::create('cache', static function (Blueprint $table): void {
            $table->string('key')->primary();
            $table->mediumText('value');
            $table->integer('expiration');
        });

        Schema::create('cache_locks', static function (Blueprint $table): void {
            $table->string('key')->primary();
            $table->string('owner');
            $table->integer('expiration');
        });

        $this->fileCachePath = sys_get_temp_dir() . '/extralaravel_cache_' . bin2hex(random_bytes(4));
        config(['cache.stores.file.path' => $this->fileCachePath]);
    }

    protected function tearDown(): void
    {
        File::deleteDirectory($this->fileCachePath);

        parent::tearDown();
    }

    private function seedDatabase(): void
    {
        DB::table('cache')->insert([
            ['key' => 'old-1', 'value' => 'x', 'expiration' => time() - 100],
            ['key' => 'old-2', 'value' => 'x', 'expiration' => time() - 10],
            ['key' => 'fresh', 'value' => 'x', 'expiration' => time() + 3600],
        ]);

        DB::table('cache_locks')->insert([
            ['key' => 'lock-old', 'owner' => 'a', 'expiration' => time() - 10],
            ['key' => 'lock-fresh', 'owner' => 'b', 'expiration' => time() + 3600],
        ]);
    }

    public function test_expired_database_cache_items_and_locks_are_deleted(): void
    {
        $this->seedDatabase();

        $this->artisan('cache:purge', ['--force' => true, '--skip-files' => true, '--skip-tokens' => true])->assertSuccessful();

        $this->assertSame(['fresh'], DB::table('cache')->pluck('key')->all());
        $this->assertSame(['lock-fresh'], DB::table('cache_locks')->pluck('key')->all());
    }

    public function test_deletion_works_in_small_batches(): void
    {
        $this->seedDatabase();

        $this->artisan('cache:purge', ['--force' => true, '--limit' => 1, '--skip-files' => true, '--skip-tokens' => true])->assertSuccessful();

        $this->assertSame(1, DB::table('cache')->count());
    }

    public function test_dry_run_deletes_nothing_and_needs_no_confirmation(): void
    {
        $this->seedDatabase();
        $this->app->detectEnvironment(fn () => 'production');

        $this->artisan('cache:purge', ['--dry-run' => true, '--skip-files' => true])->assertSuccessful();

        $this->assertSame(3, DB::table('cache')->count());
        $this->assertSame(2, DB::table('cache_locks')->count());
    }

    public function test_production_requires_force(): void
    {
        $this->seedDatabase();
        $this->app->detectEnvironment(fn () => 'production');

        // Prompts fall back to the console question helper in tests, so the question must be declared.
        $this->artisan('cache:purge', ['--skip-files' => true, '--skip-tokens' => true])
            ->expectsConfirmation('Are you sure you want to run this command?', 'no')
            ->assertFailed();

        $this->assertSame(3, DB::table('cache')->count());
    }

    public function test_expired_cache_files_are_deleted_and_empty_directories_removed(): void
    {
        File::ensureDirectoryExists($this->fileCachePath . '/ab/cd');
        File::ensureDirectoryExists($this->fileCachePath . '/ef/gh');

        File::put($this->fileCachePath . '/ab/cd/expired', (time() - 100) . 'value');
        File::put($this->fileCachePath . '/ef/gh/fresh', (time() + 3600) . 'value');

        $this->artisan('cache:purge', ['--force' => true, '--skip-database' => true, '--skip-tokens' => true])->assertSuccessful();

        $this->assertFileDoesNotExist($this->fileCachePath . '/ab/cd/expired');
        $this->assertDirectoryDoesNotExist($this->fileCachePath . '/ab');
        $this->assertFileExists($this->fileCachePath . '/ef/gh/fresh');
    }

    public function test_dry_run_keeps_expired_cache_files(): void
    {
        File::ensureDirectoryExists($this->fileCachePath . '/ab/cd');
        File::put($this->fileCachePath . '/ab/cd/expired', (time() - 100) . 'value');

        $this->artisan('cache:purge', ['--dry-run' => true, '--skip-database' => true, '--skip-tokens' => true])->assertSuccessful();

        $this->assertFileExists($this->fileCachePath . '/ab/cd/expired');
    }

    public function test_non_database_stores_are_skipped_safely(): void
    {
        $this->seedDatabase();

        $this->artisan('cache:purge', ['--force' => true, '--store' => 'array', '--skip-files' => true, '--skip-tokens' => true])->assertSuccessful();

        $this->assertSame(3, DB::table('cache')->count());
    }
}
