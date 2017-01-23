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
     * @param mixed $id
     *
     * @return \Maleficarum\Api\Model\Identifiable
     */
    public function setId($id) : \Maleficarum\Api\Model\Identifiable;

    /**
     * Fetch the currently assigned unique ID.
     *
     * @return mixed
     */
    public function getId();
}
