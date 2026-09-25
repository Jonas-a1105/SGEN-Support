<?php
/**
 * ForbiddenException
 */

declare(strict_types=1);

namespace App\Exceptions;

class ForbiddenException extends \DomainException
{
    public function __construct(string $message = 'Acceso denegado', int $code = 403, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
