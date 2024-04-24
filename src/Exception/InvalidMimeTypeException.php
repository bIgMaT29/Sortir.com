<?php

namespace App\Exception;

use RuntimeException;

class InvalidMimeTypeException extends RuntimeException
{
    public function __construct(string $message = 'Type MIME non autorisé', int $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
