<?php

namespace Maleficarum\Api\Request\Parser;

abstract class AbstractParser
{
    /**
     * Internal storage for request
     *
     * @var \Phalcon\Http\Request
     */
    protected $request;

    /* ------------------------------------ Magic methods START ---------------------------------------- */
    /**
     * AbstractParser constructor.
     *
     * @param \Phalcon\Http\Request $request
     */
    public function __construct(\Phalcon\Http\Request $request)
    {
        $this->request = $request;
    }
    /* ------------------------------------ Magic methods END ------------------------------------------ */

    /* ------------------------------------ Abstract methods START ------------------------------------- */
    /**
     * Parse POST data
     *
     * @return mixed
     */
    abstract public function parsePostData();
    /* ------------------------------------ Abstract methods END --------------------------------------- */

    /* ------------------------------------ Class methods START ---------------------------------------- */
    /**
     * Parse GET data
     *
     * @return mixed
     */
    public function parseGetData()
    {
        $data = (array)$this->getRequest()->getQuery();
        $data = $this->sanitizeData($data);

        return $data;
    }

    /**
     * Sanitize data
     *
     * @param array $data
     *
     * @return array
     */
    protected function sanitizeData(array $data)
    {
        array_walk_recursive($data, function (&$item) {
            is_string($item) and $item = trim(filter_var($item, FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES));
        });

        return $data;
    }
    /* ------------------------------------ Class methods END ------------------------------------------ */

    /* ------------------------------------ Setters & Getters START ------------------------------------ */
    /**
     * Get request
     *
     * @return \Phalcon\Http\Request
     */
    protected function getRequest()
    {
        return $this->request;
    }

    /**
     * Set request
     *
     * @param \Phalcon\Http\Request $request
     *
     * @return $this
     */
    protected function setRequest(\Phalcon\Http\Request $request)
    {
        $this->request = $request;

        return $this;
    }
    /* ------------------------------------ Setters & Getters END -------------------------------------- */
}
