<?php
/**
 * This class provides Dependency Injection functionality to CodeIgniter controllers. All app specific functionality
 * should be implemented in this or inheriting classes. This way we can easily move to other frameworks one day (hopefully Zend)
 */
declare (strict_types=1);

namespace Maleficarum\Api\Controller;

abstract class Generic {
    
    /* ------------------------------------ Class Traits START ----------------------------------------- */
    
    /**
     * \Maleficarum\Config\Dependant
     */
    use \Maleficarum\Config\Dependant;

    /**
     * \Maleficarum\Environment\Dependant
     */
    use \Maleficarum\Environment\Dependant;

    /**
     * \Maleficarum\Request\Dependant
     */
    use \Maleficarum\Request\Dependant;

    /**
     * \Maleficarum\Response\Dependant
     */
    use \Maleficarum\Response\Dependant;

    /**
     * \Maleficarum\Api\Logger\Dependant
     */
    use \Maleficarum\Api\Logger\Dependant;
    
    /* ------------------------------------ Class Traits END ------------------------------------------- */

    /* ------------------------------------ Class Methods START ---------------------------------------- */
    
    /**
     * Perform URL to class method remapping.
     *
     * @param string $method
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
     * @return void
     * @throws \Maleficarum\Exception\NotFoundException
     */
    protected function respondToNotFound(string $message) {
        throw new \Maleficarum\Exception\NotFoundException($message);
    }
    
    /* ------------------------------------ Class Methods END ------------------------------------------ */
    
}
