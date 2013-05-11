<?php

namespace User\Form;

use Zend\Form\Form;
use Zend\Form\Element;

use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;

/**
 * Bank account add/edit form.
 * 
 * @author Mateusz Mirosławski
 *
 */
class AccountForm extends Form
{
    public function __construct($name = null)
    {
        // we want to ignore the name passed
        parent::__construct('account');
        $this->setAttribute('method', 'post');
        
        // Account id
        $this->add(array(
            'type'  => 'Zend\Form\Element\Hidden',
            'name' => 'aid',
        ));
        
        // Account name
        $this->add(array(
            'type'  => 'Zend\Form\Element\Text',
            'name' => 'a_name',
            'options' => array(
                'label' => 'Nazwa: ',
            ),
            'attributes' => array(
                'maxlength' => 30,
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
 * Bank account add/edit filters.
 * 
 * @author Mateusz Mirosławski
 *
 */
class AccountFormFilter implements InputFilterAwareInterface
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

            // Account id
            $inputFilter->add($factory->createInput(array(
                'name'     => 'aid',
                'required' => true,
                'filters'  => array(
                    array('name' => 'Int'),
                ),
            )));
            
            // Account name
            $inputFilter->add($factory->createInput(array(
                'name'     => 'a_name',
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
                            'min'      => 2,
                            'max'      => 30,
                        ),
                    ),
                ),
            )));

            $this->inputFilter = $inputFilter;
        }

        return $this->inputFilter;
    }
}
