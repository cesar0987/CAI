<?php

declare(strict_types=1);

namespace Cai\Core\Support;

class Result
{
    protected function __construct(
        protected readonly bool $success,
        protected readonly mixed $value = null,
        protected readonly ?\Throwable $error = null,
    ) {
    }

    public static function success(mixed $value = null): static
    {
        return new static(true, $value);
    }

    public static function failure(\Throwable $error): static
    {
        return new static(false, null, $error);
    }

    public function isSuccess(): bool
    {
        return $this->success;
    }

    public function isFailure(): bool
    {
        return ! $this->success;
    }

    public function value(): mixed
    {
        return $this->value;
    }

    public function error(): ?\Throwable
    {
        return $this->error;
    }

    public function unwrap(): mixed
    {
        if ($this->isFailure()) {
            throw $this->error ?? new \RuntimeException('Result failure without error.');
        }

        return $this->value;
    }

    public function map(callable $mapper): static
    {
        if ($this->isFailure()) {
            return new static(false, null, $this->error);
        }

        return new static(true, $mapper($this->value));
    }

    public function orElse(callable $fallback): mixed
    {
        if ($this->isSuccess()) {
            return $this->value;
        }

        return $fallback($this->error);
    }
}
