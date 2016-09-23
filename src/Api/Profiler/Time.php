<?php
/**
 * This class provides basic time profiling for code execution. It allows for both entire process and milestone analisis.
 *
 * @implements \Maleficarum\Api\Profiler\Profiler
 */

namespace Maleficarum\Api\Profiler;

class Time implements \Maleficarum\Api\Profiler\Profiler
{
    /**
     * Definitions for initial and conclusion milestone lables.
     */
    const BEGIN_LABEL = '__BEGIN__';
    const END_LABEL = '__END__';

    /**
     * Internal storage for profiling step times.
     *
     * @var array
     */
    private $data = [];

    /**
     * Initialize a new time profiler.
     */
    public function __construct()
    {
        $this->clear();
    }

    /**
     * Clear any profiling data.
     *
     * @return \Maleficarum\Api\Profiler\Time
     */
    public function clear()
    {
        $this->data = [];

        return $this;
    }

    /**
     * Add a new profiler milestone.
     *
     * @param string $label
     * @param string|null $comment
     *
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     * @return \Maleficarum\Api\Profiler\Time
     */
    public function addMilestone($label, $comment = null)
    {
        if (count($this->data) < 1) {
            throw new \RuntimeException('Cannot add a milestone to a stopped profiler. \Maleficarum\Api\Profiler\Time::addMilestone()');
        }

        if (!is_string($label)) {
            throw new \InvalidArgumentException('Invalid milestone label - string expected. \Maleficarum\Api\Profiler\Time::addMilestone()');
        }

        if (!is_string($comment) && !is_null($comment)) {
            throw new \InvalidArgumentException('Invalid milestone comment - string or null expected. \Maleficarum\Api\Profiler\Time::addMilestone()');
        }

        $milestone = [
            'timestamp' => microtime(true),
            'comment' => $comment
        ];

        // create a nonempty label if one was not provided
        mb_strlen($label) or $label = uniqid();

        $this->data[$label] = $milestone;

        return $this;
    }

    /**
     * Fetch an existing milestone.
     *
     * @param string $label
     *
     * @throws \InvalidArgumentException
     * @return array
     */
    public function getMilestone($label)
    {
        if (!is_string($label)) {
            throw new \InvalidArgumentException('Invalid milestone label - string expected. \Maleficarum\Api\Profiler\Time::getMilestone()');
        }

        if (!array_key_exists($label, $this->data)) {
            throw new \InvalidArgumentException('Invalid milestone label provided. \Maleficarum\Api\Profiler\Time::getMilestone()');
        }

        return $this->data[$label];
    }

    /**
     * Fetch all milestone labels.
     *
     * @return array
     */
    public function getMilestoneLabels()
    {
        return array_keys($this->data);
    }

    /**
     * Begin time profiling.
     *
     * @param Float|null
     *
     * @return \Maleficarum\Api\Profiler\Time
     * @throws \RuntimeException
     */
    public function begin($start = null)
    {
        if (count($this->data)) {
            throw new \RuntimeException('Impossible to activate an already activated time profiler. \Maleficarum\Api\Profiler\Time::begin()');
        }

        $this->data[self::BEGIN_LABEL] = [
            'timestamp' => is_float($start) ? $start : microtime(true),
            'comment' => '__INITIAL PROFILING TIMESTAMP.'
        ];

        return $this;
    }

    /**
     * Stop time profiling.
     *
     * @return \Maleficarum\Api\Profiler\Time
     * @throws \RuntimeException
     */
    public function end()
    {
        if (count($this->data) < 1) {
            throw new \RuntimeException('Impossible to stop a time profiler that has not been started yet. \Maleficarum\Api\Profiler\Time::end()');
        }

        if (count($this->data) > 0 && $this->isComplete()) {
            throw new \RuntimeException('Impossible to stop an already stopped time profiler. \Maleficarum\Api\Profiler\Time::end()');
        }

        $this->data[self::END_LABEL] = [
            'timestamp' => microtime(true),
            'comment' => '__FINAL PROFILING TIMESTAMP.'
        ];

        return $this;
    }

    /**
     * Check if this profiler has completed its work.
     *
     * @return bool
     */
    public function isComplete()
    {
        return array_key_exists(self::END_LABEL, $this->data);
    }

    /* ------------------------------------ Profiler methods START ---------------------------------- */
    /**
     * Fetch profile data for the specified milestone combination.
     *
     * @param string $end
     * @param string $start
     *
     * @return float
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     */
    public function getProfile($end = self::END_LABEL, $start = self::BEGIN_LABEL)
    {
        if (!array_key_exists(self::BEGIN_LABEL, $this->data)) {
            throw new \RuntimeException('Call to getProfile prior to starting the profiling process. \Maleficarum\Api\Profiler\Time::getProfile()');
        }

        if (!is_string($end) || !is_string($start)) {
            throw new \InvalidArgumentException('Invalid milestone label - string expected. \Maleficarum\Api\Profiler\Time::getProfile()');
        }

        if (!array_key_exists($end, $this->data) || !array_key_exists($start, $this->data)) {
            throw new \InvalidArgumentException('Nonexistent profile label provided. \Maleficarum\Api\Profiler\Time::getProfile()');
        }

        return ($this->data[$end]['timestamp'] - $this->data[$start]['timestamp']);
    }
    /* ------------------------------------ Profiler methods END ------------------------------------ */
}