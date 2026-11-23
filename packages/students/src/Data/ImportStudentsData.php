<?php

declare(strict_types=1);

namespace Cai\Students\Data;

final readonly class ImportStudentsData
{
    public function __construct(
        public string $filePath,
        public bool $dryRun = true,
        public ?string $reportPath = null,
        public bool $update = false,
    ) {}
}
