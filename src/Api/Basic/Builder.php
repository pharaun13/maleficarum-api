<?php
/**
 * PHP 7.0 compatible
 */
declare (strict_types=1);

/**
 * This class contains default builders for most Maleficarum internal components.
 */
namespace Maleficarum\Api\Basic;

class Builder {
	
	/* ------------------------------------ Class Constant START --------------------------------------- */
	
	const builderList = [
		'security',
		'controller',
	    'logger',
	    'queue'
	];

	/* ------------------------------------ Class Constant END ----------------------------------------- */
	
	/**
	 * Attach default implementation of Maleficarum builder functions to the IOC container.
	 * @param string $type
	 * @param array $opts
	 * @return \Maleficarum\Api\Basic\Builder
	 */
	public function register(string $type, $opts = []) {
		if (in_array($type, self::builderList)) {
		    $type = 'register'.ucfirst($type);
			$this->$type($opts);
	    }
		
		return $this;
	}
	
	/**
	 * Register security builders.
	 * @param array $opts
	 * @return \Maleficarum\Api\Basic\Builder
	 */
	private function registerSecurity(array $opts = []) : \Maleficarum\Api\Basic\Builder {
		\Maleficarum\Ioc\Container::register('Maleficarum\Api\Security\Manager', function ($dep) {
			return (new \Maleficarum\Api\Security\Manager())
				->setConfig($dep['Maleficarum\Config'])
				->setRequest($dep['Maleficarum\Request']);
		});
		
		return $this;
	}

	/**
	 * Register logger builders.
	 * @param array $opts
	 * @return \Maleficarum\Api\Basic\Builder
	 */
	private function registerLogger(array $opts = []) : \Maleficarum\Api\Basic\Builder {
		\Maleficarum\Ioc\Container::register('Monolog\Logger', function () {
			$logger = new \Monolog\Logger('api');
			$prefix = isset($opts['prefix']) ? $opts['prefix'] : "Maleficarum";
			$handler = new \Monolog\Handler\SyslogHandler('[PHP]['.$prefix.'][Api]', \LOG_USER, \Monolog\Logger::DEBUG, true, \LOG_PID);
			$logger->pushHandler($handler);
			return $logger;
		});
		
		return $this;
	}
	
	/**
	 * Register controller builders.
	 * @param array $opts
	 * @return \Maleficarum\Api\Basic\Builder
	 */
	private function registerController(array $opts = []) : \Maleficarum\Api\Basic\Builder {
		\Maleficarum\Ioc\Container::register('Controller', function ($dep, $options) {
			$controller = new $options['__class']();
			if (!$controller instanceof \Maleficarum\Api\Controller\Generic) throw new \RuntimeException('Controller builder function used to create a non controller class. \Maleficarum\Ioc\Container::get()');
			$controller
				->setConfig($dep['Maleficarum\Config'])
				->setRequest($dep['Maleficarum\Request'])
				->setEnvironment($dep['Maleficarum\Environment'])
				->setResponse($dep['Maleficarum\Response']);

			(method_exists($controller, 'addProfiler') && isset($dep['Maleficarum\Profiler\Time'])) and $controller->addProfiler($dep['Maleficarum\Profiler\Time'], 'time');
			(method_exists($controller, 'addProfiler') && isset($dep['Maleficarum\Profiler\Database'])) and $controller->addProfiler($dep['Maleficarum\Profiler\Database'], 'database');
			(method_exists($controller, 'setLogger') && isset($dep['Maleficarum\Logger'])) and $controller->setLogger($dep['Maleficarum\Logger']); 

			return $controller;
		});
		
		return $this;
	}

	/**
	 * Register queue builders.
	 * @param array $opts
	 * @return \Maleficarum\Api\Basic\Builder
	 */
	private function registerQueue(array $opts = []) : \Maleficarum\Api\Basic\Builder {
		\Maleficarum\Ioc\Container::register('PhpAmqpLib\Connection\AMQPConnection', function ($dep) {
			if (!array_key_exists('Maleficarum\Config', $dep) || !isset($dep['Maleficarum\Config']['queue'])) {
				throw new \RuntimeException('Impossible to create a PhpAmqpLib\Connection\AMQPConnection object - no queue config found. \Maleficarum\Ioc\Container::get()');
			}
			return new \PhpAmqpLib\Connection\AMQPStreamConnection(
				$dep['Maleficarum\Config']['queue']['broker']['host'],
				$dep['Maleficarum\Config']['queue']['broker']['port'],
				$dep['Maleficarum\Config']['queue']['broker']['username'],
				$dep['Maleficarum\Config']['queue']['broker']['password']
			);
		});
		
		\Maleficarum\Ioc\Container::register('Maleficarum\Rabbitmq\Connection', function ($dep) {
			return (new \Maleficarum\Rabbitmq\Connection())
				->setConfig($dep['Maleficarum\Config']);
		});
		
		return $this;
	}
}