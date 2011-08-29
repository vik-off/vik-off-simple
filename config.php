<?

if(!defined('WWW_ROOT'))
	die('access denided (config file)');


// установить уровень сообщений об ошибках (максимальный)
error_reporting(E_ALL | E_STRICT);
ini_set('display_errors', 1);

// установить текущий часовой пояс
date_default_timezone_set('Europe/Kiev');

// отсеивать дублируемые формы
define('CHECK_FORM_DUPLICATION', 0);

// название сайта
define('CFG_SITE_NAME', 'site-utilit');

// конфигурация подключения к БД
db::create(array(
	'adapter' => 'mysql',
	'host' => 'localhost',
	'user' => 'root',
	'pass' => '0000',
	'database' => 'mysql',
	'encoding' => 'utf8',
	'fileLog' => FALSE,
));
	
	
?>