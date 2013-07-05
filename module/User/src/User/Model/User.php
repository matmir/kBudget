<?php
/**
 *  User model.
 *  Copyright (C) 2013 Mateusz Mirosławski
 * 
 *  This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 * 
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 * 
 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace User\Model;

use Base\Model\BaseModel;
use \DateTime;

class User extends BaseModel
{
    /**
     * User identifier
     * 
     * @var int
     */
    protected $userId;
    
    /**
     * User e-mail
     * 
     * @var string
     */
    protected $email;
    
    /**
     * User login
     * 
     * @var string
     */
    protected $login;
    
    /**
     * User password
     * 
     * @var string
     */
    protected $pass;
    
    /**
     * User type. (0 - user, 1 - admin, 2 - demo)
     * 
     * @var int
     */
    protected $type;
    
    /**
     * Userv active flag (0/1)
     * 
     * @var int
     */
    protected $active;
    
    /**
     * User register date
     * 
     * @var DateTime
     */
    protected $registerDate;
    
    /**
     * User last login date
     * 
     * @var DateTime
     */
    protected $lastLoginDate;
    
    /**
     * Default bank account identifier
     * 
     * @var int
     */
    protected $defaultAccountId;
    
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
     * Gets the User e-mail.
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Sets the User e-mail.
     *
     * @param string $email the email
     *
     * @return self
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Gets the User login.
     *
     * @return string
     */
    public function getLogin()
    {
        return $this->login;
    }

    /**
     * Sets the User login.
     *
     * @param string $login the login
     *
     * @return self
     */
    public function setLogin($login)
    {
        $this->login = $login;

        return $this;
    }

    /**
     * Gets the User password.
     *
     * @return string
     */
    public function getPass()
    {
        return $this->pass;
    }

    /**
     * Sets the User password.
     *
     * @param string $pass the pass
     *
     * @return self
     */
    public function setPass($pass)
    {
        $this->pass = $pass;

        return $this;
    }

    /**
     * Gets the User type. (0 - user, 1 - admin, 2 - demo).
     *
     * @return int
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Sets the User type. (0 - user, 1 - admin, 2 - demo).
     *
     * @param int $type the type
     *
     * @return self
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Gets the Userv active flag (0/1).
     *
     * @return int
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * Sets the Userv active flag (0/1).
     *
     * @param int $active the active
     *
     * @return self
     */
    public function setActive($active)
    {
        $this->active = $active;

        return $this;
    }

    /**
     * Gets the User register date.
     *
     * @return DateTime
     */
    public function getRegisterDate()
    {
        return $this->registerDate;
    }

    /**
     * Sets the User register date.
     *
     * @param DateTime/String $registerDate the registerDate
     *
     * @return self
     */
    public function setRegisterDate($registerDate)
    {
        if (!($registerDate instanceof DateTime)) {
            $this->registerDate = new DateTime($registerDate);
        } else {
            $this->registerDate = $registerDate;
        }

        return $this;
    }

    /**
     * Gets the User last login date.
     *
     * @return DateTime
     */
    public function getLastLoginDate()
    {
        return $this->lastLoginDate;
    }

    /**
     * Sets the User last login date.
     *
     * @param DateTime/String $lastLoginDate the lastLoginDate
     *
     * @return self
     */
    public function setLastLoginDate($lastLoginDate)
    {
        if (!($lastLoginDate instanceof DateTime)) {
            $this->lastLoginDate = new DateTime($lastLoginDate);
        } else {
            $this->lastLoginDate = $lastLoginDate;
        }

        return $this;
    }

    /**
     * Gets the Default bank account identifier.
     *
     * @return int
     */
    public function getDefaultAccountId()
    {
        return $this->defaultAccountId;
    }

    /**
     * Sets the Default bank account identifier.
     *
     * @param int $defaultAccountId the defaultAccountId
     *
     * @return self
     */
    public function setDefaultAccountId($defaultAccountId)
    {
        $this->defaultAccountId = $defaultAccountId;

        return $this;
    }
}
