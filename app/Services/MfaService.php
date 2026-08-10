<?php

namespace App\Services;

use Exception;

/**
 * MfaService
 *
 * Provides a dependency-light TOTP (Time-based One-Time Password) implementation
 * compatible with standard authenticator apps (Google Authenticator, Authy, etc.).
 *
 * Algorithm:
 *   - HMAC-SHA1 over 8-byte big-endian counter (unix time / 30s interval)
 *   - 6 digit code, extracted per RFC 4226 / RFC 6238
 *   - Base32 secrets using the RFC 4648 alphabet (no padding '=')
 */
class MfaService
{
    /** Number of digits in the generated code. */
    protected const CODE_LENGTH = 6;

    /** TOTP time step in seconds. */
    protected const TIME_STEP = 30;

    /** Allowed clock-drift windows (before/after) in steps to accept. */
    protected const WINDOW = 1;

    /**
     * Generate a new random base32 secret for a user.
     */
    public function generateSecret(): string
    {
        // 20 random bytes => 160 bits of entropy (standard recommendation).
        $bytes = random_bytes(20);
        return $this->base32Encode($bytes);
    }

    /**
     * Build the otpauth:// URI for provisioning into an authenticator app.
     */
    public function provisioningUri(string $secret, string $accountName): string
    {
        $label = rawurlencode($accountName);
        return "otpauth://totp/HIMS%20($label)?secret=$secret&issuer=HIMS&algorithm=SHA1&digits=6&period=30";
    }

    /**
     * Generate the current TOTP code for a given secret.
     */
    public function generateCode(string $secret, int $atTimestamp = null): string
    {
        $timestamp = $atTimestamp ?? time();
        $counter = intdiv($timestamp, self::TIME_STEP);
        return $this->totp($secret, $counter);
    }

    /**
     * Verify a user-supplied code. Accepts a time-window of +/- WINDOW steps
     * to tolerate small clock skew. A constant-time comparison prevents
     * timing attacks.
     */
    public function verifyCode(string $secret, string $code, int $atTimestamp = null): bool
    {
        $timestamp = $atTimestamp ?? time();
        $counter = intdiv($timestamp, self::TIME_STEP);

        for ($offset = -self::WINDOW; $offset <= self::WINDOW; $offset++) {
            $expected = $this->totp($secret, $counter + $offset);
            if ($this->hashEquals($expected, $code)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Compute a TOTP code for a given secret and counter.
     */
    protected function totp(string $secret, int $counter): string
    {
        $decoded = $this->base32Decode($secret);
        if ($decoded === false || $decoded === '') {
            throw new Exception('Invalid MFA secret.');
        }

        // 8-byte big-endian counter per RFC 4226.
        $binary = pack('N*', 0, $counter);

        $hash = hash_hmac('sha1', $binary, $decoded, true);

        // Dynamic truncation (RFC 4226 section 5.3).
        $offset = ord($hash[19]) & 0x0f;
        $value =
            ((ord($hash[$offset]) & 0x7f) << 24) |
            ((ord($hash[$offset + 1]) & 0xff) << 16) |
            ((ord($hash[$offset + 2]) & 0xff) << 8) |
            (ord($hash[$offset + 3]) & 0xff);

        $mod = $value % (10 ** self::CODE_LENGTH);

        return str_pad((string) $mod, self::CODE_LENGTH, '0', STR_PAD_LEFT);
    }

    /**
     * Encode raw bytes to a base32 string (RFC 4648, no padding).
     */
    protected function base32Encode(string $data): string
    {
        $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $result = '';

        $bits = 0;
        $value = 0;
        $length = strlen($data);

        for ($i = 0; $i < $length; $i++) {
            $value = ($value << 8) | ord($data[$i]);
            $bits += 8;

            while ($bits >= 5) {
                $result .= $alphabet[($value >> ($bits - 5)) & 0x1f];
                $bits -= 5;
            }
        }

        if ($bits > 0) {
            $result .= $alphabet[($value << (5 - $bits)) & 0x1f];
        }

        return $result;
    }

    /**
     * Decode a base32 string (case-insensitive, no padding tolerated) to bytes.
     *
     * @return string|false
     */
    protected function base32Decode(string $data): string|false
    {
        $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $data = strtoupper(rtrim($data, '= '));

        $bits = 0;
        $value = 0;
        $result = '';

        $length = strlen($data);
        for ($i = 0; $i < $length; $i++) {
            $char = $data[$i];
            $position = strpos($alphabet, $char);
            if ($position === false) {
                return false;
            }

            $value = ($value << 5) | $position;
            $bits += 5;

            if ($bits >= 8) {
                $result .= chr(($value >> ($bits - 8)) & 0xff);
                $bits -= 8;
            }
        }

        return $result;
    }

    /**
     * Constant-time string comparison.
     */
    protected function hashEquals(string $expected, string $provided): bool
    {
        return hash_equals($expected, $provided);
    }
}

