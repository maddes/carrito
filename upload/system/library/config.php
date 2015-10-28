<?php

class config
{
    private $data = array();

    public function __construct($app)
    {
        // Default Store Settings
        if (APP !== 'install') {
            $settings = $app->get('db')->query('SELECT * FROM '.DB_PREFIX."setting WHERE store_id = '0'");

            foreach ($settings->rows as $setting) {
                if ($setting['serialized']) {
                    $this->set($setting['key'], json_decode($setting['value'], true));
                } else {
                    $this->set($setting['key'], $setting['value']);
                }
            }
        }

        // Store settings for current store
        if (APP === 'catalog') {
            // Search settings for current store based on url
            $protocol = $app->get('request')->server['HTTPS'] ? 'https' : 'http';
            $domain = str_replace('www.', '', $app->get('request')->server['HTTP_HOST']);
            $path = rtrim(dirname($app->get('request')->server['PHP_SELF']), '/.\\');
            $store_query = $app->get('db')->query('SELECT * FROM '.DB_PREFIX."store WHERE REPLACE(`ssl`, 'www.', '') = '{$protocol}://".$app->get('db')->escape($domain.$path)."/'");

            // Set store id on settings
            $this->set('config_store_id', $store_query->num_rows ? $store_query->row['store_id'] : 0);

            if ($store_query->num_rows) {
                // We are on a secondary store, load its settings
                $settings = $app->get('db')->query('SELECT * FROM `'.DB_PREFIX."setting` WHERE store_id = '".$this->get('config_store_id')."'");

                foreach ($settings->rows as $setting) {
                    if ($setting['serialized']) {
                        $this->set($setting['key'], json_decode($setting['value'], true));
                    } else {
                        $this->set($setting['key'], $setting['value']);
                    }
                }
            }
        }

        switch (APP) {
            case 'admin':
                $this->set('config_url',  'http://'.HTTP_DOMAIN.HTTP_ROOT.'admin/');
                $this->set('config_ssl', 'https://'.HTTP_DOMAIN.HTTP_ROOT.'admin/');
                break;
            case 'install':
                $this->set('config_url',  ($this->request->server['HTTPS'] ? 'http://' : 'https://').HTTP_DOMAIN.HTTP_ROOT.'admin/');
                break;
            default:
                if (!$this->get('config_url')) {
                    $this->set('config_url',  'http://'.HTTP_DOMAIN.HTTP_ROOT);
                    $this->set('config_ssl', 'https://'.HTTP_DOMAIN.HTTP_ROOT);
                }
                break;
        }
    }

    public function get($key)
    {
        return (isset($this->data[$key]) ? $this->data[$key] : null);
    }

    public function set($key, $value)
    {
        $this->data[$key] = $value;
    }

    public function has($key)
    {
        return isset($this->data[$key]);
    }
}
