<?php

/**
 * Dependency Injection Container.
 *
 * This will be instantiated at index.php and shared across all controllers and
 * many other objects.
 */
final class Registry
{
    /** @var array Contains the DI Container hash to Class associations */
    private $mapper = [
        'affiliate' => 'Affiliate',
		'cache' => 'Cache\\File',
        'cart' => 'Cart',
        'config' => 'Config',
        'currency' => 'Currency',
        'customer' => 'Customer',
        'db' => 'DB',
        'document' => 'Document',
        'event' => 'Event',
        'front' => 'Front',
        'language' => 'Language',
        'length' => 'Length',
        'load' => 'Loader',
        'openbay' => 'Openbay',
        'request' => 'Request',
        'response' => 'Response',
        'session' => 'Session',
        'tax' => 'Tax',
        'url' => 'Url',
        'user' => 'User',
        'weight' => 'Weight',
    ];

    /** @var array Contains all loaded objects ready for injection */
    private $data = [];

    /**
     * Retrieves an object from the Container.
     *
     * Also provides lazy-loading of models.
     *
     * @param string $key
     *
     * @return mixed|null
     */
    public function get($key)
    {
        // If a model is requested, try to lazy-load as necessary
        // Using preg_match as it compiles the regex on the first check.
        if (preg_match('/^model_/', $key) === 1) {
            // Has it being loaded?
            if (!isset($this->data[$key])) {
                // Load the requested model
                // Due to name colissions, only one folder deep is allowed
                $this->get('load')->model($key);
            }
        }
        // Return the object or null if unnavailable
        if (array_key_exists($key, $this->data)) {
            return $this->data[$key];
        } elseif (array_key_exists($key, $this->mapper)) {
            return $this->data[$key] = new $this->mapper[$key]($this);
        } else {
            return;
        }
    }

    /**
     * Stores an object in the Container.
     *
     * @param string $key
     * @param string $value
     */
    public function set($key, $value)
    {
        $this->data[$key] = $value;
    }

    /**
     * Checks for an object for a given key in the Container.
     *
     * @param string $key
     *
     * @return bool
     */
    public function has($key)
    {
        return array_key_exists($key, $this->data);
    }
}
