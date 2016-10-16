<?php

/**
 * This class provides NotFound exception handling functionality.
 */

namespace Maleficarum\Api\Handler\Exception;

class NotFound
{
    /**
     * Use \Maleficarum\Response\Http\Dependant functionality.
     *
     * @trait
     */
    use \Maleficarum\Response\Dependant;

    /**
     * NotFound exception handling functionality.
     *
     * @param \Exception $e
     * @param int $debugLevel
     *
     * @throws \InvalidArgumentException
     */
    public function handle(\Exception $e, $debugLevel)
    {
        if (!is_int($debugLevel)) throw new \InvalidArgumentException('Incorrect debug level - integer expected. \Maleficarum\Api\Handler\Exception\NotFound::handle()');

        // set response status
        $this->getResponse()->setStatusCode(\Maleficarum\Response\Status::STATUS_CODE_404);

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
        if ($debugLevel >= \Maleficarum\Api\Handler\AbstractHandler::DEBUG_LEVEL_LIMITED) {
            $this->getResponse()->render(
                [],
                ['msg' => $e->getMessage()],
                false
            )->output();
        } else {
            $this->getResponse()->render(
                [],
                ['msg' => '404 Not found'],
                false
            )->output();
        }

        exit;
    }
}
