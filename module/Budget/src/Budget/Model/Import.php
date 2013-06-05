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
    public $uid;
    
    /**
     * Bank account identifier into which imports transactions
     * 
     * @var int
     */
    public $aid;
    
    /**
     * CSV file name
     * 
     * @var string
     */
    public $fname;
    
    /**
     * File bank id
     * 
     * @var string
     */
    public $bank;
    
    /**
     * Actual position in the CSV file
     * 
     * @var int
     */
    public $fpos;
    
    /**
     * New position in the CSV file.
     * Position will be updated after saving the transaction.
     * 
     * @var int
     */
    public $nfpos;
    
    /**
     * Number of the all transactions in the CSV file
     * 
     * @var int
     */
    public $count;
    
    /**
     * Number of the imported transactions
     * 
     * @var int
     */
    public $counted;
}