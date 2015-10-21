<?php

// Error Reporting
error_reporting(E_ALL);

// Check Version
if (version_compare(phpversion(), '5.3.0', '<') == true) {
	exit('PHP5.3+ Required');
}

// Configuration
if (is_file(dirname(__DIR__).'/config.php') and APP !== 'install')
{
	require_once(dirname(__DIR__).'/config.php');
}

// Install
if (defined('HTTP_DOMAIN'))
{
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

	if (APP === 'admin')
	{
		define('HTTP_SERVER',    'http://'.HTTP_DOMAIN.HTTP_ROOT.'admin/');
		define('HTTPS_SERVER',  'https://'.HTTP_DOMAIN.HTTP_ROOT.'admin/');
		define('HTTP_CATALOG',   'http://'.HTTP_DOMAIN.HTTP_ROOT);
		define('HTTPS_CATALOG', 'https://'.HTTP_DOMAIN.HTTP_ROOT);
		define('DIR_CATALOG',     DIR_BASE.'catalog/');
	}
	else
	{
		define('HTTP_SERVER',   'http://'.HTTP_DOMAIN.HTTP_ROOT);
		define('HTTPS_SERVER', 'https://'.HTTP_DOMAIN.HTTP_ROOT);
	}
}
elseif (APP !== 'install')
{
	if (APP === 'catalog')
	{
		header('Location: install/index.php');
	}
	else {
		header('Location: ../install/index.php');
	}
	exit;
}
else {
	// HTTP
	$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
	define('HTTP_SERVER', $protocol . $_SERVER['HTTP_HOST'] . rtrim(dirname($_SERVER['SCRIPT_NAME']), '/.\\') . '/');
	define('HTTP_OPENCART', $protocol . $_SERVER['HTTP_HOST'] . rtrim(rtrim(dirname($_SERVER['SCRIPT_NAME']), 'install'), '/.\\') . '/');

	// DIR
	define('DIR_SYSTEM',      str_replace('\\', '/', realpath(__DIR__)) . '/');
	define('DIR_OPENCART',    dirname(DIR_SYSTEM) . '/');
	define('DIR_CONFIG',      DIR_SYSTEM      . 'config/');
	define('DIR_APPLICATION', DIR_OPENCART    . 'install/');
	define('DIR_LANGUAGE',    DIR_APPLICATION . 'language/');
	define('DIR_TEMPLATE',    DIR_APPLICATION . 'view/theme/');
}

// Magic Quotes Fix
if (ini_get('magic_quotes_gpc')) {
	function clean($data) {
   		if (is_array($data)) {
  			foreach ($data as $key => $value) {
    			$data[clean($key)] = clean($value);
  			}
		} else {
  			$data = stripslashes($data);
		}

		return $data;
	}

	$_GET = clean($_GET);
	$_POST = clean($_POST);
	$_COOKIE = clean($_COOKIE);
}

if (!ini_get('date.timezone')) {
	date_default_timezone_set('UTC');
}

// Windows IIS Compatibility
if (!isset($_SERVER['DOCUMENT_ROOT'])) {
	if (isset($_SERVER['SCRIPT_FILENAME'])) {
		$_SERVER['DOCUMENT_ROOT'] = str_replace('\\', '/', substr($_SERVER['SCRIPT_FILENAME'], 0, 0 - strlen($_SERVER['PHP_SELF'])));
	}
}

if (!isset($_SERVER['DOCUMENT_ROOT'])) {
	if (isset($_SERVER['PATH_TRANSLATED'])) {
		$_SERVER['DOCUMENT_ROOT'] = str_replace('\\', '/', substr(str_replace('\\\\', '\\', $_SERVER['PATH_TRANSLATED']), 0, 0 - strlen($_SERVER['PHP_SELF'])));
	}
}

if (!isset($_SERVER['REQUEST_URI'])) {
	$_SERVER['REQUEST_URI'] = substr($_SERVER['PHP_SELF'], 1);

	if (isset($_SERVER['QUERY_STRING'])) {
		$_SERVER['REQUEST_URI'] .= '?' . $_SERVER['QUERY_STRING'];
	}
}

if (!isset($_SERVER['HTTP_HOST'])) {
	$_SERVER['HTTP_HOST'] = getenv('HTTP_HOST');
}

