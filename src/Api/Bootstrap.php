<?php
/**
 * This class manages all bootstrap operations for the application.
 */

namespace Maleficarum\Api;

class Bootstrap
{
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

    /* ------------------------------------ Bootstrap methods START ------------------------------------ */
    /**
     * Perform full api init.
     *
     * @param \Phalcon\Mvc\Micro $app
     * @param string $routesPath
     * @param float $start
     *
     * @return \Maleficarum\Api\Bootstrap
     */
    public function init(\Phalcon\Mvc\Micro $app, string $routesPath, float $start = 0.0) : \Maleficarum\Api\Bootstrap {
        return $this
            ->setUpErrorHandling()
            ->setUpProfilers($start)
            ->setUpEnvironment()
            ->setUpConfig()
            ->setUpRequest()
            ->setUpResponse()
            ->setUpLogger()
            ->setUpQueue()
            ->setUpDatabase()
            ->setUpSecurity()
            ->setUpRoutes($app, $routesPath);
    }

    /**
     * Perform any final maintenance actions. This will be called at the end of a request.
     *
     * @return \Maleficarum\Api\Bootstrap
     */
    public function conclude() : \Maleficarum\Api\Bootstrap {
        // complete profiling
        $this->timeProfiler->end();

        // output any response data
        $this->response->output();

        return $this;
    }

    /**
     * Bootstrap step method - set up error/exception handling.
     *
     * @return \Maleficarum\Api\Bootstrap
     */
    private function setUpErrorHandling() : \Maleficarum\Api\Bootstrap {
        /** @var \Maleficarum\Handler\Http\Strategy\JsonStrategy $strategy */
        $strategy = \Maleficarum\Ioc\Container::get('Maleficarum\Handler\Http\Strategy\JsonStrategy');
        /** @var \Maleficarum\Handler\Http\ExceptionHandler $handler */
        $handler = \Maleficarum\Ioc\Container::get('Maleficarum\Handler\Http\ExceptionHandler', [$strategy]);

        \set_exception_handler([$handler, 'handle']);
        \set_error_handler([\Maleficarum\Ioc\Container::get('Maleficarum\Handler\ErrorHandler'), 'handle']);

        return $this;
    }

    /**
     * Bootstrap step method - set up logger object.
     *
     * @return \Maleficarum\Api\Bootstrap
     */
    private function setUpLogger() : \Maleficarum\Api\Bootstrap {
        $this->logger = \Maleficarum\Ioc\Container::get('Monolog\Logger');
        \Maleficarum\Ioc\Container::registerDependency('Maleficarum\Logger', $this->logger);

        !is_null($this->timeProfiler) && $this->timeProfiler->addMilestone('logger_init', 'Logger initialized.');

        return $this;
    }

    /**
     * Bootstrap step method - set up profiler objects.
     *
     * @param float $start
     *
     * @return \Maleficarum\Api\Bootstrap
     */
    private function setUpProfilers(float $start = 0.0) : \Maleficarum\Api\Bootstrap {
        $this->timeProfiler = \Maleficarum\Ioc\Container::get('Maleficarum\Profiler\Time')->begin($start);
        \Maleficarum\Ioc\Container::registerDependency('Maleficarum\Profiler\Time', $this->timeProfiler);

        $this->dbProfiler = \Maleficarum\Ioc\Container::get('Maleficarum\Profiler\Database');
        \Maleficarum\Ioc\Container::registerDependency('Maleficarum\Profiler\Database', $this->dbProfiler);

        $this->timeProfiler->addMilestone('profiler_init', 'All profilers initialized.');

        return $this;
    }

    /**
     * Bootstrap step method - detect application environment.
     *
     * @return \Maleficarum\Api\Bootstrap
     * @throws \RuntimeException
     */
    private function setUpEnvironment() : \Maleficarum\Api\Bootstrap {
        /* @var $environment \Maleficarum\Environment\Server */
        $environment = \Maleficarum\Ioc\Container::get('Maleficarum\Environment\Server');
        \Maleficarum\Ioc\Container::registerDependency('Maleficarum\Environment', $environment);

        // fetch current env
        $environment = $environment->getCurrentEnvironment();

        // set handler debug level and error display value based on env
        if (in_array($environment, ['local', 'development', 'staging'])) {
            \Maleficarum\Handler\AbstractHandler::setDebugLevel(\Maleficarum\Handler\AbstractHandler::DEBUG_LEVEL_FULL);
            ini_set('display_errors', '1');
        } elseif ('uat' === $environment) {
            \Maleficarum\Handler\AbstractHandler::setDebugLevel(\Maleficarum\Handler\AbstractHandler::DEBUG_LEVEL_LIMITED);
            ini_set('display_errors', '0');
        } elseif ('production' === $environment) {
            \Maleficarum\Handler\AbstractHandler::setDebugLevel(\Maleficarum\Handler\AbstractHandler::DEBUG_LEVEL_CRUCIAL);
            ini_set('display_errors', '0');
        } else {
            throw new \RuntimeException(sprintf('Unrecognised environment. \%s::setUpEnvironment()', static::class));
        }

        // error reporting is to be enabled on all envs (non-dev ones will have errors logged into syslog)
        error_reporting(-1);

        !is_null($this->timeProfiler) && $this->timeProfiler->addMilestone('env_init', 'Environment initialized.');

        return $this;
    }

