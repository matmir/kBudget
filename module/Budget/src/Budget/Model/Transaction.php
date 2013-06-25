<?php

namespace Budget\Model;

use Base\Model\BaseModel;

/**
 * Transaction model
 * 
 * @author Mateusz Mirosławski
 *
 */
class Transaction extends BaseModel
{
    /**
     * Transaction identifier
     * 
     * @var int
     */
    public $tid;
    
    /**
     * Bank account identifier
     *
     * @var int
     */
    public $aid;
    
    /**
     * Bank account identifier which transfers money
     * 
     * @var int
     */
    public $taid;
    
    /**
     * User identifier
     * 
     * @var int
     */
    public $uid;
    
    /**
     * Transaction type (0 - income, 1 - expense, 2 - outgoing transfer, 3 - incoming transfer)
     * 
     * @var int
     */
    public $t_type;
    
    /**
     * Category identifier
     * 
     * @var int
     */
    public $cid;
    
    /**
     * Transaction date
     * 
     * @var string
     */
    public $t_date;
    
    /**
     * Transaction description
     * 
     * @var string
     */
    public $t_content;
    
    /**
     * Transaction value
     * 
     * @var float
     */
    public $t_value;
    
    /**
     * Initialize the object.
     *
     * @param array $params
     */
    public function __construct(array $params = array())
    {
        $this->tid = null;
        $this->taid = null;
        
        parent::__construct($params);
    }
}
