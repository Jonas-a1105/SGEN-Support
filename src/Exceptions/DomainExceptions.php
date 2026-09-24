<?php
/**
 * Excepciones de dominio - Tipadas para catch granular
 */

declare(strict_types=1);

namespace App\Exceptions;

class ValidationException extends \DomainException
{
    /** @var array<string, string> */
    public array $errors;

    public function __construct(string|array $message, int $code = 422, ?\Throwable $previous = null)
    {
        $this->errors = is_array($message) ? $message : ['general' => $message];
        parent::__construct(is_array($message) ? implode('; ', $message) : $message, $code, $previous);
    }
}

class NotFoundException extends \DomainException
{
    public function __construct(string $message = 'Recurso no encontrado', int $code = 404, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}

class ForbiddenException extends \DomainException
{
    public function __construct(string $message = 'Acceso denegado', int $code = 403, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}

class ConcurrencyException extends \DomainException
{
    public function __construct(string $message = 'Conflicto de concurrencia', int $code = 409, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}

class BusinessRuleException extends \DomainException
{
    public function __construct(string $message, int $code = 400, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}