<?php

declare(strict_types=1);

namespace Cai\Identity\Authorization;

final class PermissionCatalog
{
    /**
     * @return array<string, Permission>
     */
    public static function all(): array
    {
        return [
            'students.read' => new Permission('students.read', 'students', false, 'Ver alumnos y datos básicos'),
            'students.write' => new Permission('students.write', 'students', false, 'Crear y editar alumnos'),
            'students.contacts.view' => new Permission('students.contacts.view', 'students', false, 'Ver datos de contacto de alumnos'),
            'students.contacts.manage' => new Permission('students.contacts.manage', 'students', false, 'Gestionar contactos de alumnos'),
            'scheduling.sessions.manage' => new Permission('scheduling.sessions.manage', 'scheduling', false, 'Gestionar sesiones'),
            'scheduling.sessions.view' => new Permission('scheduling.sessions.view', 'scheduling', false, 'Ver agenda y sesiones'),
            'care.notes.read' => new Permission('care.notes.read', 'care', true, 'Leer notas clínicas'),
            'care.notes.write' => new Permission('care.notes.write', 'care', true, 'Escribir notas clínicas'),
            'billing.invoices.manage' => new Permission('billing.invoices.manage', 'billing', false, 'Gestionar facturas'),
            'billing.charges.view' => new Permission('billing.charges.view', 'billing', false, 'Ver cargos y pagos'),
            'documents.manage' => new Permission('documents.manage', 'documents', false, 'Gestionar documentos'),
            'notifications.send' => new Permission('notifications.send', 'notifications', false, 'Enviar notificaciones'),
            'identity.users.manage' => new Permission('identity.users.manage', 'identity', false, 'Gestionar usuarios'),
            'identity.roles.manage' => new Permission('identity.roles.manage', 'identity', false, 'Gestionar roles y permisos'),
            'system.audit.view' => new Permission('system.audit.view', 'system', true, 'Ver auditoría del sistema'),
            'system.settings.manage' => new Permission('system.settings.manage', 'system', false, 'Configurar ajustes del sistema'),
        ];
    }
}
