<?php

declare(strict_types=1);

namespace Teksite\Extralaravel\Blades;

use Illuminate\View\Compilers\BladeCompiler;

/**
 * Registers @honeypot, which renders the hidden field checked by HoneypotMiddleware.
 */
final class HoneypotDirective
{
    public static function register(BladeCompiler $blade): void
    {
        $blade->directive('honeypot', static fn(): string => '<?php echo \\Teksite\\Extralaravel\\Support\\Honeypot::field(); ?>');
    }

    private function __construct() {}
}
