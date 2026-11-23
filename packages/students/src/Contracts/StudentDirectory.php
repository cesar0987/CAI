<?php

declare(strict_types=1);

namespace Cai\Students\Contracts;

/**
 * Frontera pública del módulo students (08.05 §2.4): los consumidores
 * reciben DTOs, nunca modelos Eloquent ni sus eventos.
 */
interface StudentDirectory
{
    public function find(int $id): ?StudentSummary;

    /**
     * @since 1.0.0
     */
    public function findByCode(string $code): ?StudentSummary;

    /**
     * @return list<StudentSummary>
     */
    public function findByDocument(string $documentId): array;
}
