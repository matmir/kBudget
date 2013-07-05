<?php
/**
 *  Transfer model
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

namespace Budget\Model;

use Base\Model\BaseModel;

class Transfer extends BaseModel
{
    /**
     * Transfer identifier
     * 
     * @var int
     */
    protected $transferId;
    
    /**
     * User identifier
     *
     * @var int
     */
    protected $userId;
    
    
    /**
     * Outgoing transaction identifier
     *
     * @var int
     */
    protected $outTransactionId;
    
    /**
     * Incoming transaction identifier
     * 
     * @var int
     */
    protected $inTransactionId;
    
    /**
     * Initialize the object.
     *
     * @param array $params
     */
    public function __construct(array $params = array())
    {
        parent::__construct($params);
    }

    /**
     * Gets the Transfer identifier.
     *
     * @return int
     */
    public function getTransferId()
    {
        return $this->transferId;
    }

    /**
     * Sets the Transfer identifier.
     *
     * @param int $transferId the transferId
     *
     * @return self
     */
    public function setTransferId($transferId)
    {
        $this->transferId = $transferId;

        return $this;
    }

    /**
     * Gets the User identifier.
     *
     * @return int
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * Sets the User identifier.
     *
     * @param int $userId the userId
     *
     * @return self
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;

        return $this;
    }

    /**
     * Gets the Outgoing transaction identifier.
     *
     * @return int
     */
    public function getOutTransactionId()
    {
        return $this->outTransactionId;
    }

    /**
     * Sets the Outgoing transaction identifier.
     *
     * @param int $outTransactionId the outTransactionId
     *
     * @return self
     */
    public function setOutTransactionId($outTransactionId)
    {
        $this->outTransactionId = $outTransactionId;

        return $this;
    }

    /**
     * Gets the Incoming transaction identifier.
     *
     * @return int
     */
    public function getInTransactionId()
    {
        return $this->inTransactionId;
    }

    /**
     * Sets the Incoming transaction identifier.
     *
     * @param int $inTransactionId the inTransactionId
     *
     * @return self
     */
    public function setInTransactionId($inTransactionId)
    {
        $this->inTransactionId = $inTransactionId;

        return $this;
    }
}
