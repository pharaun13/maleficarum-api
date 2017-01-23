<?php
/**
 * This trait defines common functionality for all \Maleficarum\Api\Database\Manager dependant classes.
 *
 * @trait
 */

namespace Maleficarum\Api\Database;

trait Dependant
{
    /**
     * Internal storage for the database connection manager object.
     *
     * @var \Maleficarum\Api\Database\Manager|null
     */
    protected $db = null;

    /**
     * Get the currently assigned database connection manager object.
     *
     * @return $this
     */
    public function getDb() {
        return $this->db;
    }

    /**
     * Inject a new database connection manager.
     *
     * @param \Maleficarum\Api\Database\Manager $db
     *
     * @return $this
     */
    public function setDb(\Maleficarum\Api\Database\Manager $db) {
        $this->db = $db;

        return $this;
    }

    /**
     * Unassign the current database connection manager object.
     *
     * @return $this
     */
    public function detachDb() {
        $this->db = null;

        return $this;
    }
}
