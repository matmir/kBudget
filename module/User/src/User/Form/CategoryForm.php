<?php
/**
 *  Category add/edit form.
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

namespace User\Form;

use Zend\Form\Form;
use Zend\Form\Element;

use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;

class CategoryForm extends Form
{
    public function __construct($name = null)
    {
        // we want to ignore the name passed
        parent::__construct('category');
        $this->setAttribute('method', 'post');
        
        // Category id
        $this->add(array(
            'type'  => 'Zend\Form\Element\Hidden',
            'name' => 'categoryId',
        ));
        
        // Parent category id
        $this->add(array(
                'type'  => 'Zend\Form\Element\Hidden',
                'name' => 'parentCategoryId',
        ));
        
        // Category type (0 - income, 1 - expense)
        $this->add(array(
            'type'  => 'Zend\Form\Element\Hidden',
            'name' => 'categoryType',
        ));
        
        // Category name
        $this->add(array(
            'type'  => 'Zend\Form\Element\Text',
            'name' => 'categoryName',
            'options' => array(
                'label' => 'Nazwa: ',
            ),
            'attributes' => array(
                'maxlength' => 100,
                'size' => 8,
            ),
        ));
        
        $this->add(array(
            'type'  => 'Zend\Form\Element\Submit',
            'name' => 'submit',
            'attributes' => array(
                'value' => 'Dodaj',
                'id' => 'submitbutton',
            ),
        ));
    }
}

/**
 * Category add/edit filters.
 * 
 * @author Mateusz Mirosławski
 *
 */
class CategoryFormFilter implements InputFilterAwareInterface
{
    protected $inputFilter;
    
    public function setInputFilter(InputFilterInterface $inputFilter)
    {
        throw new \Exception("Not used");
    }

    public function getInputFilter()
    {
        if (!$this->inputFilter) {
            $inputFilter = new InputFilter();
            $factory     = new InputFactory();

            // Category d
            $inputFilter->add($factory->createInput(array(
                'name'     => 'categoryId',
                'required' => true,
                'filters'  => array(
                    array('name' => 'Int'),
                ),
            )));
            
            // Parent category id
            $inputFilter->add($factory->createInput(array(
                    'name'     => 'parentCategoryId',
                    'required' => true,
                    'filters'  => array(
                            array('name' => 'Int'),
                    ),
            )));
            
            // Category type
            $inputFilter->add($factory->createInput(array(
                'name'     => 'categoryType',
                'required' => true,
                'filters'  => array(
                    array('name' => 'Int'),
                ),
            )));
            
            // Category name
            $inputFilter->add($factory->createInput(array(
                'name'     => 'categoryName',
                'required' => true,
                'filters'  => array(
                    array('name' => 'StripTags'),
                    array('name' => 'StringTrim'),
                ),
                'validators' => array(
                    array(
                        'name'    => 'StringLength',
                        'options' => array(
                            'encoding' => 'UTF-8',
                            'min'      => 4,
                            'max'      => 100,
                        ),
                    ),
                ),
            )));

            $this->inputFilter = $inputFilter;
        }

        return $this->inputFilter;
    }
}
