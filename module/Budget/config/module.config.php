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
                    'transfer-add' => array(
                        'type' => 'segment',
                        'description' => 'Route to add transfer',
                        'options' => array(
                            'route' => 'transfer-add/:aid',
                            'constraints' => array(
                                'aid' => '\d+',
                            ),
                            'defaults' => array(
                                'controller' => 'Budget\Controller\Transaction',
                                'action' => 'transferAdd',
                                'aid' => 0,
                            ),
                        ),
                    ),
                    'transfer-edit' => array(
                        'type' => 'segment',
                        'description' => 'Route to edit transfer',
                        'options' => array(
                            'route' => 'transfer-edit/:month/:year/:tid/:aid/:page',
                            'constraints' => array(
                                'month' => '\d+',
                                'year' => '\d+',
                                'tid' => '\d+',
                                'aid' => '\d+',
                                'page' => '\d+',
                            ),
                            'defaults' => array(
                                'controller' => 'Budget\Controller\Transaction',
                                'action' => 'transferEdit',
                            ),
                        ),
                    ),
                    'transfer-delete' => array(
                        'type' => 'segment',
                        'description' => 'Route to delete transfer',
                        'options' => array(
                            'route' => 'transfer-delete/:month/:year/:tid/:page',
                            'constraints' => array(
                                'month' => '\d+',
                                'year' => '\d+',
                                'tid' => '\d+',
                                'page' => '\d+',
                            ),
                            'defaults' => array(
                                'controller' => 'Budget\Controller\Transaction',
                                'action' => 'transferDelete',
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
                                'dateType' => 'month',
                                'aid' => 0,
                                'month' => date('m'),
                                'year' => date('Y')
                            ),
                        ),
                        'may_terminate' => true,
                        'child_routes' => array(
                            'month' => array(
                                'type' => 'segment',
                                'description' => 'Route to time charts with only one month',
                                'options' => array(
                                    'route' => 'month/:aid/:month/:year',
                                    'constraints' => array(
                                        'month' => '\d+',
                                        'year' => '\d+',
                                        'aid' => '\d+',
                                    ),
                                    'defaults' => array(
                                        'controller' => 'Budget\Controller\Analysis',
                                        'action' => 'category',
                                        'dateType' => 'month'
                                    ),
                                ),
                            ),
                            'between' => array(
                                'type' => 'segment',
                                'description' => 'Route to time charts with month range',
                                'options' => array(
                                    'route' => 'between/:aid/:dayUp/:monthUp/:yearUp/:dayDown/:monthDown/:yearDown',
                                    'constraints' => array(
                                        'aid' => '\d+',
                                        'dayUp' => '\d+',
                                        'monthUp' => '\d+',
                                        'yearUp' => '\d+',
                                        'dayDown' => '\d+',
                                        'monthDown' => '\d+',
                                        'yearDown' => '\d+',
                                        
                                    ),
                                    'defaults' => array(
                                        'controller' => 'Budget\Controller\Analysis',
                                        'action' => 'category',
                                        'dateType' => 'between'
                                    ),
                                ),
                            ),
                            'all' => array(
                                'type' => 'segment',
                                'description' => 'Route to time charts with all data',
                                'options' => array(
                                    'route' => 'all/:aid',
                                    'constraints' => array(
                                        'aid' => '\d+',
                                        
                                    ),
                                    'defaults' => array(
                                        'controller' => 'Budget\Controller\Analysis',
                                        'action' => 'category',
                                        'dateType' => 'all'
                                    ),
                                ),
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
                                'dateType' => 'month',
                                'aid' => 0,
                                'month' => date('m'),
                                'year' => date('Y')
                            ),
                        ),
                        'may_terminate' => true,
                        'child_routes' => array(
                            'month' => array(
                                'type' => 'segment',
                                'description' => 'Route to time charts with only one month',
                                'options' => array(
                                    'route' => 'month/:aid/:month/:year',
                                    'constraints' => array(
                                        'month' => '\d+',
                                        'year' => '\d+',
                                        'aid' => '\d+',
                                    ),
                                    'defaults' => array(
                                        'controller' => 'Budget\Controller\Analysis',
                                        'action' => 'time',
                                        'dateType' => 'month'
                                    ),
                                ),
                            ),
                            'between' => array(
                                'type' => 'segment',
                                'description' => 'Route to time charts with month range',
                                'options' => array(
                                    'route' => 'between/:aid/:dayUp/:monthUp/:yearUp/:dayDown/:monthDown/:yearDown',
                                    'constraints' => array(
                                        'aid' => '\d+',
                                        'dayUp' => '\d+',
                                        'monthUp' => '\d+',
                                        'yearUp' => '\d+',
                                        'dayDown' => '\d+',
                                        'monthDown' => '\d+',
                                        'yearDown' => '\d+',
                                        
                                    ),
                                    'defaults' => array(
                                        'controller' => 'Budget\Controller\Analysis',
                                        'action' => 'time',
                                        'dateType' => 'between'
                                    ),
                                ),
                            ),
                            'all' => array(
                                'type' => 'segment',
                                'description' => 'Route to time charts with all data',
                                'options' => array(
                                    'route' => 'all/:aid',
                                    'constraints' => array(
                                        'aid' => '\d+',
                                        
                                    ),
                                    'defaults' => array(
                                        'controller' => 'Budget\Controller\Analysis',
                                        'action' => 'time',
                                        'dateType' => 'all'
                                    ),
                                ),
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
    // Controllers
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
        'template_path_stack' => array(
            __DIR__ . '/../view',
        ),
    ),
    // View helpers
    'view_helpers' => array(
        'invokables' => array(
            'filterForm' => 'Budget\View\Helper\filterForm',
            'xyChart' => 'Budget\View\Helper\xyChart',
            'balanceChart' => 'Budget\View\Helper\balanceChart',
            'pieChart' => 'Budget\View\Helper\pieChart',
        ),
    ),
    // Services
    'service_manager' => array(
        'invokables' => array(
            'Budget\ImportMapper' => 'Budget\Mapper\ImportMapper',
            'Budget\TransactionMapper' => 'Budget\Mapper\TransactionMapper',
            'Budget\TransferMapper' => 'Budget\Mapper\TransferMapper',
            'Budget\BankService' => 'Budget\Service\BankService',
            'Budget\AnalysisService' => 'Budget\Service\AnalysisService',
        ),
    ),
);
