<?php
/**
 * This trait provides functionality common to all classes dependant on the \Maleficarum\Api\Request\Request class
 */

namespace Maleficarum\Api\Request;

trait Dependant
{
    /**
     * Internal storage for the request provider object.
     *
     * @var \Maleficarum\Api\Request\Request|null
     */
    protected $requestStorage = null;

    /**
     * Inject a new request provider object into this collection.
     *
     * @param \Maleficarum\Api\Request\Request $req
     *
     * @return $this
     */
    public function setRequest(\Maleficarum\Api\Request\Request $req)
    {
        $this->requestStorage = $req;

        return $this;
    }

    /**
     * Fetch the currently assigned request provider object.
     *
     * @return \Maleficarum\Api\Request\Request|null
     */
    public function getRequest()
    {
        return $this->requestStorage;
    }

    /**
     * Detach the currently assigned request provider object.
     *
     * @return $this
     */
    public function detachRequest()
    {
        $this->requestStorage = null;

        return $this;
    }
}
