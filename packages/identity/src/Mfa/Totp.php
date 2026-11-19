<?php

declare(strict_types=1);

namespace Cai\Identity\Mfa;

/**
 * TOTP — RFC 6238 (HMAC-SHA1), el método de MFA del diseño (04.01 §6:
 * app autenticadora; SMS no se usa). Implementación pura y sin dependencias
 * externas, verificable contra los vectores de prueba del RFC.
 */
final class Totp
{
    private const BASE32_ALPHABET = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';

    /**
     * Secreto aleatorio de 160 bits (recomendación RFC 4226 §4) en base32.
     */
    public static function generateSecret(): string
    {
        return self::base32Encode(random_bytes(20));
    }

    /**
     * URI de aprovisionamiento para la app autenticadora (formato otpauth).
     */
    public static function provisioningUri(string $secret, string $accountName, string $issuer = 'CAI'): string
    {
        return sprintf(
            'otpauth://totp/%s:%s?secret=%s&issuer=%s&algorithm=SHA1&digits=6&period=30',
            rawurlencode($issuer),
            rawurlencode($accountName),
            $secret,
            rawurlencode($issuer),
        );
    }

    /**
     * Código TOTP de un instante dado (por defecto, ahora).
     */
    public static function code(string $secret, ?int $timestamp = null, int $digits = 6, int $period = 30): string
    {
        $timestamp ??= time();
        $counter = intdiv($timestamp, $period);

        $binary = hash_hmac('sha1', pack('J', $counter), self::base32Decode($secret), true);
        $offset = ord($binary[19]) & 0x0F;
        $value = (
            ((ord($binary[$offset]) & 0x7F) << 24)
            | ((ord($binary[$offset + 1]) & 0xFF) << 16)
            | ((ord($binary[$offset + 2]) & 0xFF) << 8)
            | (ord($binary[$offset + 3]) & 0xFF)
        ) % (10 ** $digits);

        return str_pad((string) $value, $digits, '0', STR_PAD_LEFT);
    }

    /**
     * Verifica un código con ventana de ±$window períodos (tolerancia de
     * reloj). Comparación en tiempo constante; el código debe ser numérico
     * y de largo exacto para no filtrar información por tiempo.
     */
    public static function verify(string $secret, string $code, ?int $timestamp = null, int $window = 1, int $digits = 6, int $period = 30): bool
    {
        if (preg_match('/^\d{'.$digits.'}$/', $code) !== 1) {
            return false;
        }

        $timestamp ??= time();

        for ($offset = -$window; $offset <= $window; $offset++) {
            if (hash_equals(self::code($secret, $timestamp + $offset * $period, $digits, $period), $code)) {
                return true;
            }
        }

        return false;
    }

    public static function base32Encode(string $binary): string
    {
        $bits = '';
        foreach (str_split($binary) as $char) {
            $bits .= str_pad(decbin(ord($char)), 8, '0', STR_PAD_LEFT);
        }

        $encoded = '';
        foreach (str_split($bits, 5) as $chunk) {
            $encoded .= self::BASE32_ALPHABET[bindec(str_pad($chunk, 5, '0'))];
        }

        return $encoded;
    }

    public static function base32Decode(string $encoded): string
    {
        $encoded = strtoupper(trim($encoded));

        if ($encoded === '' || preg_match('/[^A-Z2-7]/', $encoded) === 1) {
            throw new \InvalidArgumentException('El secreto base32 no es válido.');
        }

        $bits = '';
        foreach (str_split($encoded) as $char) {
            $bits .= str_pad(decbin(strpos(self::BASE32_ALPHABET, $char)), 5, '0', STR_PAD_LEFT);
        }

        $binary = '';
        foreach (str_split($bits, 8) as $chunk) {
            if (strlen($chunk) === 8) {
                $binary .= chr(bindec($chunk));
            }
        }

        return $binary;
    }
}
