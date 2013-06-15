<?php

namespace Budget\Form;

use Zend\Form\Form;
use Zend\Form\Element;

use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;

/**
 * Time select form for analysis charts
 * 
 * @author Mateusz Mirosławski
 * 
 */
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
