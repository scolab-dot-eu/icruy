<?php

namespace App\Exceptions;

use Exception;

class ImportLayerException extends Exception
{
    public $messages;
    public $values;
    /**
     * Create a new exception instance.
     *
     * @param  \Illuminate\Contracts\Validation\Validator  $validator
     * @param  \Symfony\Component\HttpFoundation\Response  $response
     * @param  string  $errorBag
     * @return void
     */
    public function __construct(array $messages, array $values)
    {
        parent::__construct('The given data was invalid.');
        
        $this->messages = $messages;
        $this->values = $values;
    }
}