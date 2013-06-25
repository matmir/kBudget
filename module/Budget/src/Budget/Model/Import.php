<?php

namespace Budget\Model;

use Base\Model\BaseModel;

/**
 * Class with the information about importing of the transactions from CSV file
 * 
 * @author Mateusz Mirosławski
 *
 */
class Import extends BaseModel
{
    /**
     * User identifier
     * 
     * @var int
     */
    protected $userId;
    
    /**
     * Bank account identifier into which imports transactions
     * 
     * @var int
     */
    protected $accountId;
    
    /**
     * CSV file name
     * 
     * @var string
     */
    protected $fileName;
    
    /**
     * File bank name
     * 
     * @var string
     */
    protected $bankName;
    
    /**
     * Actual position in the CSV file
     * 
     * @var int
     */
    protected $positionInFile;
    
    /**
     * New position in the CSV file.
     * Position will be updated after saving the transaction.
     * 
     * @var int
     */
    protected $newPositionInFile;
    
    /**
     * Number of the all transactions in the CSV file
     * 
     * @var int
     */
    protected $count;
    
    /**
     * Number of the imported transactions
     * 
     * @var int
     */
    protected $counted;

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
     * Gets the Bank account identifier into which imports transactions.
     *
     * @return int
     */
    public function getAccountId()
    {
        return $this->accountId;
    }

    /**
     * Sets the Bank account identifier into which imports transactions.
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
     * Gets the CSV file name.
     *
     * @return string
     */
    public function getFileName()
    {
        return $this->fileName;
    }

    /**
     * Sets the CSV file name.
     *
     * @param string $fileName the fileName
     *
     * @return self
     */
    public function setFileName($fileName)
    {
        $this->fileName = $fileName;

        return $this;
    }

    /**
     * Gets the File bank name.
     *
     * @return string
     */
    public function getBankName()
    {
        return $this->bankName;
    }

    /**
     * Sets the File bank name.
     *
     * @param string $bankName the bankName
     *
     * @return self
     */
    public function setBankName($bankName)
    {
        $this->bankName = $bankName;

        return $this;
    }

    /**
     * Gets the Actual position in the CSV file.
     *
     * @return int
     */
    public function getPositionInFile()
    {
        return $this->positionInFile;
    }

    /**
     * Sets the Actual position in the CSV file.
     *
     * @param int $positionInFile the positionInFile
     *
     * @return self
     */
    public function setPositionInFile($positionInFile)
    {
        $this->positionInFile = $positionInFile;

        return $this;
    }

    /**
     * Gets the New position in the CSV file.
     *
     * @return int
     */
    public function getNewPositionInFile()
    {
        return $this->newPositionInFile;
    }

    /**
     * Sets the New position in the CSV file.
     *
     * @param int $newPositionInFile the newPositionInFile
     *
     * @return self
     */
    public function setNewPositionInFile($newPositionInFile)
    {
        $this->newPositionInFile = $newPositionInFile;

        return $this;
    }

    /**
     * Gets the Number of the all transactions in the CSV file.
     *
     * @return int
     */
    public function getCount()
    {
        return $this->count;
    }

    /**
     * Sets the Number of the all transactions in the CSV file.
     *
     * @param int $count the count
     *
     * @return self
     */
    public function setCount($count)
    {
        $this->count = $count;

        return $this;
    }

    /**
     * Gets the Number of the imported transactions.
     *
     * @return int
     */
    public function getCounted()
    {
        return $this->counted;
    }

    /**
     * Sets the Number of the imported transactions.
     *
     * @param int $counted the counted
     *
     * @return self
     */
    public function setCounted($counted)
    {
        $this->counted = $counted;

        return $this;
    }

    /**
     * Initialize the object.
     * 
     * @param array $params
     */
    public function __construct(array $params = array())
    {
        parent::__construct($params);
    }
}
