<?php
/**
 * NotFoundException
 */

declare(strict_types=1);

namespace App\Exceptions;

class NotFoundException extends \DomainException
{
    public function __construct(string $message = 'Recurso no encontrado', int $code = 404, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
