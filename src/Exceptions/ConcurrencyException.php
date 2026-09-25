<?php
/**
 * ConcurrencyException
 */

declare(strict_types=1);

namespace App\Exceptions;

class ConcurrencyException extends \DomainException
{
    public function __construct(string $message = 'Conflicto de concurrencia', int $code = 409, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
