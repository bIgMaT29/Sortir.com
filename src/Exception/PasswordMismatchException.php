<?php

namespace App\Exception;

use RuntimeException;

class PasswordMismatchException extends RuntimeException
{
    public function __construct(string $message = 'Les mots de passe ne correspondent pas', int $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
