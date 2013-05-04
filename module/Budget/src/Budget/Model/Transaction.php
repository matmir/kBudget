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
     * User identifier
     * 
     * @var int
     */
    public $uid;
    
    /**
     * Transaction type (0 - income, 1 - expense)
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
     * Category name
     * 
     * @var string
     */
    public $c_name;
    
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
        parent::__construct($params);
    }
}