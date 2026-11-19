<?php

declare(strict_types=1);

/**
 * RETIRADO (F2, 2026-11-19): Pest solo carga el `tests/Pest.php` raíz
 * (BootFiles::boot), nunca los Pest.php de paquetes. Los bindings de estos
 * Feature tests (TestCase + RefreshDatabase) viven en `tests/Pest.php`.
 * Este archivo se conserva sin efecto como referencia histórica.
 */
