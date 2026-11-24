<?php

declare(strict_types=1);

namespace Cai\Care\Exceptions;

use DomainException;

/** Motivo de corrección o grant por debajo de 20 caracteres (04.04 §3.3). */
final class ReasonTooShortException extends DomainException {}
