# Runbook del Sistema CAI

> **Estado:** Borrador (se completa en F8)  
> **Versión:** 0.1  
> **Última actualización:** 2026-10-23

## 1. Comandos frecuentes

```bash
# Desarrollo
php artisan serve
php artisan migrate
php artisan queue:work

# Testing
vendor/bin/pest
vendor/bin/pint
vendor/bin/phpstan analyse
vendor/bin/deptrac analyse
```

## 2. Deploy

> Pendiente de definir en F8. Se seguirá el procedimiento de [[ADR-0005 Release por Symlink]].

## 3. Rollback

> Pendiente de definir en F8.

## 4. Backups

> Pendiente de definir en F8. Ver [[06.07 Backups y Recuperación]].

## 5. Verificación

> Pendiente de definir en F8.

## 6. Incidentes

> Pendiente de definir en F8. Ver [[10.02 Gestión de Incidentes]].

## Referencias

- [[ADR-0005 Release por Symlink]]
- [[06.07 Backups y Recuperación]]
- [[07.03 Instalación del Sistema]]
- [[08.08 F8 Despliegue, Operación y Hardening]]
