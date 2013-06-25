<?php

namespace User\Form;

use Zend\Form\Form;
use Zend\Form\Element;

use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;

/**
 * E-mail edit form
 * 
 * @author Mateusz Mirosławski
 *
 */
class EmailForm extends Form
{
    public function __construct($name = null)
    {
        // we want to ignore the name passed
        parent::__construct('email');
        $this->setAttribute('method', 'post');
        
        // e-mail
        $this->add(array(
            'type'  => 'Zend\Form\Element\Email',
            'name' => 'email',
            'options' => array(
                'label' => 'Nowy adres e-mail: ',
            ),
            'attributes' => array(
                'maxlength' => 50,
            ),
        ));
        
        $this->add(array(
            'type'  => 'Zend\Form\Element\Submit',
            'name' => 'submit',
            'attributes' => array(
                'value' => 'Zmień',
                'id' => 'submitbutton',
            ),
        ));
    }
}

/**
 * E-mail edit form filter
 * 
 * @author Mateusz Mirosławski
 *
 */
class EmailFormFilter implements InputFilterAwareInterface
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

            // e-mail
            $inputFilter->add($factory->createInput(array(
                'name'     => 'email',
                'required' => true,
            )));

            $this->inputFilter = $inputFilter;
        }

        return $this->inputFilter;
    }
}
