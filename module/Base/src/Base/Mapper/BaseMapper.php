<?php
/**
 *  Base mapper service class
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

namespace Base\Mapper;

use Base\Service\BaseService;

use Zend\Db\Adapter\Adapter;

class BaseMapper extends BaseService
{
    /**
     * Instance of Zend db adapter
     * 
     * @var \Zend\Db\Adapter\Adapter
     */
    private $adapter;
    
    /**
     * Get database adapter instance
     * 
     * @return \Zend\Db\Adapter\Adapter
     */
    public function getDbAdapter()
    {
        // Check if there is adapter instance
        if (!($this->adapter instanceof Adapter)) {
            
            // Get system configuration
            $config = $this->getServiceLocator()->get('Configuration');
            
            // Create db adapter
            $this->adapter = new Adapter($config['db']);
            
        }
        
        return $this->adapter;
    }
}
