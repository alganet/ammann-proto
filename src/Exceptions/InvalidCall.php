<?php

namespace Ammann\Exceptions;

use OutOfBoundsException;
use ReflectionFunctionAbstract;

class InvalidCall extends OutOfBoundsException
{
    const KEY_INVALID_CALL = 2;

    private $functionAbstract;
	
    public function getFunctionAbstract()
    {
    	return $this->functionAbstract;
    }

	public function __construct(ReflectionFunctionAbstract $functionAbstract, $keyName)
	{
		$this->functionAbstract = $functionAbstract;

		$name   = $functionAbstract->getName();
		$class  = $functionAbstract->getDeclaringClass();
		$message = "Function '$class::$name' could not be called for '$keyName' key.";

		if ($class) {
			$class   = $class->getName();
			$message = "Method '$class::$name' could not be called for '$keyName' key.";
		}

		parent::__construct(
            $message,
			static::KEY_INVALID_CALL
		);
	}
}