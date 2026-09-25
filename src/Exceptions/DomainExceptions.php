<?php
/**
 * Excepciones de dominio - Tipadas para catch granular
 */

declare(strict_types=1);

namespace App\Exceptions;

require_once __DIR__ . '/ValidationException.php';
require_once __DIR__ . '/NotFoundException.php';
require_once __DIR__ . '/ForbiddenException.php';
require_once __DIR__ . '/ConcurrencyException.php';
require_once __DIR__ . '/BusinessRuleException.php';