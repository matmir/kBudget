<?php

namespace User\Model;

use Base\Model\BaseModel;

/**
 * User model.
 * 
 * @author Mateusz Mirosławski
 *
 */
class User extends BaseModel
{
    /**
     * User identifier
     * 
     * @var int
     */
    public $uid;
    
    /**
     * User e-mail
     * 
     * @var string
     */
    public $email;
    
    /**
     * User login
     * 
     * @var string
     */
    public $login;
    
    /**
     * User password
     * 
     * @var string
     */
    public $pass;
    
    /**
     * User type. (0 - user, 1 - admin, 2 - demo)
     * 
     * @var int
     */
    public $u_type;
    
    /**
     * Userv active flag (0/1)
     * 
     * @var int
     */
    public $active;
    
    /**
     * User register date
     * 
     * @var string
     */
    public $register_date;
    
    /**
     * User last login date
     * 
     * @var string
     */
    public $last_login_date;
    
    /**
     * Default bank account identifier
     * 
     * @var int
     */
    public $default_aid;
    
    /**
     * Gen new password
     * 
     * @return string
     */
    public function genNewPass()
    {
        $pas = '';
        for ($i = 0; $i < 8; $i++) {
            $pas .= chr(rand(33, 126));
        }
        
        return $pas;
    }
    
    /**
     * Construct the user object
     *
     * @param array $params
     */
    public function __construct(array $params = array())
    {
        parent::__construct($params);
    }
}