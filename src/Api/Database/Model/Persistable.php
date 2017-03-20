<?php
/**
 * This class provides a basis for all persistable model objects.
 */

namespace Maleficarum\Api\Database\Model;

abstract class Persistable extends \Maleficarum\Data\Model\AbstractModel implements \Maleficarum\Api\Database\Model\CRUD {
    /**
     * Use \Maleficarum\Api\Database\Dependant trait functionality.
     *
     * @trait
     */
    use \Maleficarum\Api\Database\Dependant;

    /**
     * Internal cache storage for prepared PDO statements.
     *
     * @var array
     */
    protected static $st = [];
    /* ------------------------------------ Persistable methods START ------------------------------------ */
    /**
     * This method returns an array of properties to be used in INSERT and UPDATE CRUD operations. The format for each entry is as follows:
     *
     * $entry['param'] = ':bindParamName';
     * $entry['value'] = 'Param value (as used during the bind process)';
     * $entry['column'] = 'Name of the storage column to bind against.';
     *
     * This is a generic persistable model implementation so it will be useful in most cases but it's not optimal in
     * terms of performance. Reimplement this method to avoid \ReflectionClass use in high-stress classes.
     *
     * @return array
     */
    protected function getDbDTO() {
        $result = [];

        $properties = \Maleficarum\Ioc\Container::get('ReflectionClass', [static::class])->getProperties(\ReflectionProperty::IS_PRIVATE);
        foreach ($properties as $key => $prop) {
            if ($prop->name === $this->getModelPrefix() . "Id") continue;
            if (strpos($prop->name, $this->getModelPrefix()) !== 0) continue;

            $methodName = 'get' . str_replace(' ', "", ucwords($prop->name));
            if (!method_exists($this, $methodName)) continue;

            $result[$prop->name] = ['param' => ':' . $prop->name . '_token_' . $key, 'value' => $this->$methodName(), 'column' => $prop->name];
        }

        return $result;
    }
    /* ------------------------------------ Persistable methods END -------------------------------------- */

    /* ------------------------------------ AbstractModel methods START ---------------------------------- */
    
    /**
     * @see \Maleficarum\Data\Model\AbstractModel.getId()
     */
    public function getId() {
        $method = 'get' . ucfirst($this->getModelPrefix()) . 'Id';

        return $this->$method();
    }

    /**
     * @see \Maleficarum\Data\Model\AbstractModel.setId()
     */
    public function setId($id) : \Maleficarum\Data\Model\AbstractModel {
        $method = 'set' . ucfirst($this->getModelPrefix()) . 'Id';
        $this->$method($id);

        return $this;
    }
    
    /* ------------------------------------ AbstractModel methods END ------------------------------------ */

    /* ------------------------------------ CRUD methods START ------------------------------------------- */
    
    /**
     * Persist data stored in this model as a new storage entry.
     * 
     * @see \Maleficarum\Api\Database\Model\CRUD::create()
     * @return \Maleficarum\Api\Database\Model\CRUD
     */
    public function create() : \Maleficarum\Api\Database\Model\CRUD {
        // connect to shard if necessary
        $shard = $this->getDb()->fetchShard($this->getShardRoute());
        $shard->isConnected() or $shard->connect();

        // fetch DB DTO object
        $data = $this->getDbDTO();

        // build the query
        $query = 'INSERT INTO "' . $this->getTable() . '" (';

        // attach column names
        $temp = [];
        foreach ($data as $el) $temp[] = $el['column'];
        count($temp) and $query .= '"' . implode('", "', $temp) . '"';

        // attach query transitional segment
        $query .= ') VALUES (';

        // attach parameter names
        $temp = [];
        foreach ($data as $el) $temp[] = $el['param'];
        count($temp) and $query .= implode(', ', $temp);

        // conclude query building
        $query .= ')';

        // attach returning
        $query .= ' RETURNING *;';

        // prepare the statement if necessary
        array_key_exists(static::class . '::' . __FUNCTION__, self::$st) or self::$st[static::class . '::' . __FUNCTION__] = $shard->prepare($query);

        // bind parameters
        foreach ($data as $el) {
            $type = is_bool($el['value']) ? \PDO::PARAM_BOOL : \PDO::PARAM_STR;
            self::$st[static::class . '::' . __FUNCTION__]->bindValue($el['param'], $el['value'], $type);
        }

        // execute the query
        self::$st[static::class . '::' . __FUNCTION__]->execute($this->getTable(), $this->getShardRoute());

        // set new model ID if possible
        $this->merge(self::$st[static::class . '::' . __FUNCTION__]->fetch());

        return $this;
    }

