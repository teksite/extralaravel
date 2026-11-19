<?php

namespace Teksite\Extralaravel\Support;

/**
 * Exports PHP values as short-array-syntax source code.
 *
 * The output is generated structurally instead of rewriting var_export() output with
 * regular expressions, which corrupted string values that happened to contain text
 * such as "array (".
 */
final class ArrayExporter
{
    private const string INDENT = '    ';

    public static function export(mixed $value, int $level = 0): string
    {
        if (!is_array($value)) return self::exportScalar($value);

        if ($value === [])  return '[]';

        $isList = array_is_list($value);
        $padding = str_repeat(self::INDENT, $level + 1);
        $lines = [];

        foreach ($value as $key => $item) {
            $prefix = $isList ? '' : var_export($key, true) . ' => ';
            $lines[] = $padding . $prefix . self::export($item, $level + 1) . ',';
        }

        return "[\n" . implode("\n", $lines) . "\n" . str_repeat(self::INDENT, $level) . ']';
    }

    private static function exportScalar(mixed $value): string
    {
        return match (true) {
            $value === null => 'null',
            is_bool($value) => $value ? 'true' : 'false',
            default => var_export($value, true),
        };
    }

}
