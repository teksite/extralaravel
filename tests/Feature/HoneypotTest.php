<?php

namespace Teksite\Extralaravel\Tests\Feature;

use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Blade;
use Teksite\Extralaravel\Tests\TestCase;

final class HoneypotTest extends TestCase
{
    /**
     * @param Router $router
     */
    protected function defineRoutes($router): void
    {
        $router->post('/honeypot', static fn (): string => 'ok')->middleware('honeypot');
    }

    public function test_requests_without_a_honeypot_value_pass(): void
    {
        $this->post('/honeypot', ['name' => 'Sina'])->assertOk();
        $this->post('/honeypot', ['honeypot' => ''])->assertOk();
    }

    public function test_requests_filling_the_honeypot_are_rejected(): void
    {
        $this->post('/honeypot', ['honeypot' => 'spam'])->assertForbidden();
    }

    public function test_the_configured_field_name_is_used(): void
    {
        config(['extralaravel.honeypot.field_name' => 'website']);

        $this->post('/honeypot', ['honeypot' => 'spam'])->assertOk();
        $this->post('/honeypot', ['website' => 'spam'])->assertForbidden();
    }

    public function test_the_status_code_is_configurable_and_invalid_values_fall_back_to_403(): void
    {
        config(['extralaravel.honeypot.status' => 422]);
        $this->post('/honeypot', ['honeypot' => 'spam'])->assertStatus(422);

        config(['extralaravel.honeypot.status' => 99]);
        $this->post('/honeypot', ['honeypot' => 'spam'])->assertForbidden();
    }

    public function test_the_middleware_can_be_disabled(): void
    {
        config(['extralaravel.honeypot.enabled' => false]);

        $this->post('/honeypot', ['honeypot' => 'spam'])->assertOk();
    }

    public function test_the_blade_directive_renders_the_hidden_field_with_the_configured_name(): void
    {
        config(['extralaravel.honeypot.field_name' => 'website']);

        $html = Blade::render('@honeypot');

        $this->assertStringContainsString('name="website"', $html);
        $this->assertStringContainsString('tabindex="-1"', $html);
        $this->assertStringContainsString('aria-hidden="true"', $html);
    }
}
