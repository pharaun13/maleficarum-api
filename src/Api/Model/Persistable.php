<?php
/**
 * This class provides a basis for all persistable model objects.
 *
 * @extends \Maleficarum\Api\Model\AbstractModel
 *
 * @implements \Maleficarum\Api\Model\iCRUD
 */

namespace Maleficarum\Api\Model;

abstract class Persistable extends \Maleficarum\Api\Model\AbstractModel implements \Maleficarum\Api\Model\CRUD
{
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

    /* ------------------------------------ AbstractModel methods START ---------------------------------- */
    /**
     * @see \Maleficarum\Api\Model\AbstractModel::getId()
     */
    public function getId()
    {
        $method = 'get' . ucfirst($this->getModelPrefix()) . 'Id';

        return $this->$method();
    }

    /**
     * @see \Maleficarum\Api\Model\AbstractModel::setId()
     */
    public function setId($id)
    {
        $method = 'set' . ucfirst($this->getModelPrefix()) . 'Id';
        $this->$method($id);

        return $this;
    }
    /* ------------------------------------ AbstractModel methods END ------------------------------------ */

    /* ------------------------------------ CRUD methods START ------------------------------------------- */
    /**
     * @see \Maleficarum\Api\Model\CRUD::create()
     */
    public function create()
    {
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
     * @see \Maleficarum\Api\Model\CRUD::read()
     */
    public function read()
    {
        // connect to shard if necessary
        $shard = $this->getDb()->fetchShard($this->getShardRoute());
        $shard->isConnected() or $shard->connect();

        // build the query
        $query = 'SELECT * FROM "' . $this->getTable() . '" WHERE "' . $this->getModelPrefix() . 'Id" = :id';
        array_key_exists(static::class . '::' . __FUNCTION__, self::$st) or self::$st[static::class . '::' . __FUNCTION__] = $shard->prepare($query);

        // bind query params
        self::$st[static::class . '::' . __FUNCTION__]->bindValue(":id", $this->getId());
        if (!self::$st[static::class . '::' . __FUNCTION__]->execute($this->getTable(), $this->getShardRoute()) || self::$st[static::class . '::' . __FUNCTION__]->rowCount() !== 1) {
            throw new \Maleficarum\Api\Exception\NotFoundException('No entity found - ID: ' . $this->getId() . '. ' . static::class . '::read()');
        }

        // fetch results and merge them into this object
        $result = self::$st[static::class . '::' . __FUNCTION__]->fetch();
        $this->merge($result);

        return $this;
    }

    /**
     * @see \Maleficarum\Api\Model\CRUD::update()
     */
    public function update()
    {
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
     * @see \Maleficarum\Api\Model\CRUD::delete()
     */
    public function delete()
    {
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
     * @see \Maleficarum\Api\Model\CRUD::validate()
     */
    abstract public function validate($clear = true);
    /* ------------------------------------ CRUD methods END --------------------------------------------- */

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
    protected function getDbDTO()
    {
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

    /**
     * Fetch the name of current shard.
     *
     * @return string
     */
    abstract public function getShardRoute();

    /**
     * Fetch the name of db table used as data source for this model.
     *
     * @return string
     */
    abstract protected function getTable();

    /**
     * Fetch the prefix used as a prefix for database column property names.
     *
     * @return string
     */
    abstract protected function getModelPrefix();
}
