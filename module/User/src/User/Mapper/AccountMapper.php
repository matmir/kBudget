<?php

namespace User\Mapper;

use Base\Mapper\BaseMapper;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Expression;

use User\Model\Account;

/**
 * Bank account mapper
 * 
 * @author Mateusz Mirosławski
 *
 */
class AccountMapper extends BaseMapper
{
    
    /**
     * Get user accounts.
     * Return array of objects.
     * 
     * @param int $uid User identifier
     * @return array
     */
    public function getAccounts($uid)
    {
        $sql = new Sql($this->getDbAdapter());
        $select = $sql->select();
    
        $select->from(array('a' => 'accounts'))
                ->where(array(
                    'a.uid' => (int)$uid,
                ))
                ->order(array(
                    'a.a_name ASC',
                ));
    
        $statement = $sql->prepareStatementForSqlObject($select);
        $results = $statement->execute();
        
        $retObj = array();
        
        // Insert result into the account object
        while (($tbl=$results->current())!=null)
        {
            array_push($retObj, new Account($tbl));
        }
        
        return $retObj;
    }
    
    /**
     * Checks if the given account name exists in database.
     * Return 0 if not exists or account id if exists
     * 
     * @param string $a_name Account name
     * @param int $uid User id
     * @return int
     */
    public function isAccountNameExists($a_name, $uid)
    {
        $sql = new Sql($this->getDbAdapter());
        $select = $sql->select();
        
        $select->from(array('a' => 'accounts'))
                ->where(array('a.a_name' => (string)$a_name,
                              'a.uid' => (int)$uid,
                              ));
        
        $statement = $sql->prepareStatementForSqlObject($select);
        $row = $statement->execute();
        
        $data = $row->current();
        
        if ($data == null) {
            
            return 0;
            
        } else {
            
            return $data['aid'];
            
        }
    }
    
    /**
     * Save bank account
     * 
     * @param Account $account Existing or new account object
     * @throws \Exception
     */
    public function saveAccount(Account $account)
    {
        $data = array(
            'uid' => (int)$account->uid,
            'a_name'  => (string)$account->a_name,
            'balance' => (float)$account->balance,
        );
        
        $sql = new Sql($this->getDbAdapter());

        $aid = (int)$account->aid;
        
        // Add new account
        if ($aid == 0) {
            $insert = $sql->insert();
            $insert->into('accounts');
            $insert->values($data);
            
            $statement = $sql->prepareStatementForSqlObject($insert);
            $statement->execute();
        } else { // edit existing account
            // Checks if the account exists
            if ($this->getAccount($aid, $data['uid'])) {
                
                $update = $sql->update();
                
                $update->table('accounts');
                $update->set($data);
                $update->where(array('aid' => $aid));
                
                $statement = $sql->prepareStatementForSqlObject($update);
                $statement->execute();
            } else {
                throw new \Exception('Chosen account does not exists!');
            }
        }
    }
    
    /**
     * Get the account data
     * 
     * @param int $aid Account id
     * @param int $uid User id
     * @throws \Exception
     * @return \User\Model\Account
     */
    public function getAccount($aid, $uid)
    {
        $sql = new Sql($this->getDbAdapter());
        $select = $sql->select();
        
        $select->from(array('a' => 'accounts'))
                ->where(array('a.aid' => (int)$aid,
                              'a.uid' => (int)$uid));
        
        $statement = $sql->prepareStatementForSqlObject($select);
        $row = $statement->execute();
        
        if (!$row) {
            throw new \Exception('There is no account with id '.$aid);
        }
        
        $account = new Account($row->current());
        
        return $account;
    }
    
    /**
     * Checks if the account has transactions.
     * 
     * @param int $aid Account id
     * @param int $uid User id
     * @return bool
     */
    public function isAccountEmpty($aid, $uid)
    {
        $sql = new Sql($this->getDbAdapter());
        $select = $sql->select();
        
        $select->columns(array('cn' => new Expression('count(*)')))
                ->from(array('t' => 'transaction'))
                ->where(array('t.aid' => (int)$aid,
                              't.uid' => (int)$uid,
                              ));
        
        $statement = $sql->prepareStatementForSqlObject($select);
        $row = $statement->execute();
        
        $data = $row->current();
        
        return ($data['cn']==0)?(true):(false);
    }
    
    /**
     * Get count of user bank accounts
     * 
     * @param int $uid Userd id
     * @return int
     */
    public function getUserAccountCount($uid)
    {
        $sql = new Sql($this->getDbAdapter());
        $select = $sql->select();
        
        $select->columns(array('cn' => new Expression('count(*)')))
        ->from(array('a' => 'accounts'))
        ->where(array(
                'a.uid' => (int)$uid,
        ));
        
        $statement = $sql->prepareStatementForSqlObject($select);
        $row = $statement->execute();
        
        $data = $row->current();
        
        return $data['cn'];
    }
    
    /**
     * Delete account. If success return true.
     * 
     * @param int $aid Account id
     * @param int $uid User id
     * @return bool
     */
    public function deleteAccount($aid, $uid)
    {
        // delete only if user has more than 1 bank account
        if ($this->getUserAccountCount($uid) > 1) {
            
            $sql = new Sql($this->getDbAdapter());
            
            $delete = $sql->delete();
            $delete->from('accounts');
            $delete->where(array('aid' => (int)$aid,
                    'uid' => (int)$uid));
            
            $statement = $sql->prepareStatementForSqlObject($delete);
            $row = $statement->execute();
            
            return ($row->getAffectedRows()==1)?(true):(false);
            
        } else {
            
            return false;
            
        }
        
    }

}
