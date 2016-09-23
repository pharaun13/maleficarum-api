<?php
/**
 * This class is a specific PDO wrapper designed to work with the PostgreSQL database.
 * @extends \Maleficarum\Api\Database\AbstractConnection
 */

namespace Maleficarum\Api\Database\Pgsql;

class Connection extends \Maleficarum\Api\Database\AbstractConnection
{
    /**
     * @see Maleficarum\Api\Database.AbstractConnection::getDSN()
     */
    protected function getDSN()
    {
        return 'pgsql:host=' . $this->getHost() . ';port=' . $this->getPort() . ';dbname=' . $this->getDbname() . ';user=' . $this->getUsername() . ';password=' . $this->getPassword();
    }

    /**
     * @see Maleficarum\Api\Database\AbstractConnection::lockTable()
     *
     * @return \Maleficarum\Api\Database\Pgsql\Connection
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     */
    public function lockTable($table, $mode = 'ACCESS EXCLUSIVE')
    {
        if (!is_string($table)) {
            throw new \InvalidArgumentException('Incorrect argument - String expected. \Maleficarum\Api\Database\Pgsql\Connection::lockTable()');
        }

        if (is_null($this->connection)) {
            throw new \RuntimeException('Cannot execute DB methods prior to establishing a connection. \Maleficarum\Api\Database\Pgsql\Connection::lockTable()');
        }

        if (!$this->inTransaction()) {
            throw new \RuntimeException('No active transaction - cannot lock a table outside of a transaction scope. \Maleficarum\Api\Database\Pgsql\Connection::lockTable()');
        }

        $this->query('LOCK "' . $table . '" IN ' . $mode . ' MODE');

        return $this;
    }
}
