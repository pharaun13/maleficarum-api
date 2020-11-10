<?php
/**
 * This class contains default initializers used as Maleficarum bootstrap methods.
 */
declare (strict_types=1);

namespace Maleficarum\Api\Basic;

class Initializer {
    /* ------------------------------------ Class Methods START ---------------------------------------- */

    /**
     * Set up error/exception handling.
     * @return string
     */
    static public function setUpErrorHandling(): string {
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
     *
     * @param array $opts
     *
     * @throws \RuntimeException
     * @return string
     */
    static public function setUpDebugLevel(array $opts = []): string {
        try {
            $environment = \Maleficarum\Ioc\Container::retrieveShare('Maleficarum\Environment');
            $environment = $environment->getCurrentEnvironment();
        } catch (\Exception $e) {
            throw new \RuntimeException(sprintf('Environment object not initialized. \%s', __METHOD__));
        }

        if (stripos($environment, 'uat') === 0) {
            \Maleficarum\Handler\AbstractHandler::setDebugLevel(\Maleficarum\Handler\AbstractHandler::DEBUG_LEVEL_LIMITED);

            return __METHOD__;
        }

        // set handler debug level and error display value based on env
        switch ($environment) {
            case 'local':
            case 'development':
            case 'staging':
                \Maleficarum\Handler\AbstractHandler::setDebugLevel(\Maleficarum\Handler\AbstractHandler::DEBUG_LEVEL_FULL);
                break;
            case 'uat':
            case 'sandbox':
                \Maleficarum\Handler\AbstractHandler::setDebugLevel(\Maleficarum\Handler\AbstractHandler::DEBUG_LEVEL_LIMITED);
                break;
            case 'production':
                \Maleficarum\Handler\AbstractHandler::setDebugLevel(\Maleficarum\Handler\AbstractHandler::DEBUG_LEVEL_CRUCIAL);
                break;
            default:
                throw new \RuntimeException(sprintf('Unrecognised environment. \%s', __METHOD__));
        }

        // return initializer name
        return __METHOD__;
    }

    /**
     * Prepare and register the security object.
     *
     * @param array $opts
     *
     * @return string
     */
    static public function setUpSecurity(array $opts = []): string {
        // load default builder if skip not requested
        $builders = $opts['builders'] ?? [];
        is_array($builders) or $builders = [];
        isset($builders['security']['skip']) or \Maleficarum\Ioc\Container::get('Maleficarum\Api\Basic\Builder')->register('security');

        /** @var \Maleficarum\Api\Security\Manager $security */
        $security = \Maleficarum\Ioc\Container::get('Maleficarum\Api\Security\Manager');
        try {
            $security->verify();
        } catch (\Maleficarum\Exception\SecurityException $e) {
            throw new \Maleficarum\Exception\SecurityException('');
        }

        // return initializer name
        return __METHOD__;
    }

    /**
     * Bootstrap step method - prepare and register application routes.
     *
     * @param array $opts
     *
     * @throws \RuntimeException
     * @return string
     */
    static public function setUpRoutes(array $opts = []): string {
        try {
            $request = \Maleficarum\Ioc\Container::retrieveShare('Maleficarum\Request');
        } catch (\RuntimeException $e) {
            throw new \RuntimeException(sprintf('Request object not initialized. \%s', __METHOD__), $e->getCode(), $e);
        }

        try {
            $config = \Maleficarum\Ioc\Container::retrieveShare('Maleficarum\Config');
        } catch (\RuntimeException $e) {
            throw new \RuntimeException(sprintf('Config object not initialized. \%s', __METHOD__), $e->getCode(), $e);
        }

        // validate input container
        $app = $opts['app'] ?? null;
        $routesPath = $opts['routes'] ?? null;

        if (!is_object($app)) {
            throw new \RuntimeException(sprintf('Phalcon application not defined for bootstrap. \%s()', __METHOD__));
        }
        if (!is_readable($routesPath)) {
            throw new \RuntimeException(sprintf('Routes path not readable. \%s()', __METHOD__));
        }

        // include outside routes
        $route = explode('?', strtolower($request->getUri()))[0];
        $route = explode('/', preg_replace('/^\//', '', $route));
        $route = ucfirst(array_shift($route));

        // set route filename for root path
        if (0 === mb_strlen($route)) {
            $route = 'Generic';
        }

        $routesPathSuffix = DIRECTORY_SEPARATOR . $route . '.php';
        $path = $routesPath . $routesPathSuffix;

        $versionHeader = $config['routes']['version_header'] ?? null;
        $defaultPath = $config['routes']['default_path'] ?? null;
        $availableVersions = $config['routes']['versions'] ?? [];
        $requestVersion = $request->getHeaders()[$versionHeader] ?? null;

        $versionedRoutesPath = self::determineVersionedRoutePath($availableVersions, $routesPathSuffix, $routesPath, $defaultPath, $requestVersion);
        if (is_string($versionedRoutesPath)) {
            require_once $versionedRoutesPath;
        }

        if (null === $versionedRoutesPath && is_readable($path)) {
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
     *
     * @param array $opts
     *
     * @return string
     */
    static public function setUpController(array $opts = []): string {
        // load default builder if skip not requested
        $builders = $opts['builders'] ?? [];
        is_array($builders) or $builders = [];
        isset($builders['controller']['skip']) or \Maleficarum\Ioc\Container::get('Maleficarum\Api\Basic\Builder')->register('controller');

        // return initializer name
        return __METHOD__;
    }

    /**
     * Determines versioned routes path
     *
     * @param string[] $availableVersions
     * @param string $pathSuffix
     * @param null|string $routesPath
     * @param null|string $defaultPath
     * @param null|string $requestVersion
     *
     * @return null|string
     */
    static private function determineVersionedRoutePath(array $availableVersions, string $pathSuffix, ?string $routesPath, ?string $defaultPath, ?string $requestVersion): ?string {
        if (empty($routesPath)) {
            return null;
        }

        if (empty($availableVersions) && empty($defaultPath)) {
            return null;
        }

        if (empty($pathSuffix)) {
            return null;
        }

        if (null === $requestVersion || !preg_match('/^\d+\.\d+$/', $requestVersion)) {
            return null;
        }

        $routesPath .= '/';
        $versionedRoutesPath = isset($availableVersions[$requestVersion]) ? $routesPath . $availableVersions[$requestVersion] . $pathSuffix : null;
        if (!empty($versionedRoutesPath) && is_readable($versionedRoutesPath)) {
            return $versionedRoutesPath;
        }

        $defaultPath = isset($defaultPath) ? $routesPath . $defaultPath . $pathSuffix : null;
        if (!empty($defaultPath) && is_readable($defaultPath)) {
            return $defaultPath;
        }

        return null;
    }
    /* ------------------------------------ Class Methods END ------------------------------------------ */
}
