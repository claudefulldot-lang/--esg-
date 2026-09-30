<?php
namespace App\Helpers;

class Security {
    public static function startSession(): void {
        if (session_status() !== PHP_SESSION_NONE) {
            return;
        }

        $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
        $cookiePath = defined('BASE_URL') ? (BASE_URL ?: '/') : '/';
        session_name('ESGSESSID');
        session_set_cookie_params([
            'lifetime' => 0,
            'path' => $cookiePath,
            'secure' => $secure,
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
        ini_set('session.use_strict_mode', '1');
        ini_set('session.use_only_cookies', '1');
        session_start();
    }

    public static function sendSecurityHeaders(): void {
        if (headers_sent()) return;
        header_remove('X-Powered-By');
        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: DENY');
        header('Referrer-Policy: strict-origin-when-cross-origin');
        header('Permissions-Policy: camera=(), microphone=(), geolocation=()');
        header("Content-Security-Policy: default-src 'self'; base-uri 'self'; form-action 'self'; frame-ancestors 'none'; object-src 'none'; img-src 'self' data:; font-src 'self' https://fonts.gstatic.com https://cdnjs.cloudflare.com; style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://cdn.datatables.net https://cdnjs.cloudflare.com https://fonts.googleapis.com; script-src 'self' 'unsafe-inline' https://code.jquery.com https://cdn.jsdelivr.net https://cdn.datatables.net; connect-src 'self' https://cdn.datatables.net");
        if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
            header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
        }
    }
    /**
     * Escape HTML output to prevent XSS
     */
    public static function e(?string $string): string {
        return htmlspecialchars((string)$string, ENT_QUOTES, 'UTF-8');
    }

    /**
     * Generate or get current CSRF token
     */
    public static function getCsrfToken(): string {
        self::startSession();
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    /**
     * Render hidden CSRF input field
     */
    public static function csrfField(): string {
        $token = self::getCsrfToken();
        return '<input type="hidden" name="csrf_token" value="' . $token . '">';
    }

    /**
     * Validate CSRF token from POST or headers
     */
    public static function validateCsrf(): bool {
        self::startSession();
        $submitted = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        $sessionToken = $_SESSION['csrf_token'] ?? '';
        if (empty($submitted) || empty($sessionToken)) {
            return false;
        }
        return hash_equals($sessionToken, $submitted);
    }

    /**
     * Return JSON response and exit
     */
    public static function jsonResponse(array $data, int $status = 200): void {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
        exit;
    }

    public static function jsonForHtml(mixed $data): string {
        return json_encode($data, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_THROW_ON_ERROR);
    }

    /**
     * Get client IP address
     */
    public static function getClientIp(): string {
        $remote = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        $trustedProxies = array_filter(array_map('trim', explode(',', getenv('ESG_TRUSTED_PROXIES') ?: '')));
        if (in_array($remote, $trustedProxies, true) && !empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            $forwarded = trim($ips[0]);
            return filter_var($forwarded, FILTER_VALIDATE_IP) ? $forwarded : $remote;
        }
        return filter_var($remote, FILTER_VALIDATE_IP) ? $remote : '127.0.0.1';
    }
}
