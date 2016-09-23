<?php
/**
 * This PDOStatement descendant class provides a functionality to create an audit trail for all modification queries.
 *
 * @extends \PDOStatement
 */

namespace Maleficarum\Api\Database\Statement;

class Trailable extends \PDOStatement
{
    /**
     * Internal storage for the attached PDO object.
     *
     * @var \PDO
     */
    protected $pdo = null;

    /**
     * Internal storage for bound parameters. Used for profiling only.
     *
     * @var array
     */
    protected $boundParams = [];

    /**
     * Internal storage for a database profiler to use when executing queries.
     *
     * @var \Maleficarum\Api\Profiler\Database|null
     */
    protected $profiler = null;

    /**
     * Internal storage for data - since we perform trailing we need to persist returned data for outside use - this is the container for that persistence
     *
     * @var array
     */
    protected $data = [];

    /**
     * Initialize a new Statement instance and allow for trailer injection.
     *
     * @param \Maleficarum\Api\Database\PDO\Trailable $pdo
     * @param \Maleficarum\Api\Profiler\Database|null $profiler
     */
    protected function __construct(\Maleficarum\Api\Database\PDO\Trailable $pdo, \Maleficarum\Api\Profiler\Database $profiler = null)
    {
        $this->pdo = $pdo;
        $this->profiler = $profiler;
    }

    /**
     * Binds a new value to the statement and stores it in an internal storage array for future use.
     *
     * @see \PDOStatement::bindValue()
     * @return bool
     */
    public function bindValue($parameter, $value, $dataType = \PDO::PARAM_STR)
    {
        $this->boundParams[$parameter] = $value;

        return parent::bindValue($parameter, $value, $dataType);
    }

    /**
     * Overloaded \PDOStatement::fetchAll() to allow for multiple fetching of a single result set.
     *
     * @see \PDOStatement::fetchAll()
     * @return array
     */
    public function fetchAll($how = null, $class_name = null, $ctor_args = null)
    {
        return $this->data;
    }

    /**
     * Overloaded \PDOStatement::fetch() to allow for multiple fetching of a single result set.
     *
     * @see \PDOStatement::fetch()
     * @return mixed
     */
    public function fetch($how = null, $orientation = null, $offset = null)
    {
        return count($this->data) ? array_shift($this->data) : null;
    }

    /**
     * Execute the statement and pass trail data on modification queries back to the initial PDO object.
     *
     * @see \PDOStatement::execute()
     * @return bool
     * @throws \InvalidArgumentException
     */
    public function execute($storage = null, $shard = null, $args = null)
    {
        // validate trailing data
        if (!mb_strlen($storage) || !mb_strlen($shard)) throw new \InvalidArgumentException('Incorrect trailing data. \Maleficarum\Api\Database\Statement\Trailable::execute()');

        // handle incoming bound params
        if (is_array($args)) foreach ($args as $key => $val) $this->boundParams[$key] = $val;

        $start = microtime(true);
        $result = parent::execute($args);
        $end = microtime(true);

        is_null($this->profiler) or $this->profiler->addQuery($start, $end, $this->queryString, $this->boundParams);

        // fetch returned data
        $this->data = parent::fetchAll(\PDO::FETCH_ASSOC);

        /** Handle trailing */

        // inserts
        preg_match('/^INSERT/i', $this->queryString) || preg_match('/ INSERT /i', $this->queryString) and $this->trailInsert($storage, $shard);

        // updates
        preg_match('/^UPDATE/i', $this->queryString) || preg_match('/ UPDATE /i', $this->queryString) and $this->trailUpdate($storage, $shard);

        // delete
        preg_match('/^DELETE/i', $this->queryString) || preg_match('/ DELETE /i', $this->queryString) and $this->trailDelete($storage, $shard);

        return $result;
    }

    /**
     * Send current statement for audit trailing as a CREATE command.
     *
     * @param string $storage
     * @param string $shard
     *
     * @return \Maleficarum\Api\Database\Statement\Trailable
     */
    protected function trailInsert($storage, $shard)
    {
        $data = [
            'command' => 'CREATE',
            'storage' => $storage,
            'shard' => $shard,
            'typeData' => [
                'query' => $this->queryString,
                'bindParams' => $this->boundParams,
                'storageReturnedStructure' => $this->data
            ]
        ];
        $this->pdo->trail($data);

        return $this;
    }

    /**
     * Send current statement for audit trailing as an UPDATE command.
     *
     * @param string $storage
     * @param string $shard
     *
     * @return \Maleficarum\Api\Database\Statement\Trailable
     */
    protected function trailUpdate($storage, $shard)
    {
        $data = [
            'command' => 'UPDATE',
            'storage' => $storage,
            'shard' => $shard,
            'typeData' => [
                'query' => $this->queryString,
                'bindParams' => $this->boundParams,
                'storageReturnedStructure' => $this->data
            ]
        ];
        $this->pdo->trail($data);

        return $this;
    }

    /**
     * Send current statement for audit trailing as a DELETE command.
     *
     * @param string $storage
     * @param string $shard
     *
     * @return \Maleficarum\Api\Database\Statement\Trailable
     */
    protected function trailDelete($storage, $shard)
    {
        $data = [
            'command' => 'DELETE',
            'storage' => $storage,
            'shard' => $shard,
            'typeData' => [
                'query' => $this->queryString,
                'bindParams' => $this->boundParams,
                'storageReturnedStructure' => $this->data
            ]
        ];
        $this->pdo->trail($data);

        return $this;
    }
}
