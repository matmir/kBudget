<?php

namespace Base\Model;

/**
 * Base model abstract class
 * 
 * @author Mateusz Mirosławski
 *
 */
abstract class BaseModel
{
    /**
     * Initialize the object.
     * 
     * @param array $params
     */
    public function __construct(array $params = array())
    {
        $this->exchangeArray($params);
    }
    
    /**
     * Exchange params values into the object fields
     * 
     * @param array $params
     */
    public function exchangeArray(array $params = array())
    {
        foreach ($params as $name => $value) {
            if (property_exists($this, $name)) {
                $this->{'set'.ucwords($name)}($value);
            }
        }
    }
    
    /**
     * Get the array with object fields
     * 
     * @return array
     */
    public function getArrayCopy()
    {
        return get_object_vars($this);
    }
}
