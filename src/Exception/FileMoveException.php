<?php

namespace App\Exception;

use RuntimeException;

class FileMoveException extends RuntimeException
{
    public function __construct(string $message = 'Erreur lors du déplacement du fichier', int $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
