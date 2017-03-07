<?php
/**
 * PHP 7.0 compatible
 */
declare (strict_types=1);

/**
 * This class contains default initializers used as Maleficarum bootstrap methods.
 */
namespace Maleficarum\Api\Initializers;

class Basic {
	/**
	 * Set up error/exception handling.
	 * @param \Maleficarum\Api\Bootstrap $bootstrap
	 * @return string
	 */
	static public function setUpErrorHandling(\Maleficarum\Api\Bootstrap $bootstrap) : string {
		/** @var \Maleficarum\Handler\Http\Strategy\JsonStrategy $strategy */
		$strategy = \Maleficarum\Ioc\Container::get('Maleficarum\Handler\Http\Strategy\JsonStrategy');
		
		/** @var \Maleficarum\Handler\Http\ExceptionHandler $handler */
		$handler = \Maleficarum\Ioc\Container::get('Maleficarum\Handler\Http\ExceptionHandler', [$strategy]);

		\set_exception_handler([$handler, 'handle']);
		\set_error_handler([\Maleficarum\Ioc\Container::get('Maleficarum\Handler\ErrorHandler'), 'handle']);

		// return initializer name
		return __METHOD__;
	}

	/**
	 * Set up time profiler object.
	 * @param \Maleficarum\Api\Bootstrap $bootstrap
	 * @return string
	 */
	static public function setUpTimeProfiler(\Maleficarum\Api\Bootstrap $bootstrap) : string {
		$bootstrap->setTimeProfiler(\Maleficarum\Ioc\Container::get('Maleficarum\Profiler\Time')->begin((float)$bootstrap->getParamContainer()['start'] ?? 0));
		\Maleficarum\Ioc\Container::registerDependency('Maleficarum\Profiler\Time', $bootstrap->getTimeProfiler());

		// return initializer name
		return __METHOD__;
	}

	/**
	 * Set up database profiler object.
	 * @param \Maleficarum\Api\Bootstrap $bootstrap
	 * @return string
	 */
	static public function setUpDbProfiler(\Maleficarum\Api\Bootstrap $bootstrap) : string {
		$bootstrap->setDbProfiler(\Maleficarum\Ioc\Container::get('Maleficarum\Profiler\Database'));
		\Maleficarum\Ioc\Container::registerDependency('Maleficarum\Profiler\Database', $bootstrap->getDbProfiler());

		// return initializer name
		return __METHOD__;
	}

	/**
	 * Detect application environment.
	 * @param \Maleficarum\Api\Bootstrap $bootstrap
	 * @throws \RuntimeException
	 * @return string
	 */
	static public function setUpEnvironment(\Maleficarum\Api\Bootstrap $bootstrap) : string {
		/* @var $environment \Maleficarum\Environment\Server */
		$environment = \Maleficarum\Ioc\Container::get('Maleficarum\Environment\Server');
		\Maleficarum\Ioc\Container::registerDependency('Maleficarum\Environment', $environment);

		// error reporting is to be enabled on all envs (non-dev ones will have errors logged into syslog)
		error_reporting(-1);
		
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
			throw new \RuntimeException(sprintf('Unrecognised environment. \%s', __METHOD__));
		}

