<?php
/**
 * This trait defines common functionality for all \Maleficarum\Api\Environment dependant classes.
 */

namespace Maleficarum\Api\Environment;

trait Dependant
{
    /**
     * Internal storage for the environment object.
     *
     * @var \Maleficarum\Api\Environment\Server|null
     */
    protected $environment = null;

    /**
     * Get the currently set environment object.
     *
     * @return \Maleficarum\Api\Environment\Server
     */
    public function getEnvironment()
    {
        return $this->environment;
    }

    /**
     * Set environment.
     *
     * @param \Maleficarum\Api\Environment\Server $environment
     *
     * @return $this
     */
    public function setEnvironment(\Maleficarum\Api\Environment\Server $environment)
    {
        $this->environment = $environment;

        return $this;
    }

    /**
     * Detach the current environment object.
     *
     * @return $this
     */
    public function detachEnvironment()
    {
        $this->environment = null;

        return $this;
    }
}
