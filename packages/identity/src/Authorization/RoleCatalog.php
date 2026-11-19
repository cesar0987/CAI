<?php

declare(strict_types=1);

namespace Cai\Identity\Authorization;

/**
 * Catálogo canónico de roles — 04.02 §1 y §2 (matriz maestra).
 *
 * Cada rol recibe los permisos marcados `✓` y también los condicionados
 * (`*`): la condición (asignación vigente, grant, modo mínimo, …) la
 * verifican las policies y scopes de 04.03, no el catálogo.
 *
 * Excepción explícita de la matriz: `care.clinical.read` NO se concede
 * por rol a Dirección (04.02 §3); Dirección accede solo por grant
 * excepcional con motivo y expiración.
 *
 * Los roles adicionales de 04.02 §7 (clinical_admin, cashier, auditor)
 * no se siembran: se crean solo ante una necesidad concreta documentada.
 */
final class RoleCatalog
{
    /**
     * @return array<string, Role>
     */
    public static function all(): array
    {
        return [
            'direccion' => self::role('direccion', 'Dirección', [
                // Identidad: todo
                'identity.users.view', 'identity.users.manage', 'identity.roles.manage',
                'identity.permissions.view', 'identity.audit.view', 'identity.security_events.view',
                'identity.settings.manage', 'identity.flags.manage',
                // Alumnos: todo
                'students.students.view', 'students.students.create', 'students.students.update',
                'students.students.deactivate', 'students.guardians.manage',
                'students.family_accounts.view', 'students.family_accounts.manage',
                'students.authorized_pickups.manage',
                // Servicios y agenda: todo
                'scheduling.services.manage', 'scheduling.services.view',
                'scheduling.professionals.manage', 'scheduling.enrollments.manage',
                'scheduling.enrollments.view', 'scheduling.groups.manage',
                'scheduling.sessions.view', 'scheduling.sessions.create',
                'scheduling.sessions.update', 'scheduling.sessions.cancel',
                'scheduling.attendance.record', 'scheduling.attendance.correct',
                // Clínico: solo grants (care.clinical.read NO se concede por rol)
                'care.grants.issue', 'care.grants.revoke',
                // Finanzas: supervisión, sin registrar pagos ni manejar caja
                'billing.charges.view', 'billing.charges.create', 'billing.charges.void',
                'billing.charges.adjust', 'billing.payments.view', 'billing.payments.void',
                'billing.receipts.view', 'billing.receipts.void',
                'billing.reports.view', 'billing.export',
                // Documentos: todo
                'documents.documents.view', 'documents.documents.upload',
                'documents.documents.download', 'documents.consents.manage',
                'documents.consents.view',
                // Notificaciones: todo
                'notifications.messages.send', 'notifications.templates.manage',
                // Reportes: todo
                'reporting.reports.view', 'reporting.reports.export',
                'reporting.financial.view', 'reporting.clinical_summary.view',
            ]),
            'coordinacion' => self::role('coordinacion', 'Coordinación', [
                'identity.users.view', 'identity.audit.view',
                'students.students.view', 'students.students.create', 'students.students.update',
                'students.students.deactivate', 'students.guardians.manage',
                'students.family_accounts.view', 'students.family_accounts.manage',
                'students.authorized_pickups.manage',
                'scheduling.services.manage', 'scheduling.services.view',
                'scheduling.professionals.manage', 'scheduling.enrollments.manage',
                'scheduling.enrollments.view', 'scheduling.groups.manage',
                'scheduling.sessions.view', 'scheduling.sessions.create',
                'scheduling.sessions.update', 'scheduling.sessions.cancel',
                'scheduling.attendance.record', 'scheduling.attendance.correct',
                // Clínico condicionado (*): requiere rol clínico asignado por Dirección
                'care.clinical.read', 'care.plans.view', 'care.plans.manage',
                'care.goals.manage', 'care.notes.view', 'care.notes.create',
                'care.notes.finalize', 'care.notes.supersede',
                'care.documents.view', 'care.documents.upload',
                'care.grants.issue', 'care.grants.revoke',
                'documents.documents.view', 'documents.documents.upload',
                'documents.documents.download', 'documents.consents.view',
                'notifications.messages.send',
                'reporting.reports.view', 'reporting.reports.export',
            ]),
            'secretaria' => self::role('secretaria', 'Secretaría', [
                'students.students.view', 'students.students.create', 'students.students.update',
                'students.guardians.manage', 'students.family_accounts.view',
                'students.family_accounts.manage', 'students.authorized_pickups.manage',
                'scheduling.services.manage', 'scheduling.services.view',
                'scheduling.professionals.manage', 'scheduling.enrollments.manage',
                'scheduling.enrollments.view', 'scheduling.groups.manage',
                'scheduling.sessions.view', 'scheduling.sessions.create',
                'scheduling.sessions.update', 'scheduling.sessions.cancel',
                'documents.documents.view', 'documents.documents.upload',
                'documents.documents.download', 'documents.consents.manage',
                'documents.consents.view',
                'notifications.messages.send',
                'reporting.reports.view', 'reporting.reports.export',
            ]),
            'profesional' => self::role('profesional', 'Profesional', [
                // Condicionados (*): asignación profesional vigente
                'students.students.view', 'students.students.update',
                'scheduling.services.view', 'scheduling.enrollments.view',
                'scheduling.sessions.view', 'scheduling.sessions.update',
                'scheduling.sessions.cancel', 'scheduling.attendance.record',
                'scheduling.attendance.correct',
                'care.clinical.read', 'care.plans.view', 'care.plans.manage',
                'care.goals.manage', 'care.notes.view',
                // Sin condición: el profesional escribe y firma sus notas
                'care.notes.create', 'care.notes.finalize', 'care.notes.supersede',
                'care.documents.view', 'care.documents.upload',
                'documents.documents.view', 'documents.documents.upload',
                'documents.documents.download',
                // Condicionado (*): solo plantillas administrativas
                'notifications.messages.send',
                // Línea roja 3: ningún permiso de billing (04.02 §4)
            ]),
            'finanzas' => self::role('finanzas', 'Administración Financiera', [
                // Condicionado (*): modo mínimo (código, nombre, tutor)
                'students.students.view',
                'students.family_accounts.view',
                'billing.charges.view', 'billing.charges.create', 'billing.charges.void',
                // Condicionado (*): requiere authorized_by de Dirección
                'billing.charges.adjust',
                'billing.payments.view', 'billing.payments.create', 'billing.payments.void',
                'billing.allocations.manage', 'billing.receipts.view', 'billing.receipts.void',
                'billing.cash.open', 'billing.cash.close',
                'billing.reports.view', 'billing.export',
                // Condicionados (*): solo documentos de categoría financial
                'documents.documents.view', 'documents.documents.download',
                'notifications.messages.send',
                'reporting.reports.view', 'reporting.reports.export',
                'reporting.financial.view',
                // Línea roja 2: ningún permiso de care (04.02 §4)
            ]),
            'recepcion' => self::role('recepcion', 'Recepción', [
                // Condicionado (*): mínimo necesario para el retiro
                'students.guardians.manage',
                'students.authorized_pickups.manage',
                'scheduling.services.view', 'scheduling.sessions.view',
                // 04.02 §6: recepcion nunca recibe students.students.view
            ]),
            'admin_tecnico' => self::role('admin_tecnico', 'Administración Técnica', [
                'identity.users.view', 'identity.users.manage',
                'identity.permissions.view', 'identity.audit.view',
                'identity.security_events.view', 'identity.settings.manage',
                'identity.flags.manage',
                // Sin identity.roles.manage: los roles los gestiona Dirección
                // Línea roja 1: ningún permiso de care (04.02 §4)
            ]),
        ];
    }

    /**
     * @param  list<string>  $permissionNames
     */
    private static function role(string $name, string $label, array $permissionNames): Role
    {
        $catalog = PermissionCatalog::all();
        $permissions = [];

        foreach ($permissionNames as $permissionName) {
            if (! isset($catalog[$permissionName])) {
                throw new \InvalidArgumentException("Permiso desconocido en el catálogo: {$permissionName}");
            }

            $permissions[$permissionName] = $catalog[$permissionName];
        }

        return new Role($name, $permissions, $label);
    }
}
