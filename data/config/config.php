<?php
ini_set('memory_limit', '200M');

define('DOC_ROOT', isset($_SERVER['DOCUMENT_ROOT']) ? $_SERVER['DOCUMENT_ROOT']:$HTTP_SERVER_VARS['DOCUMENT_ROOT']);
$base_path = str_replace(defined('ADMIN')?ADMIN:'', '', str_replace(DOC_ROOT, '', FULL_PATH));
define('BASE_PATH', $base_path);

$adodb_ver = 'adodb';
$server = isset($_SERVER['SERVER_NAME'])?$_SERVER['SERVER_NAME']:'';
define('SERVER_NAME', $server);
define('MODULES_PATH', DOC_ROOT.BASE_PATH.'/modules', true);
define('PROTOCOL', 'http://');
define('APP_PATH', DOC_ROOT.BASE_PATH.'/app');

define('PHP_SELF',str_replace(BASE_PATH, '', $_SERVER['PHP_SELF']));

define('TEMP_PATH',DOC_ROOT.BASE_PATH.'/tmp');

if( file_exists(DOC_ROOT.BASE_PATH.'/debug.php') ) // allows to specify different debug mode for each virtual host
	include_once DOC_ROOT.BASE_PATH.'/debug.php';

include_once(DOC_ROOT.BASE_PATH."/$adodb_ver/adodb.inc.php");
include_once(MODULES_PATH.'/base.php');
include_once(APP_PATH.'/session.php');
include_once(MODULES_PATH.'/application.php');

define('SQL_TYPE', 'mysql');
define('SQL_SERVER', '127.0.0.1');
define('SQL_USER', 'dev');
define('SQL_PASS', 'dev-25-sql');
define('SQL_DB', 'journal');
define('MYSQL_CHARSET', 'utf8');

define('MAIL_HOST', 'smtp.gmail.com');
define('MAIL_PORT', 25);
define('MAIL_AUTH', false);
define('MAIL_USER', '');
define('MAIL_PASS', '');

define('USER_MODEL', 'User');
define('USERS_LIST','/users/users_list.php');

define('SESSION_TIMEOUT', 3600);
$show_date_format = "m/d/Y";

include_once(MODULES_PATH.'/common.php');

?>