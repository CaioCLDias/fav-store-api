<?php

namespace App\Exceptions;

use Exception;

class ValidationException extends Exception
{
    protected $message = 'Dados de entrada inválidos';
    protected $code = 422;
    
    public function __construct(
        $message = null,
        protected array $errors = []
    ) {
        parent::__construct($message ?? $this->message);
    }
    
    public function getErrors(): array
    {
        return $this->errors;
    }
}
