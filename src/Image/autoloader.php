<?php

spl_autoload_register(function ($class) {
    if (strpos($class, 'merik\\Image\\')!== 0) {
        return;
    }

    $file = __DIR__.str_replace('\\', DIRECTORY_SEPARATOR, substr($class, strlen('merik\\Image\\'))).'.php';

    if (is_file($file)) {
        include_once $file;
    }

});
