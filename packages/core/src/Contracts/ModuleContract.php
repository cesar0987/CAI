<?php

declare(strict_types=1);

namespace Cai\Core\Contracts;

interface ModuleContract
{
    public function moduleName(): string;

    public function moduleVersion(): string;

    public function description(): string;
}
