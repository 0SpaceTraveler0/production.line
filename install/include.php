<?php
// Подключение автозагрузчика
spl_autoload_register(function ($class) {
    $prefix = 'Production\\Line\\';
    $base_dir = $_SERVER["DOCUMENT_ROOT"] . '/local/modules/production.line/lib/';

    // Проверяем, что класс принадлежит нашему пространству имен
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    // Определяем путь к файлу
    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

    // Подключаем файл, если он существует
    if (file_exists($file)) {
        require $file;
    }
});
