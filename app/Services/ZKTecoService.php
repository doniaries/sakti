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
        return $this->zk->connect();
    }

    /**
     * Disconnect from the machine
     */
    public function disconnect()
    {
        return $this->zk->disconnect();
    }

    /**
     * Get all users from machine
     * Returns array of users with structure:
     * [uid, role, password, name, cardno, userid (PIN)]
     */
    public function getUsers()
    {
        $this->connect();
        $users = $this->zk->getUser();
        $this->disconnect();

        return $users;
    }

    /**
     * Set user to machine
     */
    public function setUser($uid, $userid, $name, $password = '', $role = 0, $cardno = 0)
    {
        $this->connect();
        $result = $this->zk->setUser($uid, $userid, $name, $password, $role, $cardno);
        $this->disconnect();

        return $result;
    }

    /**
     * Get attendance logs from machine
     * Returns array of attendance with structure:
     * [uid, id (PIN), state, timestamp, type]
     */
    public function getAttendance()
    {
        $this->connect();
        $attendance = $this->zk->getAttendance();
        $this->disconnect();

        return $attendance;
    }

    /**
     * Clear attendance logs from machine (DANGER)
     */
    public function clearAttendance()
    {
        $this->connect();
        $result = $this->zk->clearAttendance();
        $this->disconnect();

        return $result;
    }
}
