<?php
/**
 * This class implements Functionality common to all configuration storage classes.
 *
 * @implements \ArrayAccess
 */

namespace Maleficarum\Api\Config;

abstract class AbstractConfig implements \ArrayAccess
{
    /**
     * Internal storage for config data.
     *
     * @var array
     */
    protected $data = [];

    /* ------------------------------------ Magic methods START ---------------------------------------- */
    /**
     * Create and load a new config instance.
     *
     * @param string $id
     */
    public function __construct($id)
    {
        $this->load($id);
    }
    /* ------------------------------------ Magic methods END ------------------------------------------ */

    /* ------------------------------------ ArrayAccess methods START ---------------------------------- */
    /**
     * @see ArrayAccess::offsetExists()
     */
    public function offsetExists($offset)
    {
        return array_key_exists($offset, $this->data);
    }

    /**
     * This method will always throw a RuntimeException. It's not allowed to set config data from the execution context.
     * 
     * @see ArrayAccess::offsetUnset()
     * @throws \RuntimeException
     */
    public function offsetUnset($offset)
    {
        throw new \RuntimeException(sprintf('Config data is read-only. \%s::offsetUnset()', get_class($this)));
    }

    /**
     * @see ArrayAccess::offsetGet()
     */
    public function offsetGet($offset)
    {
        if (array_key_exists($offset, $this->data)) {
            return $this->data[$offset];
        } else {
            return null;
        }
    }

    /**
     * This method will always throw a RuntimeException. It's not allowed to set config data from the execution context.
     * 
     * @see ArrayAccess::offsetSet()
     * @throws \RuntimeException
     */
    public function offsetSet($offset, $value)
    {
        throw new \RuntimeException(sprintf('Config data is read-only. \%s::offsetSet()', get_class($this)));
    }
    /* ------------------------------------ ArrayAccess methods END ------------------------------------ */

    /* ------------------------------------ Abstract methods START ------------------------------------- */
    /**
     * Load the specified config from a storage.
     *
     * @param string $id
     *
     * @return $this
     */
    abstract public function load($id);
    /* ------------------------------------ Abstract methods END --------------------------------------- */
}
