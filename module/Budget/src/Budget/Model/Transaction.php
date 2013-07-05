<?php
/**
 *  Transaction model
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
use \DateTime;

class Transaction extends BaseModel
{
    /**
     * Transaction types
     */
    const PROFIT = 0;
    const EXPENSE = 1;
    const OUTGOING_TRANSFER = 2;
    const INCOMING_TRANSFER = 3;

    /**
     * Transaction identifier
     * 
     * @var int
     */
    protected $transactionId;
    
    /**
     * Bank account identifier
     *
     * @var int
     */
    protected $accountId;
    
    /**
     * Bank account identifier which transfers money
     * 
     * @var int
     */
    protected $transferAccountId;
    
    /**
     * User identifier
     * 
     * @var int
     */
    protected $userId;

    /**
     * Category identifier
     * 
     * @var int
     */
    protected $categoryId;
    
    /**
     * Transaction type (0 - income, 1 - expense, 2 - outgoing transfer, 3 - incoming transfer)
     * 
     * @var int
     */
    protected $transactionType;
    
    /**
     * Transaction date
     * 
     * @var DateTime
     */
    protected $date;
    
    /**
     * Transaction content
     * 
     * @var string
     */
    protected $content;
    
    /**
     * Transaction value
     * 
     * @var float
     */
    protected $value;
    
    /**
     * Initialize the object.
     *
     * @param array $params
     */
    public function __construct(array $params = array())
    {
        $this->transactionId = null;
        $this->transferAccountId = null;
        
        parent::__construct($params);
    }

    /**
     * Gets the Transaction identifier.
     *
     * @return int
     */
    public function getTransactionId()
    {
        return $this->transactionId;
    }

    /**
     * Sets the Transaction identifier.
     *
     * @param int $transactionId the transactionId
     *
     * @return self
     */
    public function setTransactionId($transactionId)
    {
        $this->transactionId = $transactionId;

        return $this;
    }

    /**
     * Gets the Bank account identifier.
     *
     * @return int
     */
    public function getAccountId()
    {
        return $this->accountId;
    }

    /**
     * Sets the Bank account identifier.
     *
     * @param int $accountId the accountId
     *
     * @return self
     */
    public function setAccountId($accountId)
    {
        $this->accountId = $accountId;

        return $this;
    }

    /**
     * Gets the Bank account identifier which transfers money.
     *
     * @return int
     */
    public function getTransferAccountId()
    {
        return $this->transferAccountId;
    }

    /**
     * Sets the Bank account identifier which transfers money.
     *
     * @param int $transferAccountId the transferAccountId
     *
     * @return self
     */
    public function setTransferAccountId($transferAccountId)
    {
        $this->transferAccountId = $transferAccountId;

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
     * Gets the Category identifier.
     *
     * @return int
     */
    public function getCategoryId()
    {
        return $this->categoryId;
    }

    /**
     * Sets the Category identifier.
     *
     * @param int $categoryId the categoryId
     *
     * @return self
     */
    public function setCategoryId($categoryId)
    {
        $this->categoryId = $categoryId;

        return $this;
    }

    /**
     * Gets the Transaction type (0 - income, 1 - expense, 2 - outgoing transfer, 3 - incoming transfer).
     *
     * @return int
     */
    public function getTransactionType()
    {
        return $this->transactionType;
    }

    /**
     * Sets the Transaction type (0 - income, 1 - expense, 2 - outgoing transfer, 3 - incoming transfer).
     *
     * @param int $type the type
     *
     * @return self
     */
    public function setTransactionType($type)
    {
        $this->transactionType = $type;

        return $this;
    }

    /**
     * Gets the Transaction date.
     *
     * @return DateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Sets the Transaction date.
     *
     * @param DateTime/string $date the date
     *
     * @return self
     */
    public function setDate($date)
    {
        if (!($date instanceof DateTime)) {
            $this->date = new DateTime($date);
        } else {
            $this->date = $date;
        }

        return $this;
    }

    /**
     * Gets the Transaction content.
     *
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Sets the Transaction content.
     *
     * @param string $content the content
     *
     * @return self
     */
    public function setContent($content)
    {
        $this->content = $content;

        return $this;
    }

    /**
     * Gets the Transaction value.
     *
     * @return float
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Sets the Transaction value.
     *
     * @param float $value the value
     *
     * @return self
     */
    public function setValue($value)
    {
        $this->value = abs($value);

        return $this;
    }
}
