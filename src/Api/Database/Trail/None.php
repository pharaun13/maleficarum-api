<?php
/**
 * This audit trail simply abandons any trail packets sent to it.
 * @extends \Maleficarum\Api\Database\Trail\AbstractTrail
 */

namespace Maleficarum\Api\Database\Trail;

class None extends \Maleficarum\Api\Database\Trail\AbstractTrail
{
    /**
     * Returns current object
     * 
     * @see \Maleficarum\Api\Database\Trail\AbstractTrail::trail()
     * 
     * @param array $data
     *
     * @return \Maleficarum\Api\Database\Trail\AbstractTrail
     */
    public function trail(array $data) {
        return $this;
    }
}
