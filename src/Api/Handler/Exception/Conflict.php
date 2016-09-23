<?php
/**
 * This class provides Conflict exception handling functionality.
 */

namespace Maleficarum\Api\Handler\Exception;

class Conflict
{
    /**
     * Use \Maleficarum\Api\Response\Http\Dependant functionality.
     *
     * @trait
     */
    use \Maleficarum\Api\Response\Dependant;

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
        if (!is_int($debugLevel)) throw new \InvalidArgumentException('Incorrect debug level - integer expected. \Maleficarum\Api\Handler\Exception\Conflict::handle()');

        // set response status
        $this->getResponse()->setStatusCode(\Maleficarum\Api\Response\Status::STATUS_CODE_409);

        // handle response
        $this->handleJSON($e, $debugLevel);
    }

    /**
     * Perform error handling in API mode.
     *
     * @param \Exception $e
     * @param $debugLevel
     */
    private function handleJSON(\Exception $e, $debugLevel)
    {
        if ($debugLevel >= \Maleficarum\Api\Handler\AbstractHandler::DEBUG_LEVEL_LIMITED) {
            $this->getResponse()->render(
                ['errors' => $e->getErrors()],
                ['msg' => $e->getMessage()],
                false
            )->output();
        } else {
            $this->getResponse()->render(
                ['errors' => $e->getErrors()],
                ['msg' => '409 Conflict'],
                false
            )->output();
        }

        exit;
    }
}