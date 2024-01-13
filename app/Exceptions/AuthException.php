<?php declare(strict_types=1);

namespace App\Exceptions;

use App\Exceptions\ContextException;

class AuthException extends ContextException
{
    public static function newUserCreationException(string $email, \Exception $e): static
    {
        return new static(
            message: $e->getMessage(),
            code: (int)$e->getCode(),
            previous: $e,
            context: [
                'email' => $email,
            ],
        );
    }
}
