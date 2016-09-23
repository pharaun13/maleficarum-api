<?php
/**
 * This class provides generic error handling functionality.
 */

namespace Maleficarum\Api\Handler\Error;

class Generic
{
    /**
     * Generic PHP error handling functionality. For now it just converts errors into runtime exceptions and
     * lets the exception handler deal with them.
     *
     * @param integer $errno
     * @param string $errstr
     * @param string $errfile
     * @param integer $errline
     * @param integer $debugLevel
     *
     * @throws \RuntimeException
     */
    public function handle($errno, $errstr, $errfile, $errline, $debugLevel)
    {
        throw new \RuntimeException("[PHP Error] Code: $errno, Comment: $errstr, File: $errfile, Line: $errline");
    }
}