<?php

namespace Ammann;

use Ammann\Exceptions\MissingParameter;
use Ammann\Exceptions\InvalidCall;

date_default_timezone_set('UTC');

class Injector
{
    protected $instanceName;
    protected $config;
    protected $instance;
    protected $reflections = array();

    public function __construct($instanceName, $config)
    {
        $this->instanceName = $instanceName;
        $this->config       = $config;
    }

    public function getInstance()
    {
        if (!isset($this->instance)) {
            $this->instance = $this->create();
        }

        return $this->instance;
    }

    public function create()
    {
        $config     = $this->config;
        $className  = key($config);
        $reflection = $this->reflect($className);
        $instance   = $this->construct($reflection, array_shift($config));

        foreach ($config as $param => $value) {
            $this->configure($instance, $param, $value);
        }

        return $instance;
    }

    public function __invoke()
    {
        return $this->getInstance();
    }

    protected function reflect($className)
    {
        if (!isset($this->reflections[$className])) {
            $this->reflections[$className] = new \ReflectionClass($className);
        }

        return $this->reflections[$className];
    }

    protected function configure($instance, $param, $value)
    {
        $reflection = $this->reflect(get_class($instance));

        if ($reflection->hasMethod($param)) {
            return $this->callMethod($instance, $param, $value);
        } 

        if ($reflection->hasProperty($param)) {
            return $this->setProperty($instance, $param, $value);
        }
    }

    protected function setProperty($instance, $name, $value)
    {
        return $instance->{$name} = $value;
    }

    protected function callMethod($instance, $name, $value)
    {
        $reflection = $this->reflect(get_class($instance));

        return call_user_func_array(
            array($instance, $name),
            $this->params($reflection->getMethod($name), $value)
        );
    }

    protected function construct($reflection, $config)
    {
        try {
            $constructor = $reflection->getConstructor();

            if ($constructor) {
                $params = $this->params($constructor, $config);

                return $reflection->newInstanceArgs($params);
            }

            return $reflection->newInstance();
        } catch (MissingParameter $e) {
            throw $e;
        } catch (\Exception $e) {
            throw new InvalidCall($constructor, $this->instanceName, $e);
        }
    }

    protected function params($reflection, $config)
    {
        $required = 1;
        $params   = $reflection->getParameters();
        $synced   = array();
        
        foreach ($params as $paramReflection) {
            $name = $paramReflection->getName();
            if (isset($config[$name])) {
                $synced[$name] = $config[$name];
            } elseif ($paramReflection->isDefaultValueAvailable()) {
                $synced[$name] = $paramReflection->getDefaultValue();
            } elseif($paramReflection->allowsNull()) {
                $synced[$name] = null;
            } elseif($paramReflection->isOptional()) {
                break;
            } elseif (count($synced) < $required) {
                throw new MissingParameter($paramReflection, $this->instanceName);
            }
        }

        foreach ($synced as &$param) {
            if ($param instanceof static) {
                $param = $param->getInstance();
            }
        }

        return $synced;
    }
}