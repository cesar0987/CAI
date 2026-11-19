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
    ],
];
