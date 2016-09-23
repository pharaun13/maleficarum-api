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
    public function __call($name, $args)
    {
        if (is_null($this->connection)) {
            throw new \RuntimeException(sprintf('Cannot execute DB methods prior to establishing a connection. \%s::_call()', get_class($this)));
        }

        if (!method_exists($this->connection, $name)) {
            throw new \InvalidArgumentException(sprintf('Method %s unsupported by PDO. \%s::_call()', $name, get_class($this)));
        }

        return call_user_func_array([$this->connection, $name], $args);
    }
    /* ------------------------------------ Magic methods END ------------------------------------------ */

    /**
     * Connect this instance to a database engine.
     *
     * @return $this
     */
    public function connect()
    {
        $this->connection = \Maleficarum\Ioc\Container::get('Maleficarum\Api\Database\PDO\Trailable', ['dsn' => $this->getDSN()]);

        return $this;
    }

    /**
     * Check if this wrapper is connected to a database engine.
     *
     * @returns bool
     */
    public function isConnected()
    {
        return !is_null($this->connection);
    }

    /* ------------------------------------ Setters & Getters START ------------------------------------ */
    /**
     * Getter.
     *
     * @returns string
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * Setter.
     *
     * @param string $host
     *
     * @throws \InvalidArgumentException
     * @return $this
     */
    public function setHost($host)
    {
        if (!is_string($host)) {
            throw new \InvalidArgumentException(sprintf('Incorrect host provided - string expected. \%s::setHost()', get_class($this)));
        }

        $this->host = $host;

        return $this;
    }

    /**
     * Getter.
     *
     * @returns integer
     */
    public function getPort()
    {
        return $this->port;
    }

    /**
     * Setter.
     *
     * @param string $port
     *
     * @throws \InvalidArgumentException
     * @return $this
     */
    public function setPort($port)
    {
        if (!is_int($port)) {
            throw new \InvalidArgumentException(sprintf('Incorrect port provided - integer expected. \%s::setPort()', get_class($this)));
        }

        $this->port = $port;

        return $this;
    }

    /**
     * Getter.
     *
     * @returns string
     */
    public function getDbname()
    {
        return $this->dbname;
    }

    /**
     * Setter.
     *
     * @param string $dbname
     *
     * @throws \InvalidArgumentException
     * @return $this
     */
    public function setDbname($dbname)
    {
        if (!is_string($dbname)) {
            throw new \InvalidArgumentException(sprintf('Incorrect dbname provided - string expected. \%s::setDbname()', get_class($this)));
        }

        $this->dbname = $dbname;

        return $this;
    }

    /**
     * Getter.
     *
     * @returns string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Setter.
     *
     * @param string $username
     *
     * @throws \InvalidArgumentException
     * @return $this
     */
    public function setUsername($username)
    {
        if (!is_string($username)) {
            throw new \InvalidArgumentException(sprintf('Incorrect username provided - string expected. \%s::setUsername()', get_class($this)));
        }

        $this->username = $username;

        return $this;
    }

    /**
     * Getter.
     *
     * @returns string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Setter.
     *
     * @param string $password
     *
     * @throws \InvalidArgumentException
     * @return $this
     */
    public function setPassword($password)
    {
        if (!is_string($password)) {
            throw new \InvalidArgumentException(sprintf('Incorrect password provided - string expected. \%s::setPassword()', get_class($this)));
        }

        $this->password = $password;

        return $this;
    }
    /* ------------------------------------ Setters & Getters END -------------------------------------- */

    /* ------------------------------------ Abstract methods START ------------------------------------- */
    /**
     * Fetch a database specific DSN to create a connection.
     *
     * @return string
     */
    abstract protected function getDSN();

    /**
     * Lock the specified table.
     *
     * @param string $table
     * @param string $mode
     *
     * @return $this
     */
    abstract protected function lockTable($table, $mode = 'ACCESS EXCLUSIVE');
    /* ------------------------------------ Abstract methods END --------------------------------------- */
}
