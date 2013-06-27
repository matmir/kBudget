<?php

namespace Budget\Model;

use Base\Model\BaseModel;

/**
 * Transfer model
 * 
 * @author Mateusz Mirosławski
 *
 */
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
