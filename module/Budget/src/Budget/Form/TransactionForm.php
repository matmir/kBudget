<?php
/**
    @author Mateusz Mirosławski
    
    Formularz dodawania/edycji transakcji.
*/

namespace Budget\Form;

use Zend\Form\Form;
use Zend\Form\Element;

use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;

class TransactionForm extends Form
{
    public function __construct($name = null)
    {
        // we want to ignore the name passed
        parent::__construct('transaction');
        $this->setAttribute('method', 'post');
        
        // Id transakcji (w przypadku edycji)
        $this->add(array(
            'type'  => 'Zend\Form\Element\Hidden',
            'name' => 'tid',
        ));
        
        // Typ transakcji
        $this->add(array(
            'type'  => 'Zend\Form\Element\Hidden',
            'name' => 't_type',
        ));
        
        // Lista kategorii
        $this->add(array(
            'type'  => 'Zend\Form\Element\Select',
            'name' => 'cid',
            'options' => array(
                'label' => 'Kategoria: ',
                'value_options' => array(
                  '0' => 'Jedzenie',
                ),
            ),
        ));
        
        // Nowa kategoria
        $this->add(array(
            'type'  => 'Zend\Form\Element\Text',
            'name' => 'c_name',
            'options' => array(
                'label' => 'Nowa kategoria: ',
            ),
            'attributes' => array(
                'maxlength' => 100,
                'size' => 9,
            ),
        ));
        
        // Data
        $this->add(array(
            'type'  => 'Zend\Form\Element\Date',
            'name' => 't_date',
            'options' => array(
                'label' => 'Data: ',
            ),
            'attributes' => array(
                'step' => '1',
            ),
        ));
        
        // Opis
        $this->add(array(
            'type'  => 'Zend\Form\Element\Text',
            'name' => 't_content',
            'options' => array(
                'label' => 'Opis: ',
            ),
            'attributes' => array(
                'maxlength' => 400,
            ),
        ));
        
        // Wartość
        $this->add(array(
            'type'  => 'Zend\Form\Element\Text',
            'name' => 't_value',
            'options' => array(
                'label' => 'Wartość: ',
            ),
            'attributes' => array(
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

/*
    Filtry dla formularza
*/
class TransactionFilter implements InputFilterAwareInterface
{
    protected $inputFilter;
    private $n_c_required; // Czy wymagane pole od nowej kategorii
    
    /**
        Konstruktor
        @param bool $new_category_required Flaga wymagania pola z nową kategorią
    */
    public function __construct($new_category_required=false)
    {
        $this->n_c_required = (bool)$new_category_required;
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

            // Identyfikator transakcji
            $inputFilter->add($factory->createInput(array(
                'name'     => 'tid',
                'required' => true,
                'filters'  => array(
                    array('name' => 'Int'),
                ),
            )));

            // Typ transakcji
            $inputFilter->add($factory->createInput(array(
                'name'     => 't_type',
                'required' => true,
                'filters'  => array(
                    array('name' => 'Int'),
                ),
            )));

            // Lista kategorii
            $inputFilter->add($factory->createInput(array(
                'name'     => 'cid',
                'required' => true,
                'filters'  => array(
                    array('name' => 'Int'),
                ),
            )));
            
            // Nowa kategoria
            $inputFilter->add($factory->createInput(array(
                'name'     => 'c_name',
                'required' => $this->n_c_required,
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
                            'max'      => 100,
                        ),
                    ),
                ),
            )));
            
            // Data
            $inputFilter->add($factory->createInput(array(
                'name'     => 't_date',
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
            
            // Opis
            $inputFilter->add($factory->createInput(array(
                'name'     => 't_content',
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
            
            // Wartość
            $inputFilter->add($factory->createInput(array(
                'name'     => 't_value',
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
