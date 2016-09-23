<?php
/**
 * This trait defines a set of helper methods for all objects that require transactional database access.
 *
 * @trait
 */

namespace Maleficarum\Api\Database;

trait Transaction
{
    /**
     * Helper method to detect if any of the specified daos are NOT currently in transaction.
     *
     * @param array $daos
     *
     * @return array - list of daos not in transaction
     */
    protected function detectTransaction(array $daos)
    {
        $result = [];

        foreach ($daos as $key => $dao) {
            // detect connection
            $shard = $dao->getDb()->fetchShard($dao->getShardRoute());
            $shard->isConnected() or $shard->connect();

            // if not in transaction - attach shard to result
            $shard->inTransaction() or $result[$key] = $shard;
        }

        return $result;
    }

    /**
     * Helper method to establish a transaction on a set of shards.
     *
     * @param array $daos
     *
     * @return $this
     */
    protected function beginTransaction(array $daos)
    {
        foreach ($daos as $dao) {
            $shard = $dao->getDb()->fetchShard($dao->getShardRoute());
            $shard->isConnected() or $shard->connect();
            $shard->inTransaction() or $shard->beginTransaction();
        }

        return $this;
    }

    /**
     * Helper method to commit a transaction on a set of shards.
     *
     * @param array $daos
     *
     * @return $this
     */
    protected function commit(array $daos)
    {
        foreach ($daos as $dao) {
            $shard = $dao->getDb()->fetchShard($dao->getShardRoute());
            $shard->inTransaction() and $shard->commit();
        }

        return $this;
    }

    /**
     * Helper method to rollback a transaction on a set of shards.
     *
     * @param array $daos
     *
     * @return $this
     */
    protected function rollback(array $daos)
    {
        foreach ($daos as $dao) {
            $shard = $dao->getDb()->fetchShard($dao->getShardRoute());
            try {
                $shard->rollback();
            } catch (\PDOException $e) {
            }
        }

        return $this;
    }
}
