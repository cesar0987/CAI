<?php

declare(strict_types=1);

namespace Cai\Students\Results;

final readonly class ImportResult
{
    /**
     * @param  list<array{row: int, code: ?string, reason: string}>  $rejected
     */
    public function __construct(
        public int $accepted,
        public array $rejected,
        public bool $dryRun,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'accepted' => $this->accepted,
            'rejected' => count($this->rejected),
            'dry_run' => $this->dryRun,
        ];
    }
}
