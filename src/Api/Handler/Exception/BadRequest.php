<?php
/**
 * This class provides BadRequest exception handling functionality.
 */

namespace Maleficarum\Api\Handler\Exception;

class BadRequest
{
    /**
     * Use \Maleficarum\Response\Dependant functionality.
     *
     * @trait
     */
    use \Maleficarum\Response\Dependant;

    /**
     * Authentication exception handling functionality.
     *
     * @param \Exception $e
     * @param int $debugLevel
     *
     * @throws \InvalidArgumentException
     */
    public function handle(\Exception $e, $debugLevel)
    {
        if (!is_int($debugLevel)) throw new \InvalidArgumentException('Incorrect debug level - integer expected. \Maleficarum\Api\Handler\Exception\BadRequest::handle()');

        // set response status
        $this->getResponse()->setStatusCode(\Maleficarum\Response\Status::STATUS_CODE_400);

        // handle response
        $this->handleJSON($e, $debugLevel);
    }

    /**
     * Perform error handling in API mode.
     *
     * @param \Exception $e
     * @param int $debugLevel
     */
    private function handleJSON(\Exception $e, $debugLevel)
    {
        $this->getResponse()->render(
            ['errors' => $e->getErrors()],
            ['msg' => '400 Bad Request'],
            false
        )->output();

        exit;
    }
}
