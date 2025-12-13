<?php

declare(strict_types=1);

namespace ConduitUI\Issue\Security;

class TokenSanitizer
{
    private const PATTERNS = [
        // GitHub tokens (classic and fine-grained)
        '/ghp_[a-zA-Z0-9]{36}/',
        '/gho_[a-zA-Z0-9]{36}/',
        '/ghu_[a-zA-Z0-9]{36}/',
        '/ghs_[a-zA-Z0-9]{36}/',
        '/ghr_[a-zA-Z0-9]{36}/',
        '/github_pat_[a-zA-Z0-9]{22}_[a-zA-Z0-9]{59}/',
        // Bearer tokens
        '/Bearer\s+[a-zA-Z0-9\-_.~+\/]+=*/',
        // Generic API keys (40+ char hex)
        '/[a-fA-F0-9]{40,}/',
    ];

    public static function sanitize(string $content): string
    {
        $result = $content;

        foreach (self::PATTERNS as $pattern) {
            $sanitized = preg_replace_callback($pattern, function ($matches) {
                $length = strlen($matches[0]);

                return str_repeat('*', $length);
            }, $result);

            if ($sanitized !== null) {
                $result = $sanitized;
            }
        }

        return $result;
    }

    public static function sanitizeArray(array $data): array
    {
        $result = [];

        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $result[$key] = self::sanitizeArray($value);
            } elseif (is_string($value)) {
                $result[$key] = self::sanitize($value);
            } else {
                $result[$key] = $value;
            }
        }

        return $result;
    }

    public static function sanitizeHeaders(array $headers): array
    {
        $sensitiveHeaders = ['authorization', 'x-github-token', 'x-access-token'];

        $result = [];

        foreach ($headers as $name => $value) {
            $lowerName = strtolower($name);

            if (in_array($lowerName, $sensitiveHeaders, true)) {
                $result[$name] = '[REDACTED]';
            } elseif (is_array($value)) {
                $result[$name] = self::sanitizeArray($value);
            } elseif (is_string($value)) {
                $result[$name] = self::sanitize($value);
            } else {
                $result[$name] = $value;
            }
        }

        return $result;
    }
}
