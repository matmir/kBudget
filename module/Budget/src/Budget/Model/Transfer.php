<?php

namespace Budget\Model;

use Base\Model\BaseModelTemp;

/**
 * Transfer model
 * 
 * @author Mateusz Mirosławski
 *
 */
class Transfer extends BaseModelTemp
{
    /**
     * Transfer identifier
     * 
     * @var int
     */
    public $trid;
    
    /**
     * User identifier
     *
     * @var int
     */
    public $uid;
    
    
    /**
     * Outgoing transaction identifier
     *
     * @var int
     */
    public $tid_out;
    
    /**
     * Incoming transaction identifier
     * 
     * @var int
     */
    public $tid_in;
    
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
