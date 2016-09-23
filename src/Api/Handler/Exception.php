<?php
/**
 * The purpose of this class is to provide a default exception handler method.
 * No exception handling functionality is to be implemented here!!! This is only supposed to call Other classes that provide type specific handling.
 *
 * @extends \Maleficarum\Api\Handler\AbstractHandler
 */

namespace Maleficarum\Api\Handler;

class Exception extends AbstractHandler
{
    /**
     * Delegate exception handling to type specific classes or if not possible delegate it to the default
     * exception handling functionality.
     *
     * @param \Exception $exception
     *
     * @throws \InvalidArgumentException
     */
    public function handle()
    {
        if (func_num_args() !== 1) {
            throw new \InvalidArgumentException('Incorrect number of arguments. \Maleficarum\Api\Handler\Exception::handle()');
        }

        $exception = func_get_arg(0);
        $type = preg_replace('/Exception$/', "", get_class($exception));
        $type = explode('\\', $type);
        $type = array_pop($type);
        $type = 'Maleficarum\Api\Handler\Exception\\' . $type;

        // try to load type specific handling
        if (class_exists($type, true)) {
            $handler = \Maleficarum\Ioc\Container::get($type);
        } else { //no type specific handling available, use the default handler
            $handler = \Maleficarum\Ioc\Container::get('Maleficarum\Api\Handler\Exception\Generic');
        }

        $handler->handle($exception, self::$debugLevel);
    }
}
