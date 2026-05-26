<?php

$publicPath = __DIR__.'/public';
$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '');
$file = $publicPath.$uri;

if ($uri !== '/' && is_file($file)) {
    return false;
}

require_once $publicPath.'/index.php';
