<?php

namespace App\Exceptions;

use Illuminate\Validation\ValidationException;

class TableCreationException extends ValidationException
{
    /**
     * Create a new exception instance.
     *
     * @param  \Illuminate\Contracts\Validation\Validator  $validator
     * @param  \Symfony\Component\HttpFoundation\Response  $response
     * @param  string  $errorBag
     * @return void
     */
    public function __construct($validator, $response = null, $errorBag = 'default')
    {
        parent::__construct('The given data was invalid.');
        
        $this->response = $response;
        $this->errorBag = $errorBag;
        $this->validator = $validator;
    }
}