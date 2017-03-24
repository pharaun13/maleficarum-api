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
		'controller'
	];

	/* ------------------------------------ Class Constant END ----------------------------------------- */
	
	/* ------------------------------------ Class Methods START ---------------------------------------- */
	
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
			(method_exists($controller, 'setRedis') && isset($dep['Maleficarum\Redis'])) and $controller->setRedis($dep['Maleficarum\Redis']);
			(method_exists($controller, 'setQueue') && isset($dep['Maleficarum\CommandQueue'])) and $controller->setQueue($dep['Maleficarum\CommandQueue']);

			return $controller;
		});
		
		return $this;
	}
	
	/* ------------------------------------ Class Methods END ------------------------------------------ */
	
}