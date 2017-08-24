# Maleficarum Api installation

This document describes all the installation process for the Maleficarum API starting from scratch.

Please remember to replace `/var/www/project` with the proper project path e.g. `/var/www/campaign_service`

## Requirements
* PHP 7.1
* Phalcon 3.2
* Composer

## Installation
1. Create project directory structure
    ```shell
    mkdir -p /var/www/project/api/config/{local,development,staging,uat,production}
    mkdir -p /var/www/project/api/src/Route
    mkdir -p /var/www/project/api/src/Controller/Status
    mkdir -p /var/www/project/api/public
    ```

2. Create `.gitignore` file `/var/www/project/api/.gitignore` and add the following content
    ```
    /vendor/
    /config/local/
    .idea
    ```

3. Create config file template `/var/www/project/api/config/__example-config.ini` and add the following content
    ```ini
    ;##
    ;#   GLOBAL application settings
    ;##
    [global]
    ; Enable global setting section.
    ; Possible Values: true - enabled, false - disabled
    enabled = true

    ; Current API version
    version = '1.0'
    ```

4. Create config file for each environment:
    ```shell
    cp /var/www/project/api/config/__example-config.ini /var/www/project/api/config/local/config.ini
    cp /var/www/project/api/config/__example-config.ini /var/www/project/api/config/development/config.ini
    cp /var/www/project/api/config/__example-config.ini /var/www/project/api/config/staging/config.ini
    cp /var/www/project/api/config/__example-config.ini /var/www/project/api/config/uat/config.ini
    cp /var/www/project/api/config/__example-config.ini /var/www/project/api/config/production/config.ini
    ```

5. Create composer file `/var/www/project/api/composer.json` and add the following content
    ```json
    {
        "name": "service_name-api",
        "description": "service_name - Api",
        "license": "proprietary",
        "autoload": {
            "psr-4": {
                "": "src/"
            }
        },
        "require": {
        },
        "repositories": [
            {
                "type": "vcs",
                "url": "git@github.com:pharaun13/maleficarum-api.git"
            },
            {
                "type": "vcs",
                "url": "git@github.com:pharaun13/maleficarum-ioc.git"
            },
            {
                "type": "vcs",
                "url": "git@github.com:pharaun13/maleficarum-config.git"
            },
            {
                "type": "vcs",
                "url": "git@github.com:pharaun13/maleficarum-profiler.git"
            },
            {
                "type": "vcs",
                "url": "git@github.com:pharaun13/maleficarum-environment.git"
            },
            {
                "type": "vcs",
                "url": "git@github.com:pharaun13/maleficarum-request.git"
            },
            {
                "type": "vcs",
                "url": "git@github.com:pharaun13/maleficarum-response.git"
            },
            {
                "type": "vcs",
                "url": "git@github.com:pharaun13/maleficarum-http-response.git"
            },
            {
                "type": "vcs",
                "url": "git@github.com:pharaun13/maleficarum-handler.git"
            },
            {
                "type": "vcs",
                "url": "git@github.com:pharaun13/maleficarum-logger.git"
            }
        ]
    }
    ```

    **Please remember to update project name and description like in the example listed below**
    ```json
    {
       "name": "campaign_service-api",
       "description": "Campaign service - Api",
    }
    ```

6. Install dependencies by running composer
    ```shell
    cd /var/www/project/api/
    composer require maleficarum/api
    composer require maleficarum/profiler
    composer require maleficarum/logger
    ```

7. Create status controller file `/var/www/project/api/src/Controller/Status/Controller.php` and add the following content
    ```php
    <?php
    declare(strict_types=1);
    
    namespace Controller\Status;
    
    use Maleficarum\Api\Controller\Generic;
    
    /**
     * This controller handles status reporting.
     */
    class Controller extends Generic {
        /**
         * Send system status.
         */
        public function getAction() {
            return $this->getResponse()->render([
                'name' => 'service_name-api',
                'status' => 'OK'
            ]);
        }
    }
    ```

    **Please remember to update service name like in the example listed below**
    ```php
    return $this->getResponse()->render([
        'name' => 'campaign-service-api',
        'status' => 'OK'
    ]);
    ```

8. Create status route file `/var/www/project/api/src/Route/Status.php` and add the following content
    ```php
    <?php
    /**
     * Route definitions for the /status resource
     */
    declare(strict_types=1);

    /** @var \Maleficarum\Request\Request $request */
    $app->map('/status', function () use ($request) {
        \Maleficarum\Ioc\Container::get('Controller\Status\Controller')->__remap('get');
    })->via(['GET']);
    ```

9. Create front controller file `/var/www/project/api/public/index.php` and add the following content
    ```php
    <?php
    declare (strict_types=1);
    
    // initialize time profiling
    $start = microtime(true);
    
    // define path constants
    define('CONFIG_PATH', realpath('../config'));
    define('VENDOR_PATH', realpath('../vendor'));
    define('SRC_PATH', realpath('../src'));
    
    // add vendor based autoloading
    require_once VENDOR_PATH . '/autoload.php';
    
    // create Phalcon micro application
    $app = \Maleficarum\Ioc\Container::get('Phalcon\Mvc\Micro');
    $app->getRouter()->setUriSource(\Phalcon\Mvc\Router::URI_SOURCE_SERVER_REQUEST_URI);
    
    // create the bootstrap object and run internal init
    $bootstrap = \Maleficarum\Ioc\Container::get('Maleficarum\Api\Bootstrap')
        ->setParamContainer([
            'app' => $app,
            'routes' => SRC_PATH . DIRECTORY_SEPARATOR . 'Route',
            'start' => $start,
            'builders' => [],
            'prefix' => 'service_name-API',
            'logger.message_prefix' => '[PHP] '
        ])
        ->setInitializers([
            \Maleficarum\Api\Bootstrap::INITIALIZER_ERRORS,
            [\Maleficarum\Handler\Initializer\Initializer::class, 'initialize'],
            [\Maleficarum\Profiler\Initializer\Initializer::class, 'initializeTime'],
            [\Maleficarum\Environment\Initializer\Initializer::class, 'initialize'],
            \Maleficarum\Api\Bootstrap::INITIALIZER_DEBUG_LEVEL,
            [\Maleficarum\Config\Initializer\Initializer::class, 'initialize'],
            [Maleficarum\Request\Initializer\Initializer::class, 'initialize'],
            [\Maleficarum\Response\Initializer\Initializer::class, 'initialize'],
            [\Maleficarum\Logger\Initializer\Initializer::class, 'initialize'],
            \Maleficarum\Api\Bootstrap::INITIALIZER_CONTROLLER,
            \Maleficarum\Api\Bootstrap::INITIALIZER_SECURITY,
            \Maleficarum\Api\Bootstrap::INITIALIZER_ROUTES,
        ])
        ->initialize();
    
    // run the app
    $app->handle();
    
    // conclude application run
    $bootstrap->conclude();
    ```

    **Please remember to update log prefix like in the example listed below**
    ```php
    // ...
    $bootstrap = \Maleficarum\Ioc\Container::get('Maleficarum\Api\Bootstrap')
        ->setParamContainer([
            // ...
            'prefix' => 'service_name-API',
            // ...
        ])
    // ...
    ```
