<?php
/**
    @author Mateusz Mirosławski
    
    Formularz do sortowania transakcji wg. miesiąca.
*/

namespace Budget\Form;

use Zend\Form\Form;
use Zend\Form\Element;

use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;

class TransactionRangeSelectForm extends Form
{
    public function __construct($name = null)
    {
        // we want to ignore the name passed
        parent::__construct('transaction-month');
        $this->setAttribute('method', 'post');
        
        // Miesiące
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
        
        // Rok
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

/*
    Filtry dla formularza
*/
class TransactionRangeSelectFilter implements InputFilterAwareInterface
{
    protected $inputFilter;
    private $minYear; // minimalny rok
    
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

            // miesiąc
            $inputFilter->add($factory->createInput(array(
                'name'     => 'month',
                'required' => true,
                'filters'  => array(
                    array('name' => 'Int'),
                ),
            )));

            // rok
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
