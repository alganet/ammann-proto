<?php

namespace Ammann\Exceptions;

use RuntimeException;

class InvalidPrefix extends RuntimeException
{
    const INVALID_PREFIX_CODE = 1;

    private $name;
	
    public function getName()
    {
    	return $this->name;
    }
	
	public function __construct($name)
	{
		$this->name = $name;

		parent::__construct(
			"Prefix '$name' is invalid. Prefixes should be punctuation chars.",
			static::INVALID_PREFIX_CODE
		);
	}
}