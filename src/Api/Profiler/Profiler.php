<?php
/**
 * This interface defines the profiler contract.
 *
 * @interface
 */

namespace Maleficarum\Api\Profiler;

interface Profiler
{
    /**
     * Get profiler data.
     *
     * @return float
     */
    public function getProfile();
}
