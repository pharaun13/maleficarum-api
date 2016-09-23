<?php
/**
 * This interface is to be implemented by all CRUD enabled model classes.
 */

namespace Maleficarum\Api\Model;

interface CRUD
{
    /**
     * Persist data stored in this model as a new storage entry.
     *
     * @return $this
     */
    public function create();

    /**
     * Refresh this model with current data from the storage
     *
     * @return $this
     * @throws \Maleficarum\Api\Exception\NotFoundException
     */
    public function read();

    /**
     * Update storage entry with data currently stored in this model.
     *
     * @return $this
     */
    public function update();

    /**
     * Delete an entry from the storage based on ID data stored in this model
     *
     * @return $this
     */
    public function delete();

    /**
     * Validate data stored in this model to check if it can be persisted in storage.
     *
     * @param bool $clear
     *
     * @return bool
     */
    public function validate($clear = true);
}
