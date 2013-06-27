<?php

namespace Budget\Mapper;

use Base\Mapper\BaseMapper;
use Zend\Db\Sql\Sql;
use Budget\Model\Import;

/**
 * Import CSV file mapper
 * 
 * @author Mateusz Mirosławski
 *
 */
class ImportMapper extends BaseMapper
{
    /**
     * MySQL import table name
     *
     * @var string
     */
    const TABLE = 'imports';
    
    /**
     * Get information about actual user import
     * 
     * @param int $uid User identifier
     * @return Import
     */
    public function getUserImport($uid)
    {
        $sql = new Sql($this->getDbAdapter());
        $select = $sql->select();
        
        $select->from(array('i' => self::TABLE))
                ->where(array('i.userId' => (int)$uid));
        
        $statement = $sql->prepareStatementForSqlObject($select);
        $row = $statement->execute();
        
        // Check if there is information
        return ($row->count()==0)?(null):(new Import($row->current()));
    }
    
    /**
     * Set user import information
     * 
     * @param Import $import Import object
     */
    public function setUserImport($import)
    {
        $data = array(
            'accountId' => (int)$import->getAccountId(),
            'fileName' => (string)$import->getFileName(),
            'bankName' => (string)$import->getBankName(),
            'positionInFile' => (int)$import->getPositionInFile(),
            'newPositionInFile' => (int)$import->getNewPositionInFile(),
            'count' => (int)$import->getCount(),
            'counted' => (int)$import->getCounted(),
        );
        
        $sql = new Sql($this->getDbAdapter());

        // Add new entry?
        if ($this->getUserImport($import->getUserId()) == null) {
            
            $data['userId'] = (int)$import->getUserId();
            
            $insert = $sql->insert();
            $insert->into(self::TABLE);
            $insert->values($data);
            
            $statement = $sql->prepareStatementForSqlObject($insert);
            $statement->execute();
            
        } else { // Update entry
                
            $update = $sql->update();
            
            $update->table(self::TABLE);
            $update->set($data);
            $update->where(array('userId' => (int)$import->getUserId()));
            
            $statement = $sql->prepareStatementForSqlObject($update);
            $statement->execute();
        }
    }
    
    /**
     * Delete import information from the database
     * 
     * @param int $uid User identifier
     */
    public function delUserImport($uid)
    {
        $sql = new Sql($this->getDbAdapter());
    
        $delete = $sql->delete();
        $delete->from(self::TABLE);
        $delete->where(array('userId' => (int)$uid));
        
        $statement = $sql->prepareStatementForSqlObject($delete);
        $row = $statement->execute();
    }

}
