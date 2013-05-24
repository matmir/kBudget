<?php

namespace User\Mapper;

use Base\Mapper\BaseMapper;
use Zend\Db\Sql\Sql;

use User\Model\User;

use Zend\Paginator\Paginator;
use Zend\Paginator\Adapter\DbSelect;

/**
 * User mapper
 * 
 * @author Mateusz Mirosławski
 *
 */
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
            return $data['uid'];
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
            return $data['uid'];
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
                              'u.uid' => (int)$uid,
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
            'login'  => $user->login,
            'email'  => $user->email,
            'pass' => $user->pass,
            'u_type' => 0,
            'active' => 1,
            'register_date' => date('Y-m-d H:i:s'),
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
        $update->where(array('uid' => (int)$uid));
        
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
        $update->where(array('uid' => (int)$uid));
        
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
        $update->where(array('uid' => (int)$uid));
        
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
        $update->set(array('last_login_date' => date('Y-m-d H:i:s')));
        $update->where(array('uid' => (int)$uid));
        
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
                ->where(array('u.uid' => (int)$uid));
        
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
            'default_aid' => (int)$aid,
        );
        
        $sql = new Sql($this->getDbAdapter());
        
        $update = $sql->update();
        $update->table(self::TABLE);
        $update->set($data);
        $update->where(array('uid' => (int)$uid));
        
        $statement = $sql->prepareStatementForSqlObject($update);
        $statement->execute();
    }

}
