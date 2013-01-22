<?php

// config/autoload/local.php:
return array(
    'db' => array(
        'username' => 'usr',
        'password' => 'psw',
    ),
    
    // Konfiguracja e-maila
    'email' => array(
        'login' => 'usr',
        'pass' => 'psw',
        'host' => 'mail.somedomain.pl',
        'FromAddr' => 'kbudget@somedomain.pl',
        'FromName' => 'kBudget',
    ),
    
    // Konfiguracja długości loginu/hasła
    'user_login' => array(
        'minLoginLength' => 4,
        'maxLoginLength' => 30,
        'minPassLength' => 6,
        'maxPassLength' => 100,
    ),
    
    // Konfiguracja ścieżek do generowanych obrazków
    'img_dir' => array(
        'Zend_dir' => 'public/images/charts/',  // Ścieżka dla Zenda
        'browser_dir' => 'images/charts/',      // Ścieżka dla przeglądarki
    ),
    
    // Konfiguracja nazwy generowanych obrazków [md5(uid+reszta).img_ex]
    'img_names' => array(
        'img_ex' => '.png',
        'pie_expense' => '_pie_expense',
        'pie_profit' => '_pie_profit',
        'balacne' => '_balance',
        'time_expense' => '_time_expense',
        'time_profit' => '_time_profit',
    ),
);