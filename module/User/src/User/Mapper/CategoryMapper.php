<?php

namespace User\Mapper;

use Base\Mapper\BaseMapper;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Expression;

use User\Model\Category;

/**
 * Category mapper
 * 
 * @author Mateusz Mirosławski
 *
 */
class CategoryMapper extends BaseMapper
{
    /**
     * Get user categories of the same type
     * 
     * @param int $uid User identifier
     * @param int $c_type Category type (0 - income, 1 - expense)
     * @param int $pcid Parent category identifier
     * @throws \Exception
     * @return \Zend\Db\Adapter\Driver\ResultInterface
     */
    private function getUserCategories($uid, $c_type=-1, $pcid=null)
    {
        $sql = new Sql($this->getDbAdapter());
        $select = $sql->select();
    
        $select->from(array('c' => 'category'))
        ->where(array(
                'c.uid' => (int)$uid,
        )
        )
        ->order(array(
                'c.c_name ASC',
        ));
    
        // Check category type
        if ($c_type != -1) {
    
            // Spr. parametru
            if (!($c_type==0 || $c_type==1)) {
                throw new \Exception("Incorrect category type!");
            }
    
            $select->where(array('c.c_type' => (int)$c_type));
    
        }
    
        // Check parent category id
        if ($pcid !==null) {
    
            $select->where(array('c.pcid' => (int)$pcid));
    
        } else {
            
            $select->where(array('c.pcid' => null));
            
        }
    
        $statement = $sql->prepareStatementForSqlObject($select);
        $results = $statement->execute();
        
        return $results;
    }
    
    /**
     * Get user categories of the same type.
     * Return array of objects.
     * 
     * @param int $uid User identifier
     * @param int $c_type Category type (0 - income, 1 - expense)
     * @param int $pcid Parent category identifier
     * @return array
     */
    public function getCategories($uid, $c_type=-1, $pcid=null)
    {
        // Get categories
        $results = $this->getUserCategories($uid, $c_type, $pcid);
        
        $retObj = array();
        
        // Insert result into the category object
        while (($tbl=$results->current())!=null)
        {
            array_push($retObj, new Category($tbl));
        }
        
        return $retObj;
    }
    
    /**
     * Get user categories of the same type.
     * Return array (tbl['cid'] = category_name)
     * 
     * @param int $uid User identifier
     * @param int $c_type Category type (0 - income, 1 - expense)
     * @param int $pcid Parent category identifier
     * @return array
     */
    public function getUserCategoriesToSelect($uid, $c_type, $pcid=null)
    {
        // Get categories
        $results = $this->getUserCategories($uid, $c_type, $pcid);
        
        // Return array
        $retArray = array();
        
        // Default entry
        $retArray['-1'] = 'Wybierz...';
        $retArray['0'] = 'Dodaj nową...';
        
        // Insert values into the return array
        while (($tbl=$results->current())!=null)
        {
            $retArray[$tbl['cid']] = $tbl['c_name'];
        }
        
        return $retArray;
    }
    
    /**
     * Checks if the given category name exists in database.
     * Return 0 if not exists or category id if exists
     * 
     * @param string $c_name Category name
     * @param int $c_type Category type 
     * @param int $uid User id
     * @param int $pcid Parent category id
     * @return int
     */
    public function isCategoryNameExists($c_name, $c_type, $uid, $pcid=null)
    {
        $sql = new Sql($this->getDbAdapter());
        $select = $sql->select();
        
        $select->from(array('c' => 'category'))
                ->where(array('c.c_name' => (string)$c_name,
                              'c.uid' => (int)$uid,
                              'c.c_type' => (int)$c_type,
                              ));
                
        // Check parent category id
        if (($pcid !== null) && ($pcid !== 0)) {
            $select->where(array('c.pcid' => (int)$pcid));
        }
        
        $statement = $sql->prepareStatementForSqlObject($select);
        $row = $statement->execute();
        
        $data = $row->current();
        
        if ($data == null) {
            
            return 0;
            
        } else {
            
            return $data['cid'];
            
        }
    }
    
