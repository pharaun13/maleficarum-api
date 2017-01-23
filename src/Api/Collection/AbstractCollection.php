<?php
/**
 * This class provides basic collection functionality common to all collections.
 *
 * @abstract
 *
 * @implements \ArrayAccess
 * @implements \Iterator
 * @implements \Countable
 * @implements \JsonSerializable
 */

namespace Maleficarum\Api\Collection;

abstract class AbstractCollection implements \ArrayAccess, \Iterator, \Countable, \JsonSerializable
{
    /**
     * Internal container for collection objects.
     *
     * @var array
     */
    protected $data = [];

    /* ------------------------------------ AbstractCollection methods START --------------------------- */
    /**
     * Attach provided input and flatten result into a 1:1 relation.
     *
     * @param array|\Maleficarum\Api\Collection\AbstractCollection $input
     * @param string $newParamName
     * @param array $mapping
     *
     * @return \Maleficarum\Api\Collection\AbstractCollection
     */
    public function attachflat($input, string $newParamName, array $mapping) : \Maleficarum\Api\Collection\AbstractCollection {
        $this->attach($input, $newParamName, $mapping);

        // flatten
        foreach ($this->data as &$val) count($val[$newParamName]) and $val[$newParamName] = array_shift($val[$newParamName]);

        return $this;
    }

    /**
     * Attach data from another collection into this one base on a matching column.
     *
     * @param array|\Maleficarum\Api\Collection\AbstractCollection $input
     * @param string $newParamName
     * @param array $mapping
     *
     * @return \Maleficarum\Api\Collection\AbstractCollection
     * @throws \InvalidArgumentException
     */
    public function attach($input, string $newParamName, array $mapping) : \Maleficarum\Api\Collection\AbstractCollection {
        if (!count($this->data)) return $this;

        is_array($input) || $input instanceof \Maleficarum\Api\Collection\AbstractCollection or $this->respondToInvalidArgument('Incorrect input provided - array or Collection expected. \%s::attach()');
        array_key_exists('local', $mapping) or $this->respondToInvalidArgument('Incorrect mapping provided - correct array expected. \%s::attach()');

        array_key_exists('remote', $mapping) or $mapping['remote'] = $mapping['local'];

        $temp = [];
        foreach ($this->data as &$val) {
            $val[$newParamName] = [];
            $temp[$val[$mapping['local']]] = [];
        }

        foreach ($input as $remoteVal) $temp[$remoteVal[$mapping['remote']]][] = $remoteVal;
        foreach ($this->data as &$val) $val[$newParamName] = $temp[$val[$mapping['local']]];

        return $this;
    }

    /**
     * Attach provided input but use one of it's columns as indexes of the attached list.
     *
     * @param array|\Maleficarum\Api\Collection\AbstractCollection $input
     * @param string $newParamName
     * @param array $mapping
     *
     * @return \Maleficarum\Api\Collection\AbstractCollection
     * @throws \InvalidArgumentException
     */
    public function attachindexed($input, string $newParamName, array $mapping) : \Maleficarum\Api\Collection\AbstractCollection {
        if (!count($this->data)) return $this;

        is_array($input) || $input instanceof \Maleficarum\Api\Collection\AbstractCollection or $this->respondToInvalidArgument('Incorrect input provided - array or Collection expected. \%s::attachindexed()');
        array_key_exists('local', $mapping) && array_key_exists('index', $mapping) or $this->respondToInvalidArgument('Incorrect mapping provided - correct array expected. \%s::attachindexed()');

        array_key_exists('remote', $mapping) or $mapping['remote'] = $mapping['local'];

        $temp = [];
        foreach ($this->data as &$val) {
            $val[$newParamName] = [];
            $temp[$val[$mapping['local']]] = [];
        }

        foreach ($input as $remoteVal) {
            isset($temp[$remoteVal[$mapping['remote']]][$remoteVal[$mapping['index']]]) or $temp[$remoteVal[$mapping['remote']]][$remoteVal[$mapping['index']]] = [];
            $temp[$remoteVal[$mapping['remote']]][$remoteVal[$mapping['index']]][] = $remoteVal;
        }

        foreach ($this->data as &$val) $val[$newParamName] = $temp[$val[$mapping['local']]];

        return $this;
    }