    /**
     * Bootstrap step method - prepare, load and register the config object.
     *
     * @return \Maleficarum\Api\Bootstrap
     * @throws \RuntimeException
     */
    private function setUpConfig() : \Maleficarum\Api\Bootstrap {
        $this->config = \Maleficarum\Ioc\Container::get('Maleficarum\Config\Ini\Config', ['id' => 'config.ini']);
        \Maleficarum\Ioc\Container::registerDependency('Maleficarum\Config', $this->config);

        // check the disabled/enabled switch
        if (!isset($this->config['global']['enabled']) || (!$this->config['global']['enabled'])) throw new \RuntimeException(sprintf('Application disabled! \%s::setUpConfig()', static::class));

        !is_null($this->timeProfiler) && $this->timeProfiler->addMilestone('conf_init', 'Config initialized.');

        return $this;
    }

    /**
     * Bootstrap step method - prepare and register the request object.
     *
     * @return \Maleficarum\Api\Bootstrap
     */
    private function setUpRequest() : \Maleficarum\Api\Bootstrap {
        $this->request = \Maleficarum\Ioc\Container::get('Maleficarum\Request\Request');
        \Maleficarum\Ioc\Container::registerDependency('Maleficarum\Request', $this->request);

        !is_null($this->timeProfiler) && $this->timeProfiler->addMilestone('request_init', 'Request initialized.');

        return $this;
    }

    /**
     * Bootstrap step method - prepare and register the response object.
     *
     * @return \Maleficarum\Api\Bootstrap
     */
    private function setUpResponse() : \Maleficarum\Api\Bootstrap {
        $this->response = \Maleficarum\Ioc\Container::get('Maleficarum\Response\Http\Response');
        \Maleficarum\Ioc\Container::registerDependency('Maleficarum\Response', $this->response);

        !is_null($this->timeProfiler) && $this->timeProfiler->addMilestone('response_init', 'Response initialized.');

        return $this;
    }

    /**
     * Bootstrap step method - prepare and register the security object.
     *
     * @return \Maleficarum\Api\Bootstrap
     */
    private function setUpSecurity() : \Maleficarum\Api\Bootstrap {
        /** @var \Maleficarum\Api\Security\Manager $security */
        $security = \Maleficarum\Ioc\Container::get('Maleficarum\Api\Security\Manager');
        $security->verify();

        !is_null($this->timeProfiler) && $this->timeProfiler->addMilestone('security_init', 'Security verified.');

        return $this;
    }

    /**
     * Bootstrap step method - prepare and register the command queue connection object.
     *
     * @return \Maleficarum\Api\Bootstrap
     */
    private function setUpQueue() : \Maleficarum\Api\Bootstrap {
        if (\Maleficarum\Ioc\Container::isRegistered('Maleficarum\Rabbitmq\Connection')) {
            /** @var \Maleficarum\Rabbitmq\Connection $rabbitConnection */
            $rabbitConnection = \Maleficarum\Ioc\Container::get('Maleficarum\Rabbitmq\Connection');
            \Maleficarum\Ioc\Container::registerDependency('Maleficarum\CommandQueue', $rabbitConnection);

            !is_null($this->timeProfiler) && $this->timeProfiler->addMilestone('queue_init', 'RabbitMQ broker connection initialized.');
        }

        return $this;
    }

    /**
     * Bootstrap step method - prepare and register database shard manager.
     *
     * @return \Maleficarum\Api\Bootstrap
     */
    private function setUpDatabase() : \Maleficarum\Api\Bootstrap {
        /** @var \Maleficarum\Api\Database\Manager $shards */
        $shards = \Maleficarum\Ioc\Container::get('Maleficarum\Api\Database\Manager');
        \Maleficarum\Ioc\Container::registerDependency('Maleficarum\Database', $shards);

        !is_null($this->timeProfiler) && $this->timeProfiler->addMilestone('db_init', 'Database shard manager initialized.');

        return $this;
    }

    /**
     * Bootstrap step method - prepare and register application routes.
     *
     * @param \Phalcon\Mvc\Micro $app
     * @param string $routesPath
     *
     * @return \Maleficarum\Api\Bootstrap
     */
    private function setUpRoutes(\Phalcon\Mvc\Micro $app, string $routesPath) : \Maleficarum\Api\Bootstrap {
        // include outside routes
        $route = explode('?', strtolower($this->request->getURI()))[0];
        $route = explode('/', preg_replace('/^\//', '', $route));
        $route = ucfirst(array_shift($route));

        // set route filename for root path
        if (0 === mb_strlen($route)) {
            $route = 'Generic';
        }

        $path = $routesPath . DIRECTORY_SEPARATOR . $route . '.php';
        if (is_readable($path)) {
            $request = $this->request;
            require_once $path;
        }

        /** DEFAULT Route: call the default controller to check for redirect SEO entries **/
        $app->notFound(function () {
            \Maleficarum\Ioc\Container::get('Maleficarum\Api\Controller\Fallback')->__remap('notFound');
        });

        return $this;
    }
    /* ------------------------------------ Bootstrap methods END -------------------------------------- */
}
