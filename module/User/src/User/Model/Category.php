<?php

namespace User\Model;

use Base\Model\BaseModelTemp;

/**
 * Category model
 * 
 * @author Mateusz Mirosławski
 *
 */
class Category extends BaseModelTemp
{
    /**
     * Category identifier
     * 
     * @var int
     */
    public $cid;
    
    /**
     * Parent category identifier
     * 
     * @var int
     */
    public $pcid;
    
    /**
     * User identifier
     * 
     * @var int
     */
    public $uid;
    
    /**
     * Category type (0 - income, 1 - expense)
     * 
     * @var int
     */
    public $c_type;
    
    /**
     * Category name
     * 
     * @var string
     */
    public $c_name;
    
    /**
     * Construct the category object
     * 
     * @param array $params
     */
    public function __construct(array $params = array())
    {
        $this->cid = 0;
        $this->pcid = null;
        $this->uid = 0;
        $this->c_type = -1;
        $this->c_name = '';
        
        parent::__construct($params);
    }
}
