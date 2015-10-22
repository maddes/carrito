<?php

// Error Reporting
error_reporting(E_ALL);

// Check Version
if (version_compare(phpversion(), '5.4.0', '<') == true) {
    exit('PHP5.4+ Required');
}

// Configuration
if (is_file(dirname(__DIR__).'/config.php') and APP !== 'install') {
    require_once dirname(__DIR__).'/config.php';
}

// Install
if (defined('HTTP_DOMAIN')) {
    // Version
    define('VERSION', '4.0.0');

    define('DIR_BASE', dirname(__DIR__).'/');

    define('DIR_APPLICATION', DIR_BASE.APP.'/');
    define('DIR_LANGUAGE',    DIR_BASE.APP.'/language/');
    define('DIR_TEMPLATE',    DIR_BASE.APP.'/view/theme/');

    define('DIR_IMAGE',    DIR_BASE.'image/');
    define('DIR_SYSTEM',   __DIR__.'/');
    define('DIR_CONFIG',   __DIR__.'/config/');
    define('DIR_CACHE',    __DIR__.'/storage/cache/');
    define('DIR_DOWNLOAD', __DIR__.'/storage/download/');
    define('DIR_LOGS',     __DIR__.'/storage/logs/');
    define('DIR_UPLOAD',   __DIR__.'/storage/upload/');

    if (APP === 'admin') {
        define('HTTP_SERVER',    'http://'.HTTP_DOMAIN.HTTP_ROOT.'admin/');
        define('HTTPS_SERVER',  'https://'.HTTP_DOMAIN.HTTP_ROOT.'admin/');
        define('HTTP_CATALOG',   'http://'.HTTP_DOMAIN.HTTP_ROOT);
        define('HTTPS_CATALOG', 'https://'.HTTP_DOMAIN.HTTP_ROOT);
        define('DIR_CATALOG',     DIR_BASE.'catalog/');
    } else {
        define('HTTP_SERVER',   'http://'.HTTP_DOMAIN.HTTP_ROOT);
        define('HTTPS_SERVER', 'https://'.HTTP_DOMAIN.HTTP_ROOT);
    }
} elseif (APP !== 'install') {
    if (APP === 'catalog') {
        header('Location: install/index.php');
    } else {
        header('Location: ../install/index.php');
    }
    exit;
} else {
    // HTTP
    $protocol = (!empty($_SERVER['HTTPS']) and $_SERVER['HTTPS'] !== 'off' or $_SERVER['SERVER_PORT'] == 443) ? 'https://' : 'http://';
    define('HTTP_SERVER',   $protocol.$_SERVER['HTTP_HOST'].rtrim(dirname($_SERVER['SCRIPT_NAME']), '/.\\').'/');
    define('HTTP_OPENCART', $protocol.$_SERVER['HTTP_HOST'].rtrim(rtrim(dirname($_SERVER['SCRIPT_NAME']), 'install'), '/.\\').'/');

    // DIR
    define('DIR_SYSTEM',      str_replace('\\', '/', realpath(__DIR__)).'/');
    define('DIR_OPENCART',    dirname(DIR_SYSTEM).'/');
    define('DIR_CONFIG',      DIR_SYSTEM.'config/');
    define('DIR_APPLICATION', DIR_OPENCART.'install/');
    define('DIR_LANGUAGE',    DIR_APPLICATION.'language/');
    define('DIR_TEMPLATE',    DIR_APPLICATION.'view/theme/');
}

define('CACHE_EXPIRE', 3600);

if (!ini_get('date.timezone')) {
    date_default_timezone_set('UTC');
}

/**
 * Autoloader for base system.
 *
 * This function is registered with the spl_autoload_register and searches
 * the corresponding file for the base classes.
 *
 * @param string $class The class name to load
 *
 * @return bool Whether the class file has been loaded
 */
function autoload($class)
{
    // Search class in folders engine, library and vendor
    foreach (['engine/', 'library/', 'vendor/'] as $folder) {
        // Replace namespace separators with folder separators
        $file = DIR_SYSTEM.$folder.str_replace('\\', DIRECTORY_SEPARATOR, strtolower($class)).'.php';

        // Only try loading if the file exists
        if (is_file($file)) {
            include_once $file;

            return true;
        }
    }

    return false;
}

spl_autoload_register('autoload');
spl_autoload_extensions('.php');

// Helper
require_once DIR_SYSTEM.'helper/general.php';
require_once DIR_SYSTEM.'helper/json.php';
require_once DIR_SYSTEM.'helper/utf8.php';

// Registry
$registry = new Registry();

if (APP === 'install') {
    // Upgrade
    $upgrade = false;

    if (file_exists(DIR_OPENCART.'/config.php')) {
        if (filesize(DIR_OPENCART.'/config.php') > 0) {
            $upgrade = true;

            $lines = file(DIR_OPENCART.'config.php');

            foreach ($lines as $line) {
                if (strpos(strtoupper($line), 'DB_') !== false) {
                    eval($line);
                }
            }
        }
    }
} else {
    // Log
    $registry->set('log', new Log($registry->get('config')->get('config_error_filename')));
}

// Front Controller
$registry->get('front')->dispatch();

// Output
$registry->get('response')->output();
