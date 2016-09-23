<?php
/**
 * This class provides the response capabilities that include:
 *  * setting headers
 *  * generating output
 *  * setting a HTTP response code and status message
 */

namespace Maleficarum\Api\Response;

class Response
{
    /**
     * Use \Maleficarum\Api\Config\Dependant functionality.
     *
     * @trait
     */
    use \Maleficarum\Api\Config\Dependant;

    /**
     * Use \Maleficarum\Api\Profiler\Dependant functionality.
     *
     * @trait
     */
    use \Maleficarum\Api\Profiler\Dependant;

    /**
     * Internal storage for the delegation object.
     *
     * @var \Phalcon\Http\Response|null
     */
    private $phalconResponse = null;

    /**
     * Internal storage for the response content data.
     *
     * @var array
     */
    private $content = [];

    /** INITIALIZATION METHODS */

    /**
     * Initialize a new instance of the response object.
     *
     * @param \Phalcon\Http\Response $resp
     */
    public function __construct(\Phalcon\Http\Response $resp)
    {
        // initialize delegation
        $this->setResponseDelegation($resp);

        // initialize response values
        $this->getResponseDelegation()
            ->setStatusCode(200, \Maleficarum\Api\Response\Status::getMessageForStatus(200));
    }

    /**
     * Render a JSON format response.
     *
     * @param array $data
     * @param array $meta
     * @param bool $success
     *
     * @return \Maleficarum\Api\Response\Response
     */
    public function render(array $data = [], array $meta = [], $success = true)
    {
        // initialize response content
        $this->content = array_merge([
            'status' => $success ? 'success' : 'failure',
            'version' => !is_null($this->getConfig()) ? $this->getConfig()['global']['version'] : null
        ], $meta);

        $this->content = [
            'meta' => $this->content,
            'data' => $data
        ];

        return $this;
    }

    /**
     * Output this response object. That includes sending out headers (if possible) and outputting response body
     *
     * @return \Maleficarum\Api\Response\Response
     * @throws \InvalidArgumentException
     * @internal param bool $saveSession
     */
    public function output()
    {
        // add typical response headers
        $this->getResponseDelegation()
            ->setHeader('Content-Type', 'application/json');

        // add time profiling data if available
        if (!is_null($this->getProfiler('time')) && isset($this->content['meta']) && $this->getProfiler('time')->isComplete()) {
            $this->content['meta']['time_profile'] = [
                'exec_time' => $this->getProfiler('time')->getProfile(),
                'req_per_s' => $this->getProfiler('time')->getProfile() > 0 ? round(1 / $this->getProfiler('time')->getProfile(), 2) : "0",
            ];
        }

        // add database profiling data if available
        if (!is_null($this->getProfiler('database')) && isset($this->content['meta'])) {
            $count = $exec = 0;
            foreach ($this->getProfiler('database') as $key => $profile) {
                $count++;
                $exec += $profile['execution'];
            }

            $this->content['meta']['database_profile'] = [
                'query_count' => $count,
                'overall_query_exec_time' => $exec
            ];
        }

        // send the response
        $this->getResponseDelegation()->setContent(json_encode($this->content))->send();

        return $this;
    }

    /** RESPONSE DATA METHODS */

    /**
     * Redirect the request to a new URI
     *
     * @param string $url
     * @param bool $immediate
     *
     * @throws \InvalidArgumentException
     * @return \Maleficarum\Api\Response\Response
     */
    public function redirect($url, $immediate = true)
    {
        if (!is_string($url)) throw new \InvalidArgumentException("Incorrect URL - string expected. \Maleficarum\Api\Response\Http\Response::redirect()");

        // send redirect header to the response object
        $this->getResponseDelegation()->redirect($url);

        // stop execution and redirect immediately
        if ($immediate) {
            $this->getResponseDelegation()->send();
            exit;
        }

        return $this;
    }

    /**
     * Add a new header.
     *
     * @param string $name
     * @param string $value
     *
     * @throws \InvalidArgumentException
     * @return \Maleficarum\Api\Response\Response
     */
    public function addHeader($name, $value)
    {
        if (!is_string($name)) throw new \InvalidArgumentException("Incorrect header name - string expected. \Maleficarum\Api\Response\Http\Response::addHeader()");
        if (!is_string($value)) throw new \InvalidArgumentException("Incorrect header value - string expected. \Maleficarum\Api\Response\Http\Response::addHeader()");

        $this->getResponseDelegation()->setHeader($name, $value);

        return $this;
    }

    /**
     * Clear all headers.
     *
     * @return \Maleficarum\Api\Response\Response
     */
    public function clearHeaders()
    {
        $this->getResponseDelegation()->resetHeaders();

        return $this;
    }

    /**
     * Detect if the response has already been sent.
     *
     * @return bool
     */
    public function isSent()
    {
        return $this->getResponseDelegation()->isSent();
    }

    /**
     * This method will set the current status code and a RFC recommended status message for that code. Setting
     * an unsupported HTTP status code will result in an exception.
     *
     * @param Integer $code
     *
     * @throws \InvalidArgumentException
     * @return \Maleficarum\Api\Response\Response
     */
    public function setStatusCode($code)
    {
        $this->getResponseDelegation()->setStatusCode($code, \Maleficarum\Api\Response\Status::getMessageForStatus($code));

        return $this;
    }

    /**
     * Set the current response delegation object.
     *
     * @param \Phalcon\Http\Response $resp
     *
     * @return \Maleficarum\Api\Response\Response
     */
    private function setResponseDelegation(\Phalcon\Http\Response $resp)
    {
        $this->phalconResponse = $resp;

        return $this;
    }

    /**
     * Fetch the current response delegation object.
     *
     * @return \Phalcon\Http\Response
     */
    private function getResponseDelegation()
    {
        return $this->phalconResponse;
    }
}
