<?php
final class Loader {
	private $registry;

	public function __construct($registry) {
		$this->registry = $registry;
	}

	public function controller($route, $data = array()) {
		// $this->event->trigger('pre.controller.' . $route, $data);

		$parts = explode('/', str_replace('../', '', (string)$route));

		// Break apart the route
		while ($parts) {
			$file = DIR_APPLICATION . 'controller/' . implode('/', $parts) . '.php';
			$class = 'Controller' . preg_replace('/[^a-zA-Z0-9]/', '', implode('/', $parts));

			if (is_file($file)) {
				include_once($file);

				break;
			} else {
				$method = array_pop($parts);
			}
		}

		$controller = new $class($this->registry);

		if (!isset($method)) {
			$method = 'index';
		}

		// Stop any magical methods being called
		if (substr($method, 0, 2) == '__') {
			return false;
		}

		$output = '';

		if (is_callable(array($controller, $method))) {
			$output = call_user_func(array($controller, $method), $data);
		}

		// $this->event->trigger('post.controller.' . $route, $output);

		return $output;
	}

	/**
	 * Loads a model into the IOC
	 *
	 * @param string $model The model IOC key
	 */
	public function model($model)
	{
		// Load the model file, only one folder deep is allowed.
		// Notice that the 'model/' folder is still hardcoded, should fix.
		require_once(DIR_APPLICATION.implode('/', explode('_', $model, 3)).'.php');

		// Calculate the model Class name (case insensitively)
		$class = preg_replace('/[^a-zA-Z0-9]/', '', $model);

		// Instance the model and insert it on the IOC
		$this->registry->set($model, new $class($this->registry));
	}

	public function view($template, $data = array()) {
		// $this->event->trigger('pre.view.' . str_replace('/', '.', $template), $data);

		$file = DIR_TEMPLATE.'default/template/'.$template.'.php';

		if ($this->registry->get('config'))
		{
			if (file_exists(DIR_TEMPLATE.$this->registry->get('config')->get('config_template').'/template/'.$template.'.php'))
			{
				$file = DIR_TEMPLATE.$this->registry->get('config')->get('config_template').'/template/'.$template.'.php';
			}
		}

		if (file_exists($file))
		{
			extract($data);
			ob_start();
			require($file);
			$output = ob_get_contents();
			ob_end_clean();
		}
		else
		{
			trigger_error('Error: Could not load template ' . $file . '!');
			exit();
		}

		// $this->event->trigger('post.view.' . str_replace('/', '.', $template), $output);

		return $output;
	}

	public function helper($helper) {
		$file = DIR_SYSTEM . 'helper/' . str_replace('../', '', (string)$helper) . '.php';

		if (file_exists($file)) {
			include_once($file);
		} else {
			trigger_error('Error: Could not load helper ' . $file . '!');
			exit();
		}
	}

	public function config($config) {
		$this->registry->get('config')->load($config);
	}

	public function language($language) {
		return $this->registry->get('language')->load($language);
	}
}
