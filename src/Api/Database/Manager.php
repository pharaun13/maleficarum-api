<?php
/**
 * This class acts as a routing layer for database accessors and database shards.
 */

namespace Maleficarum\Api\Database;

class Manager
{
    /**
     * Name of the default shard route
     *
     * @var String
     */
    const DEFAULT_ROUTE = '__DEFAULT__';

    /**
     * Internal storage for route to shard mapping.
     *
     * @var array
     */
    protected $routes = [];

    /**
     * Attach a shard to the specified route.
     *
     * @param \Maleficarum\Api\Database\AbstractConnection $shard
     * @param string $route
     *
     * @return \Maleficarum\Api\Database\Manager
     * @throws \InvalidArgumentException
     */
    public function attachShard(\Maleficarum\Api\Database\AbstractConnection $shard, $route)
    {
        if (!is_string($route) || !mb_strlen($route)) {
            throw new \InvalidArgumentException('Incorrect route provided - non empty string expected. \Maleficarum\Api\Database\Manager::attachShard()');
        }

        $this->routes[$route] = $shard;

        return $this;
    }

    /**
     * Detach a shard from the specified route.
     *
     * @param string $route
     *
     * @return \Maleficarum\Api\Database\Manager
     * @throws \InvalidArgumentException
     */
    public function detachShard($route)
    {
        if (!is_string($route) || !mb_strlen($route)) {
            throw new \InvalidArgumentException('Incorrect route provided - non empty string expected. \Maleficarum\Api\Database\Manager::detachShard()');
        }

        if (array_key_exists($route, $this->routes)) {
            unset($this->routes[$route]);
        }

        return $this;
    }

    /**
     * Fetch a shard for the specified route. If such route is not defined a default shard will be fetched.
     *
     * @param string $route
     *
     * @throws \InvalidArgumentException
     * @return \Maleficarum\Api\Database\AbstractConnection
     */
    public function fetchShard($route)
    {
        if (!is_string($route) || !mb_strlen($route)) {
            throw new \InvalidArgumentException('Incorrect route provided - non empty string expected. \Maleficarum\Api\Database\Manager::fetchShard()');
        }

        if (array_key_exists($route, $this->routes)) return $this->routes[$route];
        if (array_key_exists(self::DEFAULT_ROUTE, $this->routes)) return $this->routes[self::DEFAULT_ROUTE];

        throw new \InvalidArgumentException('Impossible to fetch the specified route. \Maleficarum\Api\Database\Manager::fetchShard()');
    }
}
