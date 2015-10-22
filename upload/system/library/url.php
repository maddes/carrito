<?php
class Url {

	private $domain;
	private $ssl;
	private $rewrite = array();

	public function __construct($registry)
	{
		switch (APP) {
			case 'catalog':
				$this->domain = $registry->get('config')->get('config_url');
				$this->ssl = $registry->get('config')->get('config_secure') ? $registry->get('config')->get('config_ssl') : $registry->get('config')->get('config_url');
				break;
			case 'admin':
				$this->domain = HTTP_SERVER;
				$this->ssl = $registry->get('config')->get('config_secure') ? HTTPS_SERVER : HTTP_SERVER;
				break;
			default:
				$this->domain = HTTP_SERVER;
				$this->ssl = '';
		}
	}

	public function addRewrite($rewrite)
	{
		$this->rewrite[] = $rewrite;
	}

	public function link($route, $args = '', $secure = false)
	{
		if (!$secure)
		{
			$url = $this->domain;
		}
		else
		{
			$url = $this->ssl;
		}

		$url .= 'index.php?route=' . $route;

		if ($args)
		{
			if (is_array($args))
			{
				$url .= '&amp;' . http_build_query($args);
			}
			else
			{
				$url .= str_replace('&', '&amp;', '&' . ltrim($args, '&'));
			}
		}

		foreach ($this->rewrite as $rewrite)
		{
			$url = $rewrite->rewrite($url);
		}

		return $url;
	}
}
