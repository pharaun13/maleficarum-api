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
		'config',
	    'environment',
	    'request',
	    'security',
	    'logger',
	    'response',
	    'database',
	    'controller',
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
	 * Register config builders.
	 * @param array $opts
	 * @return \Maleficarum\Api\Basic\Builder
	 */
	private function registerConfig(array $opts = []) : \Maleficarum\Api\Basic\Builder {
		\Maleficarum\Ioc\Container::register('Maleficarum\Config\Ini\Config', function ($dep, $opts) {
			$file = CONFIG_PATH . DIRECTORY_SEPARATOR . $dep['Maleficarum\Environment']->getCurrentEnvironment() . DIRECTORY_SEPARATOR . $opts['id'];
			return (new \Maleficarum\Config\Ini\Config($file));
		});
		
		return $this;
	}

	/**
	 * Register environment builders.
	 * @param array $opts
	 * @return \Maleficarum\Api\Basic\Builder
	 */
	private function registerEnvironment(array $opts = []) : \Maleficarum\Api\Basic\Builder {
		\Maleficarum\Ioc\Container::register('Maleficarum\Environment\Server', function () {
			return (new \Maleficarum\Environment\Server($_SERVER));
		});
		
		return $this;
	}

	/**
	 * Register request builders.
	 * @param array $opts
	 * @return \Maleficarum\Api\Basic\Builder
	 */
	private function registerRequest(array $opts = []) : \Maleficarum\Api\Basic\Builder {
		\Maleficarum\Ioc\Container::register('Maleficarum\Request\Request', function () {
			return (new \Maleficarum\Request\Request(new \Phalcon\Http\Request, \Maleficarum\Request\Request::PARSER_JSON));
		});
		
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
	 * Register response builders.
	 * @param array $opts
	 * @return \Maleficarum\Api\Basic\Builder
	 */
	private function registerResponse(array $opts = []) : \Maleficarum\Api\Basic\Builder {
		\Maleficarum\Ioc\Container::register('Maleficarum\Response\Http\Response', function ($dep) {
			/** @var \Maleficarum\Response\Handler\JsonHandler $responseHandler */
			$responseHandler = \Maleficarum\Ioc\Container::get('Maleficarum\Response\Http\Handler\JsonHandler');
			$responseHandler->setConfig($dep['Maleficarum\Config']);

			// add profilers on internal envs 
			if (isset($dep['Maleficarum\Environment']) && in_array($dep['Maleficarum\Environment']->getCurrentEnvironment(), ['local', 'development', 'staging'])) {
				$responseHandler
					->addProfiler($dep['Maleficarum\Profiler\Time'], 'time')
					->addProfiler($dep['Maleficarum\Profiler\Database'], 'database');
			}

			$resp = (new \Maleficarum\Response\Http\Response(new \Phalcon\Http\Response, $responseHandler));
			return $resp;
		});
		
		return $this;
	}

	/**
	 * Register database builders.
	 * @param array $opts
	 * @return \Maleficarum\Api\Basic\Builder
	 */
	private function registerDatabase(array $opts = []) : \Maleficarum\Api\Basic\Builder {
		\Maleficarum\Ioc\Container::register('Maleficarum\Api\Database\Manager', function ($dep) {
			$manager = new \Maleficarum\Api\Database\Manager();

			// check shard list
			if (!isset($dep['Maleficarum\Config']['database']['shards']) || !count($dep['Maleficarum\Config']['database']['shards'])) {
				throw new \RuntimeException('Cannot set up database access - no shards are defined.');
			}

			// create shard connections
			$shards = [];
			foreach ($dep['Maleficarum\Config']['database']['shards'] as $shard) {
				if (!isset($dep['Maleficarum\Config']['database_shards'][$shard]) || !count($dep['Maleficarum\Config']['database_shards'][$shard])) {
					throw new \RuntimeException('No config defined for the shard: ' . $shard);
				}
				$cfg = $dep['Maleficarum\Config']['database_shards'][$shard];
				$shards[$shard] = \Maleficarum\Ioc\Container::get('Maleficarum\Api\Database\Pgsql\Connection');
				$shards[$shard]
					->setHost($cfg['host'])
					->setPort((int)$cfg['port'])
					->setDbname($cfg['dbName'])
					->setUsername($cfg['user'])
					->setPassword($cfg['password']);
			}

			// check routes
			if (!isset($dep['Maleficarum\Config']['database']['routes']) || !count($dep['Maleficarum\Config']['database']['routes'])) {
				throw new \RuntimeException('Cannot set up database access - no shard routes are defined.');
			}
			if (!array_key_exists(\Maleficarum\Api\Database\Manager::DEFAULT_ROUTE, $dep['Maleficarum\Config']['database']['routes'])) {
				throw new \RuntimeException('Cannot set up database access - default route is not defined.');
			}

			// attach shards to routes
			foreach ($dep['Maleficarum\Config']['database']['routes'] as $route => $shard) {
				$manager->attachShard($shards[$shard], $route);
			}

			return $manager;
		});

		\Maleficarum\Ioc\Container::register('Maleficarum\Api\Database\Pgsql\Connection', function () {
			return (new \Maleficarum\Api\Database\Pgsql\Connection());
		});

		\Maleficarum\Ioc\Container::register('Maleficarum\Api\Database\PDO\Trailable', function ($dep, $opts) {
			$pdo = new \Maleficarum\Api\Database\PDO\Trailable(\Maleficarum\Ioc\Container::get('Maleficarum\Api\Database\Trail\None'), $opts['dsn']);
			$pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
			$args = [$pdo, isset($dep['Maleficarum\Profiler\Database']) ? $dep['Maleficarum\Profiler\Database'] : null];
			$pdo->setAttribute(\PDO::ATTR_STATEMENT_CLASS, ['Maleficarum\Api\Database\Statement\Trailable', $args]);
			return $pdo;
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