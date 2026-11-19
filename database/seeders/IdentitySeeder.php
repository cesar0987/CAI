<?php

namespace Database\Seeders;

use Cai\Identity\Authorization\FeatureFlagCatalog;
use Cai\Identity\Authorization\PermissionCatalog;
use Cai\Identity\Authorization\RoleCatalog;
use Cai\Identity\Models\FeatureFlag;
use Cai\Identity\Models\Permission;
use Cai\Identity\Models\Role;
use Illuminate\Database\Seeder;

/**
 * Siembra canónica de identidad — F2 E-03/E-08 (08.04).
 *
 * Fuente de verdad: 04.02 (matriz de permisos por rol) a través de los
 * catálogos del módulo identity. Idempotente: puede ejecutarse las veces
 * que haga falta sin duplicar filas.
 *
 * - Permisos y roles: updateOrCreate por (name, guard_name); la matriz
 *   manda, así que syncPermissions deja exactamente los permisos del rol
 *   (los `*` condicionados se conceden como puerta; la condición la
 *   verifican las policies de 04.03).
 * - Feature flags: firstOrCreate para NO pisar el estado que el operador
 *   haya cambiado en caliente (06.04).
 * - Los roles adicionales de 04.02 §7 no se siembran "por si acaso".
 */
class IdentitySeeder extends Seeder
{
    public function run(): void
    {
        foreach (PermissionCatalog::all() as $definition) {
            Permission::updateOrCreate(
                ['name' => $definition->name, 'guard_name' => 'web'],
                [
                    'label' => $definition->description,
                    'module' => $definition->module,
                    'is_sensitive' => $definition->sensitive,
                ],
            );
        }

        foreach (RoleCatalog::all() as $definition) {
            $role = Role::updateOrCreate(
                ['name' => $definition->name, 'guard_name' => 'web'],
                ['label' => $definition->label],
            );

            $role->syncPermissions(array_keys($definition->permissions));
        }

        foreach (FeatureFlagCatalog::all() as $definition) {
            FeatureFlag::firstOrCreate(
                ['key' => $definition->key],
                [
                    'description' => $definition->description,
                    'scope' => $definition->scope,
                    'is_enabled' => $definition->isEnabled(),
                ],
            );
        }
    }
}
