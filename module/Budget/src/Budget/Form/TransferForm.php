<?php
namespace Budget\Form;

use Zend\Form\Form;
use Zend\Form\Element;

use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;

/**
 * Add/edit transfer form
 * 
 * @author Mateusz Mirosławski
 *
 */
class TransferForm extends Form
{
    public function __construct($name = null)
    {
        // we want to ignore the name passed
        parent::__construct('transfer');
        $this->setAttribute('method', 'post');
        
        // Transaction id (for editing)
        $this->add(array(
            'type'  => 'Zend\Form\Element\Hidden',
            'name' => 'tid',
        ));
        
        // Bank account id from which we transfer money
        $this->add(array(
            'type'  => 'Zend\Form\Element\Select',
            'name' => 'aid',
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
            'name' => 'taid',
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
            'name' => 't_date',
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
            'name' => 't_content',
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
            'name' => 't_value',
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

            // Transaction id
            $inputFilter->add($factory->createInput(array(
                'name'     => 'tid',
                'required' => true,
                'filters'  => array(
                    array('name' => 'Int'),
                ),
            )));
            
            // Bank account id from which we transfer money
            $inputFilter->add($factory->createInput(array(
                'name'     => 'aid',
                'required' => true,
                'filters'  => array(
                    array('name' => 'Int'),
                ),
            )));
            
            // Bank account id to which we transfer money
            $inputFilter->add($factory->createInput(array(
                'name'     => 'taid',
                'required' => true,
                'filters'  => array(
                    array('name' => 'Int'),
                ),
            )));
            
            // Date
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
            
            // Description
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
            
            // Value
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
