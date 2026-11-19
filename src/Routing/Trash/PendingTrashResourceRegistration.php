<?php

namespace Teksite\Extralaravel\Routing\Trash;

final class PendingTrashResourceRegistration
{
    private bool $registered = false;

    public function __construct(
        private readonly TrashResourceRegistrar $registrar,
        private readonly string $name,
        private readonly string $controller,
        private array $options = [],
    ) {}

    public function only(array|string $actions): self
    {
        $this->options['only'] = is_array($actions) ? $actions : func_get_args();
        return $this;
    }

    public function except(array|string $actions): self
    {
        $this->options['except'] = is_array($actions) ? $actions : func_get_args();
        return $this;
    }

    public function where(array $wheres): self
    {
        $this->options['where'] = $wheres;
        return $this;
    }

    public function parameters(array $parameters): self
    {
        $this->options['parameters'] = $parameters;
        return $this;
    }

    public function names(array $names): self
    {
        $this->options['names'] = $names;
        return $this;
    }

    public function middleware(array|string $middleware): self
    {
        $this->options['middleware'] = is_array($middleware) ? $middleware : func_get_args();
        return $this;
    }

    public function as(string $name): self
    {
        $this->options['as'] = $name;
        return $this;
    }

    public function scoped(array $scoped = []): self
    {
        $this->options['scoped'] = $scoped;
        $this->options['scopeBindings'] = true;
        return $this;
    }

    public function scopeBindings(bool $value = true): self
    {
        $this->options['scopeBindings'] = $value;
        return $this;
    }

    public function withoutScopedBindings(): self
    {
        return $this->scopeBindings(false);
    }

    public function shallow(bool $value = true): self
    {
        $this->options['shallow'] = $value;
        return $this;
    }

    public function bindingFields(array $fields): self
    {
        $this->options['bindingFields'] = $fields;
        return $this;
    }

    public function prefix(string $prefix): self
    {
        $this->options['prefix'] = $prefix;
        return $this;
    }

    public function register(): void
    {
        if ($this->registered) return;

        $this->registered = true;
        $this->registrar->register($this->name, $this->controller, TrashResourceOptions::from($this->options));
    }

    public function __destruct()
    {
        $this->register();
    }
}
