<?php
/**
 * This class provides a most generic collection functionality possible.
 *
 * @extends \Maleficarum\Api\Collection\AbstractCollection
 */

namespace Maleficarum\Api\Collection\Generic;

class Collection extends \Maleficarum\Api\Collection\AbstractCollection
{
    /* ------------------------------------ Collection methods START ----------------------------------- */
    /**
     * Populate this collection with data.
     *
     * @see Maleficarum\Api\Collection\AbstractCollection::populate()
     *
     * @param array $data
     *
     * @return \Maleficarum\Api\Collection\AbstractCollection
     */
    public function populate(array $data = []) : \Maleficarum\Api\Collection\AbstractCollection {
        $this->data = $data;

        return $this;
    }
    /* ------------------------------------ Collection methods END ------------------------------------- */
}
