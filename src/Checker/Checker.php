<?php

declare(strict_types=1);

namespace Checker;

use PDO;
use PDOException;

class Checker
{
    private PDO $pdo;

    private array $errors = [];

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Run checker.
     */
    public function run(): void
    {
        $this->errors = [];

        try {
            $slaveStatus = $this->pdo->query('SHOW SLAVE STATUS')->fetch();

            if ($slaveStatus === false) {
                $this->errors[] = 'Replication not configured.';
            } else {
                if ($slaveStatus['Last_Errno'] !== 0) {
                    $this->errors[] = 'Error when processing relay log (Last_Errno)';
                }
                if ($slaveStatus['Slave_IO_Running'] !== 'Yes') {
                    $this->errors[] = "I/O thread for reading the master's binary log is not running (Slave_IO_Running)";
                }
                if ($slaveStatus['Slave_SQL_Running'] !== 'Yes') {
                    $this->errors[] = 'SQL thread for executing events in the relay log is not running (Slave_SQL_Running)';
                }
                if ($slaveStatus['Seconds_Behind_Master'] > 1800) {
                    $this->errors[] = 'The Slave is at least 1800 seconds behind the master (Seconds_Behind_Master)';
                }
            }
        } catch (PDOException $e) {
            $this->errors[] = 'Slave status query error: ' . $e->getMessage();
        }
    }

    /**
     * @return array
     */
    public function getErrors(): array
    {
        return $this->errors;
    }
}
