<?php

namespace Teksite\Extralaravel\Traits;

trait StudyPathNamespace
{
    function normalizePath(string $path): string
    {
        // Replace all "/" and "\" with DIRECTORY_SEPARATOR
        $normalizedPath = str_replace(['/', '\\' ,'/\\' ,'\\/'], DIRECTORY_SEPARATOR, $path);

        // Ensure the path ends with DIRECTORY_SEPARATOR
        return rtrim($normalizedPath, DIRECTORY_SEPARATOR);
    }
    function normalizeNamespace(string $path): string
    {
        // Replace all "/" and "\" with DIRECTORY_SEPARATOR
        $normalizedPath = str_replace(['/', '\\' ,'/\\' ,'\\/' ,'\\\\' , DIRECTORY_SEPARATOR], '\\', $path);

        // Ensure the path ends with DIRECTORY_SEPARATOR
        return rtrim($normalizedPath, DIRECTORY_SEPARATOR) ;
    }
}
