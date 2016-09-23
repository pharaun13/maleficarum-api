<?php
/**
 * This class provides a most generic collection functionality possible.
 *
 * @extends \Maleficarum\Api\Collection\AbstractCollection
 */

namespace Maleficarum\Api\Collection\Generic;

class Collection extends \Maleficarum\Api\Collection\AbstractCollection
{
    /**
     * @see Maleficarum\Api\Collection\AbstractCollection::populate()
     */
    public function populate(array $data = [])
    {
        $this->data = $data;

        return $this;
    }
}
