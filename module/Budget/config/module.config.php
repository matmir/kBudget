<?php

// module/Budget/config/module.config.php:

return array(
    // Kontrolery w module
    'controllers' => array(
        'invokables' => array(
            'Budget\Controller\Main' => 'Budget\Controller\MainController',
            'Budget\Controller\Transaction' => 'Budget\Controller\TransactionController',
            'Budget\Controller\Analysis' => 'Budget\Controller\AnalysisController',
            'Budget\Controller\User' => 'Budget\Controller\UserController',
            'Budget\Controller\Configuration' => 'Budget\Controller\ConfigurationController',
        ),
    ),
    
    // ACL
    'controller_plugins' => array(
        'invokables' => array(
            'MyAcl' => 'Budget\Controller\Plugin\MyAcl',
        ),
    ),
    
    // Routing
    'router' => array(
        'routes' => array(
            
            // Główna strona
            'main' => array(
                'type'    => 'literal',
                'options' => array(
                    'route'    => '/index.html',
                    'defaults' => array(
                        'controller' => 'Budget\Controller\Main',
                        'action'     => 'index',
                    ),
                ),
            ),
            
            // Transakcje - wyświetlanie
            'transaction' => array(
                'type'    => 'regex',
                'options' => array(
                    'regex' => '/transactions-(?<month>[0-9_-]+)-(?<year>[0-9_-]+)\.html',
                    'defaults' => array(
                        'controller' => 'Budget\Controller\Transaction',
                        'action'     => 'index',
                    ),
                    'spec' => '/transactions-%month%-%year%.html',
                ),
            ),
            
            // Transakcje - dodanie
            'transaction-add' => array(
                'type'    => 'regex',
                'options' => array(
                    'regex' => '/transaction-add-(?<type>[0-9_-]+)\.html',
                    'defaults' => array(
                        'controller' => 'Budget\Controller\Transaction',
                        'action'     => 'add',
                    ),
                    'spec' => '/transaction-add-%type%.html',
                ),
            ),
            
            // Transakcje - edycja
            'transaction-edit' => array(
                'type'    => 'regex',
                'options' => array(
                    'regex' => '/transaction-edit-(?<month>[0-9_-]+)-(?<year>[0-9_-]+)-(?<tid>[0-9_-]+)\.html',
                    'defaults' => array(
                        'controller' => 'Budget\Controller\Transaction',
                        'action'     => 'edit',
                    ),
                    'spec' => '/transaction-edit-%month%-%year%-%tid%.html',
                ),
            ),
            
            // Transakcje - usunięcie
            'transaction-delete' => array(
                'type'    => 'regex',
                'options' => array(
                    'regex' => '/transaction-delete-(?<month>[0-9_-]+)-(?<year>[0-9_-]+)-(?<tid>[0-9_-]+)\.html',
                    'defaults' => array(
                        'controller' => 'Budget\Controller\Transaction',
                        'action'     => 'delete',
                    ),
                    'spec' => '/transaction-delete-%month%-%year%-%tid%.html',
                ),
            ),
            
            // Transakcje - filtracja
            'transaction_filter' => array(
                'type'    => 'literal',
                'options' => array(
                    'route'    => '/transaction-filter.html',
                    'defaults' => array(
                        'controller' => 'Budget\Controller\Transaction',
                        'action'     => 'filter',
                    ),
                ),
            ),
            
            // User - logowanie
            'user-login' => array(
                'type'    => 'literal',
                'options' => array(
                    'route'    => '/login.html',
                    'defaults' => array(
                        'controller' => 'Budget\Controller\User',
                        'action'     => 'login',
                    ),
                ),
            ),
            
            // User - wylogowanie
            'user-logout' => array(
                'type'    => 'literal',
                'options' => array(
                    'route'    => '/logout.html',
                    'defaults' => array(
                        'controller' => 'Budget\Controller\User',
                        'action'     => 'logout',
                    ),
                ),
            ),
            
            // User - rejestracja
            'user-register' => array(
                'type'    => 'literal',
                'options' => array(
                    'route'    => '/register.html',
                    'defaults' => array(
                        'controller' => 'Budget\Controller\User',
                        'action'     => 'register',
                    ),
                ),
            ),
            
            // User - odzyskanie hasła
            'user-passrst' => array(
                'type'    => 'literal',
                'options' => array(
                    'route'    => '/password_reset.html',
                    'defaults' => array(
                        'controller' => 'Budget\Controller\User',
                        'action'     => 'passrst',
                    ),
                ),
            ),
            
            // Konfiguracja - główna strona
            'configuration' => array(
                'type'    => 'literal',
                'options' => array(
                    'route'    => '/configuration.html',
                    'defaults' => array(
                        'controller' => 'Budget\Controller\Configuration',
                        'action'     => 'index',
                    ),
                ),
            ),
            
            // Konfiguracja - lista kategorii
            'configuration-category' => array(
                'type'    => 'literal',
                'options' => array(
                    'route'    => '/configuration-category.html',
                    'defaults' => array(
                        'controller' => 'Budget\Controller\Configuration',
                        'action'     => 'category',
                    ),
                ),
            ),
            
            // Konfiguracja - zmiana e-maila
            'configuration-email' => array(
                'type'    => 'literal',
                'options' => array(
                    'route'    => '/configuration-email.html',
                    'defaults' => array(
                        'controller' => 'Budget\Controller\Configuration',
                        'action'     => 'email',
                    ),
                ),
            ),
            
            // Konfiguracja - zmiana hasła
            'configuration-pass' => array(
                'type'    => 'literal',
                'options' => array(
                    'route'    => '/configuration-password.html',
                    'defaults' => array(
                        'controller' => 'Budget\Controller\Configuration',
                        'action'     => 'password',
                    ),
                ),
            ),
            
            // Konfiguracja - dodanie kategorii
            'configuration-category-add' => array(
                'type'    => 'literal',
                'options' => array(
                    'route'    => '/configuration-category-add.html',
                    'defaults' => array(
                        'controller' => 'Budget\Controller\Configuration',
                        'action'     => 'categoryadd',
                    ),
                ),
            ),
            
            // Konfiguracja - edycja kategorii
            'configuration-category-edit' => array(
                'type'    => 'regex',
                'options' => array(
                    'regex' => '/configuration-category-edit-(?<cid>[0-9_-]+)\.html',
                    'defaults' => array(
                        'controller' => 'Budget\Controller\Configuration',
                        'action'     => 'categoryedit',
                    ),
                    'spec' => '/configuration-category-edit-%cid%.html',
                ),
            ),
            
            // Konfiguracja - usunięcie kategorii
            'configuration-category-del' => array(
                'type'    => 'regex',
                'options' => array(
                    'regex' => '/configuration-category-del-(?<cid>[0-9_-]+)\.html',
                    'defaults' => array(
                        'controller' => 'Budget\Controller\Configuration',
                        'action'     => 'categorydel',
                    ),
                    'spec' => '/configuration-category-del-%cid%.html',
                ),
            ),
            
            // Analiza - główna strona
            'analysis' => array(
                'type'    => 'literal',
                'options' => array(
                    'route'    => '/analysis.html',
                    'defaults' => array(
                        'controller' => 'Budget\Controller\Analysis',
                        'action'     => 'index',
                    ),
                ),
            ),
            
            // Analiza - podział na kategorie
            'analysis-category' => array(
                'type'    => 'literal',
                'options' => array(
                    'route'    => '/analysis-category.html',
                    'defaults' => array(
                        'controller' => 'Budget\Controller\Analysis',
                        'action'     => 'category',
                    ),
                ),
            ),
            
            // Analiza - wykresy czasowe
            'analysis-time' => array(
                'type'    => 'literal',
                'options' => array(
                    'route'    => '/analysis-time.html',
                    'defaults' => array(
                        'controller' => 'Budget\Controller\Analysis',
                        'action'     => 'time',
                    ),
                ),
            ),
            
        ),
    ),
    
    'view_manager' => array(
        'template_path_stack' => array(
            'album' => __DIR__ . '/../view',
        ),
    ),
);