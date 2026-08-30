<?php

namespace Teksite\Extralaravel\Blades;

use Illuminate\Support\Facades\Blade;

class RecursiveDirectives
{

    public static function recursive(): void
    {
        Blade::directive('recursive', function (string $expression): string {
            [$collection, $item] = array_map('trim', explode(',', trim($expression, '()'), 2));
            if ($collection === '' || $item === '') throw new \InvalidArgumentException('The @recursive directive expects: @recursive($collection, $item)');

            return <<<PHP
<?php
    \$__recursiveData = {$collection};

    if (is_iterable(\$__recursiveData)):
        foreach (\$__recursiveData as \${$item}):
            if (is_iterable(\${$item})):
                echo \$this->getContainer()
                    ->make('view')
                    ->make('recursive', [
                        '{$item}' => \${$item},
                    ])
                    ->render();
            else:
?>
PHP;
        });

        Blade::directive('endrecursive', function (): string {
            return <<<'PHP'
<?php
            endif;
        endforeach;
    endif;
?>
PHP;
        });
    }
}
