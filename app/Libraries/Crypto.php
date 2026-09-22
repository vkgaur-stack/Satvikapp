<?php

namespace App\Libraries;

/**
 * Field-level encryption (AES-256 via CodeIgniter's Encryption service / OpenSSL)
 * and keyed hashes for duplicate detection on encrypted columns.
 *
 * The key lives in .env (encryption.key) - generate it with:  php spark key:generate
 */
class Crypto
{
    public static function encrypt(?string $plain): ?string
    {
        if ($plain === null || $plain === '') {
            return null;
        }

        return base64_encode(self::raw()->encrypt($plain));
    }

    public static function decrypt(?string $stored): ?string
    {
        if ($stored === null || $stored === '') {
            return null;
        }
        try {
            return self::raw()->decrypt(base64_decode($stored, true) ?: '');
        } catch (\Throwable $e) {
            log_message('error', 'Decrypt failed: ' . $e->getMessage());

            return null;
        }
    }

    public static function encryptBinary(string $data): string
    {
        return self::raw()->encrypt($data);
    }

    public static function decryptBinary(string $data): string
    {
        return self::raw()->decrypt($data);
    }

    /** Deterministic keyed hash (HMAC-SHA256) of a normalised value, for equality look-ups only. */
    public static function hash(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }
        $norm = strtolower(preg_replace('/[\s\-]+/', '', $value));
        if ($norm === '') {
            return null;
        }

        return hash_hmac('sha256', $norm, (string) config('Encryption')->key);
    }

    private static function raw(): \CodeIgniter\Encryption\EncrypterInterface
    {
        if ((string) config('Encryption')->key === '') {
            throw new \RuntimeException('encryption.key is empty. Run "php spark key:generate" and keep the key safe - losing it makes encrypted data unreadable.');
        }

        return service('encrypter');
    }
}
