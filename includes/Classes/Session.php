<?php

namespace Auth;

class Session
{
    private $db;
    private $row = null;
    private $resolved = false;

    # Constructor
    public function __construct()
    {
        global $db;
        $this->db = $db;
    }

    # Cookie name
    public function cookie_name()
    {
        return ENV === 'local' ? 'sid' : '__Host-sid';
    }

    # Remember cookie name
    public function remember_name()
    {
        return ENV === 'local' ? 'rmb' : '__Host-rmb';
    }

    # Clear cache
    public function reset()
    {
        $this->row = null;
        $this->resolved = false;
    }

    # Read raw token
    private function raw_token()
    {
        $name = $this->cookie_name();
        if (isset($_COOKIE[$name])) return $_COOKIE[$name];
        $header = isset($_SERVER['HTTP_AUTHORIZATION']) ? $_SERVER['HTTP_AUTHORIZATION'] : '';
        if (stripos($header, 'Bearer ') === 0) return substr($header, 7);
        return null;
    }

    # Resolve session
    public function resolve()
    {
        if ($this->resolved) return $this->row;
        $this->resolved = true;

        $raw = $this->raw_token();
        if ($raw && preg_match('/^[a-f0-9]{64}$/', $raw)) {
            $hash = hash('sha256', $raw);
            $rows = $this->db->query("SELECT * FROM `sessions` WHERE `token_hash` = '$hash' AND `revoked_at` IS NULL LIMIT 1", ['select_query' => true]);
            $row = count($rows) ? $rows[0] : null;

            if ($row) {
                $now = time();
                $expired = $now > strtotime($row['expires_at']);
                $idle = ($now - strtotime($row['last_seen_at'])) > AUTH_SESSION_IDLE;

                if (!$expired && !$idle) {
                    $this->db->update('sessions', ['last_seen_at' => date('Y-m-d H:i:s')], ['id' => $row['id']]);
                    $this->row = $row;
                    return $row;
                }
                $this->revoke($row['id']);
            }
        }

        # Fall back to a remembered device
        $this->row = $this->resume_remembered();
        $this->resolved = true;
        return $this->row;
    }

    # Restore from a remember cookie
    private function resume_remembered()
    {
        $name = $this->remember_name();
        $raw = isset($_COOKIE[$name]) ? $_COOKIE[$name] : null;
        if (!$raw || !preg_match('/^[a-f0-9]{64}$/', $raw)) return null;

        $hash = hash('sha256', $raw);
        $now = date('Y-m-d H:i:s');
        $rows = $this->db->query("SELECT * FROM `sessions` WHERE `remember_hash` = '$hash' AND `revoked_at` IS NULL AND `remember_expires_at` > '$now' LIMIT 1", ['select_query' => true]);
        if (!count($rows)) {
            $this->clear_remember_cookie();
            return null;
        }
        $old = $rows[0];

        # One use per token, then issue a fresh pair
        $this->revoke($old['id']);
        $new = $this->create($old['user_id'], true);
        if (!$new) return null;

        $rows = $this->db->query("SELECT * FROM `sessions` WHERE `token_hash` = '" . hash('sha256', $new) . "' LIMIT 1", ['select_query' => true]);
        return count($rows) ? $rows[0] : null;
    }

    # Current session
    public function current()
    {
        return $this->resolve();
    }

