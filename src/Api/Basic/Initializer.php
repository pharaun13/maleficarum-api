<?php
/**
 * This class contains default initializers used as Maleficarum bootstrap methods.
 */
declare (strict_types=1);

namespace Maleficarum\Api\Basic;

class Initializer {
	/**
	 * Set up logger object.
	 * @param \Maleficarum\Api\Bootstrap $bootstrap
	 * @return string
	 */
	static public function setUpLogger(array $opts = []) : string {
		// load default builder if skip not requested
		$builders = $opts['builders'] ?? [];
		is_array($builders) or $builders = [];
		isset($builders['logger']['skip']) or \Maleficarum\Ioc\Container::get('Maleficarum\Api\Basic\Builder')->register('logger');
		
		// load logger object
		\Maleficarum\Ioc\Container::registerDependency('Maleficarum\Logger', \Maleficarum\Ioc\Container::get('Monolog\Logger'));

		// return initializer name
		return __METHOD__;
	}

	/**
	 * Prepare and register the command queue connection object.
	 * @param \Maleficarum\Api\Bootstrap $bootstrap
	 * @return string
	 */
	static public function setUpQueue(array $opts = []) : string {
		// load default builder if skip not requested
		$builders = $bootstrap->getParamContainer()['builders'] ?? [];
		is_array($builders) or $builders = [];
		isset($builders['queue']['skip']) or \Maleficarum\Ioc\Container::get('Maleficarum\Api\Basic\Builder')->register('queue');
		
		// load queue object
		$rabbitConnection = \Maleficarum\Ioc\Container::get('Maleficarum\Rabbitmq\Connection');
		\Maleficarum\Ioc\Container::registerDependency('Maleficarum\CommandQueue', $rabbitConnection);
		
		// return initializer name
		return __METHOD__;
	}

	/* ------------------------------------ Class Methods START ---------------------------------------- */

	/**
	 * Set up error/exception handling.
	 * @return string
	 */
	static public function setUpErrorHandling() : string {
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
	 * Detect application environment.
	 * @param array $opts
	 * @throws \RuntimeException
	 * @return string
	 */
	static public function setUpDebugLevel(array $opts = []) : string {
		try {
			$environment = \Maleficarum\Ioc\Container::getDependency('Maleficarum\Environment');
			$environment = $environment->getCurrentEnvironment();
		} catch (\Exception $e) {
			throw new \RuntimeException(sprintf('Environment object not initialized. \%s', __METHOD__));
		}
		
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
	 * Prepare and register the security object.
	 * @param array $opts
	 * @return string
	 */
	static public function setUpSecurity(array $opts = []) : string {
		// load default builder if skip not requested
		$builders = $opts['builders'] ?? [];
		is_array($builders) or $builders = [];
		isset($builders['security']['skip']) or \Maleficarum\Ioc\Container::get('Maleficarum\Api\Basic\Builder')->register('security');
		
		/** @var \Maleficarum\Api\Security\Manager $security */
		$security = \Maleficarum\Ioc\Container::get('Maleficarum\Api\Security\Manager');
		$security->verify();

		// return initializer name
		return __METHOD__;
	}

	/**
	 * Bootstrap step method - prepare and register application routes.
	 * @param array $opts
	 * @throws \RuntimeException
	 * @return string
	 */
	static public function setUpRoutes(array $opts = []) : string {
		try {
			$request = \Maleficarum\Ioc\Container::getDependency('Maleficarum\Request');
		} catch (\RuntimeException $e) {
			throw new \RuntimeException(sprintf('Request object not initialized. \%s', __METHOD__));
		}
		
		// validate input container
		$app = $opts['app'] ?? null;
		$routesPath = $opts['routes'] ?? null;
		
		if (!is_object($app)) throw new \RuntimeException(sprintf('Phalcon application not defined for bootstrap. \%s()', __METHOD__));
		if (!is_readable($routesPath)) throw new \RuntimeException(sprintf('Routes path not readable. \%s()', __METHOD__));

		// include outside routes
		$route = explode('?', strtolower($request->getUri()))[0];
		$route = explode('/', preg_replace('/^\//', '', $route));
		$route = ucfirst(array_shift($route));

		// set route filename for root path
		if (0 === mb_strlen($route)) {
			$route = 'Generic';
		}

		$path = $routesPath . DIRECTORY_SEPARATOR . $route . '.php';
		if (is_readable($path)) {
			require_once $path;
		}

		/** DEFAULT Route: call the default controller to check for redirect SEO entries **/
		$app->notFound(function () {
			\Maleficarum\Ioc\Container::get('Maleficarum\Api\Controller\Fallback')->__remap('notFound');
		});
		
		// return initializer name
		return __METHOD__;
	}

	/**
	 * Register default Controller builder function.
	 * @param array $opts
	 * @return string
	 */
	static public function setUpController(array $opts = []) : string {
		// load default builder if skip not requested
		$builders = $opts['builders'] ?? [];
		is_array($builders) or $builders = [];
		isset($builders['controller']['skip']) or \Maleficarum\Ioc\Container::get('Maleficarum\Api\Basic\Builder')->register('controller');

		// return initializer name
		return __METHOD__;
	}
	
	/* ------------------------------------ Class Methods END ------------------------------------------ */
}

