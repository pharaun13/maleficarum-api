<?php
/**
 * This class provides basic database collection functionality. It expands on the abstract collection class by providing
 * setter methods for database connection DI.
 *
 * @abstract
 *
 * @extends \Maleficarum\Api\Collection\AbstractCollection
 */

namespace Maleficarum\Api\Database\Collection;

abstract class Collection extends \Maleficarum\Data\Collection\AbstractCollection
{
    /**
     * Use \Maleficarum\Api\Database\Dependant trait functionality.
     *
     * @trait
     */
    use \Maleficarum\Api\Database\Dependant;

    /**
     * Internal storage for special param tokens to skip during query generation
     *
     * @var array
     */
    protected static $specialTokens = [
        '__sorting',                    // sort/order config
        '__subset',                     // limit/subset config
        '__distinct',                   // add DISTINCT to generated query
        '__count',                      // add COUNT(_first_provided_value_) to generated query and GROUP BY by _remaining_provided_values_
        '__sum',                        // add SUM(_first_provided_value_) to generated query and GROUP BY by _remaining_provided_values_
        '__lock',                       // add FOR UPDATE to generated query - since locking works inside a transaction, using this will trigger an Exception when used in an atomic operation
    ];

    /* ------------------------------------ Populate component methods START --------------------------- */
    /**
     * Populate this collection with data based on filters and options provided in the input parameter.
     *
     * @param array $data
     *
     * @return \Maleficarum\Data\Collection\AbstractCollection
     */
    public function populate(array $data = []) : \Maleficarum\Data\Collection\AbstractCollection {
        // apply initial tests
        $this->populate_testDb()->populate_testLock($data)->populate_testSorting($data)->populate_testSubset($data);

        // create the DTO transfer object
        $dto = (object)['params' => [], 'data' => $data];

        // initialize query prepend section
        $query = $this->populate_prependSection($dto);

        // initial query definition
        $query = $this->populate_basicQuery($query, $dto);

        // attach filters
        $query = $this->populate_attachFilter($query, $dto);

        // blanket SQL statement
        $query = $this->populate_blanketSQL($query);

        // attach grouping info
        (array_key_exists('__count', $data) || array_key_exists('__sum', $data)) and $query = $this->populate_grouping($query, $dto);

        // attach sorting info
        array_key_exists('__sorting', $data) and $query = $this->populate_sorting($query, $dto);

        // attach subset info
        array_key_exists('__subset', $data) and $query = $this->populate_subset($query, $dto);

        // attach lock directive (caution - this can cause deadlocks when used incorrectly)
        array_key_exists('__lock', $data) and $query = $this->populate_lock($query, $dto);

        // fetch data from storage
        $this->populate_fetchData($query, $dto);

        // format all data entries
        $this->format();

        return $this;
    }

    /**
     * Test database connection.
     *
     * @return \Maleficarum\Api\Database\Collection\Collection
     */
    protected function populate_testDb() : \Maleficarum\Api\Database\Collection\Collection {
        if (is_null($this->getDb())) {
            throw new \RuntimeException(sprintf('Cannot populate this collection with data prior to injecting a database shard manager. \%s::populate()', static::class));
        }

        return $this;
    }

    /**
     * Test the provided data object for existence and format of sorting directives.
     *
     * @param array $data
     *
     * @return \Maleficarum\Api\Database\Collection\Collection
     * @throws \InvalidArgumentException
     */
    protected function populate_testSorting(array $data) : \Maleficarum\Api\Database\Collection\Collection {
        if (array_key_exists('__sorting', $data)) {
            is_array($data['__sorting']) && count($data['__sorting']) or $this->respondToInvalidArgument('Incorrect sorting data. \%s::populate()');

            foreach ($data['__sorting'] as $val) {
                // check structure and sort type
                !is_array($val) || count($val) !== 2 || ($val[1] !== 'ASC' && $val[1] !== 'DESC') and $this->respondToInvalidArgument('Incorrect sorting data. \%s::populate()');

                // check column validity
                in_array($val[0], $this->getSortColumns()) or $this->respondToInvalidArgument('Incorrect sorting data. \%s::populate()');
            }
        }

        return $this;
    }

