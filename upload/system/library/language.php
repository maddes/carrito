<?php

class language
{
    private $default = 'english';
    private $directory;
    private $data = array();

    public function __construct($directory = 'english')
    {
        if ($directory instanceof App) {
            $this->directory = $this->detect($directory);
        } elseif (is_string($directory)) {
            $this->directory = $directory;
        } else {
            $this->directory = 'english';
        }
        $this->load($this->directory);
    }

    public function get($key)
    {
        return (isset($this->data[$key]) ? $this->data[$key] : $key);
    }

    public function all()
    {
        return $this->data;
    }

    public function load($filename)
    {
        $_ = array();

        $file = DIR_LANGUAGE.$this->default.'/'.$filename.'.php';

        if (file_exists($file)) {
            require $file;
        }

        $file = DIR_LANGUAGE.$this->directory.'/'.$filename.'.php';

        if (file_exists($file)) {
            require $file;
        }

        $this->data = array_merge($this->data, $_);

        return $this->data;
    }

    public function detect($app)
    {
        switch (APP) {
            case 'admin':
                $query = $app->get('db')->query('SELECT * FROM `'.DB_PREFIX.'language` WHERE code = '.(int) $app->get('config')->get('config_admin_language'));
                $app->get('config')->set('config_language_id', $query->row['language_id']);

                return $query->row['directory'];
            case 'catalog':
                // Get a list of available languages
                $languages = [];
                $query = $app->get('db')->query('SELECT * FROM `'.DB_PREFIX."language` WHERE status = '1'");
                foreach ($query->rows as $result) {
                    $languages[$result['code']] = $result;
                }

                // Search the language in the session and check its availability
                if (array_key_exists('language', $app->get('session')->data) and array_key_exists($app->get('session')->data['language'], $languages)) {
                    $code = $app->get('session')->data['language'];
                }
                // Search the language in the cookies and check its availability
                elseif (array_key_exists('language', $app->get('request')->cookie) and array_key_exists($app->get('request')->cookie['language'], $languages)) {
                    $code = $app->get('request')->cookie['language'];
                }
                // Detect the language with headers magic…
                else {
                    // Check that the HTTP_ACCEPT_LANGUAGE header exists and has a useful value
                    if (isset($app->get('request')->server['HTTP_ACCEPT_LANGUAGE'])) {
                        // Make a list of the accepted languages
                        $browser_languages = explode(',', $app->get('request')->server['HTTP_ACCEPT_LANGUAGE']);

                        // Check if any locale from any active language matches any browser accepted locale
                        foreach ($browser_languages as $browser_language) {
                            foreach ($languages as $key => $value) {
                                if (in_array($browser_language, explode(',', $value['locale']))) {
                                    $code = $key;
                                    break 2;
                                }
                            }
                        }
                    }

                    // Language detection failed. Fall back to config setting.
                    if (!isset($code)) {
                        $code = $app->get('config')->get('config_language');
                    }
                }

                // Set the language in the sesion
                $app->get('session')->data['language'] = $code;

                // Set the language in the cookie
                setcookie('language', $code, time() + 60 * 60 * 24 * 30, '/', $app->get('request')->server['HTTP_HOST']);

                // Set config value, because reasons…
                $app->get('config')->set('config_language_id', $languages[$code]['language_id']);

                // This is a very high derp level, but I'll just leave it here to laugh later
                $app->get('config')->set('config_language', $languages[$code]['code']);

                return $languages[$code]['directory'];

            default:
                return 'english';
        }
    }
}
