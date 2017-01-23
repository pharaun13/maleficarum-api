<?php
/**
 * This class is a specific PDO wrapper designed to work with the PostgreSQL database.
 * @extends \Maleficarum\Api\Database\AbstractConnection
 */

namespace Maleficarum\Api\Database\Pgsql;

class Connection extends \Maleficarum\Api\Database\AbstractConnection
{
    /* ------------------------------------ AbstractConnection methods START --------------------------- */
    /**
     * Fetch a postgresql DSN to create a connection.
     *
     * @see \Maleficarum\Api\Database\AbstractConnection::getDSN()
     * @return string
     */
    protected function getDSN() : string {
        return 'pgsql:host=' . $this->getHost() . ';port=' . $this->getPort() . ';dbname=' . $this->getDbname() . ';user=' . $this->getUsername() . ';password=' . $this->getPassword();
    }

    /**
     * Lock the specified table.
     *
     * @see \Maleficarum\Api\Database\AbstractConnection::lockTable()
     *
     * @param string $table
     * @param string $mode
     *
     * @return \Maleficarum\Api\Database\AbstractConnection
     */
    public function lockTable(string $table, string $mode = 'ACCESS EXCLUSIVE') : \Maleficarum\Api\Database\AbstractConnection {
        if (is_null($this->connection)) {
            throw new \RuntimeException(sprintf('Cannot execute DB methods prior to establishing a connection. \%s::lockTable()', static::class));
        }

        if (!$this->inTransaction()) {
            throw new \RuntimeException(sprintf('No active transaction - cannot lock a table outside of a transaction scope. \%s::lockTable()', static::class));
        }

        $this->query('LOCK "' . $table . '" IN ' . $mode . ' MODE');

        return $this;
    }
    /* ------------------------------------ AbstractConnection methods END ----------------------------- */
}
