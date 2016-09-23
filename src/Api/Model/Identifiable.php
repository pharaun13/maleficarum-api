<?php
/**
 * This interface is to be implemented by all objects that can and need to be identified by a single unique value.
 */

namespace Maleficarum\Api\Model;

interface Identifiable
{
    /**
     * Set a unique ID for this object.
     *
     * @param string $id
     *
     * @return $this
     */
    public function setId($id);

    /**
     * Fetch the currently assigned unique ID.
     *
     * @return string
     */
    public function getId();
}
