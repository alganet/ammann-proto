<?php

namespace Ammann\Exceptions;

use OutOfBoundsException;
use ReflectionParameter;

class MissingParameter extends OutOfBoundsException
{
    const PARAMETER_NOT_CONFIGURED = 2;

    private $reflectionParameter;
	
    public function getReflectionParameter()
    {
    	return $this->reflectionParameter;
    }

	public function __construct(ReflectionParameter $reflectionParameter, $keyName)
	{
		$this->parameter = $reflectionParameter;
		$class    = $reflectionParameter->getDeclaringClass();
		$method   = $reflectionParameter->getDeclaringFunction()->getName();
		$name     = $reflectionParameter->getName();
		$position = $reflectionParameter->getPosition();
		$message = "Parameter '$name' (for '$method') missing on '$keyName' key.";
		if ($class) {
			$class   = $class->getName();
			$message = "Parameter '$name' (for '$class::$method') missing on '$keyName' key.";
		}
		
		parent::__construct(
            $message,
			static::PARAMETER_NOT_CONFIGURED
		);
	}
}