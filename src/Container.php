<?php

namespace Ammann;

use OutOfBoundsException;
use RuntimeException;
use Ammann\Exceptions\MissingKey;
use Ammann\Exceptions\InvalidPrefix;

class Container
{
    const TOKEN_PATTERN = '/^([[:punct:]]+)/';
    const NAME_PATTERN  = '([[:alpha:]][[:alnum:]]+)$';

    private $lastPattern = '';

    protected $config = array();

    public function __construct($config)
    {
        $this->append($config);
    }

    public function inject($name)
    {
        if (!isset($this->config[$name])) {
            throw new MissingKey($name);
        }

        $injector = new Injector($name, $this->config[$name]);
        
        return $this->config[$name] = $injector->getInstance();
    }

    public function append($prefixedKeys)
    {
        $this->findNamePattern(key($prefixedKeys));

        foreach ($prefixedKeys as $prefixedKey => $value) {
            $this->absorb($prefixedKey, $value);
        }

        foreach ($this->config as $name => $subConfig) {
            $this->config[$name] = $this->ingest($subConfig);
        }
    }

    protected function ingest($value)
    {
        if (is_array($value)) {
            return $this->ingestChildren($value);
        }

        return $this->ingestSingle($value);
    }

    protected function ingestSingle($value)
    {
        if (preg_match($this->lastPattern, $value, $valueMatches)) {
            return $this->configure($valueMatches[1]);
        }

        return $value;
    }

    protected function ingestChildren($value)
    {
        foreach ($value as $key => &$subValue) {
            $subValue = $this->ingest($subValue);
        }

        return $value;
    }

    protected function configure($keyName)
    {
        $value = new Injector($keyName, $this->config[$keyName]);
        $this->config[$keyName] = $value;
        return $value;
    }

    protected function absorb($keyName, $value)
    {
        if (preg_match($this->lastPattern, $keyName, $nameMatches)) {
            $name = $nameMatches[1];
            $this->config[$name] = $value;
        }
    }

    protected function findNamePattern($name)
    {
        $prefix  = preg_quote($this->findToken($name));
        $lazyDef = static::NAME_PATTERN;

        return $this->lastPattern = "/^{$prefix}{$lazyDef}/";
    }

    protected function findToken($name)
    {
        if (!preg_match(static::TOKEN_PATTERN, $name, $prefixMatches)) {
            throw new InvalidPrefix($name);
        }

        return $prefixMatches[1];    
    }
}