    /**
     * Test the provided data object for existence and format of limit/offset directives.
     *
     * @param array $data
     *
     * @return \Maleficarum\Api\Database\Collection\Collection
     * @throws \InvalidArgumentException
     */
    protected function populate_testSubset(array $data) : \Maleficarum\Api\Database\Collection\Collection {
        if (array_key_exists('__subset', $data)) {
            is_array($data['__subset']) or $this->respondToInvalidArgument('Incorrect subset data. \%s::populate()');
            !isset($data['__subset']['limit']) || !is_int($data['__subset']['limit']) || $data['__subset']['limit'] < 1 and $this->respondToInvalidArgument('Incorrect subset data. \%s::populate()');
            !isset($data['__subset']['offset']) || !is_int($data['__subset']['offset']) || $data['__subset']['offset'] < 0 and $this->respondToInvalidArgument('Incorrect subset data. \%s::populate()');
        }

        return $this;
    }

    /**
     * Test provided data object for existence of the __lock directive and check if it can be applied in current connection state.
     *
     * @param array $data
     *
     * @return \Maleficarum\Api\Database\Collection\Collection
     * @throws \InvalidArgumentException
     */
    protected function populate_testLock(array $data) : \Maleficarum\Api\Database\Collection\Collection {
        if (array_key_exists('__lock', $data)) {
            $this->getDb()->fetchShard($this->getShardRoute())->isConnected() or $this->respondToInvalidArgument('Cannot lock table outside of a transaction. \%s::populate()');
            $this->getDb()->fetchShard($this->getShardRoute())->inTransaction() or $this->respondToInvalidArgument('Cannot lock table outside of a transaction. \%s::populate()');
        }

        return $this;
    }

    /**
     * Initialize the query with a proper prepend section. By default the prepend section is empty and should be overloaded when necessary.
     *
     * @param \stdClass $dto
     *
     * @return string
     */
    protected function populate_prependSection(\stdClass $dto) : string {
        return '';
    }

    /**
     * Fetch initial populate query segment.
     *
     * @param string $query
     * @param \stdClass $dto
     *
     * @return string
     */
    protected function populate_basicQuery(string $query, \stdClass $dto) : string {
        // add basic select clause
        $query .= 'SELECT ';

        // add distinct values if requested
        if (array_key_exists('__distinct', $dto->data)) {
            // validate input
            is_array($dto->data['__distinct']) && count($dto->data['__distinct']) or $this->respondToInvalidArgument('Incorrect __distinct data. \%s::populate()');

            // proceed
            $query .= 'DISTINCT ON("';
            $query .= implode('", "', $dto->data['__distinct']);
            $query .= '") ';

            unset($dto->data['__distinct']);
        }

        // make sure the count and sum options are not used together
        if (array_key_exists('__count', $dto->data) && array_key_exists('__sum', $dto->data)) $this->respondToInvalidArgument('__count and __sum are mutually exclusive. \%s::populate()');

        // add grouping/counting clauses
        if (array_key_exists('__count', $dto->data)) {
            is_array($dto->data['__count']) && count($dto->data['__count']) or $this->respondToInvalidArgument('Incorrect __count data. \%s::populate()');

            // add count
            if (!array_key_exists('__distinct', $dto->data['__count'])) {
                $query .= 'COUNT("' . array_shift($dto->data['__count']) . '") AS "__count" ';
            } else {
                unset($dto->data['__count']['__distinct']);
                $query .= 'COUNT(DISTINCT "' . array_shift($dto->data['__count']) . '") AS "__count" ';
            }

            // add group columns
            count($dto->data['__count']) and $query .= ', "' . implode('", "', $dto->data['__count']) . '" ';
        } elseif (array_key_exists('__sum', $dto->data)) {
            is_array($dto->data['__sum']) && count($dto->data['__sum']) or $this->respondToInvalidArgument('Incorrect __sum data. \%s::populate()');

            // add sum
            $query .= 'SUM("' . array_shift($dto->data['__sum']) . '") AS "__sum" ';

            // add group columns
            count($dto->data['__sum']) and $query .= ', "' . implode('", "', $dto->data['__sum']) . '" ';
        } else {
            $query .= '* ';
        }

        // add basic FROM clause
        $query .= 'FROM "' . $this->getTable() . '" WHERE ';

        return $query;
    }

