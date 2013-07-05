<?php
/**
 *  Base model abstract class
 *  Copyright (C) 2013 Mateusz Mirosławski
 * 
 *  This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 * 
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 * 
 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace Base\Model;

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
