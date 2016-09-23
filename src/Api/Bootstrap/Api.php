<?php
/**
 * This class manages all bootstrap operations for the application.
 */

namespace Maleficarum\Api\Bootstrap;

class Api
{
    /**
     * Internal storage for the config object
     *
     * @var \Maleficarum\Api\Config\AbstractConfig|null
     */
    private $config = null;

    /**
     * Internal storage for the time profiler
     *
     * @var \Maleficarum\Api\Profiler\Time|null
     */
    private $timeProfiler = null;

    /**
     * Internal storage for the database profiler
     *
     * @var \Maleficarum\Api\Profiler\Database|null
     */
    private $dbProfiler = null;

    /**
     * Internal storage for the request object
     *
     * @var \Maleficarum\Api\Request\Request
     */
    private $request = null;

    /**
     * Internal storage for the response object
     *
     * @var \Maleficarum\Api\Response\Response
     */
    private $response = null;

    /**
     * Perform full api init.
     *
     * @param \Phalcon\Mvc\Micro $app
     * @param string $routesPath
     * @param int|float $start
     *
     * @return \Maleficarum\Api\Bootstrap\Api
     */
    public function init(\Phalcon\Mvc\Micro $app, $routesPath, $start = 0)
    {
        return $this
            ->setUpErrorHandling()
            ->setUpProfilers($start)
            ->setUpEnvironment()
            ->setUpConfig()
            ->setUpRequest()
            ->setUpResponse()
            ->setUpSecurity()
            ->setUpQueue()
            ->setUpDatabase()
            ->setUpRoutes($app, $routesPath);
    }

    /**
     * Bootstrap step method - prepare and register application routes.
     *
     * @param \Phalcon\Mvc\Micro $app
     * @param string $routesPath
     *
     * @throws \Maleficarum\Api\Exception\NotFoundException
     * @return \Maleficarum\Api\Bootstrap\Api
     */
    private function setUpRoutes(\Phalcon\Mvc\Micro $app, $routesPath)
    {
        // include outside routes
        $route = explode('?', strtolower($this->request->getURI()))[0];
        $route = explode('/', preg_replace('/^\//', '', $route));
        $route = ucfirst(array_shift($route));

        $path = $routesPath . DIRECTORY_SEPARATOR . $route . '.php';
        if (is_readable($path)) {
            $request = $this->request;
            require_once $path;
        }

        /** DEFAULT Route: call the default controller to check for redirect SEO entries **/
        $app->notFound(function () {
            \Maleficarum\Ioc\Container::get('Controller\Fallback')->__remap('notFound');
        });

        return $this;
    }

    /**
     * Bootstrap step method - prepare and register database shard manager.
     *
     * @return \Maleficarum\Api\Bootstrap\Api
     */
    private function setUpDatabase()
    {
        /** @var \Maleficarum\Api\Database\Manager $shards */
        $shards = \Maleficarum\Ioc\Container::get('Maleficarum\Api\Database\Manager');
        \Maleficarum\Ioc\Container::registerDependency('Maleficarum\Api\Database', $shards);

        !is_null($this->timeProfiler) && $this->timeProfiler->addMilestone('db_init', 'Database shard manager initialized.');

        return $this;
    }

    /**
     * Bootstrap step method - prepare and register the command queue connection object.
     *
     * @return \Maleficarum\Api\Bootstrap\Api
     */
    private function setUpQueue()
    {
        /** @var \Maleficarum\Api\Rabbitmq\Connection $rabbitConnection */
        $rabbitConnection = \Maleficarum\Ioc\Container::get('Maleficarum\Api\Rabbitmq\Connection');
        \Maleficarum\Ioc\Container::registerDependency('Maleficarum\Api\CommandQueue', $rabbitConnection);

        !is_null($this->timeProfiler) && $this->timeProfiler->addMilestone('queue_init', 'RabbitMQ broker connection initialized.');

        return $this;
    }

    /**
     * Bootstrap step method - prepare and register the security object.
     *
     * @return \Maleficarum\Api\Bootstrap\Api
     */
    private function setUpSecurity()
    {
        /** @var \Maleficarum\Api\Security\Manager $security */
        $security = \Maleficarum\Ioc\Container::get('Maleficarum\Api\Security\Manager');
        $security->verify();

        !is_null($this->timeProfiler) && $this->timeProfiler->addMilestone('security_init', 'Security verified.');

        return $this;
    }

    /**
     * Bootstrap step method - prepare and register the response object.
     *
     * @return \Maleficarum\Api\Bootstrap\Api
     */
    private function setUpResponse()
    {
        $this->response = \Maleficarum\Ioc\Container::get('Maleficarum\Api\Response\Response');
        \Maleficarum\Ioc\Container::registerDependency('Maleficarum\Api\Response', $this->response);

        !is_null($this->timeProfiler) && $this->timeProfiler->addMilestone('response_init', 'Response initialized.');

        return $this;
    }

