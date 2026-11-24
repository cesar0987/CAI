<?php

declare(strict_types=1);

namespace Cai\Care\Exceptions;

use DomainException;

/** 03.05 §5.3: una nota finalizada no se edita; se corrige con otra. */
final class NoteAlreadyFinalizedException extends DomainException {}
