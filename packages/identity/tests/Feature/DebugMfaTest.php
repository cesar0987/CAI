<?php

declare(strict_types=1);

/**
 * RETIRADO (F2 E-02, 2026-11-19): archivo de diagnóstico temporal usado
 * para encontrar el desajuste de model_type polimórfico entre la factory
 * (App\Models\User inferido) y el modelo real del módulo identity.
 * La corrección vive en UserFactory ($model) y config/auth.php, y la
 * regresión se cubre en AuthenticationTest.php. Se conserva sin efecto.
 */