    /**
     * Attach provided input, but use one of it's columns as indexes of the attached list, and flatten result into a 1:1 relation.
     *
     * @param array|\Maleficarum\Api\Collection\AbstractCollection $input
     * @param string $newParamName
     * @param array $mapping
     *
     * @return \Maleficarum\Api\Collection\AbstractCollection
     * @throws \InvalidArgumentException
     */
    public function attachflatindexed($input, string $newParamName, array $mapping) : \Maleficarum\Api\Collection\AbstractCollection {
        if (!count($this->data)) return $this;

        is_array($input) || $input instanceof \Maleficarum\Api\Collection\AbstractCollection or $this->respondToInvalidArgument('Incorrect input provided - array or Collection expected. \%s::attachflatindexed()');
        array_key_exists('local', $mapping) && array_key_exists('index', $mapping) or $this->respondToInvalidArgument('Incorrect mapping provided - correct array expected. \%s::attachflatindexed()');

        array_key_exists('remote', $mapping) or $mapping['remote'] = $mapping['local'];

        $temp = [];
        foreach ($this->data as &$val) {
            $val[$newParamName] = [];
            $temp[$val[$mapping['local']]] = [];
        }

        foreach ($input as $remoteVal) {
            isset($temp[$remoteVal[$mapping['remote']]][$remoteVal[$mapping['index']]]) or $temp[$remoteVal[$mapping['remote']]][$remoteVal[$mapping['index']]] = [];
            $temp[$remoteVal[$mapping['remote']]][$remoteVal[$mapping['index']]] = $remoteVal;
        }

        foreach ($this->data as &$val) $val[$newParamName] = $temp[$val[$mapping['local']]];

        return $this;
    }

    /**
     * Attach input to this collection using map as the intermediary.
     *
     * CAUTION: This method has a highly complicated logic but thanks to that it remains at the O(n) complexity.
     *
     * @param \Maleficarum\Api\Collection\AbstractCollection $input
     * @param \Maleficarum\Api\Collection\AbstractCollection $map
     * @param string $newParamName
     * @param array $mapping
     *
     * @return \Maleficarum\Api\Collection\AbstractCollection
     * @throws \InvalidArgumentException
     */
    public function usemap(\Maleficarum\Api\Collection\AbstractCollection $input, \Maleficarum\Api\Collection\AbstractCollection $map, string $newParamName, array $mapping) : \Maleficarum\Api\Collection\AbstractCollection {
        if (!count($this->data)) return $this;
        if (!count($input)) return $this;
        if (!count($map)) return $this;

        array_key_exists('local', $mapping) or $this->respondToInvalidArgument('Incorrect mapping provided - correct array expected. \%s::usemap()');

        /** Init */
        // remote not present - use local are remote
        array_key_exists('remote', $mapping) or $mapping['remote'] = $mapping['local'];

        // establish map local/remote names
        $mapping['map_local'] = 'map' . ucfirst($mapping['local']);
        $mapping['map_remote'] = 'map' . ucfirst($mapping['remote']);

        /** Preparations */
        // reindex input by remote name
        $temp = $input->reindex($mapping['remote']);

        // create a copy of map for manipulation
        $tempMap = $map->toArray();

        // prepare local data set with a placeholder to receive data
        foreach ($this->data as &$val) $val[$newParamName] = [];

        /** Logic */
        foreach ($tempMap as $key => &$tmEntry) {
            // check if the current map entry has a corresponding input value
            if (isset($temp[$tmEntry[$mapping['map_remote']]])) {
                // attach corresponding input value to map
                $tmEntry[$mapping['map_remote']] = $temp[$tmEntry[$mapping['map_remote']]];
            } else {
                // unset map entry if it has no corresponding value
                unset($tempMap[$key]);
            }
        }

        $temp = [];
        foreach ($tempMap as $tmpEntry) {
            isset($temp[$tmpEntry[$mapping['map_local']]]) or $temp[$tmpEntry[$mapping['map_local']]] = [];
            $temp[$tmpEntry[$mapping['map_local']]][] = $tmpEntry[$mapping['map_remote']];
        }

        foreach ($this->data as &$val) isset($temp[$val[$mapping['local']]]) and $val[$newParamName] = $temp[$val[$mapping['local']]];

        return $this;
    }

