<?php

namespace Teksite\Extralaravel\Blades;

use Illuminate\Support\Facades\Blade;
use Illuminate\View\Compilers\BladeCompiler;
use InvalidArgumentException;

/**
 * Registers @recursive($items, $item) ... @endrecursive.
 *
 * The directive runs its body once for every leaf (non-iterable) value found anywhere inside the
 * nested iterable. It compiles to a plain foreach over NestedLeaves, so the body shares the
 * template's scope and needs no extra view.
 */
class RecursiveDirectives
{

    private const string USAGE = 'The @recursive directive expects: @recursive($collection, $item)';

    public static function register(BladeCompiler $blade): void
    {
        $blade->directive('recursive', static fn(string $expression): string => self::compileOpening($expression));

        $blade->directive('endrecursive', static fn(): string => '<?php endforeach; ?>');
    }

    /**
     * Kept for backward compatibility with code that called the original static entry point.
     *
     * @deprecated Use register() with the Blade compiler instead.
     */
    public static function recursive(): void
    {
        self::register(Blade::getFacadeRoot());
    }


    private static function compileOpening(string $expression): string
    {
        [$collection, $variable] = self::parse($expression);

        return sprintf(
            '<?php foreach (\\Teksite\\Extralaravel\\Support\\NestedLeaves::of(%s) as $%s): ?>',
            $collection,
            $variable,
        );
    }

    /**
     * @return array{0: string, 1: string} The collection expression and the loop variable name without "$".
     */
    private static function parse(string $expression): array
    {
        $expression = trim($expression);

        if (str_starts_with($expression, '(') && str_ends_with($expression, ')')) $expression = substr($expression, 1, -1);


        // The loop variable is a bare name, so the last comma always separates it from the collection
        // expression, even when that expression itself contains commas (e.g. array_merge($a, $b)).
        $comma = strrpos($expression, ',');

        if ($comma === false)  throw new InvalidArgumentException(self::USAGE);

        $collection = trim(substr($expression, 0, $comma));

        $variable = ltrim(trim(substr($expression, $comma + 1)), '$');

        if ($collection === '' || preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $variable) !== 1) throw new InvalidArgumentException(self::USAGE);

        return [$collection, $variable];
    }
}
