<?php
/**
 * This abstract class provides common functionality for all Database audit trail handler strategies.
 *
 * @abstract
 */

namespace Maleficarum\Api\Database\Trail;

abstract class AbstractTrail
{
    /**
     * Send the provided audit trail packet into the log.
     *
     * @param array $data
     *
     * @return mixed
     */
    abstract function trail(array $data);
}