    /**
     * Fetch collection data as an array indexed by the provided column.
     * CAUTION: WILL ONLY WORK FOR COLUMNS WITH UNIQUE VALUES
     *
     * @param string $column
     *
     * @return array
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     */
    public function reindex(string $column) : array {
        if (!count($this->data)) return [];

        $result = [];
        foreach ($this->data as $value) {
            is_object($value) and $value = (array)$value;

            array_key_exists($column, $value) or $this->respondToInvalidArgument('Missing column. \%s::reindex()');
            if (array_key_exists($value[$column], $result)) throw new \RuntimeException(sprintf('Non-unique key value detected. \%s::reindex()', get_class($this)));

            $result[$value[$column]] = $value;
        }

        return $result;
    }

    /**
     * Cast this collection to a primitive array type.
     *
     * @param array $skip
     *
     * @return array
     */
    public function toArray(array $skip = []) : array {
        if (!count($skip)) {
            return (array)$this->data;
        } else {
            $result = [];
            $skip = array_flip($skip);

            foreach ($this->data as $entry) $result[] = array_diff_key((array)$entry, $skip);

            return $result;
        }
    }

    /**
     * Remove a specified property from each collection element.
     *
     * @param string $property
     *
     * @return \Maleficarum\Api\Collection\AbstractCollection
     */
    public function purge(string $property) : \Maleficarum\Api\Collection\AbstractCollection {
        if (!count($this->data)) return $this;

        foreach ($this->data as &$el) {
            if (is_array($el) && array_key_exists($property, $el)) unset($el[$property]);
            if (is_object($el) && property_exists($el, $property)) unset($el->$property);
        }

        return $this;
    }

    /**
     * Extract a single column from the entire collection.
     *
     * @param string $column
     *
     * @return array
     * @throws \InvalidArgumentException
     */
    public function extract(string $column) : array {
        if (!count($this->data)) return [];

        // since the next check relies on the current internal function we need to reset our data structure to ensure that a "current" element exists
        reset($this->data);

        // check structure of current element
        !array_key_exists($column, current($this->data)) and $this->respondToInvalidArgument('Incorrect column name provided - column does not exist. \%s::extract()');

        $result = [];
        foreach ($this->data as $key => $el) {
            is_array($el) && array_key_exists($column, $el) and $result[$key] = $el[$column];
            is_object($el) && property_exists($el, $column) and $result[$key] = $el->$column;
        }

        return $result;
    }

    /**
     * Extract a few columns from the entire collection.
     *
     * @param array $columns
     *
     * @return array
     * @throws \InvalidArgumentException
     */
    public function extractMany(array $columns = []) : array {
        if (!count($this->data)) return [];

        count($columns) or $this->respondToInvalidArgument('Incorrect columns name provided - nonempty array expected. \%s::extractMany()');

        // since the next check relies on the current internal function we need to reset our data structure to ensure that a "current" element exists
        reset($this->data);

        $columns = array_flip($columns);

        count(array_diff_key($columns, current($this->data))) === 0 or $this->respondToInvalidArgument('Incorrect columns name provided - column does not exist. \%s::extractMany()');

        $result = [];
        foreach ($this->data as $key => $el) {
            $result[$key] = array_intersect_key($el, $columns);
        }

        return $result;
    }

