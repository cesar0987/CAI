<?php

declare(strict_types=1);

namespace Cai\Core\Events;

abstract class DomainEvent
{
    public function __construct(
        private readonly string $eventName,
        private readonly array $payload = [],
    ) {
    }

    public function eventName(): string
    {
        return $this->eventName;
    }

    public function payload(): array
    {
        return $this->payload;
    }

    public function toArray(): array
    {
        return [
            'event' => $this->eventName,
            'payload' => $this->normalizePayload($this->payload),
        ];
    }

    protected function normalizePayload(array $payload): array
    {
        foreach ($payload as $key => $value) {
            if (is_scalar($value) || $value === null) {
                continue;
            }

            if (is_array($value)) {
                $payload[$key] = $this->normalizePayload($value);
                continue;
            }

            if ($value instanceof \DateTimeInterface) {
                $payload[$key] = $value->format(DATE_ATOM);
                continue;
            }

            if (method_exists($value, '__toString')) {
                $payload[$key] = (string) $value;
                continue;
            }

            $payload[$key] = json_decode(json_encode($value, JSON_THROW_ON_ERROR), true);
        }

        return $payload;
    }
}
