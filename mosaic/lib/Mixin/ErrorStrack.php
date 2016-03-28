<?php

namespace Mosaic\Mixin;

trait ErrorStack
{
    /**
     * @var array
     */
    protected $errors = [];

    /**
     * Adds an error to the error stack
     * @param $error
     * @return $this
     */
    public function addError($error)
    {
        $this->errors[] = $error;
        return $this;
    }

    /**
     * Returns true if the error stack is not empty
     * @return bool
     */
    public function hasErrors()
    {
        return !empty($this->errors);
    }

    /**
     * Retrieve Error list
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * Clear Error stack
     * @return $this
     */
    public function clearErrors()
    {
        $this->errors = [];
        return $this;
    }
}