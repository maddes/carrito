<?php

namespace Carrito\Framework\Engine;

/**
 * Dependency Injection Container.
 *
 * This will be instantiated at index.php and shared across all controllers and
 * many other objects.
 */
final class App
{
    /**
     * The Carrito framework version.
     *
     * @var string
     */
    const VERSION = '6.0.0';

    /** @var array Contains the DI Container hash to Class associations */
    public $mapper = [];

    /** @var array Contains all loaded objects ready for injection */
    private $data = [];

    /**
     * The base path for the Laravel installation.
     *
     * @var string
     */
    protected $basePath;

    public function __construct($basePath)
    {
        $this->registerCoreContainerAliases();

        if ($basePath) {
            $this->setBasePath($basePath);
        }
    }

    /**
     * Set the base path for the application.
     *
     * @param string $basePath
     *
     * @return $this
     */
    public function setBasePath($basePath)
    {
        $this->basePath = rtrim($basePath, '\/');
        $this->bindPathsInContainer();

        return $this;
    }

    /**
     * Bind all of the application paths in the container.
     */
    protected function bindPathsInContainer()
    {
        $this->set('path', $this->path());

        $paths = [
            'base',
            'image',
            'system',
            'cache',
            'download',
            'logs',
            'upload',
            'application',
            'language',
            'template',
            'catalog',
            'storage',
        ];

        //foreach (['base', 'config', 'database', 'lang', 'public', 'storage'] as $path) {
        foreach ($paths as $path) {
            $this->set('path.'.$path, $this->{$path.'Path'}());
        }
    }

    /**
     * Get the path to the application "app" directory.
     *
     * @return string
     */
    public function path()
    {
        //return $this->basePath.DIRECTORY_SEPARATOR.'app';
        // TODO: Next iteration is a huge project structure change.
        return $this->basePath;
    }
    /**
     * Get the base path of the Carrito installation.
     *
     * @return string
     */
    public function basePath()
    {
        return $this->basePath;
    }

    /**
     * Get the path to the images directory.
     *
     * @return string
     */
    public function imagePath()
    {
        return $this->basePath.DIRECTORY_SEPARATOR.'image';
    }

    /**
     * Get the path to system core libraries directory.
     *
     * @return string
     */
    public function systemPath()
    {
        return $this->basePath.DIRECTORY_SEPARATOR.'system';
    }

    /**
     * Get the path to the storage directory.
     *
     * @return string
     */
    public function storagePath()
    {
        return $this->systemPath().DIRECTORY_SEPARATOR.'storage';
    }

    /**
     * Get the path to the cache directory.
     *
     * @return string
     */
    public function cachePath()
    {
        return $this->storagePath().DIRECTORY_SEPARATOR.'cache';
    }

    /**
     * Get the path to the downloads directory.
     *
     * @return string
     */
    public function downloadPath()
    {
        return $this->storagePath().DIRECTORY_SEPARATOR.'download';
    }

    /**
     * Get the path to the logs directory.
     *
     * @return string
     */
    public function logsPath()
    {
        return $this->storagePath().DIRECTORY_SEPARATOR.'logs';
    }

    /**
     * Get the path to the uploaded files.
     *
     * @return string
     */
    public function uploadPath()
    {
        return $this->storagePath().DIRECTORY_SEPARATOR.'upload';
    }

    /**
     * Get the path to the current app instance (catalog|admin|install).
     *
     * @return string
     */
    public function applicationPath()
    {
        return $this->basePath.DIRECTORY_SEPARATOR.APP;
    }

    /**
     * Get the path to the language files.
     *
     * @return string
     */
    public function languagePath()
    {
        return $this->applicationPath().DIRECTORY_SEPARATOR.'language';
    }

    /**
     * Get the path to the views directory.
     *
     * @return string
     */
    public function templatePath()
    {
        return $this->applicationPath().DIRECTORY_SEPARATOR.'view'.DIRECTORY_SEPARATOR.'theme';
    }

    /**
     * Get the path to the store app directory.
     *
     * @return string
     */
    public function catalogPath()
    {
        return $this->basePath.DIRECTORY_SEPARATOR.'catalog';
    }

    /**
     * Register the core class aliases in the container.
     */
    public function registerCoreContainerAliases()
    {
        $this->mapper = array_merge(
            $this->mapper,
            [
                'affiliate' => '\Carrito\Framework\Library\Affiliate',
                'cache' => '\Carrito\Framework\Library\Cache\File',
                'cart' => '\Carrito\Framework\Library\Cart',
                'config' => '\Carrito\Framework\Library\Config',
                'currency' => '\Carrito\Framework\Library\Currency',
                'customer' => '\Carrito\Framework\Library\Customer',
                'db' => '\Carrito\Framework\Library\Db\Mysqli',
                'document' => '\Carrito\Framework\Library\Document',
                'event' => '\Carrito\Framework\Engine\Event',
                'front' => '\Carrito\Framework\Engine\Front',
                'language' => '\Carrito\Framework\Library\Language',
                'length' => '\Carrito\Framework\Library\Length',
                'load' => '\Carrito\Framework\Engine\Loader',
                'openbay' => '\Carrito\Framework\Library\Openbay',
                'request' => '\Carrito\Framework\Library\Request',
                'response' => '\Carrito\Framework\Library\Response',
                'session' => '\Carrito\Framework\Library\Session',
                'tax' => '\Carrito\Framework\Library\Tax',
                'url' => '\Carrito\Framework\Library\Url',
                'user' => '\Carrito\Framework\Library\User',
                'weight' => '\Carrito\Framework\Library\Weight',
            ]
        );
    }

    /**
     * Get the version number of the application.
     *
     * @return string
     */
    public function version()
    {
        return static::VERSION;
    }

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
