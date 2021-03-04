<?php
define('FULL_PATH', str_replace('\\','/',dirname(realpath(__FILE__))));
define('LOGIN_REQUIRED', 1);
define('DEFAULT_LANGUAGE_ID',1);
define('ADMIN', '');
if(!defined('ADD_QUESTION')) define('ADD_QUESTION', 1);

include_once(FULL_PATH.'/config/config.php');

$app = KernelApplication::Instance();
$app->Init();
$app->Run();
$app->Done();
?>