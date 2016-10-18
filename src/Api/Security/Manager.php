<?php
/**
 * This class performs security checks during application bootstrapping.
 */

namespace Maleficarum\Api\Security;

class Manager
{
    /**
     * Use \Maleficarum\Config\Dependant functionality.
     *
     * @trait
     */
    use \Maleficarum\Config\Dependant;

    /**
     * Execute all security checks.
     *
     * @return \Maleficarum\Api\Security\Manager
     * @throws \Maleficarum\Exception\SecurityException
     * @throws \RuntimeException
     */
    public function verify()
    {
        // Check if any checks have been specified. If not the security manager returns a success.
        if (!is_array($this->getConfig()['security'])) return $this;
        if (!is_array($this->getConfig()['security']['checks']) || !count($this->getConfig()['security']['checks'])) return $this;

        foreach ($this->getConfig()['security']['checks'] as $cDef) {
            // initialize check
            $check = \Maleficarum\Ioc\Container::get($cDef);

            // validate check object
            if (!($check instanceof \Maleficarum\Api\Security\Check\AbstractCheck)) throw new \RuntimeException('Invalid security check object. \Maleficarum\Api\Security\Manager::verify()');

            // execute check object
            if (!$check->execute()) throw new \Maleficarum\Exception\SecurityException('Security check failed (' . get_class($check) . ').');
        }

        return $this;
    }
}