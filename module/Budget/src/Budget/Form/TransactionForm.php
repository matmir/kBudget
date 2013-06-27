<?php
namespace Budget\Form;

use Zend\Form\Form;
use Zend\Form\Element;

use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;

/**
 * Add/edit transaction form
 * 
 * @author Mateusz Mirosławski
 *
 */
class TransactionForm extends Form
{
    public function __construct($name = null)
    {
        // we want to ignore the name passed
        parent::__construct('transaction');
        $this->setAttribute('method', 'post');
        
        // Transaction id (for editing)
        $this->add(array(
            'type'  => 'Zend\Form\Element\Hidden',
            'name' => 'transactionId',
        ));
        
        // Bank account id
        $this->add(array(
            'type'  => 'Zend\Form\Element\Hidden',
            'name' => 'accountId',
        ));
        
        // Transaction type
        $this->add(array(
            'type'  => 'Zend\Form\Element\Hidden',
            'name' => 'transactionType',
            'attributes' => array(
                'id' => 'transactionType',
            ),
        ));
        
        // Main category list
        $this->add(array(
            'type'  => 'Zend\Form\Element\Select',
            'name' => 'pcid',
            'options' => array(
                'label' => 'Kategoria: ',
                'value_options' => array(
                  '0' => '...',
                ),
            ),
            'attributes' => array(
                'id' => 'mainCategoryList',
            ),
        ));
        
        // New main category
        $this->add(array(
            'type'  => 'Zend\Form\Element\Text',
            'name' => 'newMainCategoryName',
            'options' => array(
                'label' => 'Nowa kategoria: ',
            ),
            'attributes' => array(
                'id' => 'newMainCategory',
                'maxlength' => 100,
                'size' => 9,
            ),
        ));
        
        // Sub category list
        $this->add(array(
            'type'  => 'Zend\Form\Element\Select',
            'name' => 'ccid',
            'options' => array(
                'label' => 'Podkategoria: ',
                'value_options' => array(
                    '-1' => 'Brak',
                    '0' => 'Dodaj nową...',
                ),
            ),
            'attributes' => array(
                'id' => 'subCategoryList',
            ),
        ));
        
        // New subcategory
        $this->add(array(
            'type'  => 'Zend\Form\Element\Text',
            'name' => 'newSubCategoryName',
            'options' => array(
                'label' => 'Nowa podkategoria: ',
            ),
            'attributes' => array(
                'id' => 'newSubCategory',
                'maxlength' => 100,
                'size' => 9,
            ),
        ));
        
        // Date
        $this->add(array(
            'type'  => 'Zend\Form\Element\Date',
            'name' => 'date',
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
            'name' => 'content',
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
            'name' => 'value',
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
 * Transaction add/edit form filters
 * 
 * @author Mateusz Mirosławski
 *
 */
class TransactionFilter implements InputFilterAwareInterface
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
                'name'     => 'transactionId',
                'required' => true,
                'filters'  => array(
                    array('name' => 'Int'),
                ),
            )));
            
            // Bank account id
            $inputFilter->add($factory->createInput(array(
                'name'     => 'accountId',
                'required' => true,
                'filters'  => array(
                    array('name' => 'Int'),
                ),
            )));

            // Transaction type
            $inputFilter->add($factory->createInput(array(
                'name'     => 'transactionType',
                'required' => true,
                'filters'  => array(
                    array('name' => 'Int'),
                ),
            )));

            // Main category list
            $inputFilter->add($factory->createInput(array(
                'name'     => 'pcid',
                'required' => true,
                'filters'  => array(
                    array('name' => 'Int'),
                ),
            )));
            
            // New main category
            $inputFilter->add($factory->createInput(array(
                'name'     => 'newMainCategoryName',
                'required' => false,
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
            
            // Subcategory list
            $inputFilter->add($factory->createInput(array(
                'name'     => 'ccid',
                'required' => true,
                'filters'  => array(
                    array('name' => 'Int'),
                ),
            )));
            
            // New subcategory
            $inputFilter->add($factory->createInput(array(
                'name'     => 'newSubCategoryName',
                'required' => false,
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
            
            // Date
            $inputFilter->add($factory->createInput(array(
                'name'     => 'date',
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
                'name'     => 'content',
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
                'name'     => 'value',
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
