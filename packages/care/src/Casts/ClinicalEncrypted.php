<?php

declare(strict_types=1);

namespace Cai\Care\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use RuntimeException;

/**
 * Cifrado AEAD del contenido clínico (ADR-0006 §2).
 *
 * AES-256-GCM con `CLINICAL_ENCRYPTION_KEY` (base64 de 32 bytes), distinta de
 * APP_KEY. El cast `encrypted` de Laravel NO sirve acá: usa el cipher de
 * config/app.php, que es AES-256-CBC.
 *
 * Formato en BD: base64('v1:' | IV 12 bytes | tag 16 bytes | ciphertext).
 * Falla cerrado si la clave no está configurada o es inválida.
 *
 * @implements CastsAttributes< string|null, string|null>
 */
final class ClinicalEncrypted implements CastsAttributes
{
    private const PREFIX = 'v1:';

    private const IV_LENGTH = 12;

    private const TAG_LENGTH = 16;

    public function get(Model $model, string $key, mixed $value, array $attributes): ?string
    {
        if ($value === null) {
            return null;
        }

        $raw = base64_decode((string) $value, true);
        if ($raw === false || ! str_starts_with($raw, self::PREFIX)) {
            throw new RuntimeException('El contenido clínico tiene un formato desconocido.');
        }

        $payload = substr($raw, strlen(self::PREFIX));
        $iv = substr($payload, 0, self::IV_LENGTH);
        $tag = substr($payload, self::IV_LENGTH, self::TAG_LENGTH);
        $ciphertext = substr($payload, self::IV_LENGTH + self::TAG_LENGTH);

        $plain = openssl_decrypt($ciphertext, 'aes-256-gcm', $this->key(), OPENSSL_RAW_DATA, $iv, $tag);
        if ($plain === false) {
            throw new RuntimeException('No se pudo descifrar el contenido clínico.');
        }

        return $plain;
    }

    public function set(Model $model, string $key, mixed $value, array $attributes): ?string
    {
        if ($value === null) {
            return null;
        }

        $iv = random_bytes(self::IV_LENGTH);
        $tag = '';
        $ciphertext = openssl_encrypt((string) $value, 'aes-256-gcm', $this->key(), OPENSSL_RAW_DATA, $iv, $tag);

        if ($ciphertext === false) {
            throw new RuntimeException('No se pudo cifrar el contenido clínico.');
        }

        return base64_encode(self::PREFIX.$iv.$tag.$ciphertext);
    }

    private function key(): string
    {
        $configured = config('cai.seguridad.clinical_encryption_key');

        if (! is_string($configured) || $configured === '') {
            throw new RuntimeException('Falta CLINICAL_ENCRYPTION_KEY: el contenido clínico no se puede cifrar.');
        }

        $key = base64_decode($configured, true);
        if ($key === false || strlen($key) !== 32) {
            throw new RuntimeException('CLINICAL_ENCRYPTION_KEY debe ser base64 de 32 bytes.');
        }

        return $key;
    }
}
