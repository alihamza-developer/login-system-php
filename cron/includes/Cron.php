<?php

namespace Cron;

class Cron
{
    private $db;
    private $tasks = [];
    private $path;

    # Constructor
    public function __construct()
    {
        global $db;
        $this->db = $db;
        $this->path = __DIR__ . '/tasks/';
    }

    # Register a task
    public function task($name, $title, $every)
    {
        $this->tasks[$name] = [
            'name' => $name,
            'title' => $title,
            'every' => (int) $every,
        ];
        return $this;
    }

    # Registered tasks with their last run
    public function all()
    {
        $saved = [];
        foreach ($this->db->select('cron_jobs', '*') as $row) $saved[$row['name']] = $row;

        $out = [];
        foreach ($this->tasks as $name => $task) {
            $row = isset($saved[$name]) ? $saved[$name] : [];

            $task['last_run_at']  = arr_val($row, 'last_run_at');
            $task['last_status']  = arr_val($row, 'last_status', '');
            $task['last_message'] = arr_val($row, 'last_message', '');
            $task['duration_ms']  = (int) arr_val($row, 'duration_ms', 0);
            $task['runs']         = (int) arr_val($row, 'runs', 0);
            $task['running']      = !empty(arr_val($row, 'started_at'));
            $task['due']          = $this->is_due($task);
            $out[$name] = $task;
        }
        return $out;
    }

    # Has the interval passed
    private function is_due($task)
    {
        if (empty($task['last_run_at'])) return true;
        return (time() - strtotime($task['last_run_at'])) >= $task['every'];
    }

    # Run one task
    public function run($name, $force = false)
    {
        if (!isset($this->tasks[$name])) return false;

        $tasks = $this->all();
        $task = $tasks[$name];

        # A task already running is left alone
        if ($task['running'] && !$force) return false;
        if (!$task['due'] && !$force) return false;

        $file = $this->path . $name . '.php';
        $this->start($name);
        $started = microtime(true);

        if (!is_file($file)) return $this->finish($name, 'error', 'Task file not found', 0);

        try {
            $message = require $file;
            $status = 'ok';
            if (!is_string($message)) $message = 'Done';
        } catch (\Throwable $e) {
            $status = 'error';
            $message = $e->getMessage();
        }

        return $this->finish($name, $status, $message, (int) round((microtime(true) - $started) * 1000));
    }

    # Run everything that is due
    public function run_all($force = false)
    {
        $ran = [];
        foreach (array_keys($this->tasks) as $name) {
            $status = $this->run($name, $force);
            if ($status !== false) $ran[$name] = $status;
        }
        return $ran;
    }

    # Mark as running
    private function start($name)
    {
        $this->db->save('cron_jobs', [
            'name' => $name,
            'started_at' => date('Y-m-d H:i:s'),
        ], ['name' => $name]);
    }

    # Record the outcome
    private function finish($name, $status, $message, $ms)
    {
        $conn = $this->db->conn;
        $name = $conn->real_escape_string($name);
        $status = $conn->real_escape_string($status);
        $message = $conn->real_escape_string(substr($message, 0, 250));

        # started_at must clear to NULL, which the builder cannot write
        $this->db->query("UPDATE `cron_jobs` SET
            `started_at` = NULL,
            `last_run_at` = '" . date('Y-m-d H:i:s') . "',
            `last_status` = '$status',
            `last_message` = '$message',
            `duration_ms` = " . (int) $ms . ",
            `runs` = `runs` + 1
            WHERE `name` = '$name'");

        return $status;
    }
}

$_cron = new Cron();
