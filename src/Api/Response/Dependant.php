<?php
/**
 * This trait provides functionality common to all classes dependant on the \Maleficarum\Api\Response\Response class.
 */

namespace Maleficarum\Api\Response;

trait Dependant
{
    /**
     * Internal storage for the response object.
     *
     * @var \Maleficarum\Api\Response\Response|null
     */
    protected $responseStorage = null;

    /**
     * Inject a new response.
     *
     * @param \Maleficarum\Api\Response\Response $resp
     *
     * @return $this
     */
    public function setResponse(\Maleficarum\Api\Response\Response $resp)
    {
        $this->responseStorage = $resp;

        return $this;
    }

    /**
     * Fetch the currently assigned response object.
     *
     * @return \Maleficarum\Api\Response\Response|null
     */
    public function getResponse()
    {
        return $this->responseStorage;
    }

    /**
     * Detach the currently assigned response object.
     *
     * @return $this
     */
    public function detachResponse()
    {
        $this->responseStorage = null;

        return $this;
    }
}
