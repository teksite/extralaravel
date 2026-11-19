<?php

namespace Teksite\Extralaravel\Tests\Feature;

use Illuminate\Routing\Router;
use Teksite\Extralaravel\Middleware\HoneypotMiddleware;
use Teksite\Extralaravel\Support\StaticData;
use Teksite\Extralaravel\Tests\TestCase;

final class ServiceProviderTest extends TestCase
{
    public function test_package_defaults_are_merged(): void
    {
        $this->assertSame('honeypot', config('extralaravel.honeypot.field_name'));
        $this->assertSame('app/data', config('extralaravel.data_path'));
        $this->assertSame(8, config('extralaravel.password.min_length'));
        $this->assertContains('fa', config('extralaravel.rtl_locales'));
        $this->assertSame(['fa'], config('extralaravel.jalali_date_locales'));
    }

    public function test_route_macros_and_middleware_alias_are_registered(): void
    {
        $this->assertTrue(Router::hasMacro('trashResource'));
        $this->assertTrue(Router::hasMacro('trashResources'));
        $this->assertSame(HoneypotMiddleware::class, app('router')->getMiddleware()['honeypot']);
    }

    public function test_commands_are_registered_in_console(): void
    {
        $commands = array_keys(\Illuminate\Support\Facades\Artisan::all());

        foreach (['make:logic', 'make:service', 'make:request-api', 'make:controller-trash', 'cache:purge'] as $command) {
            $this->assertContains($command, $commands);
        }
    }

    public function test_static_data_is_scoped_and_uses_the_configured_path(): void
    {
        $this->assertInstanceOf(StaticData::class, app(StaticData::class));
        $this->assertSame(app(StaticData::class), app(StaticData::class));
    }

    public function test_rtl_detection_ignores_region_and_case(): void
    {
        $this->assertTrue(is_rtl('fa'));
        $this->assertTrue(is_rtl('fa_IR'));
        $this->assertTrue(is_rtl('AR-sa'));
        $this->assertFalse(is_rtl('en_US'));

        app()->setLocale('fa');
        $this->assertTrue(is_rtl());
    }

    public function test_get_static_data_helper_respects_the_boundary(): void
    {
        $this->assertSame('blocked', getStaticData('bootstrap.app', 'blocked'));
    }

    public function test_helper_functions_that_return_laravel_types(): void
    {
        $this->assertSame(['a', 'b', 'c'], exploding('a,b،c')->all());
        $this->assertNull(exploding(null));
        $this->assertSame('my-post', changeToSlug('MyPost')->toString());
        $this->assertSame("[\n    'a' => 1,\n]", var_export_short(['a' => 1], true));
    }
}
