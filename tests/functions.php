<?php

function fixPath($path)
{
    return str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path);
}

function cleanDirectory(
    string $directory,
    array $ignoreFiles = ['.gitkeep', '.gitignore']
) {
    $pattern = rtrim($directory, DS) . DS . '*.{jpg,png,gif}';
    $files = glob($pattern, GLOB_NOSORT | GLOB_BRACE);

    if ($files) {
        foreach ($files as $file) {
            if (in_array(basename($file), $ignoreFiles, true)) {
                continue;
            }

            if (is_writable($file)) {
                unlink($file);
            }
        }
    }
}
