<?php
declare(strict_types=1);

/**
 * ShahkotPK Global Security Middleware
 * Handles global input sanitization and basic WAF protections.
 */

class Security {
    public static function sanitizeGlobalInputs(): void {
        $_GET = self::sanitizeArray($_GET);
        $_POST = self::sanitizeArray($_POST);
        $_COOKIE = self::sanitizeArray($_COOKIE);
        $_REQUEST = self::sanitizeArray($_REQUEST);
    }

    private static function sanitizeArray(array $data): array {
        $clean = [];
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $clean[$key] = self::sanitizeArray($value);
            } else {
                $clean[$key] = self::sanitizeString((string)$value);
            }
        }
        return $clean;
    }

    private static function sanitizeString(string $val): string {
        // Strip out dangerous XSS payloads (Basic WAF)
        $val = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', '', $val);
        $val = preg_replace('/on[a-zA-Z]+\s*=\s*"[^"]*"/is', '', $val);
        $val = preg_replace('/on[a-zA-Z]+\s*=\s*\'[^\']*\'/is', '', $val);
        return $val;
    }

    public static function checkRateLimit(string $action, int $maxRequests = 60, int $timeWindow = 60): bool {
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $cacheFile = sys_get_temp_dir() . '/rate_limit_' . md5($ip . '_' . $action) . '.json';
        
        $requests = [];
        if (file_exists($cacheFile)) {
            $requests = json_decode(file_get_contents($cacheFile), true) ?: [];
        }

        $now = time();
        $requests = array_filter($requests, fn($timestamp) => $timestamp > ($now - $timeWindow));
        
        if (count($requests) >= $maxRequests) {
            return false; // Rate limit exceeded
        }

        $requests[] = $now;
        file_put_contents($cacheFile, json_encode($requests));
        return true;
    }
}

// Automatically sanitize all incoming requests
Security::sanitizeGlobalInputs();
