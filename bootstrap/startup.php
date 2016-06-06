<?php

// Error Reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check Version
if (version_compare(phpversion(), '5.4.0', '<') == true) {
    exit('PHP5.4+ Required');
}

// TODO:
// TODO: REGISTRAR SERVICE PROVIDERS PARA CONSTRUIR LAS INSTANCIAS DEL REGISTRY.
// TODO:

define('CACHE_EXPIRE', 3600);

define('DIR_LOGS',     __DIR__.'/../storage/logs/');

define('DIR_APPLICATION', dirname(__DIR__).'/'.APP.'/');
define('DIR_LANGUAGE',    DIR_APPLICATION.'language/');
define('DIR_TEMPLATE',    DIR_APPLICATION.'view/theme/');

// Configuration
if (is_file(dirname(__DIR__).'/config.php') and APP !== 'install') {
    require_once dirname(__DIR__).'/config.php';
}

if (!defined('HTTP_DOMAIN')) {
    // Run installation
    if (APP === 'install') {
        define('HTTP_DOMAIN', $_SERVER['HTTP_HOST']);
    } else {
        header('Location: install/index.php');
        exit();
    }
}

if (!defined('HTTP_ROOT')) {
    define('HTTP_ROOT', rtrim(rtrim(dirname($_SERVER['SCRIPT_NAME']), 'install'), '/.\\').'/');
}

define('HTTP_CATALOG',   'http://'.HTTP_DOMAIN.HTTP_ROOT);
define('HTTPS_CATALOG', 'https://'.HTTP_DOMAIN.HTTP_ROOT);
define('DIR_CATALOG',     dirname(__DIR__).'/catalog/');

// TODO: Move this somewhere else…

if (!ini_get('date.timezone')) {
    date_default_timezone_set('UTC');
}

/*
|--------------------------------------------------------------------------
| Register The Auto Loader
|--------------------------------------------------------------------------
|
| Composer provides a convenient, automatically generated class loader for
| our application. We just need to utilize it! We'll simply require it
| into the script here so that we don't have to worry about manual
| loading any of our classes later on. It feels nice to relax.
|
*/

require __DIR__.'/../bootstrap/autoload.php';

/*
|--------------------------------------------------------------------------
| Turn On The Lights
|--------------------------------------------------------------------------
|
| We need to illuminate PHP development, so let us turn on the lights.
| This bootstraps the framework and gets it ready for use, then it
| will load up this application so that we can run it and send
| the responses back to the browser and delight our users.
|
| I wanted to change this message, but Taylor Otwell couldn't say it better.
|
*/

$app = require_once __DIR__.'/../bootstrap/app.php';

// TODO: move this into the app constructor?
$app->set('log', new \Carrito\Framework\Library\Log($app->get('config')->get('config_error_filename')));

/*
|--------------------------------------------------------------------------
| Run The Application
|--------------------------------------------------------------------------
|
| Once we have the application, we can handle the incoming request
| through the front controller, and send the associated response back to
| the client's browser allowing them to enjoy the creative
| and wonderful application we have prepared for them.
|
| I wanted to change this message, but Taylor Otwell couldn't say it better.
|
*/

// Handle incoming request.
$app->get('front')->dispatch();

// Send the response.
$app->get('response')->output();
