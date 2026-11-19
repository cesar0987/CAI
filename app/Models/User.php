<?php

namespace App\Models;

use Cai\Identity\Models\User as IdentityUser;

/**
 * Alias de compatibilidad: la cuenta de acceso real vive en el módulo
 * identity (Cai\Identity\Models\User). Se conserva para no romper código
 * existente que importe App\Models\User.
 *
 * @deprecated Usar Cai\Identity\Models\User directamente.
 */
class User extends IdentityUser {}
