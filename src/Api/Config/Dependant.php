<?php
/**
 * This trait provides functionality common to all classes dependant on the \Maleficarum\Api\Config namespace
 */

namespace Maleficarum\Api\Config;

trait Dependant
{
    /**
     * Internal storage for the config object.
     *
     * @var \Maleficarum\Api\Config\AbstractConfig
     */
    protected $configStorage = null;

    /**
     * Inject a new config object.
     *
     * @param \Maleficarum\Api\Config\AbstractConfig $config
     *
     * @return $this
     */
    public function setConfig(\Maleficarum\Api\Config\AbstractConfig $config)
    {
        $this->configStorage = $config;

        return $this;
    }

    /**
     * Fetch the currently assigned config object.
     *
     * @return \Maleficarum\Api\Config\AbstractConfig|null
     */
    public function getConfig()
    {
        return $this->configStorage;
    }

    /**
     * Detach the currently assigned config object.
     *
     * @return $this
     */
    public function detachConfig()
    {
        $this->configStorage = null;

        return $this;
    }
}
