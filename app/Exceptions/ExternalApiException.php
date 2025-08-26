<?php

namespace App\Exceptions;

use Exception;

class ExternalApiException extends Exception
{
    protected $message = 'Erro ao comunicar com API externa.';
    protected $code = 502;
}
