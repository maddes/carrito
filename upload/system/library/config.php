<?php
class Config {

	private $data = array();

	public function __construct($registry)
	{
		// Default Store Settings
		$settings = $registry->get('db')->query('SELECT * FROM '.DB_PREFIX."setting WHERE store_id = '0'");

		foreach ($settings->rows as $setting)
		{
			if ($setting['serialized'])
			{
				$this->set($setting['key'], json_decode($setting['value'], true));
			}
			else
			{
				$this->set($setting['key'], $setting['value']);
			}
		}

		// Store settings for catalog
		if (APP === 'catalog')
		{
			// Search settings for current store based on url
			$protocol = $registry->get('request')->server['HTTPS'] ? 'https' : 'http';
			$domain   = str_replace('www.', '', $registry->get('request')->server['HTTP_HOST']);
			$path     = rtrim(dirname($registry->get('request')->server['PHP_SELF']), '/.\\');
			$store_query = $registry->get('db')->query('SELECT * FROM '.DB_PREFIX."store WHERE REPLACE(`ssl`, 'www.', '') = '{$protocol}://".$registry->get('db')->escape($domain.$path)."/'");

			// Set store id on settings
			$this->set('config_store_id', $store_query->num_rows ? $store_query->row['store_id'] : 0);

			if ($store_query->num_rows)
			{
				// We are on a secondary store, load its settings
				$settings = $registry->get('db')->query('SELECT * FROM `'.DB_PREFIX."setting` WHERE store_id = '".$this->get('config_store_id')."'");

				foreach ($settings->rows as $setting)
				{
					if ($setting['serialized'])
					{
						$this->set($setting['key'], json_decode($setting['value'], true));
					}
					else
					{
						$this->set($setting['key'], $setting['value']);
					}
				}
			}
			else
			{
				// Set the base url from config.php
				$this->set('config_url', HTTP_SERVER);
				$this->set('config_ssl', HTTPS_SERVER);
			}
		}
	}

	public function get($key) {
		return (isset($this->data[$key]) ? $this->data[$key] : null);
	}

	public function set($key, $value) {
		$this->data[$key] = $value;
	}

	public function has($key) {
		return isset($this->data[$key]);
	}

	public function load($filename) {
		$file = DIR_CONFIG . $filename . '.php';

		if (file_exists($file)) {
			$_ = array();

			require($file);

			$this->data = array_merge($this->data, $_);
		} else {
			trigger_error('Error: Could not load config ' . $filename . '!');
			exit();
		}
	}
}