    /**
     * Bootstrap step method - prepare and register the request object.
     *
     * @return \Maleficarum\Api\Bootstrap\Api
     */
    private function setUpRequest()
    {
        $this->request = \Maleficarum\Ioc\Container::get('Maleficarum\Api\Request\Request');
        \Maleficarum\Ioc\Container::registerDependency('Maleficarum\Api\Request', $this->request);

        !is_null($this->timeProfiler) && $this->timeProfiler->addMilestone('request_init', 'Request initialized.');

        return $this;
    }

    /**
     * Bootstrap step method - prepare, load and register the config object.
     *
     * @return \Maleficarum\Api\Bootstrap\Api
     * @throws \RuntimeException
     */
    private function setUpConfig()
    {
        $this->config = \Maleficarum\Ioc\Container::get('Maleficarum\Api\Config\Ini\Config', ['id' => 'config.ini']);
        \Maleficarum\Ioc\Container::registerDependency('Maleficarum\Api\Config', $this->config);

        // check the disabled/enabled switch
        if (!isset($this->config['global']['enabled']) || (!$this->config['global']['enabled'])) throw new \RuntimeException('Application disabled! \Maleficarum\Api\Bootstrap\Api::setUpConfig()');

        !is_null($this->timeProfiler) && $this->timeProfiler->addMilestone('conf_init', 'Config initialized.');

        return $this;
    }

    /**
     * Bootstrap step method - detect application environment.
     *
     * @return \Maleficarum\Api\Bootstrap\Api
     * @throws \RuntimeException
     */
    private function setUpEnvironment()
    {
        /* @var $env \Maleficarum\Api\Environment\Server */
        $env = \Maleficarum\Ioc\Container::get('Maleficarum\Api\Environment\Server');
        \Maleficarum\Ioc\Container::registerDependency('Maleficarum\Api\Environment', $env);

        // fetch current env
        $env = $env->getCurrentEnvironment();

        // set handler debug level and error display value based on env
        if (in_array($env, ['local', 'development', 'staging'])) {
            \Maleficarum\Api\Handler\AbstractHandler::setDebugLevel(\Maleficarum\Api\Handler\AbstractHandler::DEBUG_LEVEL_FULL);
            ini_set('display_errors', '1');
        } elseif ($env === 'uat') {
            \Maleficarum\Api\Handler\AbstractHandler::setDebugLevel(\Maleficarum\Api\Handler\AbstractHandler::DEBUG_LEVEL_LIMITED);
            ini_set('display_errors', '0');
        } elseif ($env === 'production') {
            \Maleficarum\Api\Handler\AbstractHandler::setDebugLevel(\Maleficarum\Api\Handler\AbstractHandler::DEBUG_LEVEL_CRUCIAL);
            ini_set('display_errors', '0');
        } else {
            throw new \RuntimeException('Unrecognised environment. \Maleficarum\Api\Bootstrap\Api::setUpEnvironment()');
        }

        // error reporting is to be enabled on all envs (non-dev ones will have errors logged into syslog)
        error_reporting(-1);

        !is_null($this->timeProfiler) && $this->timeProfiler->addMilestone('env_init', 'Environment initialized.');

        return $this;
    }

    /**
     * Bootstrap step method - set up profiler objects.
     *
     * @param float|null $start
     *
     * @return \Maleficarum\Api\Bootstrap\Api
     */
    private function setUpProfilers($start = null)
    {
        $this->timeProfiler = \Maleficarum\Ioc\Container::get('Maleficarum\Api\Profiler\Time')->begin($start);
        \Maleficarum\Ioc\Container::registerDependency('Maleficarum\Api\Profiler\Time', $this->timeProfiler);

        $this->dbProfiler = \Maleficarum\Ioc\Container::get('Maleficarum\Api\Profiler\Database');
        \Maleficarum\Ioc\Container::registerDependency('Maleficarum\Api\Profiler\Database', $this->dbProfiler);

        $this->timeProfiler->addMilestone('profiler_init', 'All profilers initialized.');

        return $this;
    }

    /**
     * Bootstrap step method - set up error/exception handling.
     *
     * @return \Maleficarum\Api\Bootstrap\Api
     */
    private function setUpErrorHandling()
    {
        \set_exception_handler([\Maleficarum\Ioc\Container::get('Maleficarum\Api\Handler\Exception'), 'handle']);
        \set_error_handler([\Maleficarum\Ioc\Container::get('Maleficarum\Api\Handler\Error'), 'handle']);

        return $this;
    }

    /**
     * Perform any final maintenance actions. This will be called at the end of a request.
     *
     * @return \Maleficarum\Api\Bootstrap\Api
     */
    public function conclude()
    {
        // complete profiling
        $this->timeProfiler->end();

        // output any response data
        $this->response->output();

        return $this;
    }
}