    /**
     * Fetch a query with filter syntax attached.
     *
     * @param string $query
     * @param \stdClass $dto
     *
     * @return string
     */
    protected function populate_attachFilter(string $query, \stdClass $dto) : string {
        foreach ($dto->data as $key => $data) {
            // skip any special tokens
            if (in_array($key, self::$specialTokens)) continue;

            // validate token data
            is_array($data) && count($data) or $this->respondToInvalidArgument('Incorrect filter param provided [' . $key . '], non-empty array expected. \%s::populate()');

            // parse key for filters
            $structure = $this->parseFilter($key);

            // create a list of statement tokens and matching values
            $temp = [];
            $hax_values = [false, 0, "0"]; // PHP sucks and empty(any of these values) returns true...
            foreach ($data as $elK => $elV) {
                (empty($elV) && !in_array($elV, $hax_values)) and $this->respondToInvalidArgument('Incorrect filter value [' . $key . '] - non empty value expected. \%s::populate()');
                $dto->params[$structure['prefix'] . $elK] = $elV;
                $temp[] = $structure['value_prefix'] . $structure['prefix'] . $elK . $structure['value_suffix'];
            }

            // attach filter to the query
            $query .= '' . $structure['column'] . ' ' . $structure['operator'] . ' (' . implode(', ', $temp) . ') AND ';
        }

        return $query;
    }

    /**
     * Fetch the blanket conditional SQL query segment.
     *
     * @param string $query
     *
     * @return string
     */
    protected function populate_blanketSQL(string $query) : string {
        return $query . '1=1 ';
    }

    /**
     * Fetch the grouping query segment.
     *
     * @param string $query
     * @param \stdClass $dto
     *
     * @return string
     */
    protected function populate_grouping(string $query, \stdClass $dto) : string {
        if (array_key_exists('__count', $dto->data) && is_array($dto->data['__count']) && count($dto->data['__count'])) {
            $query .= 'GROUP BY "' . implode('", "', $dto->data['__count']) . '" ';
        }

        if (array_key_exists('__sum', $dto->data) && is_array($dto->data['__sum']) && count($dto->data['__sum'])) {
            $query .= 'GROUP BY "' . implode('", "', $dto->data['__sum']) . '" ';
        }

        return $query;
    }

    /**
     * Attach the locking directive to the query.
     *
     * @param string $query
     * @param \stdClass $dto
     *
     * @return string
     */
    protected function populate_lock(string $query, \stdClass $dto) : string {
        array_key_exists('__lock', $dto->data) and $query .= 'FOR UPDATE ';

        return $query;
    }

    /**
     * Fetch the sorting query segment.
     *
     * @param string $query
     * @param \stdClass $dto
     *
     * @return string
     */
    protected function populate_sorting(string $query, \stdClass $dto) : string {
        $query .= 'ORDER BY ';
        $fields = [];
        foreach ($dto->data['__sorting'] as $val) $fields[] = "\"$val[0]\" $val[1]";
        $query .= implode(', ', $fields) . ' ';

        return $query;
    }

    /**
     * Fetch the subset query segment.
     *
     * @param string $query
     * @param \stdClass $dto
     *
     * @return string
     */
    protected function populate_subset(string $query, \stdClass $dto) : string {
        $query .= 'LIMIT :limit OFFSET :offset ';
        $dto->params[':limit'] = $dto->data['__subset']['limit'];
        $dto->params[':offset'] = $dto->data['__subset']['offset'];

        return $query;
    }

