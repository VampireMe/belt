<?php namespace Belt;

/**
 * Class Belt
 * @package Belt
 */
class Belt {

    /**
     * The modules you want to use
     *
     * @var array
     */
    protected $modules = [
        'Belt\Arrays',
        'Belt\Collections',
        'Belt\Objects',
        'Belt\Funcs',
        'Belt\Utils',
    ];

    /**
     * The loaded module instances
     *
     * @var array
     */
    protected $instances = [];

    /**
     * Load a module
     *
     * @param  string $module
     * @param  mixed|null $instance
     * @throws \UnexpectedValueException
     * @return mixed
     */
    public function load($module, $instance = null)
    {
        if ( ! \is_null($instance))
        {
            if ( ! \in_array($module, $this->modules))
            {
                $this->modules[] = $module;
            }

            return $this->instances[$module] = $instance;
        }

        if (\in_array($module, $this->modules))
        {
            return $this->instances[$module] = new $module;
        }

        throw new \UnexpectedValueException("Module {$module} does not exist");
    }

    /**
     * Get the list of the modules
     *
     * @return array
     */
    public function getModules()
    {
        return $this->modules;
    }

    /**
     * Determine whether the module was loaded
     *
     * @param  string  $module
     * @return boolean
     */
    public function isLoaded($module)
    {
        return \array_key_exists($module, $this->instances);
    }

    /**
     * Load a module (if not yet) and return the instance
     *
     * @param  string $module
     * @return mixed
     */
    public function getInstance($module)
    {
        if ( ! $this->isLoaded($module))
        {
            $this->load($module);
        }

        return $this->instances[$module];
    }

    /**
     * Determine whether the object has the method
     *
     * @param  mixed   $object
     * @param  string  $method
     * @return boolean
     */
    public function hasMethod($object, $method)
    {
        return (new \ReflectionClass($object))->hasMethod($method);
    }

    /**
     * Run a method and return the output
     *
     * @param  string $name
     * @param  array $arguments
     * @throws \BadMethodCallException
     * @return mixed
     */
    public function run($name, array $arguments = array())
    {
        foreach ($this->getModules() as $module)
        {
            $instance = $this->getInstance($module);

            if ($this->hasMethod($instance, $name))
            {
                return \call_user_func_array([$instance, $name], $arguments);
            }
        }

        throw new \BadMethodCallException("Method {$name} does not exist");
    }

    /**
     * Handle dynamic calls
     *
     * @param  string $method
     * @param  array  $arguments
     * @return mixed
     */
    public function __call($method, array $arguments = array())
    {
        return \call_user_func_array([$this, 'run'], [$method, $arguments]);
    }

    /**
     * Handle dynamic static calls
     *
     * @param  string $method
     * @param  array  $arguments
     * @return mixed
     */
    public static function __callStatic($method, array $arguments = array())
    {
        return \call_user_func_array([new static, $method], $arguments);
    }

}

