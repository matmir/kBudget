<?php

namespace User\Form;

use Zend\Form\Form;
use Zend\Form\Element;

use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;

/**
 * Password reset form
 * 
 * @author Mateusz Mirosławski
 *
 */
class PasswordResetForm extends Form
{
    public function __construct()
    {
        // we want to ignore the name passed
        parent::__construct('login');
        $this->setAttribute('method', 'post');
        
        // e-mail
        $this->add(array(
            'type'  => 'Zend\Form\Element\Email',
            'name' => 'email',
            'options' => array(
                'label' => 'Adres e-mail: ',
            ),
            'attributes' => array(
                'maxlength' => 50,
            ),
        ));
        
        // CSRF
        $this->add(array(
            'type'  => 'Zend\Form\Element\Csrf',
            'name' => 'csrf',
            'options' => array(
                'csrf_options' => array('timeout' => 120),
            ),
        ));
        
        $this->add(array(
            'type'  => 'Zend\Form\Element\Submit',
            'name' => 'submit',
            'attributes' => array(
                'value' => 'Wyślij',
                'id' => 'submitbutton',
            ),
        ));
    }
}

/**
 * Password reset form filters
 * 
 * @author Mateusz Mirosławski
 *
 */
class PasswordResetFormFilter implements InputFilterAwareInterface
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
            
            // CSRF
            $inputFilter->add($factory->createInput(array(
                'name'     => 'csrf',
                'required' => true,
            )));

            $this->inputFilter = $inputFilter;
        }

        return $this->inputFilter;
    }
}
