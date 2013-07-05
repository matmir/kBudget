<?php
/**
 *  Time select form for analysis charts
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

class TransactionTimeSelectForm extends Form
{
    public function __construct($name = null)
    {
        // we want to ignore the name passed
        parent::__construct('transaction-time-select');
        $this->setAttribute('method', 'post');
        
        // Bank account identifier
        $this->add(array(
            'type'  => 'Zend\Form\Element\Select',
            'name' => 'aid',
            'options' => array(
                'label' => 'Konto bankowe: ',
                'value_options' => array(
                    '0' => '...',
                ),
            ),
            'attributes' => array(
                'id' => 'aid',
            ),
        ));

        // Filter type
        $this->add(array(
            'type'  => 'Zend\Form\Element\Select',
            'name' => 'filter_type',
            'options' => array(
                'label' => 'Rodzaj filtracji: ',
                'value_options' => array(
                  'month' => 'miesiąc',
                  'between' => 'zakres dat',
                  'all' => 'wszystkie dane',
                ),
            ),
            'attributes' => array(
                'id' => 'filter_type',
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
            'attributes' => array(
                'id' => 'month',
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
                'id' => 'year'
            ),
        ));
        
        // Date from
        $this->add(array(
            'type'  => 'Zend\Form\Element\Date',
            'name' => 'date_from',
            'options' => array(
                'label' => 'Od: ',
            ),
            'attributes' => array(
                'step' => '1',
                'id' => 'date_from'
            ),
        ));
        
        // Date to
        $this->add(array(
            'type'  => 'Zend\Form\Element\Date',
            'name' => 'date_to',
            'options' => array(
                'label' => 'Do: ',
            ),
            'attributes' => array(
                'step' => '1',
                'id' => 'date_to'
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
