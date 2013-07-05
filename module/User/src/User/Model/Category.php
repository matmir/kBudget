<?php

namespace User\Model;

use Base\Model\BaseModel;

/**
 * Category model
 * 
 * @author Mateusz Mirosławski
 *
 */
class Category extends BaseModel
{
    /**
     * Categories types
     */
    const PROFIT = 0;
    const EXPENSE = 1;
    const TRANSFER = 2;

    /**
     * Category identifier
     * 
     * @var int
     */
    protected $categoryId;
    
    /**
     * Parent category identifier
     * 
     * @var int
     */
    protected $parentCategoryId;
    
    /**
     * User identifier
     * 
     * @var int
     */
    protected $userId;
    
    /**
     * Category type (0 - income, 1 - expense)
     * 
     * @var int
     */
    protected $categoryType;
    
    /**
     * Category name
     * 
     * @var string
     */
    protected $categoryName;
    
    /**
     * Construct the category object
     * 
     * @param array $params
     */
    public function __construct(array $params = array())
    {
        $this->categoryId = 0;
        $this->parentCategoryId = null;
        $this->userId = 0;
        $this->categoryType = -1;
        $this->categoryName = '';
        
        parent::__construct($params);
    }

    /**
     * Gets the Category identifier.
     *
     * @return int
     */
    public function getCategoryId()
    {
        return $this->categoryId;
    }

    /**
     * Sets the Category identifier.
     *
     * @param int $categoryId the categoryId
     *
     * @return self
     */
    public function setCategoryId($categoryId)
    {
        $this->categoryId = $categoryId;

        return $this;
    }

    /**
     * Gets the Parent category identifier.
     *
     * @return int
     */
    public function getParentCategoryId()
    {
        return $this->parentCategoryId;
    }

    /**
     * Sets the Parent category identifier.
     *
     * @param int $parentCategoryId the parentCategoryId
     *
     * @return self
     */
    public function setParentCategoryId($parentCategoryId)
    {
        $this->parentCategoryId = $parentCategoryId;

        return $this;
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
     * Gets the Category type (0 - income, 1 - expense).
     *
     * @return int
     */
    public function getCategoryType()
    {
        return $this->categoryType;
    }

    /**
     * Sets the Category type (0 - income, 1 - expense).
     *
     * @param int $categoryType the categoryType
     *
     * @return self
     */
    public function setCategoryType($categoryType)
    {
        $this->categoryType = $categoryType;

        return $this;
    }

    /**
     * Gets the Category name.
     *
     * @return string
     */
    public function getCategoryName()
    {
        return $this->categoryName;
    }

    /**
     * Sets the Category name.
     *
     * @param string $categoryName the categoryName
     *
     * @return self
     */
    public function setCategoryName($categoryName)
    {
        $this->categoryName = $categoryName;

        return $this;
    }
}
