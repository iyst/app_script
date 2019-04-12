<?php
define('APP_PATH', dirname(dirname(__FILE__)."/"));
define('LOG_PATH',APP_PATH.'/log');
define('MUSE_PATH',dirname(__FILE__).DIRECTORY_SEPARATOR.'Muse'.DIRECTORY_SEPARATOR);

require_once APP_PATH.'/vendor/autoload.php';
$env = new \Dotenv\Dotenv(APP_PATH);
$env->load();

require_once MUSE_PATH.'Common/function.php';
require_once MUSE_PATH.'Common/constants.php';

$conf = require_once MUSE_PATH.'Conf/config.php';


define('WORK_PATH',env('WORK_PATH'));
function classLoader($class)
{
    $path = str_replace('\\', DIRECTORY_SEPARATOR, $class);
    $file = __DIR__ . DIRECTORY_SEPARATOR . $path . '.php';

    if (file_exists($file)) {
        require_once $file;
    }
}
spl_autoload_register('classLoader');