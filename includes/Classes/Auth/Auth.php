<?php

namespace Auth;

use Core\App;

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
        $this->db = App::db();
        $this->session = App::session();
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

        # Unverified cannot sign in
        if ($user['verify_status'] != 1) {
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
        $created = (bool) $this->session->create($user_id, $remember);

        # Spend the guest
        if ($created) App::guest()->mark_merged($user_id);

        return $created;
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

    # Send a fresh verification link, any earlier one stops working
    public function send_verify_token($to)
    {
        $user = $this->db->select_one("users", 'id,email', ['email' => $to]);
        if (!$user) return error("user not found!");

        App::token()->revoke_all('verify', $user['id']);
        $new_token = App::token()->create('verify', $user['id'], 86400);

        if (!$new_token) return error("Error in creating the verification link. Please try again");

        $email_sent = App::email()->send([
            'template' => 'verify-email',
            'subject' => "Email Verification Required – Action Needed",
            'to' => $user['email'],
            'vars' => [
                'token' => $new_token,
                'to' => $user['email'],
            ]
        ]);

        if ($email_sent)
            return success("We sent a new verfication link to your email. Please Verify your account with in 24 hours", [
                'link' => merge_path(SITE_URL, 'login')
            ]);

        return error("Error in sending email. Please try again or contact the administrator");
    }

    # Prove an address with the token that was mailed to it
    public function verify_email($email, $token)
    {
        $user = $this->db->select_one("users", 'id,email,verify_status', ['email' => $email]);
        if (!$user) return error("User not found with this email!");

        if ($user['verify_status'] == '1') return success("Already Verified");

        $row = App::token()->verify('verify', $token);

        if (!$row || $row['user_id'] != $user['id'])
            return error("Verification Link expired. We sent a new verfication link to your email. Please Verify your account with in 24 hours");

        App::token()->consume($row['id']);
        $this->db->update("users", ['verify_status' => 1], ['id' => $user['id']]);

        return success("Congratulations! Your account is verified successfully. You can log in now.");
    }

    # Mail a password reset link
    public function send_reset_token($email, $token)
    {
        App::email()->send([
            'template' => 'forgot-email',
            'to' => $email,
            'subject' => 'Forgot your password',
            'vars' => [
                'token' => $token,
                'to' => $email,
            ]
        ]);

        return true;
    }
}
