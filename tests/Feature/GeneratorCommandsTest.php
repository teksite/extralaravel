<?php

namespace Teksite\Extralaravel\Tests\Feature;

use Illuminate\Support\Facades\File;
use Teksite\Extralaravel\Tests\TestCase;

final class GeneratorCommandsTest extends TestCase
{
    protected function tearDown(): void
    {
        File::deleteDirectory(app_path('Logics'));
        File::deleteDirectory(app_path('Services'));
        File::deleteDirectory(app_path('Http/Requests/Generated'));
        File::delete(app_path('Http/Controllers/GeneratedTrashController.php'));
        File::delete(app_path('Http/Controllers/GeneratedTrashApiController.php'));
        File::deleteDirectory(base_path('stubs/extralaravel'));

        parent::tearDown();
    }

    public function test_logic_class_is_created_inside_the_app_directory(): void
    {
        $this->artisan('make:logic', ['name' => 'Generated/UserLogic'])->assertSuccessful();

        $path = app_path('Logics/Generated/UserLogic.php');

        $this->assertFileExists($path);
        $this->assertStringContainsString('namespace App\Logics\Generated;', File::get($path));
        $this->assertStringContainsString('class UserLogic', File::get($path));
    }

    public function test_service_class_is_created_and_can_be_called_without_errors(): void
    {
        $this->artisan('make:service', ['name' => 'Generated/BillingService'])->assertSuccessful();

        $path = app_path('Services/Generated/BillingService.php');

        $this->assertFileExists($path);
        $this->assertStringContainsString('namespace App\Services\Generated;', File::get($path));

        require_once $path;

        // An empty method with a non-void return type would throw a TypeError when called.
        $this->assertNull((new \App\Services\Generated\BillingService())->execute());
    }

    public function test_service_generator_supports_force(): void
    {
        $this->artisan('make:service', ['name' => 'Generated/KeepService'])->assertSuccessful();
        File::put(app_path('Services/Generated/KeepService.php'), '<?php // edited');

        $this->artisan('make:service', ['name' => 'Generated/KeepService', '--force' => true])->assertSuccessful();

        $this->assertStringContainsString('class KeepService', File::get(app_path('Services/Generated/KeepService.php')));
    }

    public function test_generators_never_create_a_wrong_cased_directory_in_the_project_root(): void
    {
        if (PHP_OS_FAMILY !== 'Linux') {
            $this->markTestSkipped('Only a case-sensitive file system can tell "App" and "app" apart.');
        }

        $this->artisan('make:logic', ['name' => 'CaseCheckLogic'])->assertSuccessful();

        $this->assertDirectoryDoesNotExist(base_path('App'));
    }

    public function test_api_request_extends_the_package_form_request(): void
    {
        $this->artisan('make:request-api', ['name' => 'Generated/StoreThingRequest'])->assertSuccessful();

        $contents = File::get(app_path('Http/Requests/Generated/StoreThingRequest.php'));

        $this->assertStringContainsString('namespace App\Http\Requests\Generated;', $contents);
        $this->assertStringContainsString('extends ApiFormRequest', $contents);
    }

    public function test_trash_controllers_use_the_application_root_namespace(): void
    {
        $this->artisan('make:controller-trash', ['name' => 'GeneratedTrashController'])->assertSuccessful();
        $this->artisan('make:controller-trash', ['name' => 'GeneratedTrashApiController', '--api' => true])->assertSuccessful();

        $web = File::get(app_path('Http/Controllers/GeneratedTrashController.php'));
        $api = File::get(app_path('Http/Controllers/GeneratedTrashApiController.php'));

        $this->assertStringContainsString('use App\Http\Controllers\Controller;', $web);
        $this->assertStringContainsString('RedirectResponse', $web);
        $this->assertStringContainsString('JsonResponse', $api);
    }

    public function test_existing_classes_are_not_overwritten_without_force(): void
    {
        $this->artisan('make:logic', ['name' => 'Generated/KeepLogic'])->assertSuccessful();
        File::put(app_path('Logics/Generated/KeepLogic.php'), '<?php // edited');

        $this->artisan('make:logic', ['name' => 'Generated/KeepLogic']);
        $this->assertSame('<?php // edited', File::get(app_path('Logics/Generated/KeepLogic.php')));

        $this->artisan('make:logic', ['name' => 'Generated/KeepLogic', '--force' => true])->assertSuccessful();
        $this->assertStringContainsString('class KeepLogic', File::get(app_path('Logics/Generated/KeepLogic.php')));
    }

    public function test_a_published_project_stub_overrides_the_package_stub(): void
    {
        File::ensureDirectoryExists(base_path('stubs/extralaravel'));
        File::put(
            base_path('stubs/extralaravel/logic-class.stub'),
            "<?php\n\nnamespace {{ namespace }};\n\nclass {{ class }}\n{\n    // custom stub\n}\n",
        );

        $this->artisan('make:logic', ['name' => 'Generated/CustomLogic'])->assertSuccessful();

        $this->assertStringContainsString('// custom stub', File::get(app_path('Logics/Generated/CustomLogic.php')));
    }
}
