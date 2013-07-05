<?php
/**
 *  Bank account mapper
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
use Zend\Db\Sql\Expression;

use User\Model\Account;

class AccountMapper extends BaseMapper
{
    /**
     * MySQL bank account table name
     *
     * @var string
     */
    const TABLE = 'accounts';
    
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
    
        $select->from(array('a' => self::TABLE))
                ->where(array(
                    'a.userId' => (int)$uid,
                ))
                ->order(array(
                    'a.accountName ASC',
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
     * Get user accounts with specified identifiers
     * 
     * @param int $uid User identifier
     * @param array $aids Array with accounts identifiers
     */
    public function getAccountsWithGivenIds($uid, array $aids)
    {
        $sql = new Sql($this->getDbAdapter());
        $select = $sql->select();
    
        $select->from(array('a' => self::TABLE))
                ->where(array(
                        'a.userId' => (int)$uid,
                        'a.accountId' => $aids
                        )
                );

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
     * Get user bank accounts for select element.
     * Return array (tbl['aid'] = account_name)
     *
     * @param int $uid User identifier
     * @return array
     */
    public function getUserAccountsToSelect($uid)
    {
        // Get accounts
        $accounts = $this->getAccounts($uid);
        
        // Return array
        $retArray = array();
        
        // Insert values into the return array
        foreach ($accounts as $account)
        {
            $retArray[$account->getAccountId()] = $account->getAccountName();
        }
        
        return $retArray;
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
        
        $select->from(array('a' => self::TABLE))
                ->where(array('a.accountName' => (string)$a_name,
                              'a.userId' => (int)$uid,
                              ));
        
        $statement = $sql->prepareStatementForSqlObject($select);
        $row = $statement->execute();
        
        $data = $row->current();
        
        if ($data == null) {
            
            return 0;
            
        } else {
            
            return $data['accountId'];
            
        }
    }
    
    /**
     * Check if given account id is user account
     * 
     * @param int $aid Account id
     * @param int $uid User id
     * @return boolean
     */
    public function isUserAccount($aid, $uid)
    {
        $data = $this->getAccount($aid, $uid);
        
        if ($data === null) {
        
            return false;
        
        } else {
        
            return true;
        
        }
    }
    
    /**
     * Save bank account. Return new bank account id or 0 if account was edited.
     * 
     * @param Account $account Existing or new account object
     * @throws \Exception
     * @return int
     */
    public function saveAccount(Account $account)
    {
        $data = $account->getArrayCopy();
        unset($data['accountId']);
        
        $sql = new Sql($this->getDbAdapter());

        $aid = (int)$account->getAccountId();
        
        // Add new account
        if ($aid == 0) {
            $insert = $sql->insert();
            $insert->into(self::TABLE);
            $insert->values($data);
            
            $statement = $sql->prepareStatementForSqlObject($insert);
            $val = $statement->execute()->getGeneratedValue();
        
            return $val;
        } else { // edit existing account
            // Checks if the account exists
            if ($this->getAccount($aid, $data['userId'])) {
                
                $update = $sql->update();
                
                $update->table(self::TABLE);
                $update->set($data);
                $update->where(array('accountId' => $aid));
                
                $statement = $sql->prepareStatementForSqlObject($update);
                $statement->execute();
                
                return 0;
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
        
        $select->from(array('a' => self::TABLE))
                ->where(array('a.accountId' => (int)$aid,
                              'a.userId' => (int)$uid));
        
        $statement = $sql->prepareStatementForSqlObject($select);
        $row = $statement->execute();
        
        if (!$row->count()) {
            return null;
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
                ->from(array('t' => \Budget\Mapper\TransactionMapper::TABLE))
                ->where(array('t.accountId' => (int)$aid,
                              't.userId' => (int)$uid,
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
        ->from(array('a' => self::TABLE))
        ->where(array(
                'a.userId' => (int)$uid,
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
            $delete->from(self::TABLE);
            $delete->where(array('accountId' => (int)$aid,
                    'userId' => (int)$uid));
            
            $statement = $sql->prepareStatementForSqlObject($delete);
            $row = $statement->execute();
            
            return ($row->getAffectedRows()==1)?(true):(false);
            
        } else {
            
            return false;
            
        }
        
    }

}
