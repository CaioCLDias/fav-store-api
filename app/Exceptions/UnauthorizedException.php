<?php

namespace App\Exceptions;

use Exception;

class UnauthorizedException extends Exception
{
    protected $message = 'Não autorizado.';
    protected $code = 401;
}
