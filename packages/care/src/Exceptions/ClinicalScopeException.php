<?php

declare(strict_types=1);

namespace Cai\Care\Exceptions;

use DomainException;

/** El actor no tiene perfil profesional o no está asignado al alumno (04.03). */
final class ClinicalScopeException extends DomainException {}