// Check if SSL
if (isset($_SERVER['HTTPS']) && (($_SERVER['HTTPS'] == 'on') || ($_SERVER['HTTPS'] == '1'))) {
	$_SERVER['HTTPS'] = true;
} elseif (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https' || !empty($_SERVER['HTTP_X_FORWARDED_SSL']) && $_SERVER['HTTP_X_FORWARDED_SSL'] == 'on') {
	$_SERVER['HTTPS'] = true;
} else {
	$_SERVER['HTTPS'] = false;
}

// Autoloader
function library($class) {
	$file = DIR_SYSTEM . 'library/' . str_replace('\\', '/', strtolower($class)) . '.php';

	if (is_file($file)) {
		include_once($file);

		return true;
	} else {
		return false;
	}
}

function vendor($class) {
	$file = DIR_SYSTEM . 'vendor/' . str_replace('\\', '/', strtolower($class)) . '.php';

	if (is_file($file)) {
		include_once($file);

		return true;
	} else {
		return false;
	}
}

spl_autoload_register('library');
spl_autoload_register('vendor');
spl_autoload_extensions('.php');

// Engine
require_once(DIR_SYSTEM . 'engine/action.php');
require_once(DIR_SYSTEM . 'engine/controller.php');
require_once(DIR_SYSTEM . 'engine/event.php');
require_once(DIR_SYSTEM . 'engine/front.php');
require_once(DIR_SYSTEM . 'engine/loader.php');
require_once(DIR_SYSTEM . 'engine/model.php');
require_once(DIR_SYSTEM . 'engine/registry.php');

// Helper
require_once(DIR_SYSTEM . 'helper/general.php');
require_once(DIR_SYSTEM . 'helper/json.php');
require_once(DIR_SYSTEM . 'helper/utf8.php');

// Registry
$registry = new Registry();

// Loader
$registry->set('load', new Loader($registry));

if (APP === 'install')
{
	$registry->set('url', new Url(HTTP_SERVER));
}
else
{
	// Config
	$registry->set('config', new Config());

	// Database
	$registry->set('db', new DB(DB_DRIVER, DB_HOSTNAME, DB_USERNAME, DB_PASSWORD, DB_DATABASE, DB_PORT));

	// Default Store Settings
	$settings = $registry->get('db')->query("SELECT * FROM " . DB_PREFIX . "setting WHERE store_id = '0'");

	foreach ($settings->rows as $setting)
	{
		if ($setting['serialized'])
		{
			$registry->get('config')->set($setting['key'], json_decode($setting['value'], true));
		}
		else
		{
			$registry->get('config')->set($setting['key'], $setting['value']);
		}
	}

	// Store settings for catalog
	if (APP === 'catalog')
	{
		// Search settings for current store based on url
		$store_query = $registry->get('db')->query("SELECT * FROM " . DB_PREFIX . "store WHERE REPLACE(`ssl`, 'www.', '') = '" . $registry->get('db')->escape(($_SERVER['HTTPS'] ? 'https': 'http').'://' . str_replace('www.', '', $_SERVER['HTTP_HOST']) . rtrim(dirname($_SERVER['PHP_SELF']), '/.\\') . '/') . "'");

		// Set store id on settings
		$registry->get('config')->set('config_store_id', $store_query->num_rows ? $store_query->row['store_id'] : 0);

		if ($store_query->num_rows)
		{
			// We are on a secondary store, load its settings
			$settings = $registry->get('db')->query("SELECT * FROM `" . DB_PREFIX . "setting` WHERE store_id = '" . $registry->get('config')->get('config_store_id') . "'");

			foreach ($settings->rows as $setting)
			{
				if ($setting['serialized'])
				{
					$registry->get('config')->set($setting['key'], json_decode($setting['value'], true));
				}
				else
				{
					$registry->get('config')->set($setting['key'], $setting['value']);
				}
			}
		}
		else
		{
			// Set the base url from config.php
			$registry->get('config')->set('config_url', HTTP_SERVER);
			$registry->get('config')->set('config_ssl', HTTPS_SERVER);
		}

		// Url
		$registry->set('url', new Url($registry->get('config')->get('config_url'), $registry->get('config')->get('config_secure') ? $registry->get('config')->get('config_ssl') : $registry->get('config')->get('config_url')));
	}
	else {
		$registry->set('url', new Url(HTTP_SERVER, $registry->get('config')->get('config_secure') ? HTTPS_SERVER : HTTP_SERVER));
	}

	// Log
	$registry->set('log', new Log($registry->get('config')->get('config_error_filename')));
}

