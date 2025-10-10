<?php

namespace App\Exceptions;

use Exception;

class EmailSendingException extends Exception
{
    /**
     * Create a new exception instance.
     */
    public function __construct(string $message = 'Email sending failed', int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
