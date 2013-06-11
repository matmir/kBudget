<?php

namespace Budget\Form;

use Zend\Form\Form;
use Zend\Form\Element;

use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;

/**
 * Transaction import form
 * 
 * @author Mateusz Mirosławski
 *
 */
class TransactionImportForm extends Form
{
    /**
     * Number of generating fields
     * 
     * @var int
     */
    private $fCount;
    
    /**
     * Constructor
     * 
     * @param int $forms_count Number of generating fields
     * @param string $name
     * @throws \Exception
     */
    public function __construct($forms_count, $name = null)
    {
        // Check fields number
        if ($forms_count<1) {
            throw new \Exception('Number of fields must be greater or equals 1!');
        }
        
        $this->fCount = (int)$forms_count;
        
        // we want to ignore the name passed
        parent::__construct('transactionImportForm');
        $this->setAttribute('method', 'post');
        
        // Hidden field with number of generated field rows
        $this->add(array(
            'type'  => 'Zend\Form\Element\Hidden',
            'name' => 'trCount',
            'attributes' => array(
                'id' => 'trCount',
                'value' => $this->fCount,
            ),
        ));
        
        // Generation of a sufficient number of fields
        for ($i=0; $i<$this->fCount; $i++) {
            
            // Transaction type
            $this->add(array(
                'type'  => 'Zend\Form\Element\Select',
                'name' => 't_type-'.$i,
                'options' => array(
                    'label' => 'Typ transakcji: ',
                    'value_options' => array(
                        '0' => 'Przychód',
                        '1' => 'Wydatek',
                        '2' => 'Transfer wychodzący',
                        '3' => 'Transfer przychodzący',
                    ),
                ),
                'attributes' => array(
                    'class' => 'transactionType',
                    'id' => 't_type-'.$i,
                ),
            ));
            
            // Main category list
            $this->add(array(
                'type'  => 'Zend\Form\Element\Select',
                'name' => 'pcid-'.$i,
                'options' => array(
                    'label' => 'Kategoria: ',
                    'value_options' => array(
                        '-1' => 'Wybierz...',
                        '0' => 'Dodaj nową...',
                    ),
                ),
                'attributes' => array(
                    'class' => 'transactionCategory',
                    'title' => 'Główna kategoria',
                    'id' => 'mainCategoryList-'.$i,
                ),
            ));
            
            // New main category
            $this->add(array(
                'type'  => 'Zend\Form\Element\Text',
                'name' => 'newMainCategoryName-'.$i,
                'options' => array(
                    'label' => 'Nowa kategoria: ',
                ),
                'attributes' => array(
                    'class' => 'transactionNewCategory',
                    'id' => 'newMainCategory-'.$i,
                    'maxlength' => 100,
                    'size' => 9,
                ),
            ));
            
            // Sub category list
            $this->add(array(
                'type'  => 'Zend\Form\Element\Select',
                'name' => 'ccid-'.$i,
                'options' => array(
                    'label' => 'Podkategoria: ',
                    'value_options' => array(
                        '-1' => 'Brak',
                        '0' => 'Dodaj nową...',
                    ),
                ),
                'attributes' => array(
                    'class' => 'transactionCategory',
                    'title' => 'Podkategoria',
                    'id' => 'subCategoryList-'.$i,
                ),
            ));
            
            // New subcategory
            $this->add(array(
                'type'  => 'Zend\Form\Element\Text',
                'name' => 'newSubCategoryName-'.$i,
                'options' => array(
                    'label' => 'Nowa podkategoria: ',
                ),
                'attributes' => array(
                    'class' => 'transactionNewCategory',
                    'id' => 'newSubCategory-'.$i,
                    'maxlength' => 100,
                ),
            ));
            
            // Bank account id from/to which we transfer money
            $this->add(array(
                'type'  => 'Zend\Form\Element\Select',
                'name' => 'taid-'.$i,
                'options' => array(
                    'label' => 'Na konto: ',
                    'value_options' => array(
                        '0' => '...',
                    ),
                ),
                'attributes' => array(
                    'class' => 'transactionCategory',
                    'id' => 'taid-'.$i,
                ),
            ));
            
            // Transaction date
            $this->add(array(
                'type'  => 'Zend\Form\Element\Date',
                'name' => 't_date-'.$i,
                'options' => array(
                    'label' => 'Data: ',
                ),
                'attributes' => array(
                    'class' => 'transactionDate',
                    'step' => '1',
                ),
            ));
            
            // Transaction description
            $this->add(array(
                'type'  => 'Zend\Form\Element\Text',
                'name' => 't_content-'.$i,
                'options' => array(
                    'label' => 'Opis: ',
                ),
                'attributes' => array(
                    'maxlength' => 400,
                    'class' => 'transactionDescription',
                ),
            ));
            
            // Transaction value
            $this->add(array(
                'type'  => 'Zend\Form\Element\Text',
                'name' => 't_value-'.$i,
                'options' => array(
                    'label' => 'Wartość: ',
                ),
                'attributes' => array(
                    'class' => 'transactionValue',
                    'maxlength' => 12,
                ),
            ));
            
            // Ignoring importing of the transaction
            $this->add(array(
                'type'  => 'Zend\Form\Element\Checkbox',
                'name' => 'ignore-'.$i,
            ));
            
        }
        
        $this->add(array(
            'type'  => 'Zend\Form\Element\Submit',
            'name' => 'submit',
            'attributes' => array(
                'value' => 'Importuj',
                'id' => 'submitbutton',
            ),
        ));
    }
}

