<?php
/**
 * This class provides default PHP error handling. No functionality, only dispatch to type specific handling.
 *
 * @extends \Maleficarum\Api\Handler\AbstractHandler
 *
 */

namespace Maleficarum\Api\Handler;

class Error extends AbstractHandler
{
    /**
     * Handle an occurance of an error.
     *
     * @param int $errno
     * @param string $errstr
     * @param string $errfile
     * @param int $errline
     *
     * @throws \InvalidArgumentException
     */
    public function handle()
    {
        if (func_num_args() !== 5) {
            throw new \InvalidArgumentException('Incorrect number of arguments. \Maleficarum\Api\Handler\Error::handle()');
        }

        $errno = func_get_arg(0);
        $errstr = func_get_arg(1);
        $errfile = func_get_arg(2);
        $errline = func_get_arg(3);

        $handler = \Maleficarum\Ioc\Container::get('Maleficarum\Api\Handler\Error\Generic');
        $handler->handle($errno, $errstr, $errfile, $errline, self::$debugLevel);
    }
}
