<?php
/**
 * This is the default controller for the API app. It throws the 404 error on all actions.
 *
 * @extends \Maleficarum\Api\Controller\Generic
 */

namespace Maleficarum\Api\Controller;

class Fallback extends \Maleficarum\Api\Controller\Generic
{
    /**
     * Throws not found exception
     * 
     * @see \Maleficarum\Api\Controller\Generic::__remap()
     * @param string $method
     *
     * @return void
     * @throws \Maleficarum\Exception\NotFoundException
     */
    public function __remap(string $method) {
        $this->respondToNotFound('404 - page not found.');
    }
}
