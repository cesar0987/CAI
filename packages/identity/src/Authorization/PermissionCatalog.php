<?php

declare(strict_types=1);

namespace Cai\Identity\Authorization;

/**
 * Catálogo canónico de permisos — 04.02 §2 (matriz maestra).
 *
 * Formato `<módulo>.<recurso>.<acción>` (03.01 §4). La marca `sensitive`
 * sale de 04.02 §5. Los permisos condicionados (`*` en la matriz) se
 * conceden al rol como puerta de entrada; la condición concreta la
 * verifican las policies y scopes (04.03), no este catálogo.
 *
 * Reemplaza al catálogo simplificado de 16 permisos de F1 (2026-11-19,
 * F2 E-03): los nombres viejos (students.read, care.notes.read, …) no
 * existen en la matriz y se retiran sin alias porque ningún consumidor
 * publicado los usa todavía.
 */
final class PermissionCatalog
{
    /**
     * @return array<string, Permission>
     */
    public static function all(): array
    {
        return [
            // IDENTIDAD (04.02 §2)
            'identity.users.view' => new Permission('identity.users.view', 'identity', false, 'Ver usuarios del sistema'),
            'identity.users.manage' => new Permission('identity.users.manage', 'identity', true, 'Crear, editar y desactivar usuarios'),
            'identity.roles.manage' => new Permission('identity.roles.manage', 'identity', true, 'Gestionar roles y asignaciones'),
            'identity.permissions.view' => new Permission('identity.permissions.view', 'identity', false, 'Ver el catálogo de permisos'),
            'identity.audit.view' => new Permission('identity.audit.view', 'identity', false, 'Ver la auditoría del sistema'),
            'identity.security_events.view' => new Permission('identity.security_events.view', 'identity', false, 'Ver eventos de seguridad'),
            'identity.settings.manage' => new Permission('identity.settings.manage', 'identity', false, 'Configurar ajustes del centro'),
            'identity.flags.manage' => new Permission('identity.flags.manage', 'identity', true, 'Activar y apagar feature flags'),

            // ALUMNOS
            'students.students.view' => new Permission('students.students.view', 'students', false, 'Ver alumnos y datos básicos'),
            'students.students.create' => new Permission('students.students.create', 'students', false, 'Dar de alta alumnos'),
            'students.students.update' => new Permission('students.students.update', 'students', false, 'Editar datos de alumnos'),
            'students.students.deactivate' => new Permission('students.students.deactivate', 'students', false, 'Dar de baja alumnos'),
            'students.guardians.manage' => new Permission('students.guardians.manage', 'students', false, 'Gestionar tutores y contactos'),
            'students.family_accounts.view' => new Permission('students.family_accounts.view', 'students', false, 'Ver cuentas familiares'),
            'students.family_accounts.manage' => new Permission('students.family_accounts.manage', 'students', true, 'Gestionar cuentas familiares'),
            'students.authorized_pickups.manage' => new Permission('students.authorized_pickups.manage', 'students', false, 'Gestionar personas autorizadas a retirar'),

            // SERVICIOS Y AGENDA
            'scheduling.services.manage' => new Permission('scheduling.services.manage', 'scheduling', false, 'Gestionar servicios'),
            'scheduling.services.view' => new Permission('scheduling.services.view', 'scheduling', false, 'Ver servicios'),
            'scheduling.professionals.manage' => new Permission('scheduling.professionals.manage', 'scheduling', false, 'Gestionar profesionales'),
            'scheduling.enrollments.manage' => new Permission('scheduling.enrollments.manage', 'scheduling', false, 'Gestionar inscripciones'),
            'scheduling.enrollments.view' => new Permission('scheduling.enrollments.view', 'scheduling', false, 'Ver inscripciones'),
            'scheduling.groups.manage' => new Permission('scheduling.groups.manage', 'scheduling', false, 'Gestionar grupos'),
            'scheduling.sessions.view' => new Permission('scheduling.sessions.view', 'scheduling', false, 'Ver agenda y sesiones'),
            'scheduling.sessions.create' => new Permission('scheduling.sessions.create', 'scheduling', false, 'Crear sesiones'),
            'scheduling.sessions.update' => new Permission('scheduling.sessions.update', 'scheduling', false, 'Editar sesiones'),
            'scheduling.sessions.cancel' => new Permission('scheduling.sessions.cancel', 'scheduling', false, 'Cancelar sesiones'),
            'scheduling.attendance.record' => new Permission('scheduling.attendance.record', 'scheduling', false, 'Registrar asistencia'),
            'scheduling.attendance.correct' => new Permission('scheduling.attendance.correct', 'scheduling', false, 'Corregir asistencia'),

            // ÁREA CLÍNICA
            'care.clinical.read' => new Permission('care.clinical.read', 'care', true, 'Leer el registro clínico (solo por grant o rol clínico)'),
            'care.plans.view' => new Permission('care.plans.view', 'care', false, 'Ver planes de atención'),
            'care.plans.manage' => new Permission('care.plans.manage', 'care', false, 'Gestionar planes de atención'),
            'care.goals.manage' => new Permission('care.goals.manage', 'care', false, 'Gestionar objetivos de los planes'),
            'care.notes.view' => new Permission('care.notes.view', 'care', true, 'Ver notas clínicas'),
            'care.notes.create' => new Permission('care.notes.create', 'care', true, 'Crear notas clínicas'),
            'care.notes.finalize' => new Permission('care.notes.finalize', 'care', true, 'Finalizar notas clínicas'),
            'care.notes.supersede' => new Permission('care.notes.supersede', 'care', false, 'Sustituir notas clínicas'),
            'care.documents.view' => new Permission('care.documents.view', 'care', true, 'Ver documentos clínicos'),
            'care.documents.upload' => new Permission('care.documents.upload', 'care', false, 'Subir documentos clínicos'),
            'care.grants.issue' => new Permission('care.grants.issue', 'care', true, 'Conceder grants de acceso clínico excepcional'),
            'care.grants.revoke' => new Permission('care.grants.revoke', 'care', false, 'Revocar grants de acceso clínico'),

            // FINANZAS
            'billing.charges.view' => new Permission('billing.charges.view', 'billing', false, 'Ver cargos'),
            'billing.charges.create' => new Permission('billing.charges.create', 'billing', false, 'Crear cargos'),
            'billing.charges.void' => new Permission('billing.charges.void', 'billing', false, 'Anular cargos'),
            'billing.charges.adjust' => new Permission('billing.charges.adjust', 'billing', true, 'Ajustar cargos (requiere autorización de Dirección)'),
            'billing.payments.view' => new Permission('billing.payments.view', 'billing', false, 'Ver pagos'),
            'billing.payments.create' => new Permission('billing.payments.create', 'billing', true, 'Registrar pagos'),
            'billing.payments.void' => new Permission('billing.payments.void', 'billing', true, 'Anular pagos'),
            'billing.allocations.manage' => new Permission('billing.allocations.manage', 'billing', false, 'Gestionar imputaciones de pagos'),
            'billing.receipts.view' => new Permission('billing.receipts.view', 'billing', true, 'Ver recibos'),
            'billing.receipts.void' => new Permission('billing.receipts.void', 'billing', false, 'Anular recibos'),
            'billing.cash.open' => new Permission('billing.cash.open', 'billing', false, 'Abrir caja'),
            'billing.cash.close' => new Permission('billing.cash.close', 'billing', false, 'Cerrar caja'),
            'billing.reports.view' => new Permission('billing.reports.view', 'billing', true, 'Ver reportes financieros'),
            'billing.export' => new Permission('billing.export', 'billing', true, 'Exportar datos financieros'),

            // DOCUMENTOS
            'documents.documents.view' => new Permission('documents.documents.view', 'documents', false, 'Ver documentos'),
            'documents.documents.upload' => new Permission('documents.documents.upload', 'documents', false, 'Subir documentos'),
            'documents.documents.download' => new Permission('documents.documents.download', 'documents', false, 'Descargar documentos'),
            'documents.consents.manage' => new Permission('documents.consents.manage', 'documents', false, 'Gestionar consentimientos'),
            'documents.consents.view' => new Permission('documents.consents.view', 'documents', false, 'Ver consentimientos'),

            // NOTIFICACIONES
            'notifications.messages.send' => new Permission('notifications.messages.send', 'notifications', false, 'Enviar mensajes a familias'),
            'notifications.templates.manage' => new Permission('notifications.templates.manage', 'notifications', false, 'Gestionar plantillas de mensajes'),

            // REPORTES
            'reporting.reports.view' => new Permission('reporting.reports.view', 'reporting', false, 'Ver reportes operativos'),
            'reporting.reports.export' => new Permission('reporting.reports.export', 'reporting', false, 'Exportar reportes operativos'),
            'reporting.financial.view' => new Permission('reporting.financial.view', 'reporting', false, 'Ver reportes financieros agregados'),
            'reporting.clinical_summary.view' => new Permission('reporting.clinical_summary.view', 'reporting', false, 'Ver indicadores clínicos agregados'),
        ];
    }

    /**
     * Permisos sensibles según 04.02 §5 (revisión trimestral de Dirección).
     *
     * @return array<string, Permission>
     */
    public static function sensitive(): array
    {
        return array_filter(self::all(), fn (Permission $permission): bool => $permission->sensitive);
    }
}
