<?php
/**
 *  Add/edit transfer form
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

namespace Budget\Form;

use Zend\Form\Form;
use Zend\Form\Element;

use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;

class TransferForm extends Form
{
    public function __construct($name = null)
    {
        // we want to ignore the name passed
        parent::__construct('transfer');
        $this->setAttribute('method', 'post');
        
        // Bank account id from which we transfer money
        $this->add(array(
            'type'  => 'Zend\Form\Element\Select',
            'name' => 'accountId',
            'options' => array(
                'label' => 'Z konta : ',
                'value_options' => array(
                    '0' => '...',
                ),
            ),
        ));
        
        // Bank account id to which we transfer money
        $this->add(array(
            'type'  => 'Zend\Form\Element\Select',
            'name' => 'transferAccountId',
            'options' => array(
                'label' => 'Na konto: ',
                'value_options' => array(
                        '0' => '...',
                ),
            ),
        ));
        
        // Date
        $this->add(array(
            'type'  => 'Zend\Form\Element\Date',
            'name' => 'date',
            'options' => array(
                'label' => 'Data: ',
            ),
            'attributes' => array(
                'id' => 't_date',
                'step' => '1',
            ),
        ));
        
        // Description
        $this->add(array(
            'type'  => 'Zend\Form\Element\Text',
            'name' => 'content',
            'options' => array(
                'label' => 'Opis: ',
            ),
            'attributes' => array(
                'id' => 't_content',
                'maxlength' => 400,
            ),
        ));
        
        // Value
        $this->add(array(
            'type'  => 'Zend\Form\Element\Text',
            'name' => 'value',
            'options' => array(
                'label' => 'Wartość: ',
            ),
            'attributes' => array(
                'id' => 't_value',
                'maxlength' => 12,
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
 * Transfer add/edit form filters
 * 
 * @author Mateusz Mirosławski
 *
 */
class TransferFilter implements InputFilterAwareInterface
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
            
            // Bank account id from which we transfer money
            $inputFilter->add($factory->createInput(array(
                'name'     => 'accountId',
                'required' => true,
                'filters'  => array(
                    array('name' => 'Int'),
                ),
            )));
            
            // Bank account id to which we transfer money
            $inputFilter->add($factory->createInput(array(
                'name'     => 'transferAccountId',
                'required' => true,
                'filters'  => array(
                    array('name' => 'Int'),
                ),
            )));
            
            // Date
            $inputFilter->add($factory->createInput(array(
                'name'     => 'date',
                'required' => true,
                'validators'  => array(
                    array(
                          'name' => 'Between',
                          'options' => array(
                            'min' => '1970-01-01',
                            'max' => date('Y-m-d'),
                          ),
                    ),
                ),
            )));
            
            // Description
            $inputFilter->add($factory->createInput(array(
                'name'     => 'content',
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
                            'min'      => 1,
                            'max'      => 200,
                        ),
                    ),
                ),
            )));
            
            // Value
            $inputFilter->add($factory->createInput(array(
                'name'     => 'value',
                'required' => true,
                'filters'  => array(
                    array(
                          'name' => 'PregReplace',
                          'options' => array(
                            'pattern'     => '/,/',
                            'replacement' => '.',
                        ),
                    ),
                ),
            )));

            $this->inputFilter = $inputFilter;
        }

        return $this->inputFilter;
    }
}
