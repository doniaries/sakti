<?php

namespace App\Services;

use Rats\Zkteco\Lib\ZKTeco;

class ZKTecoService
{
    protected $ip;
    protected $port;
    protected $zk;

    public function __construct(string $ip, int $port = 4370)
    {
        $this->ip = $ip;
        $this->port = $port;
        $this->zk = new ZKTeco($this->ip, $this->port);
    }

    /**
     * Connect to the machine
     */
    public function connect()
    {
        try {
            return $this->zk->connect();
        } catch (\Throwable $e) {
            // Error koneksi seperti "unpack(): Type H: not enough input values"
            // terjadi ketika IP/Port tidak bisa dijangkau atau salah merespon
            return false;
        }
    }

    /**
     * Disconnect from the machine
     */
    public function disconnect()
    {
        try {
            return $this->zk->disconnect();
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Get all users from machine
     * Returns array of users with structure:
     * [uid, role, password, name, cardno, userid (PIN)]
     */
    public function getUsers()
    {
        try {
            $this->connect();
            $users = $this->zk->getUser();
            $this->disconnect();
            return $users;
        } catch (\Throwable $e) {
            return [];
        }
    }

    /**
     * Set user to machine
     */
    public function setUser($uid, $userid, $name, $password = '', $role = 0, $cardno = 0)
    {
        try {
            $this->connect();
            $result = $this->zk->setUser($uid, $userid, $name, $password, $role, $cardno);
            $this->disconnect();
            return $result;
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Clear all users from machine (DANGER)
     */
    public function clearUsers()
    {
        try {
            $this->connect();
            $result = $this->zk->clearUsers();
            $this->disconnect();
            return $result;
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Get attendance logs from machine
     * Returns array of attendance with structure:
     * [uid, id (PIN), state, timestamp, type]
     */
    public function getAttendance()
    {
        try {
            $this->connect();
            $attendance = $this->zk->getAttendance();
            $this->disconnect();
            return $attendance ?: [];
        } catch (\Throwable $e) {
            return [];
        }
    }

    /**
     * Clear attendance logs from machine (DANGER)
     */
    public function clearAttendance()
    {
        try {
            $this->connect();
            $result = $this->zk->clearAttendance();
            $this->disconnect();
            return $result;
        } catch (\Throwable $e) {
            return false;
        }
    }
}
