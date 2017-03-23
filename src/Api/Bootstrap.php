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
    const INITIALIZER_TIME_PROFILER = ['Maleficarum\Api\Basic\Initializer', 'setUpTimeProfiler'];
    const INITIALIZER_DATABASE_PROFILER = ['Maleficarum\Api\Basic\Initializer', 'setUpDbProfiler'];
    const INITIALIZER_ENVIRONMENT = ['Maleficarum\Api\Basic\Initializer', 'setUpEnvironment'];
    const INITIALIZER_CONFIG = ['Maleficarum\Api\Basic\Initializer', 'setUpConfig'];
    const INITIALIZER_REQUEST = ['Maleficarum\Api\Basic\Initializer', 'setUpRequest'];
    const INITIALIZER_RESPONSE = ['Maleficarum\Api\Basic\Initializer', 'setUpResponse'];
    const INITIALIZER_LOGGER = ['Maleficarum\Api\Basic\Initializer', 'setUpLogger'];
    const INITIALIZER_QUEUE = ['Maleficarum\Api\Basic\Initializer', 'setUpQueue'];
    
    const INITIALIZER_SECURITY = ['Maleficarum\Api\Basic\Initializer', 'setUpSecurity'];
    const INITIALIZER_ROUTES = ['Maleficarum\Api\Basic\Initializer', 'setUpRoutes'];
    const INITIALIZER_CONTROLLER = ['Maleficarum\Api\Basic\Initializer', 'setUpController'];
    
    /* ------------------------------------ Class Constant END ----------------------------------------- */
    
    /* ------------------------------------ Class Property START --------------------------------------- */
    
    /**
     * Internal storage for API component initializers to run during bootstrap execution.
     * 
     * @var array
     */
    private $initializers = [];
    
    /**
     * Internal storage for the config object
     *
     * @var \Maleficarum\Config\AbstractConfig|null
     */
    private $config = null;

    /**
     * Internal storage for the time profiler
     *
     * @var \Maleficarum\Profiler\Time|null
     */
    private $timeProfiler = null;

    /**
     * Internal storage for the database profiler
     *
     * @var \Maleficarum\Profiler\Database|null
     */
    private $dbProfiler = null;

    /**
     * Internal storage for the request object
     *
     * @var \Maleficarum\Request\Request
     */
    private $request = null;

    /**
     * Internal storage for the response object
     *
     * @var \Maleficarum\Response\Response
     */
    private $response = null;

    /**
     * Internal storage for logger object
     *
     * @var \Psr\Log\LoggerInterface|null
     */
    private $logger = null;
    
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
            if (!is_callable($initializer)) throw new \LogicException(sprintf('Invalid initializer passed to the bootstrap initialization process. \%s::\%s()', static::class, __METHOD__));
            $init_name = $initializer($this->getParamContainer());

            !is_null($this->getTimeProfiler()) && $this->getTimeProfiler()->addMilestone('initializer_'.$key, 'Initializer executed ('.$init_name.').');
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
        is_null($this->getTimeProfiler()) or $this->getTimeProfiler()->end();

        // output any response data
        is_null($this->getResponse()) or $this->getResponse()->output();

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
     * @return \Maleficarum\Profiler\Time|null
     */
    public function getTimeProfiler() {
        return $this->timeProfiler;
    }

    /**
     * @param  $timeProfiler
     * @return \Maleficarum\Api\Bootstrap
     */
    public function setTimeProfiler(\Maleficarum\Profiler\Time $timeProfiler = null) : \Maleficarum\Api\Bootstrap {
        $this->timeProfiler = $timeProfiler;
        return $this;
    }

    /**
     * @return \Maleficarum\Profiler\Database|null
     */
    public function getDbProfiler() {
        return $this->dbProfiler;
    }
    
    /**
     * @param \Maleficarum\Profiler\Database $dbProfiler
     * @return \Maleficarum\Api\Bootstrap
     */
    public function setDbProfiler(\Maleficarum\Profiler\Database $dbProfiler = null) : \Maleficarum\Api\Bootstrap {
        $this->dbProfiler = $dbProfiler;
        return $this;
    }

    /**
     * @return \Maleficarum\Config\AbstractConfig|null
     */
    public function getConfig() {
        return $this->config;
    }
    
    /**
     * @param \Maleficarum\Config\AbstractConfig $config
     * @return \Maleficarum\Api\Bootstrap
     */
    public function setConfig(\Maleficarum\Config\AbstractConfig $config = null) : \Maleficarum\Api\Bootstrap {
        $this->config = $config;
        return $this;
    }

    /**
     * @return \Maleficarum\Request\Request|null
     */
    public function getRequest() {
        return $this->request;
    }
    
    /**
     * @param \Maleficarum\Request\Request $request
     * @return \Maleficarum\Api\Bootstrap
     */
    public function setRequest(\Maleficarum\Request\Request $request = null ) : \Maleficarum\Api\Bootstrap {
        $this->request = $request;
        return $this;
    }

    /**
     * @return \Maleficarum\Response\Response
     */
    public function getResponse() {
        return $this->response;
    }
    
    /**
     * @param \Maleficarum\Response\AbstractResponse $response
     * @return \Maleficarum\Api\Bootstrap
     */
    public function setResponse(\Maleficarum\Response\AbstractResponse $response = null) : \Maleficarum\Api\Bootstrap {
        $this->response = $response;
        return $this;
    }
    
    /**
     * @return null|\Psr\Log\LoggerInterface
     */
    public function getLogger() {
        return $this->logger;
    }

    /**
     * @param \Psr\Log\LoggerInterface $logger
     * @return \Maleficarum\Api\Bootstrap
     */
    public function setLogger(\Psr\Log\LoggerInterface $logger = null) : \Maleficarum\Api\Bootstrap {
        $this->logger = $logger;
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
