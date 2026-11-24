<?php

declare(strict_types=1);

return [
    'schemas' => [
        'identity',
        'students',
        'scheduling',
        'care',
        'billing',
        'documents',
        'notifications',
        'reporting',
    ],

    /*
    |--------------------------------------------------------------------------
    | Seguridad de acceso (03.01 §9)
    |--------------------------------------------------------------------------
    | Parámetros de login, sesión, contraseñas y MFA. Los valores por defecto
    | son los del diseño; Dirección puede ajustarlos vía settings en F8.
    */
    'seguridad' => [
        'login_max_attempts' => 5,
        'login_lockout_minutes' => 15,
        'mfa_enforced_roles' => ['direccion', 'admin_tecnico'],
        'mfa' => [
            'issuer' => 'CAI',
            'digits' => 6,
            'period' => 30,
            'window' => 1,
        ],
        'session_minutes' => 480,
        'session_idle_minutes' => 30,
        'password_min_length' => 12,
        'password_rotation_days' => 180,
        // ADR-0006: clave separada de APP_KEY para el contenido clínico.
        // base64 de 32 bytes; se genera con `php artisan key:generate --show`.
        'clinical_encryption_key' => env('CLINICAL_ENCRYPTION_KEY'),
        // 04.04 §3.3: expiración máxima por defecto de un grant excepcional.
        'clinical_grant_max_days' => 30,
    ],

    /*
    |--------------------------------------------------------------------------
    | Finanzas (08.08 §2.2 / 03.06 §2)
    |--------------------------------------------------------------------------
    | El importe se guarda exacto (NUMERIC 14,2). El redondeo es solo de
    | presentación, configurable; P2 sigue abierta en Dirección.
    */
    'finanzas' => [
        'rounding_mode' => env('CAI_AMOUNT_ROUNDING', 'none'), // none | 100 | 1000
        // 08.08 §3.7: umbral propuesto, pendiente de validación (P23).
        'cash_difference_tolerance' => '5000.00',
    ],
];
