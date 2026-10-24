<?php

declare(strict_types=1);

namespace Cai\Identity\Authorization;

final class RoleCatalog
{
    /**
     * @return array<string, Role>
     */
    public static function all(): array
    {
        return [
            'direccion' => new Role('direccion', [
                'students.read' => PermissionCatalog::all()['students.read'],
                'students.write' => PermissionCatalog::all()['students.write'],
                'scheduling.sessions.manage' => PermissionCatalog::all()['scheduling.sessions.manage'],
                'care.notes.read' => PermissionCatalog::all()['care.notes.read'],
                'care.notes.write' => PermissionCatalog::all()['care.notes.write'],
                'billing.invoices.manage' => PermissionCatalog::all()['billing.invoices.manage'],
                'billing.charges.view' => PermissionCatalog::all()['billing.charges.view'],
                'documents.manage' => PermissionCatalog::all()['documents.manage'],
                'notifications.send' => PermissionCatalog::all()['notifications.send'],
                'identity.users.manage' => PermissionCatalog::all()['identity.users.manage'],
                'identity.roles.manage' => PermissionCatalog::all()['identity.roles.manage'],
                'system.audit.view' => PermissionCatalog::all()['system.audit.view'],
                'system.settings.manage' => PermissionCatalog::all()['system.settings.manage'],
            ]),
            'coordinacion' => new Role('coordinacion', [
                'students.read' => PermissionCatalog::all()['students.read'],
                'scheduling.sessions.manage' => PermissionCatalog::all()['scheduling.sessions.manage'],
                'care.notes.read' => PermissionCatalog::all()['care.notes.read'],
                'care.notes.write' => PermissionCatalog::all()['care.notes.write'],
            ]),
            'secretaria' => new Role('secretaria', [
                'students.read' => PermissionCatalog::all()['students.read'],
                'students.contacts.view' => PermissionCatalog::all()['students.contacts.view'],
                'scheduling.sessions.view' => PermissionCatalog::all()['scheduling.sessions.view'],
                'notifications.send' => PermissionCatalog::all()['notifications.send'],
            ]),
            'profesional' => new Role('profesional', [
                'students.read' => PermissionCatalog::all()['students.read'],
                'scheduling.sessions.view' => PermissionCatalog::all()['scheduling.sessions.view'],
                'care.notes.read' => PermissionCatalog::all()['care.notes.read'],
                'care.notes.write' => PermissionCatalog::all()['care.notes.write'],
            ]),
            'finanzas' => new Role('finanzas', [
                'billing.charges.view' => PermissionCatalog::all()['billing.charges.view'],
                'billing.invoices.manage' => PermissionCatalog::all()['billing.invoices.manage'],
                'documents.manage' => PermissionCatalog::all()['documents.manage'],
            ]),
            'recepcion' => new Role('recepcion', [
                'students.contacts.view' => PermissionCatalog::all()['students.contacts.view'],
                'scheduling.sessions.view' => PermissionCatalog::all()['scheduling.sessions.view'],
                'notifications.send' => PermissionCatalog::all()['notifications.send'],
            ]),
            'admin_tecnico' => new Role('admin_tecnico', [
                'students.read' => PermissionCatalog::all()['students.read'],
                'identity.users.manage' => PermissionCatalog::all()['identity.users.manage'],
                'system.audit.view' => PermissionCatalog::all()['system.audit.view'],
                'system.settings.manage' => PermissionCatalog::all()['system.settings.manage'],
            ]),
        ];
    }
}
