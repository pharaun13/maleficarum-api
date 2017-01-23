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
     * Use \Maleficarum\Config\Dependant functionality.
     *
     * @trait
     */
    use \Maleficarum\Config\Dependant;

    /**
     * Use \Maleficarum\Environment\Dependant functionality.
     *
     * @trait
     */
    use \Maleficarum\Environment\Dependant;

    /**
     * Use \Maleficarum\Profiler\Dependant functionality.
     *
     * @trait
     */
    use \Maleficarum\Profiler\Dependant;

    /**
     * Use \Maleficarum\Request\Dependant functionality.
     *
     * @trait
     */
    use \Maleficarum\Request\Dependant;

    /**
     * Use \Maleficarum\Response\Dependant functionality.
     *
     * @trait
     */
    use \Maleficarum\Response\Dependant;

    /**
     * Use \Maleficarum\Api\Logger\Dependant functionality.
     *
     * @trait
     */
    use \Maleficarum\Api\Logger\Dependant;

    /* ------------------------------------ Generic methods START -------------------------------------- */
    /**
     * Perform URL to class method remapping.
     *
     * @param string $method
     *
     * @return mixed
     * @throws \Maleficarum\Exception\NotFoundException
     */
    public function __remap(string $method) {
        $action = $method . 'Action';

        if (method_exists($this, $action)) {
            $this->{$action}();
        } else {
            $this->respondToNotFound('404 - page not found.');
        }

        return true;
    }

    /**
     * Immediately halt all actions and send a 400 Bad Request response with provided errors.
     *
     * @param array $errors
     *
     * @return void
     * @throws \Maleficarum\Exception\BadRequestException
     */
    protected function respondToBadRequest(array $errors = []) {
        throw (new \Maleficarum\Exception\BadRequestException())->setErrors($errors);
    }

    /**
     * Immediately halt all actions and send a 409 Conflict response with provided errors.
     *
     * @param array $errors
     *
     * @return void
     * @throws \Maleficarum\Exception\ConflictException
     */
    protected function respondToConflict(array $errors = []) {
        throw (new \Maleficarum\Exception\ConflictException())->setErrors($errors);
    }

    /**
     * Immediately halt all actions and send a 404 Not found response.
     *
     * @param string $message
     *
     * @return void
     * @throws \Maleficarum\Exception\NotFoundException
     */
    protected function respondToNotFound(string $message) {
        throw new \Maleficarum\Exception\NotFoundException($message);
    }
    /* ------------------------------------ Generic methods END ---------------------------------------- */
}
