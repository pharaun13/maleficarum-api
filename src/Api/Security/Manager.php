<?php
/**
 * This class performs security checks during application bootstrapping.
 */
declare (strict_types=1);

namespace Maleficarum\Api\Security;

class Manager {
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
     * @throws \RuntimeException
     * @throws \Maleficarum\Exception\SecurityException
     */
    public function verify(): \Maleficarum\Api\Security\Manager {
        // Check if any checks have been specified. If not the security manager returns a success.
        if (!is_array($this->getConfig()['security'])) {
            return $this;
        }
        if (!is_array($this->getConfig()['security']['checks']) || !count($this->getConfig()['security']['checks'])) {
            return $this;
        }
        if ($this->isSkippableRequest()) {
            return $this;
        }

        foreach ($this->getConfig()['security']['checks'] as $cDef) {
            // initialize check
            $check = \Maleficarum\Ioc\Container::get($cDef);

            // validate check object
            if (!($check instanceof \Maleficarum\Api\Security\Check\AbstractCheck)) {
                throw new \RuntimeException('Invalid security check object. \Maleficarum\Api\Security\Manager::verify()');
            }

            // execute check object
            if (!$check->execute()) {
                throw new \Maleficarum\Exception\SecurityException('Security check failed (' . get_class($check) . ').');
            }
        }

        return $this;
    }

    /**
     * Check if checks should be skipped for current request.
     *
     * @return bool
     */
    private function isSkippableRequest(): bool {
        if (is_null($this->getRequest())) {
            return false;
        }

        $path = parse_url($this->getRequest()->getUri(), \PHP_URL_PATH);
        if (false === $path) {
            throw new \Maleficarum\Exception\SecurityException('Security check failed - invalid URL provided.');
        }
        
        $securityConfig = $this->getConfig()['security'];
        $method = $this->getRequest()->getMethod();

        if (isset($securityConfig['skip_routes']) && is_array($securityConfig['skip_routes'])) {
            // wildcard on routes - skip all
            if (array_key_exists('*', $securityConfig['skip_routes'])) {
                return true;
            }

            // specific route defined as skip
            if (array_key_exists($path, $securityConfig['skip_routes'])) {
                // wildcard on method or method matches - skip checks
                if (trim($securityConfig['skip_routes'][$path]) === '*' || trim($securityConfig['skip_routes'][$path]) === $method) {
                    return true;
                }
            }
        }

        if (isset($securityConfig['skip_regex_routes']) && is_array($securityConfig['skip_regex_routes'])) {
            foreach ($securityConfig['skip_regex_routes'] as $route => $value) {
                if (\preg_match($route, $path)) {
                    if (trim($securityConfig['skip_regex_routes'][$route]) === '*' || trim($securityConfig['skip_regex_routes'][$route]) === $method) {
                        return true;
                    }
                }
            }
        }

        return false;
    }
}
