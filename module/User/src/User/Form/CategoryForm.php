<?php
/**
    @author Mateusz Mirosławski
    
    Formularz dodawania/edycji kategorii.
*/

namespace User\Form;

use Zend\Form\Form;
use Zend\Form\Element;

use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;

class CategoryForm extends Form
{
    public function __construct($name = null)
    {
        // we want to ignore the name passed
        parent::__construct('category');
        $this->setAttribute('method', 'post');
        
        // cid kategorii (w przypadku edycji)
        $this->add(array(
            'type'  => 'Zend\Form\Element\Hidden',
            'name' => 'cid',
        ));
        
        // typ kategorii (przychód/wydatek)
        $this->add(array(
            'type'  => 'Zend\Form\Element\Hidden',
            'name' => 'c_type',
        ));
        
        // Nazwa
        $this->add(array(
            'type'  => 'Zend\Form\Element\Text',
            'name' => 'c_name',
            'options' => array(
                'label' => 'Nazwa: ',
            ),
            'attributes' => array(
                'maxlength' => 100,
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
class CategoryFormFilter implements InputFilterAwareInterface
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

            // Identyfikator kategorii
            $inputFilter->add($factory->createInput(array(
                'name'     => 'cid',
                'required' => true,
                'filters'  => array(
                    array('name' => 'Int'),
                ),
            )));
            
            // Typ kategorii
            $inputFilter->add($factory->createInput(array(
                'name'     => 'c_type',
                'required' => true,
                'filters'  => array(
                    array('name' => 'Int'),
                ),
            )));
            
            // Nazwa
            $inputFilter->add($factory->createInput(array(
                'name'     => 'c_name',
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
                            'min'      => 4,
                            'max'      => 100,
                        ),
                    ),
                ),
            )));

            $this->inputFilter = $inputFilter;
        }

        return $this->inputFilter;
    }
}
