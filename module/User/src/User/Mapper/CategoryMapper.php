<?php
/**
 *  Category mapper
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

use User\Model\Category;

class CategoryMapper extends BaseMapper
{
    /**
     * MySQL category table name
     *
     * @var string
     */
    const TABLE = 'categories';
    
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
    
        $select->from(array('c' => self::TABLE))
                ->where(array(
                        'c.userId' => (int)$uid,
                        )
                )
                ->order(array(
                        'c.categoryName ASC',
                ));
    
        // Check category type
        if ($c_type != -1) {
    
            // Spr. parametru
            if (!($c_type==0 || $c_type==1)) {
                throw new \Exception('Incorrect category type!');
            }
    
            $select->where(array('c.categoryType' => (int)$c_type));
    
        }
    
        // Check parent category id
        if ($pcid !==null) {
    
            $select->where(array('c.parentCategoryId' => (int)$pcid));
    
        } else {
            
            $select->where(array('c.parentCategoryId' => null));
            
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
     * Get user categories with specified identifiers
     * 
     * @param int $uid User identifier
     * @param array $cids Array with category identifiers
     */
    public function getCategoriesWithGivenIds($uid, array $cids)
    {
        $sql = new Sql($this->getDbAdapter());
        $select = $sql->select();
    
        $select->from(array('c' => self::TABLE))
                ->where(array(
                        'c.userId' => (int)$uid,
                        'c.categoryId' => $cids
                        )
                );

        $statement = $sql->prepareStatementForSqlObject($select);
        $results = $statement->execute();

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
            $retArray[$tbl['categoryId']] = $tbl['categoryName'];
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
        
        $select->from(array('c' => self::TABLE))
                ->where(array('c.categoryName' => (string)$c_name,
                              'c.userId' => (int)$uid,
                              'c.categoryType' => (int)$c_type,
                              ));
                
        // Check parent category id
        if (($pcid !== null) && ($pcid !== 0)) {
            $select->where(array('c.parentCategoryId' => (int)$pcid));
        } else {
            $select->where(array('c.parentCategoryId' => null));
        }
        
        $statement = $sql->prepareStatementForSqlObject($select);
        $row = $statement->execute();
        
        $data = $row->current();
        
        if ($data == null) {
            
            return 0;
            
        } else {
            
            return $data['categoryId'];
            
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
        $data = $category->getArrayCopy();
        unset($data['categoryId']);
        
        if (($category->getParentCategoryId() !== null) && ($category->getParentCategoryId() !== 0)) {
            $data['parentCategoryId'] = (int)$category->getParentCategoryId();
        } else {
            $data['parentCategoryId'] = null;
        }
        
        $sql = new Sql($this->getDbAdapter());

        $cid = (int)$category->getCategoryId();
        
        // Add new category
        if ($cid == 0) {
            $insert = $sql->insert();
            $insert->into(self::TABLE);
            $insert->values($data);
            $statement = $sql->prepareStatementForSqlObject($insert);
            $statement->execute();
        } else { // edit existing category
            // Checks if the category exists
            if ($this->getCategory($cid, $data['userId'])) {
                
                $update = $sql->update();
                
                $update->table(self::TABLE);
                $update->set($data);
                $update->where(array('categoryId' => $cid));
                
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
        
        $select->from(array('c' => self::TABLE))
                ->where(array('c.categoryId' => (int)$cid,
                              'c.userId' => (int)$uid));
        
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
                ->from(array('t' => \Budget\Mapper\TransactionMapper::TABLE))
                ->where(array('t.categoryId' => (int)$cid,
                              't.userId' => (int)$uid,
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
            ->from(array('c' => self::TABLE))
            ->where(array('c.parentCategoryId' => (int)$cid,
                        'c.userId' => (int)$uid,
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
        $delete->from(self::TABLE);
        $delete->where(array('categoryId' => (int)$cid,
                             'userId' => (int)$uid));
        
        $statement = $sql->prepareStatementForSqlObject($delete);
        $row = $statement->execute();
        
        return $row->getAffectedRows();
    }
    
    /**
     * Get transfer category identifier (unique hidden category)
     * 
     * @param int $uid User id
     * @throws \Exception
     * @return int
     */
    public function getTransferCategoryId($uid)
    {
        $sql = new Sql($this->getDbAdapter());
        $select = $sql->select();
        
        $select->from(array('c' => self::TABLE))
        ->where(array('c.categoryType' => 2,
                'c.userId' => (int)$uid));
        
        $statement = $sql->prepareStatementForSqlObject($select);
        $row = $statement->execute();
        
        if (!$row->count()) {
            throw new \Exception('There is no transfer category!');
        }
        
        if ($row->count() > 1) {
            throw new \Exception('There should be only one transfer category!');
        }
        
        $category = new Category($row->current());
        
        return $category->getCategoryId();
    }

}
