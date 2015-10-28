<?php

class url
{
    private $app;
    private $rewrite = [];

    public function __construct($app)
    {
        $this->app = $app;
    }

    public function addRewrite($rewrite)
    {
        $this->rewrite[] = $rewrite;
    }

    public function link($route, $args = '', $secure = false)
    {
        if ($this->app->get('request')->server['HTTPS']) {
            $url = $this->app->get('config')->get('config_ssl');
        } else {
            $url = $this->app->get('config')->get('config_url');
        }

        $url .= 'index.php?route='.$route;

        if ($args) {
            if (is_array($args)) {
                $url .= '&amp;'.http_build_query($args);
            } else {
                $url .= str_replace('&', '&amp;', '&'.ltrim($args, '&'));
            }
        }

        foreach ($this->rewrite as $rewrite) {
            $url = $rewrite->rewrite($url);
        }

        return $url;
    }
}
