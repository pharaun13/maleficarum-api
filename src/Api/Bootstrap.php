<?php
/**
 * PHP 7.0 compatible
 */
declare (strict_types=1);

/**
 * This class manages all bootstrap operations for the application.
 */
namespace Maleficarum\Api;

class Bootstrap {

    /* ------------------------------------ Class Constant START --------------------------------------- */
    
    const INITIALIZER_ERRORS = ['Maleficarum\Api\Basic\Initializer', 'setUpErrorHandling'];
    const INITIALIZER_SECURITY = ['Maleficarum\Api\Basic\Initializer', 'setUpSecurity'];
    const INITIALIZER_ROUTES = ['Maleficarum\Api\Basic\Initializer', 'setUpRoutes'];
    const INITIALIZER_CONTROLLER = ['Maleficarum\Api\Basic\Initializer', 'setUpController'];
    const INITIALIZER_DEBUG_LEVEL = ['Maleficarum\Api\Basic\Initializer', 'setUpDebugLevel'];
    
    
    
    
    const INITIALIZER_LOGGER = ['Maleficarum\Api\Basic\Initializer', 'setUpLogger'];
    const INITIALIZER_QUEUE = ['Maleficarum\Api\Basic\Initializer', 'setUpQueue'];
    
    /* ------------------------------------ Class Constant END ----------------------------------------- */
    
    /* ------------------------------------ Class Property START --------------------------------------- */
    
    /**
     * Internal storage for API component initializers to run during bootstrap execution.
     * 
     * @var array
     */
    private $initializers = [];
    
    /**
     * Internal storage for bootstrap initializer param container.
     * 
     * @var array
     */
    private $paramContainer = [];
    
    /* ------------------------------------ Class Property END ----------------------------------------- */

    /**
     * Run all defined bootstrap initializers.
     * @return Bootstrap
     */
    public function initialize() : \Maleficarum\Api\Bootstrap {
        // register bootstrap as dependency for use in initializer steps
        \Maleficarum\Ioc\Container::registerDependency('Maleficarum\Bootstrap', $this);
        
        // validate and execute initializers
        foreach ($this->getInitializers() as $key => $initializer) {
            if (!is_callable($initializer)) {
                var_dump(class_exists('\Maleficarum\Response\Initializer\Initializer'));
                var_dump($initializer); exit;} //throw new \LogicException(sprintf('Invalid initializer passed to the bootstrap initialization process. \%s::\%s()', static::class, __METHOD__));
            $init_name = $initializer($this->getParamContainer());

            try {
                \Maleficarum\Ioc\Container::getDependency('Maleficarum\Profiler\Time')->addMilestone('initializer_'.$key, 'Initializer executed ('.$init_name.').');
            } catch (\RuntimeException $e) {}
        }
        
        return $this;
    }

    /**
     * Perform any final maintenance actions. This will be called at the end of a request.
     *
     * @return \Maleficarum\Api\Bootstrap
     */
    public function conclude() : \Maleficarum\Api\Bootstrap {
        // complete profiling
        try {
            \Maleficarum\Ioc\Container::getDependency('Maleficarum\Profiler\Time')->end();
        } catch (\RuntimeException $e) {}

        // output any response data
        try {
            \Maleficarum\Ioc\Container::getDependency('Maleficarum\Response')->output();
        } catch (\RuntimeException $e) {}

        return $this;
    }
    
    /* ------------------------------------ Setters & Getters START ------------------------------------ */
    
    /**
     * @return array
     */
    protected function getInitializers() : array {
        return $this->initializers;
    }

    /**
     * @param array $initializers
     * @return \Maleficarum\Api\Bootstrap
     */
    public function setInitializers(array $initializers) : \Maleficarum\Api\Bootstrap {
        $this->initializers = $initializers;
        return $this;
    }
    
    /**
     * @return array
     */
    public function getParamContainer() {
        return $this->paramContainer;
    }

    /**
     * @param array $paramContainer
     * @return \Maleficarum\Api\Bootstrap
     */
    public function setParamContainer(array $paramContainer = []) : \Maleficarum\Api\Bootstrap {
        $this->paramContainer = $paramContainer;
        return $this;
    }
    
    /* ------------------------------------ Setters & Getters END -------------------------------------- */
}
