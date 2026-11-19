<?php

declare(strict_types=1);

namespace Teksite\Extralaravel\Support;

use Illuminate\Support\HtmlString;

final class Honeypot
{
    private const string DEFAULT_FIELD = 'honeypot';

    /**
     * Resolve the configured honeypot field name.
     */
    public static function fieldName(): string
    {
        $name = config('extralaravel.honeypot.field_name', self::DEFAULT_FIELD);

        return is_string($name) && $name !== '' ? $name : self::DEFAULT_FIELD;
    }

    /**
     * Render the hidden honeypot input.
     *
     * The field is moved off-screen instead of using display:none because simple bots skip
     * fields that are explicitly hidden. aria-hidden and tabindex keep it away from real users.
     */
    public static function field(?string $name = null): HtmlString
    {
        $name ??= self::fieldName();

        return new HtmlString(sprintf(
            '<div style="position:absolute;left:-10000px;top:auto;width:1px;height:1px;overflow:hidden" aria-hidden="true">'
            .'<input type="text" name="%s" value="" tabindex="-1" autocomplete="off">'
            .'</div>',
            e($name),
        ));
    }

}
