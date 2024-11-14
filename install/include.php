<?php
// Подключение автозагрузчика
spl_autoload_register(function ($class) {
    $prefix = 'Production\\Line\\';
    $base_dir = $_SERVER["DOCUMENT_ROOT"] . '/local/modules/production.line/lib/';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';


    if (file_exists($file)) {
        require $file;
    }
});
