<?php

/**
 * Dependency Injection Container
 *
 * This will be instantiated at index.php and shared across all controllers and
 * many other objects.
 */
final class Registry {

	/** @var array Contains all loaded objects ready for injection */
	private $data = [];

	/**
	 * Retrieves an object from the Container
	 *
	 * Also provides lazy-loading of models.
	 *
	 * @param string $key The key for the object being retrieved
	 *
	 * @return mixed|null The object requested from the Container or null
	 */
	public function get($key)
	{
		// If a model is requested, try to lazy-load as necessary
		// Using preg_match as it compiles the regex on the first check.
		if (preg_match('/^model_/', $key) === 1)
		{
			// Has it being loaded?
			if ( ! isset($this->data[$key]))
			{
				// Load the requested model
				// Due to name colissions, only one folder deep is allowed
				$this->get('load')->model($key);
			}
		}
		// Return the object or null if unnavailable
		return (isset($this->data[$key]) ? $this->data[$key] : null);
	}

	/**
	 * Stores an object in the Container
	 *
	 * @param string $key The key for the object being stored
	 * @param string $value The instance for the object being stored
	 */
	public function set($key, $value)
	{
		$this->data[$key] = $value;
	}

	/**
	 * Checks for an object for a given key in the Container
	 *
	 * @param string $key The key for the object being checked
	 *
	 * @return boolean Whether the key exists in the container
	 */
	public function has($key)
	{
		return array_key_exists($key, $this->data);
	}
}
