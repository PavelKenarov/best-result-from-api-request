<?php
define('ROOT_DIR', realpath(dirname(__FILE__)) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR);
define('DIR_AVATAR', ROOT_DIR . 'assets' . DIRECTORY_SEPARATOR . 'users' . DIRECTORY_SEPARATOR);
define('DIR_CLASSES', ROOT_DIR . 'includes' . DIRECTORY_SEPARATOR);

spl_autoload_register(function ($class) {
	$file =  DIR_CLASSES . str_replace('\\', DIRECTORY_SEPARATOR, $class) . '.class.php';
	if (is_file($file)) { 
		require_once $file;
		return true;
	}
	return false;
});
?>