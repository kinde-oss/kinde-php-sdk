<?php

namespace Kinde\KindeSDK\Sdk\Utils;

use InvalidArgumentException;
use Kinde\KindeSDK\Sdk\Enums\AcrValues;

/**
 * Helpers for RFC 9470 step-up authentication.
 */
class StepUp
{
    // phr meets an mfa requirement
    private const RANK = [
        AcrValues::MFA => 1,
        AcrValues::PHR => 2,
    ];

    // Values from other providers that Kinde reads as mfa
    private const ALIASES = [
        'http://schemas.openid.net/pape/policies/2007/06/multi-factor' => AcrValues::MFA,
        'urn:okta:loa:2fa:any' => AcrValues::MFA,
    ];

    /**
     * Normalizes acr_values to a space-separated string.
     *
     * @param string|string[] $acrValues A space-separated string or an array of strings.
     *
     *
     * @throws InvalidArgumentException If the value is empty or not a string or array of strings.
     * @return string The normalized acr_values.
     */
    public static function normalizeAcrValues($acrValues): string
    {
        if (is_array($acrValues)) {
            foreach ($acrValues as $value) {
                if (!is_string($value)) {
                    throw new InvalidArgumentException('Please supply a valid acr_values. Expected: string or array of strings');
                }
            }
            $acrValues = implode(' ', $acrValues);
        }

        if (!is_string($acrValues)) {
            throw new InvalidArgumentException('Please supply a valid acr_values. Expected: string or array of strings');
        }

        $parts = preg_split('/\s+/', trim($acrValues), -1, PREG_SPLIT_NO_EMPTY);
        if (empty($parts)) {
            throw new InvalidArgumentException('Please supply a valid acr_values. It must not be empty');
        }

        return implode(' ', $parts);
    }

    /**
     * Normalizes max_age to a string.
     *
     * @param int|string $maxAge A non-negative whole number of seconds.
     *
     *
     * @throws InvalidArgumentException If the value is not a non-negative whole number.
     * @return string The normalized max_age.
     */
    public static function normalizeMaxAge($maxAge): string
    {
        if (is_int($maxAge) && $maxAge >= 0) {
            return (string) $maxAge;
        }
        if (is_string($maxAge) && preg_match('/^\d+$/', trim($maxAge))) {
            return (string) (int) trim($maxAge);
        }

        throw new InvalidArgumentException('Please supply a valid max_age. Expected: a non-negative whole number of seconds');
    }

    /**
     * Retrieves the strongest recognized context requested (case-insensitive).
     *
     * @param string|string[]|null $acrValues The requested context(s).
     *
     *
     * @throws InvalidArgumentException If values are given but none is recognized.
     * @return string|null mfa, phr, or null when nothing is requested.
     */
    public static function requiredAcr($acrValues): ?string
    {
        if ($acrValues === null || $acrValues === '' || $acrValues === []) {
            return null;
        }

        $best = null;
        foreach (explode(' ', strtolower(self::normalizeAcrValues($acrValues))) as $value) {
            $value = self::ALIASES[$value] ?? $value;
            if (isset(self::RANK[$value]) && ($best === null || self::RANK[$value] > self::RANK[$best])) {
                $best = $value;
            }
        }

        if ($best === null) {
            throw new InvalidArgumentException('Please supply a valid acr_values. Expected: mfa or phr');
        }

        return $best;
    }

    /**
     * Checks if an acr claim meets the required context. An absent acr meets no requirement.
     *
     * @param mixed                $acr       The acr claim (null if absent).
     * @param string|string[]|null $acrValues The required context(s).
     *
     * @return bool True if the requirement is met
     */
    private static function meetsAcr($acr, $acrValues): bool
    {
        $required = self::requiredAcr($acrValues);
        if ($required === null) {
            return true;
        }
        if (!is_string($acr)) {
            return false;
        }

        $rank = self::RANK[strtolower($acr)] ?? 0;
        return $rank >= self::RANK[$required];
    }

    /**
     * Checks if the last authentication is within maxAge seconds.
     *
     * @param mixed    $authTime The auth_time claim (null if absent).
     * @param int      $maxAge   Maximum age in seconds.
     * @param int|null $now      Current time (optional).
     *
     * @return bool True if the authentication is recent enough
     */
    private static function isRecent($authTime, int $maxAge, ?int $now = null): bool
    {
        if (!is_numeric($authTime)) {
            return false;
        }

        $now = $now ?? time();
        $age = $now - (int) $authTime;
        // A future auth_time is invalid, not "very recent" (token verification already rejects a future iat)
        return $age >= 0 && $age <= $maxAge;
    }

    /**
     * Checks if verified token claims meet the required context and/or recency.
     *
     * @param array                $claims    Decoded claims of a verified token.
     * @param string|string[]|null $acrValues The required context(s), or null.
     * @param int|null             $maxAge    Maximum age in seconds, or null.
     * @param int|null             $now       Current time (optional).
     *
     * @return bool True if the claims meet the requirement
     */
    public static function satisfies(array $claims, $acrValues = null, ?int $maxAge = null, ?int $now = null): bool
    {
        if (!self::meetsAcr($claims['acr'] ?? null, $acrValues)) {
            return false;
        }
        if ($maxAge !== null && !self::isRecent($claims['auth_time'] ?? null, $maxAge, $now)) {
            return false;
        }
        return true;
    }
}
