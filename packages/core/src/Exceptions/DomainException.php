<?php

declare(strict_types=1);

namespace Cai\Core\Exceptions;

abstract class DomainException extends \RuntimeException
{
    abstract public static function code(): string;
}
