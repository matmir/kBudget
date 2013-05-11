<?php

return array(
    // Routrs
    'router' => array(
        'routes' => array(
            // Main
            'main' => array(
                'type' => 'literal',
                'options' => array(
                    'route' => '/',
                    'defaults' => array(
                        'controller' => 'Budget\Controller\Main',
                        'action' => 'index',
                    ),
                ),
            ),
            // Transactions list
            'transactions' => array(
                'type' => 'segment',
                'description' => 'Route to Transactions list with date params',
                'options' => array(
                    'route' => '/transactions[/:aid/:month/:year/:page]',
                    'constraints' => array(
                        'aid' => '\d+',
                        'month' => '\d+',
                        'year' => '\d+',
                        'page' => '\d+'
                    ),
                    'defaults' => array(
                        'controller' => 'Budget\Controller\Transaction',
                        'action' => 'index',
                        'aid' => 0,
                        'month' => date('m'),
                        'year' => date('Y'),
                        'page' => 1
                    ),
                ),
            ),
            // Transaction
            'transaction' => array(
                'type' => 'segment',
                'description' => 'Route to Transactions list without date params',
                'options' => array(
                    'route' => '/transaction[/]',
                    'defaults' => array(
                        'controller' => 'Budget\Controller\Transaction',
                        'action' => 'index',
                    ),
                ),
                'may_terminate' => true,
                'child_routes' => array(
                    // Transaction - add
                    'add' => array(
                        'type' => 'segment',
                        'description' => 'Route to add transaction',
                        'options' => array(
                            'route' => 'add/:type/:aid',
                            'constraints' => array(
                                'type' => '\d+',
                                'aid' => '\d+',
                            ),
                            'defaults' => array(
                                'controller' => 'Budget\Controller\Transaction',
                                'action' => 'add',
                                'type' => 1,
                                'aid' => 0,
                            ),
                        ),
                    ),
                    // Transaction - edit
                    'edit' => array(
                        'type' => 'segment',
                        'description' => 'Route to edit transaction',
                        'options' => array(
                            'route' => 'edit/:month/:year/:tid/:page',
                            'constraints' => array(
                                'month' => '\d+',
                                'year' => '\d+',
                                'tid' => '\d+',
                                'page' => '\d+',
                            ),
                            'defaults' => array(
                                'controller' => 'Budget\Controller\Transaction',
                                'action' => 'edit',
                            ),
                        ),
                    ),
                    // Transaction - delete
                    'delete' => array(
                        'type' => 'segment',
                        'description' => 'Route to delete transaction',
                        'options' => array(
                            'route' => 'delete/:month/:year/:tid/:page',
                            'constraints' => array(
                                'month' => '\d+',
                                'year' => '\d+',
                                'tid' => '\d+',
                                'page' => '\d+',
                            ),
                            'defaults' => array(
                                'controller' => 'Budget\Controller\Transaction',
                                'action' => 'delete',
                            ),
                        ),
                    ),
                    // Transaction - filtering
                    'filter' => array(
                        'type' => 'literal',
                        'description' => 'Route to filter transaction',
                        'options' => array(
                            'route' => 'filter',
                            'defaults' => array(
                                'controller' => 'Budget\Controller\Transaction',
                                'action' => 'filter',
                            ),
                        ),
                    ),
                ),
            ),
            // Analysis - main
            'analysis' => array(
                'type' => 'segment',
                'description' => 'Route to analysis main site',
                'options' => array(
                    'route' => '/analysis[/]',
                    'defaults' => array(
                        'controller' => 'Budget\Controller\Analysis',
                        'action' => 'index',
                    ),
                ),
                'may_terminate' => true,
                'child_routes' => array(
                    // Analysis - categories
                    'category' => array(
                        'type' => 'segment',
                        'description' => 'Route to categories pie charts',
                        'options' => array(
                            'route' => 'category[/]',
                            'defaults' => array(
                                'controller' => 'Budget\Controller\Analysis',
                                'action' => 'category',
                            ),
                        ),
                    ),
                    // Analysis - time charts
                    'time' => array(
                        'type' => 'segment',
                        'description' => 'Route to time charts',
                        'options' => array(
                            'route' => 'time[/]',
                            'defaults' => array(
                                'controller' => 'Budget\Controller\Analysis',
                                'action' => 'time',
                            ),
                        ),
                    ),
                ),
            ),
            // Import - main
            'import' => array(
                'type' => 'segment',
                'description' => 'Route to import main site',
                'options' => array(
                    'route' => '/import[/]',
                    'defaults' => array(
                        'controller' => 'Budget\Controller\Import',
                        'action' => 'index',
                    ),
                ),
                'may_terminate' => true,
                'child_routes' => array(
                    // Import - commit transactions
                    'commit' => array(
                        'type' => 'segment',
                        'description' => 'Route to comit imported transactions',
                        'options' => array(
                            'route' => 'commit[/]',
                            'defaults' => array(
                                'controller' => 'Budget\Controller\Import',
                                'action' => 'commit',
                            ),
                        ),
                    ),
                    // Import - cancel importing
                    'cancel' => array(
                        'type' => 'segment',
                        'description' => 'Route to cancel importing transactions',
                        'options' => array(
                            'route' => 'cancel[/]',
                            'defaults' => array(
                                'controller' => 'Budget\Controller\Import',
                                'action' => 'cancel',
                            ),
                        ),
                    ),
                ),
            ),
        ),
    ),
    // Kontrolery w module
    'controllers' => array(
        'invokables' => array(
            'Budget\Controller\Main' => 'Budget\Controller\MainController',
            'Budget\Controller\Transaction' => 'Budget\Controller\TransactionController',
            'Budget\Controller\Analysis' => 'Budget\Controller\AnalysisController',
            'Budget\Controller\Import' => 'Budget\Controller\ImportController',
        ),
    ),
    // View
    'view_manager' => array(
        'display_not_found_reason' => true,
        'display_exceptions'       => true,
        'doctype'                  => 'HTML5',
        'not_found_template'       => 'error/404',
        'exception_template'       => 'error/index',
        'template_map' => array(
            'layout/layout'           => __DIR__ . '/../view/layout/layout.phtml',
            'application/index/index' => __DIR__ . '/../view/budget/index/index.phtml',
            'error/404'               => __DIR__ . '/../view/error/404.phtml',
            'error/index'             => __DIR__ . '/../view/error/index.phtml',
        ),
        'template_path_stack' => array(
            __DIR__ . '/../view',
        ),
    ),
    // Services
    'service_manager' => array(
        'invokables' => array(
            'Budget\ImportMapper' => 'Budget\Mapper\ImportMapper',
            'Budget\TransactionMapper' => 'Budget\Mapper\TransactionMapper',
        ),
        'factories' => array(
            'translator' => 'Zend\I18n\Translator\TranslatorServiceFactory',
        ),
    ),
    // Translator
    'translator' => array(
        'locale' => 'en_US',
        'translation_file_patterns' => array(
            array(
                'type'     => 'gettext',
                'base_dir' => __DIR__ . '/../language',
                'pattern'  => '%s.mo',
            ),
        ),
    ),
);