    /**
     * Refresh this model with current data from the storage
     *
     * @see \Maleficarum\Api\Database\Model\CRUD::read()
     * @return \Maleficarum\Api\Database\Model\CRUD
     * @throws \Maleficarum\Exception\NotFoundException
     */
    public function read() : \Maleficarum\Api\Database\Model\CRUD {
        // connect to shard if necessary
        $shard = $this->getDb()->fetchShard($this->getShardRoute());
        $shard->isConnected() or $shard->connect();

        // build the query
        $query = 'SELECT * FROM "' . $this->getTable() . '" WHERE "' . $this->getModelPrefix() . 'Id" = :id';
        array_key_exists(static::class . '::' . __FUNCTION__, self::$st) or self::$st[static::class . '::' . __FUNCTION__] = $shard->prepare($query);

        // bind query params
        self::$st[static::class . '::' . __FUNCTION__]->bindValue(":id", $this->getId());
        if (!self::$st[static::class . '::' . __FUNCTION__]->execute($this->getTable(), $this->getShardRoute()) || self::$st[static::class . '::' . __FUNCTION__]->rowCount() !== 1) {
            throw new \Maleficarum\Exception\NotFoundException('No entity found - ID: ' . $this->getId() . '. ' . static::class . '::read()');
        }

        // fetch results and merge them into this object
        $result = self::$st[static::class . '::' . __FUNCTION__]->fetch();
        $this->merge($result);

        return $this;
    }

    /**
     * Update storage entry with data currently stored in this model.
     *
     * @see \Maleficarum\Api\Database\Model\CRUD::update()
     * @return \Maleficarum\Api\Database\Model\CRUD
     */
    public function update() : \Maleficarum\Api\Database\Model\CRUD {
        // connect to shard if necessary
        $shard = $this->getDb()->fetchShard($this->getShardRoute());
        $shard->isConnected() or $shard->connect();

        // fetch DB DTO object
        $data = $this->getDbDTO();

        // build the query
        $query = 'UPDATE "' . $this->getTable() . '" SET ';

        // attach data definition
        $temp = [];
        foreach ($data as $el) $temp[] = '"' . $el['column'] . '" = ' . $el['param'];
        $query .= implode(", ", $temp) . " ";

        // conclude query building
        $query .= 'WHERE "' . $this->getModelPrefix() . 'Id" = :id RETURNING *';

        // prepare the statement if necessary
        array_key_exists(static::class . '::' . __FUNCTION__, self::$st) or self::$st[static::class . '::' . __FUNCTION__] = $shard->prepare($query);

        // bind parameters
        foreach ($data as $el) {
            $type = is_bool($el['value']) ? \PDO::PARAM_BOOL : \PDO::PARAM_STR;
            self::$st[static::class . '::' . __FUNCTION__]->bindValue($el['param'], $el['value'], $type);
        }

        // bind ID and execute
        self::$st[static::class . '::' . __FUNCTION__]->bindValue(":id", $this->getId());
        self::$st[static::class . '::' . __FUNCTION__]->execute($this->getTable(), $this->getShardRoute());

        // refresh current data with data returned from the database
        $this->merge(self::$st[static::class . '::' . __FUNCTION__]->fetch());

        return $this;
    }

    /**
     * Delete an entry from the storage based on ID data stored in this model
     *
     * @see \Maleficarum\Api\Database\Model\CRUD::delete()
     * @return \Maleficarum\Api\Database\Model\CRUD
     */
    public function delete() : \Maleficarum\Api\Database\Model\CRUD {
        // connect to shard if necessary
        $shard = $this->getDb()->fetchShard($this->getShardRoute());
        $shard->isConnected() or $shard->connect();

        // build the query
        $query = 'DELETE FROM "' . $this->getTable() . '" WHERE "' . $this->getModelPrefix() . 'Id" = :id';
        array_key_exists(static::class . '::' . __FUNCTION__, self::$st) or self::$st[static::class . '::' . __FUNCTION__] = $shard->prepare($query);

        // bind ID and execute
        self::$st[static::class . '::' . __FUNCTION__]->bindValue(":id", $this->getId());
        self::$st[static::class . '::' . __FUNCTION__]->execute($this->getTable(), $this->getShardRoute());

        return $this;
    }

    /**
     * Validate data stored in this model to check if it can be persisted in storage.
     * 
     * @param bool $clear
     *
     * @see \Maleficarum\Api\Database\Model\CRUD::validate()
     * @return bool
     */
    abstract public function validate(bool $clear = true) : bool;
    
    /* ------------------------------------ CRUD methods END --------------------------------------------- */

    /* ------------------------------------ Abstract methods START ------------------------------------- */
    
    /**
     * Fetch the name of current shard.
     *
     * @return string
     */
    abstract public function getShardRoute() : string;

    /**
     * Fetch the name of db table used as data source for this model.
     *
     * @return string
     */
    abstract protected function getTable() : string;

    /**
     * Fetch the prefix used as a prefix for database column property names.
     *
     * @return string
     */
    abstract protected function getModelPrefix() : string;
    
    /* ------------------------------------ Abstract methods END --------------------------------------- */
}
