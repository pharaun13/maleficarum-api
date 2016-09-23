<?php
/**
 * This abstract class provides functionality common to all Model classes.
 *
 * @implements \Maleficarum\Api\Model\iIdentifiable
 * @implements \JsonSerializable
 *
 * @abstract
 */

namespace Maleficarum\Api\Model;

abstract class AbstractModel implements \Maleficarum\Api\Model\Identifiable, \JsonSerializable
{
    /**
     * Use \Maleficarum\Api\Error\Container trait functionality.
     *
     * @trait
     */
    use \Maleficarum\Api\Error\Container;

    /**
     * Internal storage for model specific meta data. Will not be used when interacting with model directly but will allow for model
     * decorators to decorate model instance data.
     *
     * @var array
     */
    protected $_meta = [];

    /**
     * Merge provided data into this object.
     *
     * @param \stdClass|array $data
     *
     * @return $this
     */
    public function merge($data)
    {
        // cast input into a valid. unified format
        is_array($data) or $data = (array)$data;

        // attempt to merge as much input data as possible
        foreach ($data as $key => $val) {
            $methodName = 'set' . str_replace(' ', '', ucwords($key));
            method_exists($this, $methodName) and $this->$methodName($val);
        }

        // merge meta if any is available
        array_key_exists('_meta', $data) && is_array($data['_meta']) and $this->_meta = $data['meta'];

        return $this;
    }

    /**
     * Restore the model object to its most basic state - without any data.
     *
     * @return $this
     */
    public function clear()
    {
        // recover all model properties from Reflection
        $properties = (new \ReflectionClass($this))->getProperties(\ReflectionProperty::IS_PRIVATE);

        // clear all properties that have setters
        foreach ($properties as $val) {
            $methodName = 'set' . str_replace(' ', '', ucwords(str_replace('_', ' ', $val->name)));
            method_exists($this, $methodName) and $this->$methodName(null);
        }

        // clear meta
        $this->_meta = [];

        return $this;
    }

    /**
     * Skip required attributes from object
     *
     * @param array $skipProperties
     * @param bool $asArray
     *
     * @return \stdClass
     */
    public function getDTO(array $skipProperties = [], $asArray = true)
    {
        // initialize result storage
        $result = [];

        // fetch all private properties from Reflection (only private properties are part of actual model data)
        $properties = (new \ReflectionClass($this))->getProperties(\ReflectionProperty::IS_PRIVATE);

        // include all model data in the result (with the exception of specified "skip" properties)
        foreach ($properties as $val) {
            if (in_array($val->name, $skipProperties)) continue;

            $methodName = 'get' . str_replace(' ', "", ucwords($val->name));
            method_exists($this, $methodName) and $result[$val->name] = $this->$methodName();
        }

        // add meta to DTO (only if it is not empty)
        is_array($this->_meta) && count($this->_meta) and $result = array_merge($result, $this->_meta);

        // return results (structure type as defined)
        return $asArray ? $result : (object)$result;
    }

    /* ------------------------------------ Magic methods START ---------------------------------------- */
    /**
     * Fetch value from model meta.
     *
     * @param mixed $name
     *
     * @return mixed
     */
    public function __get($name)
    {
        return array_key_exists($name, $this->_meta) ? $this->_meta[$name] : null;
    }

    /**
     * Add new value to model meta.
     *
     * @param mixed $name
     * @param mixed $value
     */
    public function __set($name, $value)
    {
        $this->_meta[$name] = $value;
    }
    /* ------------------------------------ Magic methods END ------------------------------------------ */

    /* ------------------------------------ JsonSerializable methods START ----------------------------- */
    /**
     * @see JsonSerializable::count()
     */
    public function jsonSerialize()
    {
        return $this->getDTO();
    }
    /* ------------------------------------ JsonSerializable methods END ------------------------------- */

    /* ------------------------------------ Abstract methods START ------------------------------------- */
    /**
     * @see \Maleficarum\Api\Model\Identifiable::setId()
     */
    abstract public function setId($id);

    /**
     * @see \Maleficarum\Api\Model\Identifiable::getId()
     */
    abstract public function getId();
    /* ------------------------------------ Abstract methods END --------------------------------------- */
}