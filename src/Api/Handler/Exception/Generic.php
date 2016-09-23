<?php
/**
 * This class provides default exception handling functionality.
 */

namespace Maleficarum\Api\Handler\Exception;

class Generic
{
    /**
     * Use \Maleficarum\Api\Response\Dependant functionality.
     *
     * @trait
     */
    use \Maleficarum\Api\Response\Dependant;

    /**
     * Generic exception handling functionality. This handler will be called if there is no type
     * specific functionality available.
     *
     * @param \Exception $e
     * @param int $debugLevel
     *
     * @throws \InvalidArgumentException
     */
    public function handle(\Exception $e, $debugLevel)
    {
        if (!is_int($debugLevel)) {
            throw new \InvalidArgumentException('Incorrect debug level - integer expected. \Maleficarum\Api\Handler\Exception\Generic::handle()');
        }

        // regardless of debug level generic handler calls MUST be sent to syslog
        syslog(\LOG_EMERG, '[PHP][API] GENERIC EXCEPTION HANDLER :: MSG: ' . $e->getMessage() . ' :: FILE: ' . $e->getFile() . ' :: LINE: ' . $e->getLine());

        // this should only happen on incorrect JSON requests
        if (is_null($this->getResponse())) {
            $this->handleGeneric($e, $debugLevel);
        } else {
            // set response status
            $this->getResponse()->setStatusCode(\Maleficarum\Api\Response\Status::STATUS_CODE_500);

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
        header('HTTP/1.1 500 Internal Server Error');
        header('Content-type: application/json');

        // create response based on
        if ($debugLevel > \Maleficarum\Api\Handler\AbstractHandler::DEBUG_LEVEL_CRUCIAL) {
            echo json_encode([
                'meta' => [
                    'status' => 'failure',
                    'msg' => $e->getMessage(),
                    'line' => $e->getLine(),
                    'file' => $e->getFile(),
                    'trace' => $e->getTrace()
                ],
                'data' => []
            ]);
        } else {
            echo json_encode([
                'meta' => [
                    'status' => 'failure',
                    'msg' => 'API Error'
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
    protected function handleJSON(\Exception $e, $debugLevel)
    {
        // choose data to show depending on debug level
        if ($debugLevel > \Maleficarum\Api\Handler\AbstractHandler::DEBUG_LEVEL_CRUCIAL) {
            $this->getResponse()->render(
                [],
                ['msg' => $e->getMessage(), 'line' => $e->getLine(), 'file' => $e->getFile(), 'trace' => $e->getTrace()],
                false
            )->output();
        } else {
            $this->getResponse()->render(
                [],
                ['msg' => 'API error'],
                false
            )->output();
        }

        exit;
    }
}