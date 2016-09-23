<?php
/**
 * This audit trail simply abandons any trail packets sent to it.
 * @extends \Maleficarum\Api\Database\Trail\AbstractTrail
 */

namespace Maleficarum\Api\Database\Trail;

class None extends \Maleficarum\Api\Database\Trail\AbstractTrail
{
    /**
     * @see Maleficarum\Api\Database\Trail\AbstractTrail::trail()
     */
    public function trail(array $data)
    {
        return $this;
    }
}
