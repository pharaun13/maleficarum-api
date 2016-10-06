<?php
/**
 * This class provides UnsupportedMediaType exception handling functionality.
 */

namespace Maleficarum\Api\Handler\Exception;

class UnsupportedMediaType
{
    /**
     * Use \Maleficarum\Api\Response\Http\Dependant functionality.
     *
     * @trait
     */
    use \Maleficarum\Api\Response\Dependant;

    /**
     * UnsupportedMediaType exception handling functionality.
     *
     * @param \Exception $e
     * @param int $debugLevel
     *
     * @throws \InvalidArgumentException
     */
    public function handle(\Exception $e, $debugLevel)
    {
        if (!is_int($debugLevel)) throw new \InvalidArgumentException('Incorrect debug level - integer expected. \Maleficarum\Api\Handler\Exception\UnsupportedMediaType::handle()');

        // this should only happen on incorrect JSON requests
        if (is_null($this->getResponse())) {
            $this->handleGeneric($e, $debugLevel);
        } else {
            // set response status
            $this->getResponse()->setStatusCode(\Maleficarum\Api\Response\Status::STATUS_CODE_415);

            // handle response
            $this->handleJSON($e, $debugLevel);
        }
    }

    /**
     * Perform error handling in default mode.
     *
     * @param \Exception $e
     * @param int $debugLevel
     */
    protected function handleGeneric(\Exception $e, $debugLevel)
    {
        // send headers by hand
        header('HTTP/1.1 415 Unsupported Media Type');
        header('Content-type: application/json');

        // create response based on
        if ($debugLevel > \Maleficarum\Api\Handler\AbstractHandler::DEBUG_LEVEL_CRUCIAL) {
            echo json_encode([
                'meta' => [
                    'status' => 'failure',
                    'msg' => $e->getMessage(),
                ],
                'data' => []
            ]);
        } else {
            echo json_encode([
                'meta' => [
                    'status' => 'failure',
                    'msg' => '415 Unsupported Media Type'
                ],
                'data' => []
            ]);
        }

        exit;
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
                ['msg' => '415 Unsupported Media Type'],
                false
            )->output();
        }

        exit;
    }
}
