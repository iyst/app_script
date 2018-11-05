<?php
require_once 'src/autoload.php';

\Muse\Tool\DbHelper::getInstance();

var_dump(config('db.host'));