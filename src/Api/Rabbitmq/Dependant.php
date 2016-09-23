<?php
/**
 * This trait provides functionality common to all classes dependant on the \Maleficarum\Api\Config namespace
 */

namespace Maleficarum\Api\Rabbitmq;

trait Dependant
{
    /**
     * Internal storage for the rabbitmq connection object.
     *
     * @var \Maleficarum\Api\Rabbitmq\Connection
     */
    protected $rabbitmqStorage = null;

    /**
     * Inject a new rabbitmq connection object.
     *
     * @param \Maleficarum\Api\Rabbitmq\Connection $con
     *
     * @return $this
     */
    public function setQueue(\Maleficarum\Api\Rabbitmq\Connection $con)
    {
        $this->rabbitmqStorage = $con;

        return $this;
    }

    /**
     * Fetch the currently assigned rabbitmq connection object.
     *
     * @return \Maleficarum\Api\Rabbitmq\Connection|null
     */
    public function getQueue()
    {
        return $this->rabbitmqStorage;
    }

    /**
     * Detach the currently assigned rabbitmq connection object.
     *
     * @return $this
     */
    public function detachQueue()
    {
        $this->rabbitmqStorage = null;

        return $this;
    }
}