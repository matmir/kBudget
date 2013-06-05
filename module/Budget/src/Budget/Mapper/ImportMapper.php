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
                ->where(array('i.uid' => (int)$uid));
        
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
            'aid' => (int)$import->aid,
            'fname' => (string)$import->fname,
            'bank' => (string)$import->bank,
            'fpos' => (int)$import->fpos,
            'nfpos' => (int)$import->nfpos,
            'count' => (int)$import->count,
            'counted' => (int)$import->counted,
        );
        
        $sql = new Sql($this->getDbAdapter());

        // Add new entry?
        if ($this->getUserImport($import->uid) == null) {
            
            $data['uid'] = (int)$import->uid;
            
            $insert = $sql->insert();
            $insert->into(self::TABLE);
            $insert->values($data);
            
            $statement = $sql->prepareStatementForSqlObject($insert);
            $statement->execute();
            
        } else { // Update entry
                
            $update = $sql->update();
            
            $update->table(self::TABLE);
            $update->set($data);
            $update->where(array('uid' => (int)$import->uid));
            
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
        $delete->where(array('uid' => (int)$uid));
        
        $statement = $sql->prepareStatementForSqlObject($delete);
        $row = $statement->execute();
    }

}
