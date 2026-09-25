<?php
/**
 * BusinessRuleException
 */

declare(strict_types=1);

namespace App\Exceptions;

class BusinessRuleException extends \DomainException
{
    public function __construct(string $message, int $code = 400, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
