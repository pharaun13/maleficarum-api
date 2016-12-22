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
     * Use \Maleficarum\Request\Dependant functionality.
     *
     * @trait
     */
    use \Maleficarum\Request\Dependant;

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
        if ($this->isSkippableRoute()) return $this;

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

    /**
     * Check if checks should be skipped for current route
     *
     * @return bool
     */
    private function isSkippableRoute() {
        if (is_null($this->getRequest())) {
            return false;
        }

        $path = parse_url($this->getRequest()->getUri(), \PHP_URL_PATH);
        $securityConfig = $this->getConfig()['security'];
        if (isset($securityConfig['skip_routes']) && is_array($securityConfig['skip_routes']) && in_array($path, $securityConfig['skip_routes'], true)) {
            return true;
        }

        return false;
    }
}