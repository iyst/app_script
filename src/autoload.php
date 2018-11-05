<?php
define('APP_PATH', dirname(dirname(__FILE__)."/"));
require_once APP_PATH.'/vendor/autoload.php';
$env = new \Dotenv\Dotenv(APP_PATH);
$env->load();
require_once 'Muse/Common/function.php';
function classLoader($class)
{
    $path = str_replace('\\', DIRECTORY_SEPARATOR, $class);
    $file = __DIR__ . DIRECTORY_SEPARATOR . $path . '.php';
    echo $file;
    if (file_exists($file)) {
        require_once $file;
    }
}
spl_autoload_register('classLoader');