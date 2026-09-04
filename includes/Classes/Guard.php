<?php

namespace Auth;

class Guard
{
    private $db;
    private $auth;
    private $session;
    private $guest_csrf = null;

    # Constructor
    public function __construct()
    {
        global $db, $_auth, $_session;
        $this->db = $db;
        $this->auth = $_auth;
        $this->session = $_session;
    }

    # Under the limit
    public function throttle($key, $max, $window)
    {
        $since = date('Y-m-d H:i:s', time() - $window);
        $count = $this->db->count('auth_attempts', [
            'identifier' => $key,
            'attempted_at' => ['operator' => '>', 'value' => $since],
        ]);
        return $count < $max;
    }

    # Record attempt
    public function hit($key)
    {
        return (bool) $this->db->insert('auth_attempts', [
            'identifier' => $key,
            'attempted_at' => date('Y-m-d H:i:s'),
        ], ['encodeHtml' => false]);
    }

    # Reset attempts
    public function clear($key)
    {
        if ($key === '' || $key === null) return false;
        return $this->db->delete('auth_attempts', ['identifier' => $key]);
    }

    # Delete old attempts
    public function purge($window = 86400)
    {
        return $this->db->delete('auth_attempts', [
            'attempted_at' => ['operator' => '<', 'value' => date('Y-m-d H:i:s', time() - $window)],
        ]);
    }

    # Csrf token
    public function csrf_token()
    {
        $token = $this->session->csrf();
        if ($token) return $token;
        if ($this->guest_csrf) return $this->guest_csrf;

        if (isset($_COOKIE['csrf']) && preg_match('/^[a-f0-9]{64}$/', $_COOKIE['csrf'])) {
            $this->guest_csrf = $_COOKIE['csrf'];
            return $this->guest_csrf;
        }

        $this->guest_csrf = bin2hex(random_bytes(32));
        if (PHP_SAPI !== 'cli' && !headers_sent()) {
            setcookie('csrf', $this->guest_csrf, [
                'expires' => 0,
                'path' => '/',
                'secure' => ENV !== 'local',
                'httponly' => false,
                'samesite' => 'Lax',
            ]);
        }
        return $this->guest_csrf;
    }

    # Hidden input
    public function csrf_field()
    {
        $t = htmlspecialchars($this->csrf_token(), ENT_QUOTES);
        return "<input type=\"hidden\" name=\"_csrf\" value=\"$t\">";
    }

    # Check csrf
    public function verify_csrf()
    {
        $sent = isset($_POST['_csrf']) ? $_POST['_csrf'] : '';
        if ($sent === '' && isset($_SERVER['HTTP_X_CSRF_TOKEN'])) $sent = $_SERVER['HTTP_X_CSRF_TOKEN'];

        if (!hash_equals((string) $this->csrf_token(), (string) $sent)) {
            http_response_code(403);
            echo error('Your session expired. Reload the page and try again.');
            exit;
        }
    }

    # Require login
    public function require_login()
    {
        if ($this->auth->check()) return;
        $continue = isset($_SERVER['REQUEST_URI']) ? urlencode($_SERVER['REQUEST_URI']) : '';
        redirectTo(url('login?continue=' . $continue));
    }

    # Require guest
    public function require_guest()
    {
        if (!$this->auth->check()) return;
        redirectTo(url('user/dashboard'));
    }

    # Require role
    public function require_role($role)
    {
        $this->require_login();
        if ($this->auth->is($role)) return;
        http_response_code(403);
        errorMsgPage('You do not have access to that page.');
    }
}

$_guard = new Guard();
