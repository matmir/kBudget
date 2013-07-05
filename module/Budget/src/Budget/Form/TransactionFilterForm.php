<?php
/**
 *  Transaction filter form
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

class TransactionFilterForm extends Form
{
    public function __construct($name = null)
    {
        // we want to ignore the name passed
        parent::__construct('transaction-filter');
        $this->setAttribute('method', 'post');
        
        // Bank accounts
        $this->add(array(
            'type'  => 'Zend\Form\Element\Select',
            'name' => 'aid',
            'options' => array(
                'label' => 'Konto: ',
                'value_options' => array(
                    '0' => 'Main',
                ),
            ),
        ));
        
        // Months
        $this->add(array(
            'type'  => 'Zend\Form\Element\Select',
            'name' => 'month',
            'options' => array(
                'label' => 'Miesiąc: ',
                'value_options' => array(
                  '1' => 'Styczeń',
                  '2' => 'Luty',
                  '3' => 'Marzec',
                  '4' => 'Kwiecień',
                  '5' => 'Maj',
                  '6' => 'Czerwiec',
                  '7' => 'Lipiec',
                  '8' => 'Sierpień',
                  '9' => 'Wrzesień',
                  '10' => 'Październik',
                  '11' => 'Listopad',
                  '12' => 'Grudzień',
                ),
            ),
        ));
        
        // Year
        $this->add(array(
            'type'  => 'Zend\Form\Element\Text',
            'name' => 'year',
            'options' => array(
                'label' => 'Rok: ',
            ),
            'attributes' => array(
                'maxlength' => 4,
                'size' => 5,
            ),
        ));
        
        $this->add(array(
            'type'  => 'Zend\Form\Element\Submit',
            'name' => 'submit',
            'attributes' => array(
                'value' => 'Wyświetl',
                'id' => 'submitbutton',
            ),
        ));
    }
}

/**
 * Filters for TransactionFilterForm
 * 
 * @author Mateusz Mirosławski
 *
 */
class TransactionFilterFormFilter implements InputFilterAwareInterface
{
    protected $inputFilter;
    
    /**
     * Min value of the year
     * 
     * @var unknown
     */
    private $minYear;
    
    /**
     * Constructor
     * 
     * @param int $mYear Min value of the year
     */
    public function __construct($mYear=1970)
    {
        $this->minYear = (int)$mYear;
    }
    
    public function setInputFilter(InputFilterInterface $inputFilter)
    {
        throw new \Exception("Not used");
    }

    public function getInputFilter()
    {
        if (!$this->inputFilter) {
            $inputFilter = new InputFilter();
            $factory     = new InputFactory();

            // Bank account
            $inputFilter->add($factory->createInput(array(
                'name'     => 'aid',
                'required' => true,
                'filters'  => array(
                    array('name' => 'Int'),
                ),
            )));
            
            // Month
            $inputFilter->add($factory->createInput(array(
                'name'     => 'month',
                'required' => true,
                'filters'  => array(
                    array('name' => 'Int'),
                ),
            )));

            // Year
            $inputFilter->add($factory->createInput(array(
                'name'     => 'year',
                'required' => true,
                'filters'  => array(
                    array('name' => 'Int'),
                ),
                'validators'  => array(
                    array(
                          'name' => 'Between',
                          'options' => array(
                            'min' => $this->minYear,
                            'max' => (int)date('Y'),
                          ),
                    ),
                ),
            )));
            
            $this->inputFilter = $inputFilter;
        }

        return $this->inputFilter;
    }
}
