<?php
/**
 * The purpose of this class is to provide an abstract layer to accessing request data.
 */

namespace Maleficarum\Api\Request;

class Request
{
    const METHOD_POST = 'POST';
    const METHOD_GET = 'GET';

    /**
     * Internal storage for the delegation request object
     *
     * @var \Phalcon\Http\Request|null
     */
    private $phalconRequest = null;

    /**
     * Internal storage for request data fetched from the phalcon object.
     *
     * @var array
     */
    private $data = null;

    /** INITIALIZATION METHODS */

    /* ------------------------------------ Magic methods START ---------------------------------------- */
    /**
     * Initialize a new instance of the request object.
     *
     * @param \Phalcon\Http\Request $pr
     */
    public function __construct(\Phalcon\Http\Request $pr)
    {
        // set delegations
        $this->setRequestDelegation($pr);

        // fetch request data from phalcon (json is handled in a different way that $_REQUEST)
        $post = (array)$this->getRequestDelegation()->getJsonRawBody();
        $get = (array)$this->getRequestDelegation()->getQuery();

        // sanitize input
        array_walk_recursive($post, function (&$item) {
            is_string($item) and $item = trim(filter_var($item, FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES));
        });
        array_walk_recursive($get, function (&$item) {
            is_string($item) and $item = trim(filter_var($item, FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES));
        });

        // set data
        $this->setData([
            'url' => [],
            self::METHOD_POST => $post,
            self::METHOD_GET => $get
        ]);
    }

    /**
     * Fetch a request param.
     *
     * @param string $name
     *
     * @throws \InvalidArgumentException
     * @return string|null
     */
    public function __get($name)
    {
        // try post data first
        if (isset($this->getData()['url'][$name])) {
            return $this->getData()['url'][$name];
        } elseif (isset($this->getData()[self::METHOD_POST][$name])) {
            return $this->getData()[self::METHOD_POST][$name];
        } elseif (isset($this->getData()[self::METHOD_GET][$name])) {
            return $this->getData()[self::METHOD_GET][$name];
        }

        return null;
    }

    /**
     * Enforce the requests Read-Only policy.
     *
     * @param string $name
     * @param string $val
     *
     * @throws \RuntimeException
     */
    public function __set($name, $val)
    {
        throw new \RuntimeException('Request data is Read-Only. \Maleficarum\Api\Request\Http\Request::__set()');
    }
    /* ------------------------------------ Magic methods END ------------------------------------------ */
    /** REQUEST DATA METHODS */

    /**
     * Attach URL parameters
     *
     * @param array $params
     *
     * @return \Maleficarum\Api\Request\Request
     */
    public function attachUrlParams(array $params)
    {
        $data = $this->getData();
        $data['url'] = $params;

        return $this->setData($data);
    }

    /**
     * Get http method.
     *
     * @return string
     */
    public function getMethod()
    {
        return $this->getRequestDelegation()->getMethod();
    }

    /**
     * Fetch all request headers.
     *
     * @return array
     */
    public function getHeaders()
    {
        return $this->getRequestDelegation()->getHeaders();
    }

    /**
     * Fetch a specified header from the request.
     *
     * @param string $name
     *
     * @return string
     */
    public function getHeader($name)
    {
        return $this->getRequestDelegation()->getHeader($name);
    }

    /**
     * Fetch parameters of given method
     * 
     * @param string $method
     * 
     * @return array|null
     */
    public function getParameters($method = self::METHOD_GET)
    {
        $data = $this->getData();

        if ($method === self::METHOD_GET && isset($data[self::METHOD_GET])) {
            return $data[self::METHOD_GET];
        }

        if ($method === self::METHOD_POST && isset($data[self::METHOD_POST])) {
            return $data[self::METHOD_POST];
        }

        return null;
    }

    /**
     * Fetch current request URI.
     *
     * @return string
     */
    public function getUri()
    {
        return $this->getRequestDelegation()->getURI();
    }

    /**
     * Check if this request has a GET method.
     *
     * @return bool
     */
    public function isGet()
    {
        return $this->getRequestDelegation()->isGet();
    }

    /**
     * Check if this request has a POST method.
     *
     * @return bool
     */
    public function isPost()
    {
        return $this->getRequestDelegation()->isPost();
    }

    /**
     * Check if this request has a PUT method.
     *
     * @return bool
     */
    public function isPut()
    {
        return $this->getRequestDelegation()->isPut();
    }

    /**
     * Check if this request has a DELETE method.
     *
     * @return bool
     */
    public function isDelete()
    {
        return $this->getRequestDelegation()->isDelete();
    }

    /**
     * Set the request delegation instance.
     *
     * @param \Phalcon\Http\Request $pr
     *
     * @return \Maleficarum\Api\Request\Request
     */
    private function setRequestDelegation(\Phalcon\Http\Request $pr)
    {
        $this->phalconRequest = $pr;

        return $this;
    }

    /**
     * Fetch the current request object that we delegate to.
     *
     * @return \Phalcon\Http\Request
     */
    private function getRequestDelegation()
    {
        return $this->phalconRequest;
    }

    /**
     * Set data
     * 
     * @param array $data
     *
     * @return \Maleficarum\Api\Request\Request
     */
    private function setData(array $data)
    {
        $this->data = $data;

        return $this;
    }

    /**
     * Fetch current request data.
     *
     * @return array
     */
    private function getData()
    {
        return $this->data;
    }
}
