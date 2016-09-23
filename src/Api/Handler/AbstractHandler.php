<?php
/**
 * This class provides common functionality to all handler classes.
 *
 * @abstract
 */

namespace Maleficarum\Api\Handler;

abstract class AbstractHandler
{
    /**
     * Definitions of available debug levels.
     */
    const DEBUG_LEVEL_FULL = 10;
    const DEBUG_LEVEL_LIMITED = 5;
    const DEBUG_LEVEL_CRUCIAL = 0;

    /**
     * Internal storage for the handler debug level. (by default set to crucial)
     *
     * @var int
     */
    protected static $debugLevel = self::DEBUG_LEVEL_CRUCIAL;

    /**
     * Set debug level for all handlers in the application (both error and exception)
     *
     * @param int $level
     *
     * @throws \InvalidArgumentException
     */
    public static function setDebugLevel($level)
    {
        if (!is_int($level)) {
            throw new \InvalidArgumentException('Incorrect debug level provided. \Maleficarum\Api\Handler\AbstractHandler::setDebugLevel()');
        }

        self::$debugLevel = $level;
    }

    /**
     * Perform handling dispatch.
     */
    abstract public function handle();
}
