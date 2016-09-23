<?php
/**
 * This class provides Dependency Injection functionality to CodeIgniter controllers. All app specific functionality
 * should be implemented in this or inheriting classes. This way we can easily move to other frameworks one day (hopefully Zend)
 *
 * @abstract
 *
 */

namespace Maleficarum\Api\Controller;

abstract class Generic
{
    /**
     * Use \Maleficarum\Api\Config\Dependant functionality.
     *
     * @trait
     */
    use \Maleficarum\Api\Config\Dependant;

    /**
     * Use \Maleficarum\Api\Environment\Dependant functionality.
     *
     * @trait
     */
    use \Maleficarum\Api\Environment\Dependant;

    /**
     * Use \Maleficarum\Api\Profiler\Dependant functionality.
     *
     * @trait
     */
    use \Maleficarum\Api\Profiler\Dependant;

    /**
     * Use \Maleficarum\Api\Rabbitmq\Dependant functionality.
     *
     * @trait
     */
    use \Maleficarum\Api\Rabbitmq\Dependant;

    /**
     * Use \Maleficarum\Api\Request\Dependant functionality.
     *
     * @trait
     */
    use \Maleficarum\Api\Request\Dependant;

    /**
     * Use \Maleficarum\Api\Response\Dependant functionality.
     *
     * @trait
     */
    use \Maleficarum\Api\Response\Dependant;

    /**
     * Perform URL to class method remapping.
     *
     * @param string $method
     *
     * @return bool
     * @throws \Maleficarum\Api\Exception\NotFoundException
     */
    public function __remap($method)
    {
        $action = $method . 'Action';

        if (method_exists($this, $action)) {
            $this->{$action}();
        } else {
            throw new \Maleficarum\Api\Exception\NotFoundException('404 - page not found.');
        }

        return true;
    }

    /**
     * Immediately halt all actions and send a 400 Bad Request response with provided errors.
     *
     * @param array $errors
     *
     * @throws \Maleficarum\Api\Exception\BadRequestException
     */
    protected function respondToBadRequest(array $errors = [])
    {
        throw (new \Maleficarum\Api\Exception\BadRequestException())->setErrors($errors);
    }

    /**
     * Immediately halt all actions and send a 409 Conflict response with provided errors.
     *
     * @param array $errors
     *
     * @throws \Maleficarum\Api\Exception\ConflictException
     */
    protected function respondToConflict(array $errors = [])
    {
        throw (new \Maleficarum\Api\Exception\ConflictException())->setErrors($errors);
    }
}
