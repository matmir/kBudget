<?php

namespace Auth\Adapter;

use Zend\Authentication\Adapter\AbstractAdapter;
use Zend\Authentication\Result as AuthenticationResult;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Sql;
use Zend\Crypt\Password\Bcrypt;
use stdClass;

/**
 * Database authentication adapter with bcrypt encoding.
 * 
 * @author Mateusz Mirosławski
 *
 */
class AuthAdapter extends AbstractAdapter
{
    /**
     * Database adapter
     * 
     * @var \Zend\Db\Adapter\Adapter
     */
    protected $dbAdapter;
    
    /**
     * Table with data to the authentication
     * 
     * @var string
     */
    protected $tableName;
    
    /**
     * Table column with user identity
     *
     * @var string
     */
    protected $identityColumn;
    
    /**
     * Table column with user credential
     * 
     * @var string
     */
    protected $credentialColumn;
    
    /**
     * Bcrypt cost
     * 
     * @var int
     */
    protected $bCryptCost;
    
    /**
     * Other sql conditions
     * 
     * @var string
     */
    protected $condition;
    
    /**
     * Row with authentication table data (all columns)
     * 
     * @var array
     */
    protected $dbAuthData;
    
    /**
     * Create AuthAdapter
     * 
     * @param Adapter $db Database adapter
     * @param string $tableName Table with data to the authentication
     * @param string $identity Table column with user identity
     * @param string $credential Table column with user credential
     * @param int $cost Bcrypt cost param
     * @param array $condition Other sql conditions eg $condition=array('active' => '1')
     */
    public function __construct(Adapter $db, $tableName, $identity, $credential, $cost=14, $condition=null)
    {
        $this->dbAdapter = $db;
        $this->tableName = $tableName;
        $this->identityColumn = $identity;
        $this->credentialColumn = $credential;
        $this->bCryptCost = $cost;
        $this->condition = $condition;
        $this->dbAuthData = null;
    }
    
    /**
     * Get authentication data from database
     * 
     * @throws \Exception
     * @return array
     */
    private function getAuthenticationData()
    {
        $sql = new Sql($this->dbAdapter);
        $select = $sql->select();
        
        $select->from($this->tableName)
                ->where(array($this->identityColumn => (string)$this->getIdentity()));
        
        if ($this->condition !== null) {
            $select->where($this->condition);
        }
        
        $statement = $sql->prepareStatementForSqlObject($select);
        
        $data = null;
        
        try {
            
            $row = $statement->execute();
            $data = $row->current();
            
        } catch (\Exception $e) {
            
            throw new \Exception('Check provided database parameters!');
            
        }
        
        return $data;
    }
    
    /**
     * Check if there are authorization parameters
     * 
     * @throws \Exception
     */
    private function checkAuthParameters()
    {
        if ($this->tableName == null) {
            throw new \Exception('You must specify the name of the authentication table!');
        }
        if ($this->identityColumn == null) {
            throw new \Exception('You must specify table column with the user identity!');
        }
        if ($this->getIdentity() == null) {
            throw new \Exception('You must specify the user identity!');
        }
        if ($this->credentialColumn == null) {
            throw new \Exception('You must specify table column with the user credential!');
        }
        if ($this->getCredential() == null) {
            throw new \Exception('You must specify the user credential!');
        }
        if (!($this->bCryptCost >= 4 && $this->bCryptCost <= 31)) {
            throw new \Exception('Bcrypt cost must be between 4 and 31!');
        }
    }
    
    /**
     * Do user authentication
     * 
     * @return AuthenticationResult
     */
    public function authenticate()
    {
        $this->checkAuthParameters();
        
        $dbData = $this->getAuthenticationData();
        
        $bcrypt = new Bcrypt();
        $bcrypt->setCost($this->bCryptCost);
        
        // Check passwords
        if ($bcrypt->verify($this->getCredential(), $dbData[$this->credentialColumn])) {
            
            $code = AuthenticationResult::SUCCESS;
            $identity = $this->getIdentity();
            $msg = array('Authentication successful!');
            
            $this->dbAuthData = $dbData;
            
        } else {
            
            $code = AuthenticationResult::FAILURE;
            $identity = 'none';
            $msg = array('Authentication failed!');
        }
        
        return new AuthenticationResult($code, $identity, $msg);
    }
    
    /**
     * Get authentication table row
     * 
     * @param array Array with column names
     * @throws \Exception
     * @return stdClass
     */
    public function getAuthDataObject($columns)
    {
        if ($this->dbAuthData === null) {
            throw new \Exception('User is not logged in!');
        }
        
        if (!is_array($columns)) {
            throw new \Exception('Column parameter must be an array!');
        }
        
        if (empty($columns)) {
            throw new \Exception('Column array can not be empty!');
        }
        
        $retObj = new stdClass();
        
        foreach ($columns as $col) {
            
            if (!array_key_exists($col, $this->dbAuthData)) {
                throw new \Exception('The specified column does not exist!');
            }
            
            $retObj->{$col} = $this->dbAuthData[$col];
        }
        
        return $retObj;
    }
}