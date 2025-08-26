<?php

namespace App\Exceptions;

use Exception;

class UserAlreadyExistsException extends Exception
{
    protected $message = 'Usuário já existe com esse email';
    protected $code = 409;
}
