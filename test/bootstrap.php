<?php

/**
 * Define any consts that are used by the tested code.
 */
define('SRC_PATH', realpath('./test'));
define('CONFIG_PATH', SRC_PATH . DIRECTORY_SEPARATOR . 'resources/config');

/**
 * Add the default worker test case to use within this test suite.
 */
require_once 'ApiTestCase.php';

/**
 * Add an empty exception class to use for testing exception handlers.
 */
class GenericException extends \Exception {}