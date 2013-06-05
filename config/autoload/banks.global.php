<?php

/**
 * Supported Bank CSV parsers class
 * 
 * 'uniqueBankID' => array(
 *     'title' => 'Bank name',
 *     'class' => 'Bank class name with namespace'
 * )
 * 
 */
return array(
    'supportedBanks' => array(
        'mBank' => array(
            'title' => 'mBank',
            'class' => 'Budget\Model\Banking\mBank',
        ),
    ),
);