    /**
     * Fetch data based on query and dto->params from the storage.
     *
     * @param string $query
     * @param \stdClass $dto
     *
     * @return \Maleficarum\Api\Database\Collection\Collection
     */
    protected function populate_fetchData(string $query, \stdClass $dto) : \Maleficarum\Api\Database\Collection\Collection {
        // fetch a shard connection
        $shard = $this->getDb()->fetchShard($this->getShardRoute());

        // lazy connections - establish a connection if necessary
        $shard->isConnected() or $shard->connect();

        // prepare statement
        $st = $shard->prepare($query);

        // bind parameters
        foreach ($dto->params as $key => $val) $st->bindValue($key, $val, is_bool($val) ? \PDO::PARAM_BOOL : \PDO::PARAM_STR);

        $st->execute($this->getTable(), $this->getShardRoute());
        $this->data = $st->fetchAll(\PDO::FETCH_ASSOC);

        return $this;
    }
    /* ------------------------------------ Populate component methods END ----------------------------- */

    /**
     * Insert all entries in this collection to it's storage.
     *
     * @return \Maleficarum\Api\Database\Collection\Collection
     */
    public function insertAll() : \Maleficarum\Api\Database\Collection\Collection {
        // test database connection
        $this->populate_testDb();

        // check if there are any entries to insert
        if (!count($this->data)) return $this;

        // setup data set
        $data = $this->prepareElements('INSERT');

        // setup containers
        $sets = [];
        $params = [];

        // generate basic query
        $sql = 'INSERT INTO "' . $this->getTable() . '" ("' . implode('", "', array_keys($data[0])) . '") VALUES ';

        // attach params to the query
        foreach ($data as $key => $val) {
            $result = [];
            array_walk($val, function ($local_value, $local_key) use (&$result, $key) {
                $result[':' . $local_key . '_token_' . $key] = $local_value;
            });

            // append sets
            $sets[] = "(" . implode(', ', array_keys($result)) . ")";

            // append bind params
            $params = array_merge($params, $result);
        }
        $sql .= implode(', ', $sets);

        // attach returning
        $sql .= ' RETURNING *;';

        // fetch a shard connection
        $shard = $this->getDb()->fetchShard($this->getShardRoute());

        // lazy connections - establish a connection if necessary
        $shard->isConnected() or $shard->connect();

        // prepare statement
        $st = $shard->prepare($sql);

        // bind parameters
        foreach ($params as $key => $val) {
            $type = is_bool($val) ? \PDO::PARAM_BOOL : \PDO::PARAM_STR;
            $st->bindValue($key, $val, $type);
        }

        // execute the query
        $st->execute($this->getTable(), $this->getShardRoute());

        // replace current data by returned data if returning was requested
        $this->setData($st->fetchAll(\PDO::FETCH_ASSOC))->format();

        return $this;
    }

    /**
     * Delete all entries in this collection from it's storage.
     *
     * @return \Maleficarum\Api\Database\Collection\Collection
     */
    public function deleteAll() : \Maleficarum\Api\Database\Collection\Collection {
        // test database connection
        $this->populate_testDb();

        // check if there are any entries to delete
        if (!count($this->data)) return $this;

        // setup data set
        $data = $this->prepareElements('DELETE');

        // setup containers
        $sets = [];
        $params = [];

        // generate basic query
        $sql = 'DELETE FROM "' . $this->getTable() . '" WHERE ';
        // attach params to the query
        foreach ($data as $key => $val) {
            $values = [];
            $names = [];
            array_walk($val, function ($local_value, $local_key) use (&$values, &$names, $key) {
                $values[':' . $local_key . '_token_' . $key] = $local_value;
                $names[] = '"' . $local_key . '" = :' . $local_key . '_token_' . $key;
            });

            // append sets
            $sets[] = '(' . implode(' AND ', $names) . ')';

            // append bind params
            $params = array_merge($params, $values);
        }
        $sql .= implode(' OR ', $sets);

        // fetch a shard connection
        $shard = $this->getDb()->fetchShard($this->getShardRoute());

        // lazy connections - establish a connection if necessary
        $shard->isConnected() or $shard->connect();

        // prepare statement
        $st = $shard->prepare($sql);

        // bind parameters
        foreach ($params as $key => $val) $st->bindValue($key, $val);

        // execute the query
        $st->execute($this->getTable(), $this->getShardRoute());

        return $this;
    }

