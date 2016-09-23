<?php
/**
 * This class provides profiling functionality for query execution.
 *
 * @implements \Iterator
 * @implements \Countable
 * @implements \Maleficarum\Api\Profiler\Profiler
 */

namespace Maleficarum\Api\Profiler;

class Database implements \Iterator, \Countable, \Maleficarum\Api\Profiler\Profiler
{
    /**
     * Internal storage for profiling data.
     *
     * @var array
     */
    private $data = [];

    /**
     * Add a new executed query to this profiler.
     *
     * @param number $start
     * @param number $end
     * @param string $query
     * @param array $params
     *
     * @throws \InvalidArgumentException
     * @return \Maleficarum\Api\Profiler\Database
     */
    public function addQuery($start, $end, $query, array $params = [])
    {
        if (!is_numeric($start)) {
            throw new \InvalidArgumentException('Invalid start time provided - number expected. \Maleficarum\Api\Profiler\Database::addQuery()');
        }

        if (!is_numeric($end)) {
            throw new \InvalidArgumentException('Invalid end time provided - number expected. \Maleficarum\Api\Profiler\Database::addQuery()');
        }

        if (!is_string($query)) {
            throw new \InvalidArgumentException('Invalid query provided - string expected. \Maleficarum\Api\Profiler\Database::addQuery()');
        }

        $entry = [
            'start' => $start,
            'end' => $end,
            'execution' => $end - $start,
            'query' => $query,
            'params' => $params,
            'parsedQuery' => $query
        ];

        $patterns = [];
        $values = [];
        foreach ($params as $key => $val) {
            $patterns[] = '/' . $key . '([^a-zA-Z\d_]){0,1}/';
            $values[] = "'$val'";
        }

        $entry['parsedQuery'] = preg_replace($patterns, $values, $entry['parsedQuery']);

        $this->data[] = $entry;

        return $this;
    }

    /* ------------------------------------ Profiler methods START ------------------------------------- */
    /**
     * @see \Maleficarum\Api\Profiler\Profiler::getProfile()
     */
    public function getProfile()
    {
        return $this->data;
    }
    /* ------------------------------------ Profiler methods END --------------------------------------- */

    /* ------------------------------------ Countable methods START ------------------------------------ */
    /**
     * @see Countable::count()
     */
    public function count()
    {
        return count($this->data);
    }
    /* ------------------------------------ Countable methods END -------------------------------------- */

    /* ------------------------------------ Iterator methods START ------------------------------------- */
    /**
     * @see Iterator::current()
     */
    public function current()
    {
        return current($this->data);
    }

    /**
     * @see Iterator::key()
     */
    public function key()
    {
        return key($this->data);
    }

    /**
     * @see Iterator::next()
     */
    public function next()
    {
        next($this->data);
    }

    /**
     * @see Iterator::rewind()
     */
    public function rewind()
    {
        reset($this->data);
    }

    /**
     * @see Iterator::valid()
     */
    public function valid()
    {
        return isset($this->data[$this->key()]);
    }
    /* ------------------------------------ Iterator methods END --------------------------------------- */
}
