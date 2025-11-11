<?php

namespace App\Helpers;

class EpsHelper
{
    /**
     * Generate EPS-compliant HMAC-SHA512 hash (Base64 encoded)
     *
     * @param string $key  EPS provided hash key (from merchant panel)
     * @param string $data Data to hash (e.g., username or merchantTransactionId)
     * @return string Base64 encoded HMAC hash
     */
    public static function generateHash(string $key, string $data): string
    {
        // EPS requires UTF-8 encoded key
        $encodedKey = utf8_encode($key);

        // Generate raw binary HMAC-SHA512
        $hmac = hash_hmac('sha512', $data, $encodedKey, true);

        // Return Base64 encoded string
        return base64_encode($hmac);
    }
}
