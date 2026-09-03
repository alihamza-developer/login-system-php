<?php

namespace Auth;

require_once _DIR_ . 'includes/Classes/Session.php';

# Keeps failed logins constant time
define('AUTH_DUMMY_HASH', '$2y$10$XheY7vahLkevM2O4M2wk8ek3A1Tl4kEtbBa.jcGPZXDD4GXmtbSQG');

class Auth
{
    private $db;
    private $session;
    private $user = null;
    private $loaded = false;
    private $last_fail = '';

    # Constructor
    public function __construct()
    {
        global $db, $_session;
        $this->db = $db;
        $this->session = $_session;
    }

    # Clear cache
    public function reset()
    {
        $this->user = null;
        $this->loaded = false;
        $this->session->reset();
    }

    # Current user
    public function user()
    {
        if ($this->loaded) return $this->user;
        $this->loaded = true;
        $row = $this->session->resolve();
        if (!$row) return null;
        $user = $this->db->select_one('users', '*', ['id' => $row['user_id']]);
        if (!$user) {
            $this->session->revoke($row['id']);
            return null;
        }
        $this->user = $user;
        return $user;
    }

    # Current id
    public function id()
    {
        $user = $this->user();
        return $user ? $user['id'] : null;
    }

    # Is logged in
    public function check()
    {
        return $this->user() !== null;
    }

    # Verify credentials
    public function attempt($email, $password)
    {
        $this->last_fail = '';
        $user = $this->db->select_one('users', '*', ['email' => $email]);
        $hash = $user ? $user['password'] : AUTH_DUMMY_HASH;
        $ok = password_verify($password, $hash);

        if (!$user || !$ok) {
            $this->last_fail = 'credentials';
            return false;
        }

        # Zero dates are not verification
        $verified = $user['email_verified_at'];
        if (empty($verified) || strtotime($verified) < 1) {
            $this->last_fail = 'unverified';
            return false;
        }
        if (password_needs_rehash($hash, AUTH_PASSWORD_ALGO)) {
            $this->db->update('users', [
                'password' => password_hash($password, AUTH_PASSWORD_ALGO),
            ], ['id' => $user['id']], ['encodeHtml' => false]);
        }
        return $user;
    }

    # Why it failed
    public function fail_reason()
    {
        return $this->last_fail;
    }

    # Start session
    public function login($user_id, $remember = false)
    {
        $this->reset();
        return (bool) $this->session->create($user_id, $remember);
    }

    # End session
    public function logout()
    {
        $this->session->destroy();
        $this->reset();
    }

    # Role check
    public function is($role)
    {
        $user = $this->user();
        if (!$user) return false;
        return $user['role'] === $role;
    }

    # Permission check
    public function can($action)
    {
        $user = $this->user();
        if (!$user) return false;
        $map = AUTH_PERMISSIONS;
        $allowed = isset($map[$user['role']]) ? $map[$user['role']] : [];
        if (in_array('*', $allowed)) return true;
        return in_array($action, $allowed);
    }
}

$_auth = new Auth();
