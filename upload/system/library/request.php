<?php

class request
{
    public $get = array();
    public $post = array();
    public $cookie = array();
    public $files = array();
    public $server = array();

    public function __construct($registry)
    {
        if (ini_get('magic_quotes_gpc')) {
            $_GET = $this->clean_magic_quotes($_GET);
            $_POST = $this->clean_magic_quotes($_POST);
            $_COOKIE = $this->clean_magic_quotes($_COOKIE);
        }

        $this->complete_server();

        $this->get = $this->clean($_GET);
        $this->post = $this->clean($_POST);
        $this->request = $this->clean($_REQUEST);
        $this->cookie = $this->clean($_COOKIE);
        $this->files = $this->clean($_FILES);
        $this->server = $this->clean($_SERVER);

        // Tracking Code
        if (isset($this->get['tracking'])) {
            setcookie('tracking', $this->get['tracking'], time() + 3600 * 24 * 1000, '/');

            $registry->get('db')->query('UPDATE `'.DB_PREFIX."marketing` SET clicks = (clicks + 1) WHERE code = '".$registry->get('db')->escape($this->get['tracking'])."'");
        }
    }

    // Magic Quotes Fix
    public function clean_magic_quotes($data)
    {
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                $data[$this->clean_magic_quotes($key)] = $this->clean_magic_quotes($value);
            }
        } else {
            $data = stripslashes($data);
        }

        return $data;
    }

    public function clean($data)
    {
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                unset($data[$key]);
                $data[$this->clean($key)] = $this->clean($value);
            }
        } else {
            $data = htmlspecialchars($data, ENT_COMPAT, 'UTF-8');
        }

        return $data;
    }

    public function get($key, $default = null)
    {
        if (array_key_exists($key, $this->get)) {
            return $this->get[$key];
        } else {
            return $default;
        }
    }

    public function complete_server()
    {
        // Windows IIS Compatibility
        if (!array_key_exists('DOCUMENT_ROOT', $_SERVER)) {
            if (array_key_exists('SCRIPT_FILENAME', $_SERVER)) {
                $_SERVER['DOCUMENT_ROOT'] = str_replace('\\', '/', substr($_SERVER['SCRIPT_FILENAME'], 0, 0 - strlen($_SERVER['PHP_SELF'])));
            }
        }

        if (!array_key_exists('DOCUMENT_ROOT', $_SERVER)) {
            if (array_key_exists('PATH_TRANSLATED', $_SERVER)) {
                $_SERVER['DOCUMENT_ROOT'] = str_replace('\\', '/', substr(str_replace('\\\\', '\\', $_SERVER['PATH_TRANSLATED']), 0, 0 - strlen($_SERVER['PHP_SELF'])));
            }
        }

        if (!array_key_exists('REQUEST_URI', $_SERVER)) {
            $_SERVER['REQUEST_URI'] = substr($_SERVER['PHP_SELF'], 1);

            if (array_key_exists('QUERY_STRING', $_SERVER)) {
                $_SERVER['REQUEST_URI'] .= '?'.$_SERVER['QUERY_STRING'];
            }
        }

        if (!array_key_exists('HTTP_HOST', $_SERVER)) {
            $_SERVER['HTTP_HOST'] = getenv('HTTP_HOST');
        }

        // Check if SSL
        if (array_key_exists('HTTPS', $_SERVER) and (($_SERVER['HTTPS'] == 'on') or ($_SERVER['HTTPS'] == '1'))) {
            $_SERVER['HTTPS'] = true;
        } elseif (array_key_exists('HTTP_X_FORWARDED_PROTO', $_SERVER) and $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https') {
            $_SERVER['HTTPS'] = true;
        } elseif (array_key_exists('HTTP_X_FORWARDED_SSL', $_SERVER) and $_SERVER['HTTP_X_FORWARDED_SSL'] == 'on') {
            $_SERVER['HTTPS'] = true;
        } else {
            $_SERVER['HTTPS'] = false;
        }
    }
}
