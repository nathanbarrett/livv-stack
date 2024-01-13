<?php

declare(strict_types=1);

namespace App\Exceptions;

abstract class ContextException extends \Exception
{
    /**
     * @var array<string, mixed>
     */
    protected array $context = [];

    /**
     * @param  array<string, mixed>  $context
     */
    final public function __construct(string $message, int $code = 0, ?\Throwable $previous = null, array $context = [])
    {
        $this->context = $context;

        parent::__construct($message, $code, $previous);
    }

    /**
     * @return array<string, mixed>
     */
    public function context(): array
    {
        return $this->context;
    }
}
