<?php

namespace Maleficarum\Api\Request\Parser;

class UrlParser extends \Maleficarum\Api\Request\Parser\AbstractParser
{
    /* ------------------------------------ AbstractParser methods START ------------------------------- */
    /**
     * @see \Maleficarum\Api\Request\Parser\AbstractParser::parsePostData()
     */
    public function parsePostData()
    {
        // fetch request data from $_POST superglobal
        $data = (array)$this->getRequest()->getPost();
        $data = $this->sanitizeData($data);

        return $data;
    }
    /* ------------------------------------ AbstractParser methods END --------------------------------- */
}
