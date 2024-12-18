<?php

namespace App\Exception;

use Symfony\Component\HttpKernel\Exception\HttpException;

class UnauthorizedCarAccessException extends HttpException
{
    public function __construct(string $message = 'You are not authorized to access this car.', int $code = 401, \Throwable $previous = null)
    {
        parent::__construct($code, $message, $previous);
    }
}

