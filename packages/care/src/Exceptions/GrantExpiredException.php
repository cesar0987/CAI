<?php

declare(strict_types=1);

namespace Cai\Care\Exceptions;

use DomainException;

/** El grant pedido vence en el pasado o supera el máximo configurado. */
final class GrantExpiredException extends DomainException {}
