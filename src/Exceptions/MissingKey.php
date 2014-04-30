<?php

namespace Ammann\Exceptions;

use OutOfBoundsException;

class MissingKey extends OutOfBoundsException
{
    const KEY_NOT_FOUND_CODE = 2;

    private $name;
	
    public function getName()
    {
    	return $this->name;
    }

	public function __construct($name)
	{
		$this->name = $name;
		$message = "Key '$name' was called and could not be found.";

		if (empty($name)) {
			$message = "Key '$name' (empty string) was called and could not be found.";
		}

		parent::__construct(
            $message,
			static::KEY_NOT_FOUND_CODE
		);
	}
}