// Request
$registry->set('request', new Request());

// Response
$registry->set('response', new Response());
$registry->get('response')->addHeader('Content-Type: text/html; charset=utf-8');
if (APP === 'catalog')
{
	$registry->get('response')->setCompression($registry->get('config')->get('config_compression'));
}

// Session
if (APP === 'catalog' and isset($registry->get('request')->get['token']) and isset($registry->get('request')->get['route']) and substr($registry->get('request')->get['route'], 0, 4) == 'api/') {
	$registry->get('db')->query("DELETE FROM `" . DB_PREFIX . "api_session` WHERE TIMESTAMPADD(HOUR, 1, date_modified) < NOW()");

	$query = $registry->get('db')->query("SELECT DISTINCT * FROM `" . DB_PREFIX . "api` `a` LEFT JOIN `" . DB_PREFIX . "api_session` `as` ON (a.api_id = as.api_id) LEFT JOIN " . DB_PREFIX . "api_ip `ai` ON (as.api_id = ai.api_id) WHERE a.status = '1' AND as.token = '" . $registry->get('db')->escape($registry->get('request')->get['token']) . "' AND ai.ip = '" . $registry->get('db')->escape($request->server['REMOTE_ADDR']) . "'");

	if ($query->num_rows) {
		// Does not seem PHP is able to handle sessions as objects properly so so wrote my own class
		$registry->set('session', new Session($query->row['session_id'], $query->row['session_name']));

		// keep the session alive
		$registry->get('db')->query("UPDATE `" . DB_PREFIX . "api_session` SET date_modified = NOW() WHERE api_session_id = '" . $query->row['api_session_id'] . "'");
	}
} else {
	$registry->set('session', new Session());
}

if (APP === 'install')
{
	$registry->set('language', new Language('english'));
	$registry->get('language')->load('english');
}
else
{
	// Cache
	$registry->set('cache', new Cache('file'));

	// Language
	$languages = array();

	if (APP == 'admin')
	{

		$query = $registry->get('db')->query("SELECT * FROM `" . DB_PREFIX . "language`");

		foreach ($query->rows as $result)
		{
			$languages[$result['code']] = $result;
		}

		$registry->get('config')->set('config_language_id', $languages[$registry->get('config')->get('config_admin_language')]['language_id']);

		// Language
		$language = new Language($languages[$registry->get('config')->get('config_admin_language')]['directory']);
		$language->load($languages[$registry->get('config')->get('config_admin_language')]['directory']);
		$registry->set('language', $language);
	}
	else
	{
		$query = $registry->get('db')->query("SELECT * FROM `" . DB_PREFIX . "language` WHERE status = '1'");

		foreach ($query->rows as $result)
		{
			$languages[$result['code']] = $result;
		}

		if (isset($registry->get('session')->data['language']) and array_key_exists($registry->get('session')->data['language'], $languages))
		{
			$code = $registry->get('session')->data['language'];
		}
		elseif (isset($registry->get('request')->cookie['language']) and array_key_exists($registry->get('request')->cookie['language'], $languages))
		{
			$code = $registry->get('request')->cookie['language'];
		}
		else
		{
			$detect = '';

			if (isset($request->server['HTTP_ACCEPT_LANGUAGE']) and $request->server['HTTP_ACCEPT_LANGUAGE'])
			{
				$browser_languages = explode(',', $request->server['HTTP_ACCEPT_LANGUAGE']);

				foreach ($browser_languages as $browser_language)
				{
					foreach ($languages as $key => $value)
					{
						if ($value['status'])
						{
							$locale = explode(',', $value['locale']);

							if (in_array($browser_language, $locale))
							{
								$detect = $key;
								break 2;
							}
						}
					}
				}
			}

			$code = $detect ?: $registry->get('config')->get('config_language');
		}

		if (!isset($registry->get('session')->data['language']) or $registry->get('session')->data['language'] != $code) {
			$registry->get('session')->data['language'] = $code;
		}

		if (!isset($registry->get('request')->cookie['language']) or $registry->get('request')->cookie['language'] != $code) {
			setcookie('language', $code, time() + 60 * 60 * 24 * 30, '/', $registry->get('request')->server['HTTP_HOST']);
		}

		$registry->get('config')->set('config_language_id', $languages[$code]['language_id']);
		$registry->get('config')->set('config_language', $languages[$code]['code']);

		// Language
		$registry->set('language', new Language($languages[$code]['directory']));
		$registry->get('language')->load($languages[$code]['directory']);
	}
}

