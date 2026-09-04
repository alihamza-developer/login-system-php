<?php

namespace Auth;

class Settings
{
    private $db;
    private $cache = null;

    # Constructor
    public function __construct()
    {
        global $db;
        $this->db = $db;
    }

    # Keys held encrypted
    public function is_secret($key)
    {
        return defined('SETTINGS_SECRETS') && in_array($key, SETTINGS_SECRETS);
    }

    # Load all
    public function all()
    {
        if ($this->cache !== null) return $this->cache;
        $rows = $this->db->select('meta_data', ['meta_key', 'meta_value', 'meta_json']);
        if (!is_array($rows)) $rows = [];
        $this->cache = [];
        foreach ($rows as $row) {
            $json = $row['meta_json'];
            $value = $json !== '' ? json_decode($json, true) : $this->decrypt($row['meta_value']);
            $this->cache[$row['meta_key']] = $value;
        }
        return $this->cache;
    }

    # Get value
    public function get($key, $default = null)
    {
        $all = $this->all();
        if (!array_key_exists($key, $all)) return $default;
        $value = $all[$key];
        return ($value === '' || $value === null) ? $default : $value;
    }

    # Value, falling back to a constant
    public function env($key, $constant, $default = '')
    {
        $fallback = defined($constant) ? constant($constant) : $default;
        return $this->get($key, $fallback);
    }

    # Set value
    public function set($key, $value)
    {
        $is_scalar = !is_array($value) && !is_object($value);
        $plain = $is_scalar ? (string) $value : '';
        if ($is_scalar && $this->is_secret($key) && $plain !== '') $plain = $this->encrypt($plain);

        $data = [
            'meta_key' => $key,
            'meta_value' => $plain,
            'meta_json' => $is_scalar ? '' : json_encode($value),
        ];

        $exists = $this->db->select_one('meta_data', ['id'], ['meta_key' => $key]);
        if ($exists) $this->db->update('meta_data', $data, ['id' => $exists['id']], ['encodeHtml' => false]);
        else $this->db->insert('meta_data', $data, ['encodeHtml' => false]);

        $this->cache = null;
        return true;
    }

    # Set many at once
    public function set_many($values)
    {
        foreach ($values as $key => $value) $this->set($key, $value);
        return true;
    }

    # Remove key
    public function forget($key)
    {
        $this->db->delete('meta_data', ['meta_key' => $key]);
        $this->cache = null;
        return true;
    }

    # Raw key bytes
    private function key()
    {
        return hash('sha256', defined('APP_KEY') ? APP_KEY : '', true);
    }

    # Encrypt a secret
    private function encrypt($value)
    {
        $iv = random_bytes(12);
        $tag = '';
        $cipher = openssl_encrypt($value, 'aes-256-gcm', $this->key(), OPENSSL_RAW_DATA, $iv, $tag);
        if ($cipher === false) return $value;
        return 'enc:v1:' . base64_encode($iv . $tag . $cipher);
    }

    # Decrypt if it was encrypted
    private function decrypt($value)
    {
        if (!is_string($value) || strpos($value, 'enc:v1:') !== 0) return $value;

        $raw = base64_decode(substr($value, 7), true);
        if ($raw === false || strlen($raw) < 29) return '';

        $plain = openssl_decrypt(substr($raw, 28), 'aes-256-gcm', $this->key(), OPENSSL_RAW_DATA, substr($raw, 0, 12), substr($raw, 12, 16));
        return $plain === false ? '' : $plain;
    }
}

$_settings = new Settings();
