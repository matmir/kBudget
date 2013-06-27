<?php

namespace User\Model;

use Base\Model\BaseModel;

/**
 * Bank account model
 * 
 * @author Mateusz Mirosławski
 *
 */
class Account extends BaseModel
{
    /**
     * Account identifier
     * 
     * @var int
     */
    protected $accountId;
    
    /**
     * User identifier
     * 
     * @var int
     */
    protected $userId;
    
    /**
     * Account name
     * 
     * @var string
     */
    protected $accountName;
    
    /**
     * Account balance
     * 
     * @var float
     */
    protected $balance;
    
    /**
     * Construct the account object
     * 
     * @param array $params
     */
    public function __construct(array $params = array())
    {
        $this->balance = 0;
        
        parent::__construct($params);
    }

    /**
     * Gets the Account identifier.
     *
     * @return int
     */
    public function getAccountId()
    {
        return $this->accountId;
    }

    /**
     * Sets the Account identifier.
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
     * Gets the Account name.
     *
     * @return string
     */
    public function getAccountName()
    {
        return $this->accountName;
    }

    /**
     * Sets the Account name.
     *
     * @param string $accountName the accountName
     *
     * @return self
     */
    public function setAccountName($accountName)
    {
        $this->accountName = $accountName;

        return $this;
    }

    /**
     * Gets the Account balance.
     *
     * @return float
     */
    public function getBalance()
    {
        return $this->balance;
    }

    /**
     * Sets the Account balance.
     *
     * @param float $balance the balance
     *
     * @return self
     */
    public function setBalance($balance)
    {
        $this->balance = $balance;

        return $this;
    }
}
