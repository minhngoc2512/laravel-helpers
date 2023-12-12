<?php

namespace Ngocnm\LaravelHelpers;

class Helper
{

    static function encodeOpenSsl(string $string, $key = null): string
    {
        $ivSize = openssl_cipher_iv_length('AES-256-CBC');
        $iv = openssl_random_pseudo_bytes($ivSize);
        $encrypted = openssl_encrypt($string, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);
        $encoded = base64_encode($iv . $encrypted);
        return $encoded;
    }

    static function decodeOpenSsl(string $string, $key = null): string
    {
        $decoded = base64_decode($string);
        $ivSize = openssl_cipher_iv_length('AES-256-CBC');
        $iv = substr($decoded, 0, $ivSize);
        $encrypted = substr($decoded, $ivSize);
        $decrypted = openssl_decrypt($encrypted, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);
        return $decrypted;
    }

    static function BaseApiRequest()
    {
        return RequestHelper::getInstance();
    }
}
