<?php
/**
 *  User mapper
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

namespace User\Mapper;

use Base\Mapper\BaseMapper;
use Zend\Db\Sql\Sql;

use User\Model\User;

use Zend\Paginator\Paginator;
use Zend\Paginator\Adapter\DbSelect;

class UserMapper extends BaseMapper
{
    /**
     * MySQL user table name
     *
     * @var string
     */
    const TABLE = 'users';
    
    /**
     * Check if user login exist in the database.
     * If user exist return his identifier.
     * 
     * @param string $u_login User login
     * @return int
     */
    public function isUserLoginExists($u_login)
    {
        $sql = new Sql($this->getDbAdapter());
        $select = $sql->select();
        
        $select->from(array('u' => self::TABLE))
                ->where(array('u.login' => (string)$u_login));
        
        $statement = $sql->prepareStatementForSqlObject($select);
        $row = $statement->execute();
        
        $data = $row->current();
        
        if ($data === null) {
            return 0;
        } else {
            return $data['userId'];
        }
    }
    
    /**
     * Check if given e-mail address exist in the database.
     * If address exist return user identifier of this address.
     * 
     * @param string $u_email User e-mail address
     * @return int
     */
    public function isEmailExists($u_email)
    {
        $sql = new Sql($this->getDbAdapter());
        $select = $sql->select();
        
        $select->from(array('u' => self::TABLE))
                ->where(array('u.email' => (string)$u_email));
        
        $statement = $sql->prepareStatementForSqlObject($select);
        $row = $statement->execute();
        
        $data = $row->current();
        
        if ($data === null) {
            return 0;
        } else {
            return $data['userId'];
        }
    }
    
    /**
     * Check if given e-mail is user address.
     * 
     * @param string $u_email E-mail address
     * @param int $uid User identifier
     * @return bool
     */
    public function isUserEmail($u_email, $uid)
    {
        $sql = new Sql($this->getDbAdapter());
        $select = $sql->select();
        
        $select->from(array('u' => self::TABLE))
                ->where(array('u.email' => (string)$u_email,
                              'u.userId' => (int)$uid,
                              ));
        
        $statement = $sql->prepareStatementForSqlObject($select);
        $row = $statement->execute();
        
        $data = $row->current();
        
        return ($data === null)?(false):(true);
    }
    
    /**
     * Add new user. Return user identifier.
     * 
     * @param User $user User object
     * @return int
     */
    public function addUser(User $user)
    {
        $data = array(
            'login'  => $user->getLogin(),
            'email'  => $user->getEmail(),
            'pass' => $user->getPass(),
            'type' => 0,
            'active' => 1,
            'registerDate' => date('Y-m-d H:i:s'),
        );
        
        $sql = new Sql($this->getDbAdapter());

        $insert = $sql->insert();
        $insert->into(self::TABLE);
        $insert->values($data);
        
        $statement = $sql->prepareStatementForSqlObject($insert);
        $val = $statement->execute()->getGeneratedValue();
        
        return $val;
    }
    
    /**
     * Change user password.
     * 
     * @param int $uid User identifier
     * @param string $new_pass New encrypted password
     */
    public function changeUserPass($uid, $new_pass)
    {
        $data = array(
            'pass' => (string)$new_pass,
        );
        
        $sql = new Sql($this->getDbAdapter());

        $update = $sql->update();
        $update->table(self::TABLE);
        $update->set($data);
        $update->where(array('userId' => (int)$uid));
        
        $statement = $sql->prepareStatementForSqlObject($update);
        $statement->execute();
    }
    
    /**
     * Change user e-mail address
     * 
     * @param int $uid User identifier
     * @param string $new_email New e-mail address
     */
    public function changeUserEmail($uid, $new_email)
    {
        $data = array(
            'email' => (string)$new_email,
        );
        
        $sql = new Sql($this->getDbAdapter());

        $update = $sql->update();
        $update->table(self::TABLE);
        $update->set($data);
        $update->where(array('userId' => (int)$uid));
        
        $statement = $sql->prepareStatementForSqlObject($update);
        $statement->execute();
    }
    
    /**
     * Set user activation flag
     * 
     * @param int $uid User identifier
     * @param int $act New activation flag
     * @throws \Exception
     */
    public function setUserActive($uid, $act)
    {
        if (!($act==0 || $act==1)) {
            throw new \Exception('Activation flag must be 0 or 1');
        }
        
        $data = array(
            'active' => (int)$act,
        );
        
        $sql = new Sql($this->getDbAdapter());

        $update = $sql->update();
        $update->table(self::TABLE);
        $update->set($data);
        $update->where(array('userId' => (int)$uid));
        
        $statement = $sql->prepareStatementForSqlObject($update);
        $statement->execute();
    }
    
    /**
     * Set user last login date
     * 
     * @param int $uid User identifier
     */
    public function setUserLoginDate($uid)
    {
        $sql = new Sql($this->getDbAdapter());

        $update = $sql->update();
        $update->table(self::TABLE);
        $update->set(array('lastLoginDate' => date('Y-m-d H:i:s')));
        $update->where(array('userId' => (int)$uid));
        
        $statement = $sql->prepareStatementForSqlObject($update);
        $statement->execute();
    }
    
    /**
     * Get user data
     * 
     * @param int $uid User identifier
     * @throws \Exception
     * @return User
     */
    public function getUser($uid)
    {
        $sql = new Sql($this->getDbAdapter());
        $select = $sql->select();
        
        $select->from(array('u' => self::TABLE))
                ->where(array('u.userId' => (int)$uid));
        
        $statement = $sql->prepareStatementForSqlObject($select);
        $row = $statement->execute();
        
        if (!$row->count()) {
            throw new \Exception('User does not exist!');
        }
        
        return new User($row->current());
    }
    
    /**
     * Get all users.
     * 
     * @param int $pg Page number
     * @throws \Exception
     * @return \Zend\Paginator\Paginator
     */
    public function getUsers($pg)
    {
        if ($pg <=0) {
            throw new \Exception('Page number must be greater than 0!');
        }
        
        $sql = new Sql($this->getDbAdapter());
        $select = $sql->select();
        
        $select->from(array('u' => self::TABLE));
        
        $paginator = new Paginator(new DbSelect($select, $sql));
        $paginator->setItemCountPerPage(20);
        $paginator->setCurrentPageNumber((int)$pg);
        
        return $paginator;
    }
    
    /**
     * Set user default bank account.
     * 
     * @param int $uid User identifier
     * @param int $aid Account identifier
     */
    public function setUserDefaultBankAccount($uid, $aid)
    {
        $data = array(
            'defaultAccountId' => (int)$aid,
        );
        
        $sql = new Sql($this->getDbAdapter());
        
        $update = $sql->update();
        $update->table(self::TABLE);
        $update->set($data);
        $update->where(array('userId' => (int)$uid));
        
        $statement = $sql->prepareStatementForSqlObject($update);
        $statement->execute();
    }

}