    /**
     * Extract many columns from the entire collection.
     *
     * @param array $columns
     *
     * @return array
     * @throws \InvalidArgumentException
     */
    public function removeMany(array $columns) : array {
        if (!count($this->data)) return [];

        // validate input
        count($columns) or $this->respondToInvalidArgument('Incorrect column name provided - nonempty array expected. \%s::removeMany()');

        // since the next check relies on the current internal function we need to reset our data structure to ensure that a "current" element exists
        reset($this->data);

        // init
        $result = [];
        $columns = array_flip($columns);

        // execute removal
        foreach ($this->data as $key => $el) {
            is_array($el) or $el = (array)$el;
            $result[$key] = array_diff_key($el, $columns);
        }

        return $result;
    }

    /**
     * Extract two specified columns as a hash map.
     * CAUTION: THIS METHOD WILL FAIL IF $keyCol HAS NONUNIQUE VALUES - FOR NONUNIQUE VALUES USE THE extractGroupedBy() METHOD
     *
     * @param string $keyCol
     * @param string $valCol
     *
     * @return array
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     */
    public function map(string $keyCol, string $valCol) : array {
        if (!count($this->data)) return [];

        $result = [];
        foreach ($this->data as $el) {
            is_object($el) and $el = (array)$el;

            array_key_exists($keyCol, $el) or $this->respondToInvalidArgument('Missing key column. \%s::map()');
            array_key_exists($valCol, $el) or $this->respondToInvalidArgument('Missing value column. \%s::map()');
            if (array_key_exists($el[$keyCol], $result)) throw new \RuntimeException(sprintf('Non-unique key value detected. \%s::map()', get_class($this)));

            $result[$el[$keyCol]] = $el[$valCol];
        }

        return $result;
    }

    /**
     * Fetch current data as an array with indexes matching values from the specified column and only the first match element in each key.
     *
     * @param string $column
     *
     * @return array
     */
    public function groupflat(string $column) : array {
        $result = $this->group($column);

        foreach ($result as &$el) $el = array_shift($el);

        return $result;
    }

    /**
     * Fetch current data as an array with indexes matching values from the specified column.
     *
     * @param string $column
     *
     * @return array
     */
    public function group(string $column) : array {
        if (!count($this->data)) return [];

        $result = [];
        foreach ($this->data as $value) {
            is_object($value) and $value = (array)$value;

            array_key_exists($value[$column], $result) or $result[$value[$column]] = [];
            $result[$value[$column]][] = $value;
        }

        return $result;
    }

    /**
     * Extract two specified columns as a map of grouped values. Useful when the $keyCol column has non-unique values.
     *
     * @param string $keyCol
     * @param string $valCol
     *
     * @return array
     */
    public function regroup(string $keyCol, string $valCol) : array {
        if (!count($this->data)) return [];

        $result = [];
        foreach ($this->data as $el) {
            is_object($el) and $el = (array)$el;

            !array_key_exists($el[$keyCol], $result) and $result[$el[$keyCol]] = [];
            $result[$el[$keyCol]][] = $el[$valCol];
        }

        return $result;
    }

    /**
     * Extract a numerical subset of data.
     *
     * @param int $limit
     * @param int $offset
     *
     * @return array
     */
    public function subset(int $limit = 10, int $offset = 0) : array {
        return array_slice($this->data, $offset, $limit);
    }
    /* ------------------------------------ Setters & Getters START ------------------------------------ */

    /**
     * Replace current data with the provided container.
     *
     * @param array $data
     *
     * @return \Maleficarum\Api\Collection\AbstractCollection
     */
    public function setData(array $data) : \Maleficarum\Api\Collection\AbstractCollection {
        $this->data = $data;

        return $this;
    }

