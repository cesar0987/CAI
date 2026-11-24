<?php

declare(strict_types=1);

namespace Cai\Care\Exceptions;

use DomainException;

/** Solo el autor puede finalizar o editar su borrador (08.07 §2.3). */
final class NotNoteAuthorException extends DomainException {}
