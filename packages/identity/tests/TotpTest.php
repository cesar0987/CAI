<?php

declare(strict_types=1);

use Cai\Identity\Mfa\Totp;

/**
 * Vectores de prueba del RFC 6238 (apéndice B, SHA1, 8 dígitos).
 * Secreto ASCII '12345678901234567890' = base32 GEZDGNBVGY3TQOJQGEZDGNBVGY3TQOJQ.
 */
const RFC_SECRET = 'GEZDGNBVGY3TQOJQGEZDGNBVGY3TQOJQ';

it('matches the RFC 6238 test vectors (SHA1, 8 digits)', function (int $timestamp, string $expected) {
    expect(Totp::code(RFC_SECRET, $timestamp, 8))->toBe($expected);
})->with([
    [59, '94287082'],
    [1111111109, '07081804'],
    [1111111111, '14050471'],
    [1234567890, '89005924'],
    [2000000000, '69279037'],
    [20000000000, '65353130'],
]);

it('round-trips base32 encoding', function () {
    $binary = random_bytes(20);

    expect(Totp::base32Decode(Totp::base32Encode($binary)))->toBe($binary);
});

it('rejects invalid base32 secrets', function () {
    Totp::base32Decode('no-es-base32!');
})->throws(InvalidArgumentException::class);

it('generates 160-bit secrets in base32', function () {
    $secret = Totp::generateSecret();

    expect(strlen($secret))->toBe(32)
        ->and(strlen(Totp::base32Decode($secret)))->toBe(20)
        ->and(Totp::generateSecret())->not->toBe($secret);
});

it('verifies the current code and the adjacent window', function () {
    $now = 1111111111;

    expect(Totp::verify(RFC_SECRET, Totp::code(RFC_SECRET, $now), $now))->toBeTrue()
        ->and(Totp::verify(RFC_SECRET, Totp::code(RFC_SECRET, $now - 30), $now))->toBeTrue()
        ->and(Totp::verify(RFC_SECRET, Totp::code(RFC_SECRET, $now + 30), $now))->toBeTrue();
});

it('rejects codes outside the window and malformed codes', function () {
    $now = 1111111111;

    expect(Totp::verify(RFC_SECRET, Totp::code(RFC_SECRET, $now - 90), $now))->toBeFalse()
        ->and(Totp::verify(RFC_SECRET, '000000', $now))->toBeFalse()
        ->and(Totp::verify(RFC_SECRET, '12345', $now))->toBeFalse()
        ->and(Totp::verify(RFC_SECRET, 'abcdef', $now))->toBeFalse()
        ->and(Totp::verify(RFC_SECRET, '', $now))->toBeFalse();
});

it('builds an otpauth provisioning uri', function () {
    $uri = Totp::provisioningUri(RFC_SECRET, 'direccion@cai.local');

    expect($uri)->toBe(
        'otpauth://totp/CAI:direccion%40cai.local?secret='.RFC_SECRET.'&issuer=CAI&algorithm=SHA1&digits=6&period=30'
    );
});