    /**
     * Clear this collection of all data.
     *
     * @return \Maleficarum\Api\Collection\AbstractCollection
     */
    public function clear() : \Maleficarum\Api\Collection\AbstractCollection {
        $this->data = [];

        return $this;
    }
    /* ------------------------------------ Setters & Getters END -------------------------------------- */

    /* ------------------------------------ ArrayAccess methods START ---------------------------------- */
    /**
     * Checks if the given key exists in the array
     *
     * @see \ArrayAccess::offsetExists()
     *
     * @param mixed $offset
     *
     * @return bool
     */
    public function offsetExists($offset) : bool {
        return array_key_exists($offset, $this->data);
    }

    /**
     * Gets the element with the given key
     *
     * @see \ArrayAccess::offsetGet()
     *
     * @param mixed $offset
     *
     * @return mixed
     */
    public function offsetGet($offset) {
        return $this->data[$offset];
    }

    /**
     * Sets the element at the given key
     *
     * @see \ArrayAccess::offsetSet()
     *
     * @param mixed $offset
     * @param mixed $value
     *
     * @return \Maleficarum\Api\Collection\AbstractCollection
     */
    public function offsetSet($offset, $value) {
        $this->data[$offset] = $value;

        return $this;
    }

    /**
     * Unset the element at the given key
     *
     * @see \ArrayAccess::offsetUnset()
     *
     * @param mixed $offset
     *
     * @return void
     */
    public function offsetUnset($offset) {
        unset($this->data[$offset]);
    }
    /* ------------------------------------ ArrayAccess methods END ------------------------------------ */

    /* ------------------------------------ Iterator methods START ------------------------------------- */
    /**
     * Returns the current element.
     *
     * @see \Iterator::current()
     * @return mixed
     */
    public function current() {
        return current($this->data);
    }

    /**
     * Move forward to next element
     *
     * @see \Iterator::next()
     * @return void
     */
    public function next() {
        next($this->data);
    }

    /**
     * Return the key of the current element
     *
     * @see \Iterator::key()
     * @return mixed
     */
    public function key() {
        return key($this->data);
    }

    /**
     * Checks if current position is valid
     *
     * @see \Iterator::valid()
     * @return bool
     */
    public function valid() : bool {
        return array_key_exists($this->key(), $this->data);
    }

    /**
     * Rewind the Iterator to the first element
     *
     * @see \Iterator::rewind()
     * @return void
     */
    public function rewind() {
        reset($this->data);
    }
    /* ------------------------------------ Iterator methods END --------------------------------------- */

    /* ------------------------------------ Countable methods START ------------------------------------ */
    /**
     * Count elements of an object
     *
     * @see \Countable::count()
     * @return int
     */
    public function count() : int {
        return count($this->data);
    }
    /* ------------------------------------ Countable methods END -------------------------------------- */

    /* ------------------------------------ JsonSerializable methods START ----------------------------- */
    /**
     * Specify data which should be serialized to JSON
     *
     * @see \JsonSerializable::jsonSerialize()
     * @return mixed
     */
    public function jsonSerialize() {
        return $this->toArray();
    }
    /* ------------------------------------ JsonSerializable methods END ------------------------------- */

    /* ------------------------------------ Abstract methods START ------------------------------------- */
    /**
     * Populate this collection with data.
     *
     * @param array $data
     *
     * @return \Maleficarum\Api\Collection\AbstractCollection
     */
    abstract public function populate(array $data = []) : \Maleficarum\Api\Collection\AbstractCollection;
    /* ------------------------------------ Abstract methods END --------------------------------------- */

    /* ------------------------------------ Helper methods START --------------------------------------- */
    /**
     * This method is a code style helper - it will simply throw an \InvalidArgumentException
     *
     * @param string $msg
     *
     * @return void
     * @throws \InvalidArgumentException
     */
    protected function respondToInvalidArgument(string $msg) {
        throw new \InvalidArgumentException(sprintf($msg, static::class));
    }
    /* ------------------------------------ Helper methods END ----------------------------------------- */
}
