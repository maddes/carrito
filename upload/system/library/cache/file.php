<?php

namespace cache;

class file
{
    private $expire = CACHE_EXPIRE;
    private $path;

    public function __construct($app)
    {
        $this->path = $app->get('path.cache');
        $files = glob($this->path.'/cache.*');

        foreach ($files as $file) {
            $time = substr(strrchr($file, '.'), 1);

            if ($time < time()) {
                unlink($file);
            }
        }
    }

    public function get($key)
    {
        $files = glob($this->getPrefix($key).'*');

        if (isset($files[0])) {
            return json_decode(file_get_contents($files[0]), true);
        }

        return false;
    }

    public function set($key, $value)
    {
        $this->delete($key);

        $file = $this->getPrefix($key).(time() + $this->expire);

        file_put_contents($file, json_encode($value));
    }

    public function delete($key)
    {
        array_map('unlink', glob($this->getPrefix($key).'*'));
    }

    public function getPrefix($key)
    {
        return $this->path.'/cache.'.preg_replace('/[^A-Z0-9\._-]/i', '', $key).'.';
    }
}
