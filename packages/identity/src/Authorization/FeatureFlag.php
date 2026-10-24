<?php

declare(strict_types=1);

namespace Cai\Identity\Authorization;

final readonly class FeatureFlag
{
    /**
     * @var array<string>
     */
    private const VALID_SCOPES = ['module', 'feature', 'experiment'];

    public function __construct(
        public string $key,
        public string $scope,
        public string $description = '',
        public bool $enabled = false,
    ) {
        if (trim($key) === '') {
            throw new \InvalidArgumentException('La clave del flag no puede estar vacía.');
        }

        if (! in_array($scope, self::VALID_SCOPES, true)) {
            throw new \InvalidArgumentException('El ámbito del flag no es válido.');
        }
    }

    public function matches(string $flagKey): bool
    {
        return $this->key === trim($flagKey);
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function withEnabled(bool $enabled): self
    {
        return new self($this->key, $this->scope, $this->description, $enabled);
    }
}