/**
 * Transaction import form filters
 * 
 * @author Mateusz Mirosławski
 *
 */
class TransactionImportFormFilter implements InputFilterAwareInterface
{
    protected $inputFilter;
    private $fCount;
    
    /**
     * 
     * @param int $forms_count Number of generating fields
     * @throws \Exception
     */
    public function __construct($forms_count)
    {
        // Check fields number
        if ($forms_count<1) {
            throw new \Exception('Number of fields must be greater or equals 1!');
        }
        
        $this->fCount = (int)$forms_count;
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
            
            // Number of generated forms
            $inputFilter->add($factory->createInput(array(
                'name' => 'trCount',
                'required' => true,
                'filters' => array(
                    array('name' => 'Int'),
                ),
            )));
            
            // Generate filters
            for ($i=0; $i<$this->fCount; $i++) {
                
                // Transaction type
                $inputFilter->add($factory->createInput(array(
                    'name' => 't_type-'.$i,
                    'required' => true,
                    'filters'  => array(
                        array('name' => 'Int'),
                    ),
                )));
    
                // Main category
                $inputFilter->add($factory->createInput(array(
                    'name' => 'pcid-'.$i,
                    'required' => true,
                    'filters' => array(
                        array('name' => 'Int'),
                    ),
                )));
                
                // New main category
                $inputFilter->add($factory->createInput(array(
                    'name' => 'newMainCategoryName-'.$i,
                    'required' => false,
                )));
                
                // Subcategory
                $inputFilter->add($factory->createInput(array(
                    'name' => 'ccid-'.$i,
                    'required' => true,
                    'filters' => array(
                        array('name' => 'Int'),
                    ),
                )));
                
                // New subcategory
                $inputFilter->add($factory->createInput(array(
                    'name' => 'newSubCategoryName-'.$i,
                    'required' => false,
                )));
                
                // Bank account id from/to which we transfer money
                $inputFilter->add($factory->createInput(array(
                    'name' => 'taid-'.$i,
                    'required' => true,
                    'filters'  => array(
                        array('name' => 'Int'),
                    ),
                )));
                
                // Transaction date
                $inputFilter->add($factory->createInput(array(
                    'name' => 't_date-'.$i,
                    'required' => true,
                    'validators' => array(
                        array(
                            'name' => 'Between',
                            'options' => array(
                                'min' => '1970-01-01',
                                'max' => date('Y-m-d'),
                            ),
                        ),
                    ),
                )));
                
                // Transaction description
                $inputFilter->add($factory->createInput(array(
                    'name' => 't_content-'.$i,
                    'required' => true,
                    'filters' => array(
                        array('name' => 'StripTags'),
                        array('name' => 'StringTrim'),
                    ),
                    'validators' => array(
                        array(
                            'name' => 'StringLength',
                            'options' => array(
                                'encoding' => 'UTF-8',
                                'min' => 1,
                                'max' => 200,
                            ),
                        ),
                    ),
                )));
                
                // Transaction value
                $inputFilter->add($factory->createInput(array(
                    'name' => 't_value-'.$i,
                    'required' => true,
                    'filters' => array(
                        array(
                            'name' => 'PregReplace',
                            'options' => array(
                                'pattern' => '/,/',
                                'replacement' => '.',
                            ),
                        ),
                    ),
                )));
                
            }
            
            $this->inputFilter = $inputFilter;
        }

        return $this->inputFilter;
    }
}