    /* ------------------------------------ Helper methods START --------------------------------------- */
    /**
     * Fetch current data set as a prepared set used by modification methods (insertAll(), deleteAll()).
     * This method should be overridden in collections that need to behave differently than using a 1:1 mapping of the main data container.
     *
     * @param string $mode
     *
     * @return array
     * @throws \InvalidArgumentException
     */
    protected function prepareElements(string $mode) : array {
        ($mode === 'INSERT' || $mode === 'DELETE') or $this->respondToInvalidArgument('Incorrect preparation mode. \%s::prepareElements()');

        return $this->data;
    }

    /**
     * Iterate over all current data entries and perform any data formatting necessary. This method should be overloaded in any inheriting collection object
     * that requires any specific data decoding such as JSON de-serialization or date formatting.
     *
     * @return \Maleficarum\Api\Database\Collection\Collection
     */
    protected function format() : \Maleficarum\Api\Database\Collection\Collection {
        return $this;
    }

    /**
     * Parse provided key param into a set of filtering data.
     *
     * @param string $key
     *
     * @return array
     */
    private function parseFilter(string $key) : array {
        $result = ['column' => '"' . $key . '"', 'operator' => 'IN', 'prefix' => ':' . $key . '_', 'value_prefix' => '', 'value_suffix' => ''];

        // attempt to recover filters
        $data = explode('/', $key);

        // filters detected
        if (count($data) > 1) {
            // fetch filters
            $filter = array_shift($data);

            // establish a new basic result structure
            $result = ['column' => '"' . $data[0] . '"', 'operator' => $result['operator'], 'prefix' => ':' . $data[0] . '_', 'value_prefix' => $result['value_prefix'], 'value_suffix' => $result['value_suffix']];

            // apply filters
            for ($index = 0; $index < mb_strlen($filter); $index++) {
                // exclude filter
                $filter[$index] === '~' and $result = [
                    'column' => $result['column'],
                    'operator' => 'NOT IN',
                    'prefix' => $result['prefix'] . 'exclude_',
                    'value_prefix' => $result['value_prefix'],
                    'value_suffix' => $result['value_suffix']
                ];

                // case-insensitive filter
                $filter[$index] === 'i' and $result = [
                    'column' => 'LOWER(' . $result['column'] . ')',
                    'operator' => $result['operator'],
                    'prefix' => $result['prefix'] . 'case_insensitive_',
                    'value_prefix' => 'LOWER(' . $result['value_prefix'],
                    'value_suffix' => $result['value_suffix'] . ')'
                ];
            }
        }

        return $result;
    }
    /* ------------------------------------ Helper methods END ----------------------------------------- */

    /* ------------------------------------ Abstract methods START ------------------------------------- */
    /**
     * Fetch the name of current shard.
     *
     * @return string
     */
    abstract public function getShardRoute() : string;

    /**
     * Fetch the name of order column - should return null on collections without order data.
     *
     * @return null|string
     */
    abstract protected function getOrderColumn();

    /**
     * Fetch the name of main ID column - should return null on collections with no or multi-column primary keys.
     *
     * @return null|string
     */
    abstract protected function getIdColumn();

    /**
     * Fetch the name of db table used as data source for this collection.
     *
     * @abstract
     * @return string
     */
    abstract protected function getTable() : string;

    /**
     * Return a list of column names that are allowed to be used for sorting.
     *
     * @return array
     */
    abstract protected function getSortColumns() : array;
    /* ------------------------------------ Abstract methods END --------------------------------------- */
}