    # Create session
    public function create($user_id, $remember = false)
    {
        $raw = bin2hex(random_bytes(32));
        $data = [
            'user_id' => $user_id,
            'token_hash' => hash('sha256', $raw),
            'csrf_token' => bin2hex(random_bytes(32)),
            'ip' => isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : null,
            'user_agent' => isset($_SERVER['HTTP_USER_AGENT']) ? substr($_SERVER['HTTP_USER_AGENT'], 0, 255) : null,
            'created_at' => date('Y-m-d H:i:s'),
            'last_seen_at' => date('Y-m-d H:i:s'),
            'expires_at' => date('Y-m-d H:i:s', time() + AUTH_SESSION_ABSOLUTE),
        ];

        $remember_raw = null;
        if ($remember) {
            $remember_raw = bin2hex(random_bytes(32));
            $data['remember_hash'] = hash('sha256', $remember_raw);
            $data['remember_expires_at'] = date('Y-m-d H:i:s', time() + AUTH_REMEMBER_LIFETIME);
        }

        $id = $this->db->insert('sessions', $data, ['encodeHtml' => false]);
        if (!$id) return false;

        $this->send_cookie($raw);
        if ($remember_raw) $this->send_remember_cookie($remember_raw);
        $this->reset();
        return $raw;
    }

    # Revoke one
    public function revoke($id)
    {
        $done = $this->db->update('sessions', ['revoked_at' => date('Y-m-d H:i:s')], ['id' => $id]);
        $this->row = null;
        return $done;
    }

    # Revoke all
    public function revoke_all($user_id, $except = null)
    {
        $user_id = (int) $user_id;
        $rows = $this->db->query("SELECT `id` FROM `sessions` WHERE `user_id` = $user_id AND `revoked_at` IS NULL", ['select_query' => true]);
        foreach ($rows as $row) {
            if ($except && $row['id'] == $except) continue;
            $this->db->update('sessions', ['revoked_at' => date('Y-m-d H:i:s')], ['id' => $row['id']]);
        }
        $this->reset();
        return true;
    }

    # Csrf token
    public function csrf()
    {
        $row = $this->resolve();
        if (!$row) return null;
        return $row['csrf_token'];
    }

    # Active devices
    public function devices($user_id)
    {
        $user_id = (int) $user_id;
        return $this->db->query("SELECT `id`, `ip`, `user_agent`, `created_at`, `last_seen_at` FROM `sessions` WHERE `user_id` = $user_id AND `revoked_at` IS NULL", ['select_query' => true]);
    }

    # Delete expired
    public function purge()
    {
        $now = date('Y-m-d H:i:s');
        return $this->db->query("DELETE FROM `sessions` WHERE `expires_at` < '$now'");
    }

    # Destroy current
    public function destroy()
    {
        $row = $this->resolve();
        if ($row) $this->revoke($row['id']);
        $this->clear_cookie();
        $this->clear_remember_cookie();
        $this->reset();
    }

    # Write cookie
    private function send_cookie($raw)
    {
        $_COOKIE[$this->cookie_name()] = $raw;
        if (PHP_SAPI === 'cli' || headers_sent()) return;
        setcookie($this->cookie_name(), $raw, [
            'expires' => 0,
            'path' => '/',
            'secure' => ENV !== 'local',
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
    }

    # Clear cookie
    private function clear_cookie()
    {
        unset($_COOKIE[$this->cookie_name()]);
        if (PHP_SAPI === 'cli' || headers_sent()) return;
        setcookie($this->cookie_name(), '', [
            'expires' => time() - 3600,
            'path' => '/',
            'secure' => ENV !== 'local',
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
    }

    # Write remember cookie
    private function send_remember_cookie($raw)
    {
        $_COOKIE[$this->remember_name()] = $raw;
        if (PHP_SAPI === 'cli' || headers_sent()) return;
        setcookie($this->remember_name(), $raw, [
            'expires' => time() + AUTH_REMEMBER_LIFETIME,
            'path' => '/',
            'secure' => ENV !== 'local',
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
    }

    # Clear remember cookie
    private function clear_remember_cookie()
    {
        unset($_COOKIE[$this->remember_name()]);
        if (PHP_SAPI === 'cli' || headers_sent()) return;
        setcookie($this->remember_name(), '', [
            'expires' => time() - 3600,
            'path' => '/',
            'secure' => ENV !== 'local',
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
    }
}

$_session = new Session();
