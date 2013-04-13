<?php

/**
 * Images (charts) configuration
 */
return array(
    // Path to generated images
    'img_dir' => array(
        'Zend_dir' => 'public/images/charts/',  // Path for Zend
        'browser_dir' => '/images/charts/',      // Path for browser
    ),
    
    // Names of generated images [md5(uid+pie_expense).img_ex]
    'img_names' => array(
        'img_ex' => '.png',
        'pie_expense' => '_pie_expense',
        'pie_profit' => '_pie_profit',
        'balacne' => '_balance',
        'time_expense' => '_time_expense',
        'time_profit' => '_time_profit',
    ),
);
