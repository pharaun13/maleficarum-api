<?php
/**
 * This class provides common functionality to all DB connection classes. All generic PDO functionality is delegated here.
 *
 * @abstract
 */

namespace Maleficarum\Api\Database;

abstract class AbstractConnection
{
    /**
     * Internal storage for the PDO connection.
     *
     * @var \PDO|null
     */
    protected $connection = null;

    /**
     * Internal storage for the connections host.
     *
     * @var string|null
     */
    protected $host = null;

    /**
     * Internal storage for the connections TCP port.
     *
     * @var int|null
     */
    protected $port = null;

    /**
     * Internal storage for the connections database.
     *
     * @var string|null
     */
    protected $dbname = null;

    /**
     * Internal storage for the connections username.
     *
     * @var string|null
     */
    protected $username = null;

    /**
     * Internal storage for the connections password.
     *
     * @var string|null
     */
    protected $password = null;

    /* ------------------------------------ Magic methods START ---------------------------------------- */
    /**
     * Method call delegation to the wrapped PDO instance
     *
     * @param string $name
     * @param array $args
     *
     * @return mixed
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     */
    public function __call(string $name, array $args) {
        if (is_null($this->connection)) {
            throw new \RuntimeException(sprintf('Cannot execute DB methods prior to establishing a connection. \%s::_call()', static::class));
        }

        if (!method_exists($this->connection, $name)) {
            throw new \InvalidArgumentException(sprintf('Method %s unsupported by PDO. \%s::_call()', $name, static::class));
        }

        return call_user_func_array([$this->connection, $name], $args);
    }
    /* ------------------------------------ Magic methods END ------------------------------------------ */

    /* ------------------------------------ AbstractConnection methods START --------------------------- */
    /**
     * Connect this instance to a database engine.
     * 
     * @return \Maleficarum\Api\Database\AbstractConnection
     */
    public function connect() : \Maleficarum\Api\Database\AbstractConnection {
        $this->connection = \Maleficarum\Ioc\Container::get('Maleficarum\Api\Database\PDO\Trailable', ['dsn' => $this->getDSN()]);

        return $this;
    }

    /**
     * Check if this wrapper is connected to a database engine.
     *
     * @returns bool
     */
    public function isConnected() : bool {
        return !is_null($this->connection);
    }
    /* ------------------------------------ AbstractConnection methods END ----------------------------- */

    /* ------------------------------------ Abstract methods START ------------------------------------- */
    /**
     * Fetch a database specific DSN to create a connection.
     *
     * @return string
     */
    abstract protected function getDSN() : string;

    /**
     * Lock the specified table.
     *
     * @param string $table
     * @param string $mode
     *
     * @return \Maleficarum\Api\Database\AbstractConnection
     */
    abstract protected function lockTable(string $table, string $mode = 'ACCESS EXCLUSIVE') : \Maleficarum\Api\Database\AbstractConnection;
    /* ------------------------------------ Abstract methods END --------------------------------------- */

    /* ------------------------------------ Setters & Getters START ------------------------------------ */
    /**
     * Gets host
     * 
     * @return null|string
     */
    public function getHost() {
        return $this->host;
    }

    /**
     * Sets host
     * 
     * @param string $host
     *
     * @return \Maleficarum\Api\Database\AbstractConnection
     */
    public function setHost(string $host) : \Maleficarum\Api\Database\AbstractConnection {
        $this->host = $host;

        return $this;
    }

    /**
     * Gets port
     * 
     * @return int|null
     */
    public function getPort() {
        return $this->port;
    }

    /**
     * Sets port
     * 
     * @param int $port
     *
     * @return \Maleficarum\Api\Database\AbstractConnection
     */
    public function setPort(int $port) : \Maleficarum\Api\Database\AbstractConnection {
        $this->port = $port;

        return $this;
    }

    /**
     * Gets database name
     * 
     * @return null|string
     */
    public function getDbname() {
        return $this->dbname;
    }

    /**
     * Sets database name
     * 
     * @param string $dbname
     *
     * @return \Maleficarum\Api\Database\AbstractConnection
     */
    public function setDbname(string $dbname) : \Maleficarum\Api\Database\AbstractConnection {
        $this->dbname = $dbname;

        return $this;
    }

    /**
     * Gets username
     * 
     * @return null|string
     */
    public function getUsername() {
        return $this->username;
    }

    /**
     * Sets username
     * 
     * @param string $username
     *
     * @return \Maleficarum\Api\Database\AbstractConnection
     */
    public function setUsername(string $username) : \Maleficarum\Api\Database\AbstractConnection {
        $this->username = $username;

        return $this;
    }

    /**
     * Gets password
     * 
     * @return null|string
     */
    public function getPassword() {
        return $this->password;
    }

    /**
     * Sets password
     * 
     * @param string $password
     *
     * @return \Maleficarum\Api\Database\AbstractConnection
     */
    public function setPassword(string $password) : \Maleficarum\Api\Database\AbstractConnection {
        $this->password = $password;

        return $this;
    }
    /* ------------------------------------ Setters & Getters END -------------------------------------- */
}