// Document
$registry->set('document', new Document());

if (APP === 'catalog')
{
	$registry->set('customer', new Customer($registry));

	// Customer Group
	if ($registry->get('customer')->isLogged())
	{
		$registry->get('config')->set('config_customer_group_id', $registry->get('customer')->getGroupId());
	}
	elseif (isset($registry->get('session')->data['customer']) && isset($registry->get('session')->data['customer']['customer_group_id']))
	{
		// For API calls
		$registry->get('config')->set('config_customer_group_id', $registry->get('session')->data['customer']['customer_group_id']);
	}
	elseif (isset($registry->get('session')->data['guest']) && isset($registry->get('session')->data['guest']['customer_group_id']))
	{
		$registry->get('config')->set('config_customer_group_id', $registry->get('session')->data['guest']['customer_group_id']);
	}

	// Tracking Code
	if (isset($registry->get('request')->get['tracking'])) {
		setcookie('tracking', $registry->get('request')->get['tracking'], time() + 3600 * 24 * 1000, '/');

		$registry->get('db')->query("UPDATE `" . DB_PREFIX . "marketing` SET clicks = (clicks + 1) WHERE code = '" . $registry->get('db')->escape($registry->get('request')->get['tracking']) . "'");
	}

	// Affiliate
	$registry->set('affiliate', new Affiliate($registry));
}

if (APP === 'install')
{
	// Upgrade
	$upgrade = false;

	if (file_exists(DIR_OPENCART.'/config.php')) {
		if (filesize(DIR_OPENCART.'/config.php') > 0) {
			$upgrade = true;

			$lines = file(DIR_OPENCART . 'config.php');

			foreach ($lines as $line) {
				if (strpos(strtoupper($line), 'DB_') !== false) {
					eval($line);
				}
			}
		}
	}
}
else
{
	// Currency
	$registry->set('currency', new Currency($registry));

	// Tax
	$registry->set('tax', new Tax($registry));

	// Weight
	$registry->set('weight', new Weight($registry));

	// Length
	$registry->set('length', new Length($registry));

	// User
	$registry->set('user', new User($registry));

	if (APP === 'catalog')
	{
		// Cart
		$registry->set('cart', new Cart($registry));

		// Encryption
		$registry->set('encryption', new Encryption($registry->get('config')->get('config_encryption')));
	}

	// OpenBay Pro
	$registry->set('openbay', new Openbay($registry));


	// Event
	$registry->set('event', new Event($registry));
	$query = $registry->get('db')->query("SELECT * FROM " . DB_PREFIX . "event");
	foreach ($query->rows as $event)
	{
		$registry->get('event')->register($event['trigger'], $event['action']);
	}
}

// Front Controller
$controller = new Front($registry);

if (APP === 'catalog')
{
	// Maintenance Mode
	$controller->addPreAction(new Action('common/maintenance'));

	// SEO URL's
	$controller->addPreAction(new Action('common/seo_url'));
}
elseif (APP === 'admin')
{
	// Compile Sass
	$controller->addPreAction(new Action('common/sass'));

	// Login
	$controller->addPreAction(new Action('common/login/check'));

	// Permission
	$controller->addPreAction(new Action('error/permission/check'));
}

// Router
if ($registry->get('request')->get('route', false))
{
	$action = $registry->get('request')->get('route');
}
else
{
	switch (APP) {
		case 'catalog': $action = 'common/home'; break;
		case 'admin':   $action = 'common/dashboard'; break;
		case 'install': $action = 'step_1'; break;
		default: $action = 'error/not_found'; break;
	}
}

// Dispatch
$controller->dispatch(new Action($action), new Action('error/not_found'));

// Output
$registry->get('response')->output();
