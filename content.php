<?php
    $_routeFile = __DIR__ . $app->Route->getRoute($_SERVER['REQUEST_URI']) . '.php';
    include (file_exists($_routeFile)) ? $_routeFile : __DIR__ . '/views/view_404.php';
?>