		// return initializer name
		return __METHOD__;
	}

	/**
	 * Prepare, load and register the config object.
	 * @param \Maleficarum\Api\Bootstrap $bootstrap
	 * @throws \RuntimeException
	 * @return string
	 */
	static public function setUpConfig(\Maleficarum\Api\Bootstrap $bootstrap) : string {
		$bootstrap->setConfig(\Maleficarum\Ioc\Container::get('Maleficarum\Config\Ini\Config', ['id' => 'config.ini']));
		\Maleficarum\Ioc\Container::registerDependency('Maleficarum\Config', $bootstrap->getConfig());

		// check the disabled/enabled switch
		$config = $bootstrap->getConfig();
		if (!isset($config['global']['enabled']) || (!$config['global']['enabled'])) throw new \RuntimeException(sprintf('Application disabled! \%s()', __METHOD__));

		// return initializer name
		return __METHOD__;
	}

	/**
	 * Prepare and register the request object.
	 *
	 * @param \Maleficarum\Api\Bootstrap $bootstrap
	 * @return string
	 */
	static public function setUpRequest(\Maleficarum\Api\Bootstrap $bootstrap) : string {
		$bootstrap->setRequest(\Maleficarum\Ioc\Container::get('Maleficarum\Request\Request'));
		\Maleficarum\Ioc\Container::registerDependency('Maleficarum\Request', $bootstrap->getRequest());

		// return initializer name
		return __METHOD__;
	}

	/**
	 * Prepare and register the response object.
	 * @param \Maleficarum\Api\Bootstrap $bootstrap
	 * @return string
	 */
	static public function setUpResponse(\Maleficarum\Api\Bootstrap $bootstrap) : string {
		$bootstrap->setResponse(\Maleficarum\Ioc\Container::get('Maleficarum\Response\Http\Response'));
		\Maleficarum\Ioc\Container::registerDependency('Maleficarum\Response', $bootstrap->getResponse());
		
		// return initializer name
		return __METHOD__;
	}

	/**
	 * Set up logger object.
	 * @param \Maleficarum\Api\Bootstrap $bootstrap
	 * @return string
	 */
	static public function setUpLogger(\Maleficarum\Api\Bootstrap $bootstrap) : string {
		$bootstrap->setLogger(\Maleficarum\Ioc\Container::get('Monolog\Logger'));
		\Maleficarum\Ioc\Container::registerDependency('Maleficarum\Logger', $bootstrap->getLogger());

		// return initializer name
		return __METHOD__;
	}

	/**
	 * Prepare and register the command queue connection object.
	 * @param \Maleficarum\Api\Bootstrap $bootstrap
	 * @return string
	 */
	static public function setUpQueue(\Maleficarum\Api\Bootstrap $bootstrap) : string {
		$rabbitConnection = \Maleficarum\Ioc\Container::get('Maleficarum\Rabbitmq\Connection');
		\Maleficarum\Ioc\Container::registerDependency('Maleficarum\CommandQueue', $rabbitConnection);
		
		// return initializer name
		return __METHOD__;
	}
	/**
	 * Prepare and register database shard manager.
	 * @param \Maleficarum\Api\Bootstrap $bootstrap
	 * @return string
	 */
	static public function setUpDatabase(\Maleficarum\Api\Bootstrap $bootstrap) : string {
		/** @var \Maleficarum\Api\Database\Manager $shards */
		$shards = \Maleficarum\Ioc\Container::get('Maleficarum\Api\Database\Manager');
		\Maleficarum\Ioc\Container::registerDependency('Maleficarum\Database', $shards);

		// return initializer name
		return __METHOD__;
	}

	/**
	 * Prepare and register the security object.
	 * @param \Maleficarum\Api\Bootstrap $bootstrap
	 * @return string
	 */
	static public function setUpSecurity(\Maleficarum\Api\Bootstrap $bootstrap) : string {
		/** @var \Maleficarum\Api\Security\Manager $security */
		$security = \Maleficarum\Ioc\Container::get('Maleficarum\Api\Security\Manager');
		$security->verify();

		// return initializer name
		return __METHOD__;
	}

	/**
	 * Bootstrap step method - prepare and register application routes.
	 * @param \Maleficarum\Api\Bootstrap $bootstrap
	 * @throws \RuntimeException
	 * @return string
	 */
	static public function setUpRoutes(\Maleficarum\Api\Bootstrap $bootstrap) : string {
		// validate input container
		$app = $bootstrap->getParamContainer()['app'] ?? null;
		$routesPath = $bootstrap->getParamContainer()['routes'] ?? null;
		
		if (!is_object($app)) throw new \RuntimeException(sprintf('Phalcon application not defined for bootstrap. \%s()', __METHOD__));
		if (!is_readable($routesPath)) throw new \RuntimeException(sprintf('Routes path not readable. \%s()', __METHOD__));

		// include outside routes
		$route = explode('?', strtolower($bootstrap->getRequest()->getURI()))[0];
		$route = explode('/', preg_replace('/^\//', '', $route));
		$route = ucfirst(array_shift($route));

		// set route filename for root path
		if (0 === mb_strlen($route)) {
			$route = 'Generic';
		}

		$path = $routesPath . DIRECTORY_SEPARATOR . $route . '.php';
		if (is_readable($path)) {
			$request = $bootstrap->getRequest();
			require_once $path;
		}

		/** DEFAULT Route: call the default controller to check for redirect SEO entries **/
		$app->notFound(function () {
			\Maleficarum\Ioc\Container::get('Maleficarum\Api\Controller\Fallback')->__remap('notFound');
		});
		
		// return initializer name
		return __METHOD__;
	}
}

