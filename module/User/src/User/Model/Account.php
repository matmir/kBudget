<?php

namespace User\Model;

use Base\Model\BaseModelTemp;

/**
 * Bank account model
 * 
 * @author Mateusz Mirosławski
 *
 */
class Account extends BaseModelTemp
{
    /**
     * Account identifier
     * 
     * @var int
     */
    public $aid;
    
    /**
     * User identifier
     * 
     * @var int
     */
    public $uid;
    
    /**
     * Account name
     * 
     * @var string
     */
    public $a_name;
    
    /**
     * Account balance
     * 
     * @var float
     */
    public $balance;
    
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
}
