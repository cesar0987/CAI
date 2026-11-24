<?php

declare(strict_types=1);

namespace Cai\Care\Exceptions;

use DomainException;

/** Una nota en borrador se edita; la corrección exige una nota finalizada. */
final class NotFinalizedException extends DomainException {}