    /**
     * Save category
     * 
     * @param Category $category Existing or new category object
     * @throws \Exception
     */
    public function saveCategory(Category $category)
    {
        $data = array(
            'uid' => (int)$category->uid,
            'c_type'  => (int)$category->c_type,
            'c_name'  => (string)$category->c_name,
        );
        
        if (($category->pcid !== null) && ($category->pcid !== 0)) {
            $data['pcid'] = (int)$category->pcid;
        }
        
        $sql = new Sql($this->getDbAdapter());

        $cid = (int)$category->cid;
        
        // Add new category
        if ($cid == 0) {
            $insert = $sql->insert();
            $insert->into('category');
            $insert->values($data);
            
            $statement = $sql->prepareStatementForSqlObject($insert);
            $statement->execute();
        } else { // edit existing category
            // Checks if the category exists
            if ($this->getCategory($cid, $data['uid'])) {
                
                $update = $sql->update();
                
                $update->table('category');
                $update->set($data);
                $update->where(array('cid' => $cid));
                
                $statement = $sql->prepareStatementForSqlObject($update);
                $statement->execute();
            } else {
                throw new \Exception('Chosen category does not exists!');
            }
        }
    }
    
    /**
     * Get the category data
     * 
     * @param int $cid Category id
     * @param int $uid User id
     * @throws \Exception
     * @return \User\Model\Category
     */
    public function getCategory($cid, $uid)
    {
        $sql = new Sql($this->getDbAdapter());
        $select = $sql->select();
        
        $select->from(array('c' => 'category'))
                ->where(array('c.cid' => (int)$cid,
                              'c.uid' => (int)$uid));
        
        $statement = $sql->prepareStatementForSqlObject($select);
        $row = $statement->execute();
        
        if (!$row) {
            throw new \Exception('There is no category with id '.$cid);
        }
        
        $category = new Category($row->current());
        
        return $category;
    }
    
    /**
     * Checks if the category has transactions.
     * 
     * @param int $cid Category id
     * @param int $uid User id
     * @return bool
     */
    public function isCategoryEmpty($cid, $uid)
    {
        $sql = new Sql($this->getDbAdapter());
        $select = $sql->select();
        
        $select->columns(array('cn' => new Expression('count(*)')))
                ->from(array('t' => 'transaction'))
                ->where(array('t.cid' => (int)$cid,
                              't.uid' => (int)$uid,
                              ));
        
        $statement = $sql->prepareStatementForSqlObject($select);
        $row = $statement->execute();
        
        $data = $row->current();
        
        return ($data['cn']==0)?(true):(false);
    }
    
    /**
     * Checks if the category has subcategories.
     *
     * @param int $cid Category id
     * @param int $uid User id
     * @return bool
     */
    public function hasCategorySubcategories($cid, $uid)
    {
        $sql = new Sql($this->getDbAdapter());
        $select = $sql->select();
    
        $select->columns(array('cn' => new Expression('count(*)')))
            ->from(array('c' => 'category'))
            ->where(array('c.pcid' => (int)$cid,
                        'c.uid' => (int)$uid,
        ));
    
        $statement = $sql->prepareStatementForSqlObject($select);
        $row = $statement->execute();
    
        $data = $row->current();
    
        return ($data['cn']==0)?(false):(true);
    }
    
    /**
     * Delete category
     * 
     * @param int $cid Category id
     * @param int $uid User id
     */
    public function deleteCategory($cid, $uid)
    {
        $sql = new Sql($this->getDbAdapter());
    
        $delete = $sql->delete();
        $delete->from('category');
        $delete->where(array('cid' => (int)$cid,
                             'uid' => (int)$uid));
        
        $statement = $sql->prepareStatementForSqlObject($delete);
        $row = $statement->execute();
        
        return $row->getAffectedRows();
    }

}
