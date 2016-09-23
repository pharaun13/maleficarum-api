<?php
/**
 * This class expands basic PDO object with trailing capability.
 * @extends \PDO
 */

namespace Maleficarum\Api\Database\PDO;

class Trailable extends \PDO
{
    /**
     * Internal storage for in-transaction trails.
     *
     * @var array
     */
    private $log = [];

    /**
     * Internal storage for trail handler object.
     *
     * @var \Maleficarum\Api\Database\Trail\AbstractTrail|null
     */
    private $trail = null;

    /**
     * Initialize a new instance of a trailable PDO connection.
     *
     * @param \Maleficarum\Api\Database\Trail\AbstractTrail $trail
     * @param string $dsn
     */
    public function __construct(\Maleficarum\Api\Database\Trail\AbstractTrail $trail, $dsn)
    {
        $this->trail = $trail;
        parent::__construct($dsn);
    }

    /**
     * Trail provided data. (Outside of transaction the trail is immediate. Inside a transaction it will be stored in transaction log and sent to the trail logic on commit.)
     *
     * @param array $data
     *
     * @return \Maleficarum\Api\Database\PDO\Trailable
     */
    public function trail(array $data)
    {
        if ($this->inTransaction()) {
            // in transaction -> send to log
            $this->log[] = $data;
        } else {
            // atomic operation -> send to trail
            $this->trail->trail([
                'timestamp' => microtime(true),
                'id' => uniqid(),
                'id_algorithm' => 'PHP::uniqid()',
                'source' => '[API]',
                'syntax' => 'SQL',
                'type' => 'Atomic',
                'version' => '1',
                'data' => $data
            ]);
        }

        return $this;
    }

    /**
     * Commit this transaction and send all audit log entries into the trail.
     *
     * @return bool
     */
    public function commit()
    {
        $result = parent::commit();

        // send all data in commit log to trail
        count($this->log) and $trail = $this->trail->trail([
            'timestamp' => microtime(true),
            'id' => uniqid(),
            'id_algorithm' => 'PHP::uniqid()',
            'source' => '[API]',
            'syntax' => 'SQL',
            'type' => 'Transaction',
            'version' => '1',
            'data' => $this->log
        ]);

        // reset the log storage
        $this->log = [];

        return $result;
    }

    /**
     * Rollback current transaction and reset audit log storage.
     *
     * @return bool
     */
    public function rollback()
    {
        $result = parent::rollBack();

        // reset the log storage
        $this->log = [];

        return $result;
    }
}
