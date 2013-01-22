<?php
/**
    @author Mateusz Mirosławski
    
    Formularz do sortowania transakcji w analizie
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
        
        // Typ filtracji
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
        
        // Data od
        $this->add(array(
            'type'  => 'Zend\Form\Element\Date',
            'name' => 'date_from',
            'options' => array(
                'label' => 'Od: ',
            ),
            'attributes' => array(
                'step' => '1',
            ),
        ));
        
        // Data do
        $this->add(array(
            'type'  => 'Zend\Form\Element\Date',
            'name' => 'date_to',
            'options' => array(
                'label' => 'Do: ',
            ),
            'attributes' => array(
                'step' => '1',
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
class TransactionTimeSelectFormFilter implements InputFilterAwareInterface
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

            // typ filtracji
            $inputFilter->add($factory->createInput(array(
                'name'     => 'filter_type',
                'required' => true,
            )));

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
            
            // Data od
            $inputFilter->add($factory->createInput(array(
                'name'     => 'date_from',
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
            
            // Data do
            $inputFilter->add($factory->createInput(array(
                'name'     => 'date_to',
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
            
            $this->inputFilter = $inputFilter;
        }

        return $this->inputFilter;
    }
}
