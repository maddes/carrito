<?php

define('CARRITO_START', microtime(true));

/*
|--------------------------------------------------------------------------
| Register The Composer Auto Loader
|--------------------------------------------------------------------------
|
| Composer provides a convenient, automatically generated class loader
| for our application. We just need to utilize it! We'll require it
| into the script here so that we do not have to worry about the
| loading of any our classes "manually". Feels great to relax.
|
*/

// TODO: Waiting to PSR-4 implementation (not really need to wait, but still)
// require __DIR__.'/../vendor/autoload.php';

/**
 * Framework autoloader.
 *
 * In the meanwhile of the PSR-4 implementation, we have our own custom
 * autoloader. Not beautiful, but works.
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
        $file = __DIR__.'/../system/'.$folder.str_replace('\\', '/', strtolower($class)).'.php';

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

/*
|--------------------------------------------------------------------------
| Load The Helper Functions
|--------------------------------------------------------------------------
|
| These are not classes (for now), so we need to require them by name.
| I've still undecided on loading all files on system/helper/*
|
*/

require_once __DIR__.'/../system/helper/general.php';
require_once __DIR__.'/../system/helper/json.php';
require_once __DIR__.'/../system/helper/utf8.php';
