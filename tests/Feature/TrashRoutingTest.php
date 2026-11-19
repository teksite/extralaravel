<?php

namespace Teksite\Extralaravel\Tests\Feature;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Illuminate\Routing\Router;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Teksite\Extralaravel\Middleware\TrashOnlyBindingMiddleware;
use Teksite\Extralaravel\Tests\TestCase;

final class TrashRoutingTest extends TestCase
{
    /**
     * @param Router $router
     */
    protected function defineRoutes($router): void
    {
        $router->trashResource('users', TrashStubController::class);
        $router->trashResource('users.posts', TrashStubController::class);
    }

    private function route(string $name): Route
    {
        $route = app('router')->getRoutes()->getByName($name);

        $this->assertNotNull($route, "Route [{$name}] is not registered.");

        return $route;
    }

    public function test_all_five_routes_are_registered_with_expected_uris_and_methods(): void
    {
        $this->assertSame('users/trash', $this->route('users.trash.index')->uri());
        $this->assertContains('GET', $this->route('users.trash.index')->methods());

        $this->assertSame('users/trash/{user}', $this->route('users.trash.reinstate')->uri());
        $this->assertContains('PATCH', $this->route('users.trash.reinstate')->methods());

        $this->assertSame('users/trash/{user}', $this->route('users.trash.prune')->uri());
        $this->assertContains('DELETE', $this->route('users.trash.prune')->methods());

        $this->assertSame('users/trash', $this->route('users.trash.restore')->uri());
        $this->assertContains('PATCH', $this->route('users.trash.restore')->methods());

        $this->assertSame('users/trash', $this->route('users.trash.flush')->uri());
        $this->assertContains('DELETE', $this->route('users.trash.flush')->methods());
    }

    public function test_only_single_record_routes_load_trashed_models_and_get_the_guard_middleware(): void
    {
        foreach (['reinstate', 'prune'] as $action) {
            $route = $this->route("users.trash.{$action}");

            $this->assertTrue($route->allowsTrashedBindings());
            $this->assertContains(TrashOnlyBindingMiddleware::class . ':user', $route->gatherMiddleware());
        }

        foreach (['index', 'restore', 'flush'] as $action) {
            $route = $this->route("users.trash.{$action}");

            $this->assertFalse($route->allowsTrashedBindings());
            $this->assertNotContains(TrashOnlyBindingMiddleware::class . ':user', $route->gatherMiddleware());
        }
    }

    public function test_nested_resources_name_the_guard_after_the_last_segment(): void
    {
        $route = $this->route('users.posts.trash.reinstate');

        $this->assertSame('users/{user}/posts/trash/{post}', $route->uri());
        $this->assertContains(TrashOnlyBindingMiddleware::class . ':post', $route->gatherMiddleware());
        $this->assertFalse($this->route('users.posts.trash.index')->allowsTrashedBindings());
    }

    public function test_every_trash_action_has_a_route_definition(): void
    {
        $this->assertEqualsCanonicalizing(
            array_map(static fn (\Teksite\Extralaravel\Routing\Trash\TrashAction $action): string => $action->value, \Teksite\Extralaravel\Routing\Trash\TrashAction::cases()),
            array_keys(\Teksite\Extralaravel\Routing\Trash\TrashResourceDefinition::actions()),
        );
    }

    public function test_the_guard_accepts_a_trashed_target_with_an_active_parent(): void
    {
        $response = $this->runGuard(['user' => $this->active(), 'post' => $this->trashed()], 'post');

        $this->assertSame('passed', $response);
    }

    public function test_the_guard_rejects_an_active_target(): void
    {
        $this->expectException(NotFoundHttpException::class);

        $this->runGuard(['user' => $this->active(), 'post' => $this->active()], 'post');
    }

    public function test_the_guard_rejects_a_trashed_parent(): void
    {
        $this->expectException(NotFoundHttpException::class);

        $this->runGuard(['user' => $this->trashed(), 'post' => $this->trashed()], 'post');
    }

    public function test_the_guard_defaults_to_the_last_parameter_as_target(): void
    {
        $this->assertSame('passed', $this->runGuard(['user' => $this->active(), 'post' => $this->trashed()], null));
    }

    public function test_the_guard_ignores_unresolved_scalar_parameters(): void
    {
        $this->assertSame('passed', $this->runGuard(['user' => '5'], 'user'));
    }

    /**
     * @param array<string, mixed> $parameters
     */
    private function runGuard(array $parameters, ?string $target): string
    {
        $request = Request::create('/anything', 'PATCH');
        $route = new Route('PATCH', '/anything', static fn (): string => 'ok');
        $route->bind($request);

        foreach ($parameters as $name => $value) {
            $route->setParameter($name, $value);
        }

        $request->setRouteResolver(static fn (): Route => $route);

        $response = (new TrashOnlyBindingMiddleware())->handle(
            $request,
            static fn (): \Symfony\Component\HttpFoundation\Response => new \Illuminate\Http\Response('passed'),
            $target,
        );

        return (string) $response->getContent();
    }

    private function active(): Model
    {
        return new GuardedModel();
    }

    private function trashed(): Model
    {
        $model = new GuardedModel();
        $model->deleted_at = now();

        return $model;
    }
}

class GuardedModel extends Model
{
    use SoftDeletes;

    protected $guarded = [];
}

class TrashStubController
{
    public function index(): void
    {
    }

    public function reinstate(int|string $id): void
    {
    }

    public function prune(int|string $id): void
    {
    }

    public function restore(): void
    {
    }

    public function flush(): void
    {
    }
}
