<?php

namespace Auth;

class Token
{
    private $db;

    # Constructor
    public function __construct()
    {
        global $db;
        $this->db = $db;
    }

    # Create token
    public function create($type, $user_id, $ttl = 900)
    {
        $raw = bin2hex(random_bytes(32));
        $id = $this->db->insert('auth_tokens', [
            'type' => $type,
            'user_id' => $user_id,
            'token_hash' => hash('sha256', $raw),
            'created_at' => date('Y-m-d H:i:s'),
            'expires_at' => date('Y-m-d H:i:s', time() + $ttl),
        ], ['encodeHtml' => false]);
        if (!$id) return false;
        return $raw;
    }

    # Verify token
    public function verify($type, $raw)
    {
        if (!preg_match('/^[a-f0-9]{64}$/', (string) $raw)) return false;
        $row = $this->db->select_one('auth_tokens', '*', [
            'type' => $type,
            'token_hash' => hash('sha256', $raw),
        ]);
        if (!$row) return false;
        if (!empty($row['used_at'])) return false;
        if (time() > strtotime($row['expires_at'])) return false;
        return $row;
    }

    # Mark used
    public function consume($id)
    {
        return $this->db->update('auth_tokens', ['used_at' => date('Y-m-d H:i:s')], ['id' => $id], ['encodeHtml' => false]);
    }

    # Revoke unused of a type
    public function revoke_all($type, $user_id)
    {
        $user_id = (int) $user_id;
        return $this->db->query("UPDATE `auth_tokens` SET `used_at` = '" . date('Y-m-d H:i:s') . "'
            WHERE `type` = '" . $this->db->conn->real_escape_string($type) . "'
            AND `user_id` = $user_id AND `used_at` IS NULL");
    }

    # Delete expired
    public function purge()
    {
        return $this->db->delete('auth_tokens', [
            'expires_at' => ['operator' => '<', 'value' => date('Y-m-d H:i:s')],
        ]);
    }
}

$_token = new Token();
