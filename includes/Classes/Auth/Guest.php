<?php

namespace Auth;

use Core\App;

# Identity for a visitor with no account
class Guest
{
    private $db;
    private $row = null;
    private $resolved = false;

    # Constructor
    public function __construct()
    {
        $this->db = App::db();
    }

    # Cookie name
    public function cookie_name()
    {
        return ENV === 'local' ? 'gid' : '__Host-gid';
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
        return isset($_COOKIE[$name]) ? $_COOKIE[$name] : null;
    }

    # Resolve guest, never creates
    public function resolve()
    {
        if ($this->resolved) return $this->row;
        $this->resolved = true;

        $raw = $this->raw_token();
        if (!$raw || !preg_match('/^[a-f0-9]{64}$/', $raw)) return null;

        $hash = hash('sha256', $raw);
        $now = date('Y-m-d H:i:s');

        # A merged guest is spent, so it must not come back
        $rows = $this->db->query("SELECT * FROM `guests`
            WHERE `token_hash` = '$hash'
            AND `merged_at` IS NULL
            AND `expires_at` > '$now' LIMIT 1", ['select_query' => true]);

        if (!count($rows)) {
            $this->clear_cookie();
            return null;
        }

        $this->row = $rows[0];
        $this->touch();
        return $this->row;
    }

    # Current guest row
    public function current()
    {
        return $this->resolve();
    }

    # Current guest id
    public function id()
    {
        $row = $this->resolve();
        return $row ? $row['id'] : null;
    }

    # Is a guest present
    public function check()
    {
        return $this->resolve() !== null;
    }

    # Identity for this request, if one is wanted
    public function boot($user_id = null)
    {
        # Accounts and cli scripts never need one
        if ($user_id || PHP_SAPI === 'cli') return null;

        return GUEST_AUTO_START ? $this->ensure() : $this->id();
    }

    # Resolve or create, for write paths
    public function ensure()
    {
        $row = $this->resolve();
        if ($row) return $row['id'];

        $id = $this->start();
        return $id ? $id : null;
    }

    # Create guest and send cookie
    public function start()
    {
        $raw = bin2hex(random_bytes(32));
        $now = date('Y-m-d H:i:s');

        $id = $this->db->insert('guests', [
            'token_hash' => hash('sha256', $raw),
            'ip' => isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : null,
            'user_agent' => isset($_SERVER['HTTP_USER_AGENT']) ? substr($_SERVER['HTTP_USER_AGENT'], 0, 255) : null,
            'created_at' => $now,
            'last_seen_at' => $now,
            'expires_at' => date('Y-m-d H:i:s', time() + GUEST_LIFETIME),
        ], ['encodeHtml' => false]);

        if (!$id) return false;

        $this->send_cookie($raw);
        $this->reset();
        return $id;
    }

    # Spend this guest on an account
    public function mark_merged($user_id)
    {
        $row = $this->resolve();
        if (!$row) return false;

        $done = $this->db->update('guests', [
            'merged_at' => date('Y-m-d H:i:s'),
            'merged_user_id' => (int) $user_id
        ], ['id' => $row['id']]);

        $this->clear_cookie();
        $this->reset();
        return $done;
    }

    # Drop the cookie, keep the row
    public function forget()
    {
        $this->clear_cookie();
        $this->reset();
    }

    # Slide the window, at most hourly
    private function touch()
    {
        if (!$this->row) return;
        if (time() - strtotime($this->row['last_seen_at']) < GUEST_TOUCH_AFTER) return;

        $now = date('Y-m-d H:i:s');
        $expires = date('Y-m-d H:i:s', time() + GUEST_LIFETIME);

        $this->db->update('guests', [
            'last_seen_at' => $now,
            'expires_at' => $expires
        ], ['id' => $this->row['id']]);

        $this->row['last_seen_at'] = $now;
        $this->row['expires_at'] = $expires;

        # Keep the browser copy in step
        $this->send_cookie($this->raw_token());
    }

    # Write cookie
    private function send_cookie($raw)
    {
        $_COOKIE[$this->cookie_name()] = $raw;
        if (PHP_SAPI === 'cli' || headers_sent()) return;
        setcookie($this->cookie_name(), $raw, [
            'expires' => time() + GUEST_LIFETIME,
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
}
