<?php

declare(strict_types=1);

namespace Cai\Students\Import;

final readonly class RowValidation
{
    /**
     * @param  array<string, mixed>  $validated
     */
    private function __construct(
        private bool $valid,
        private array $validated = [],
        private ?string $error = null,
    ) {}

    /**
     * @param  array<string, mixed>  $validated
     */
    public static function accepted(array $validated): self
    {
        return new self(true, $validated);
    }

    public static function rejected(string $reason): self
    {
        return new self(false, [], $reason);
    }

    public function fails(): bool
    {
        return ! $this->valid;
    }

    public function firstError(): string
    {
        return $this->error ?? '';
    }

    /**
     * @return array<string, mixed>
     */
    public function validated(): array
    {
        return $this->validated;
    }
}
