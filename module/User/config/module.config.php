<?php

return array(
    // Routing
    'router' => array(
        'routes' => array(
            'user' => array(
                'type'    => 'segment',
                'description' => 'Route to configuration menu',
                'options' => array(
                    'route'    => '/user[/]',
                    'defaults' => array(
                        'controller' => 'User\Controller\Config',
                        'action'     => 'index',
                    ),
                ),
                'may_terminate' => true,
                'child_routes' => array(
                    'login' => array(
                            'type' => 'literal',
                            'description' => 'Route to login page',
                            'options' => array(
                                    'route' => 'login',
                                    'defaults' => array(
                                            'controller' => 'User\Controller\User',
                                            'action' => 'login',
                                    ),
                            ),
                    ),
                    'logout' => array(
                            'type' => 'literal',
                            'description' => 'Route to logout page',
                            'options' => array(
                                    'route' => 'logout',
                                    'defaults' => array(
                                            'controller' => 'User\Controller\User',
                                            'action' => 'logout',
                                    ),
                            ),
                    ),
                    'register' => array(
                            'type' => 'literal',
                            'description' => 'Route to register page',
                            'options' => array(
                                    'route' => 'register',
                                    'defaults' => array(
                                            'controller' => 'User\Controller\User',
                                            'action' => 'register',
                                    ),
                            ),
                    ),
                    'passrst' => array(
                            'type' => 'literal',
                            'description' => 'Route to reset password page',
                            'options' => array(
                                    'route' => 'password-reset',
                                    'defaults' => array(
                                            'controller' => 'User\Controller\User',
                                            'action' => 'passrst',
                                    ),
                            ),
                    ),
                    'email' => array(
                        'type'    => 'segment',
                        'description' => 'Route to change email form',
                        'options' => array(
                            'route'    => 'email',
                            'defaults' => array(
                                'controller' => 'User\Controller\User',
                                'action'     => 'email',
                            ),
                        ),
                    ),
                    'pass' => array(
                        'type'    => 'segment',
                        'description' => 'Route to change password form',
                        'options' => array(
                            'route'    => 'password',
                            'defaults' => array(
                                'controller' => 'User\Controller\User',
                                'action'     => 'password',
                            ),
                        ),
                    ),
                    'category' => array(
                        'type' => 'segment',
                        'description' => 'Route to category main page',
                        'options' => array(
                            'route' => 'category[/]',
                            'defaults' => array(
                                'controller' => 'User\Controller\Category',
                                'action' => 'index',
                            ),
                        ),
                        'may_terminate' => true,
                        'child_routes' => array(
                            'list' => array(
                                'type' => 'segment',
                                'description' => 'Route to category list',
                                'options' => array(
                                    'route' => 'list[/]',
                                    'defaults' => array(
                                        'controller' => 'User\Controller\Category',
                                        'action' => 'list',
                                    ),
                                ),
                            ),
                            'get-categories' => array(
                                'type' => 'segment',
                                'description' => 'Route to action which returns categories',
                                'options' => array(
                                    'route' => 'get-categories[/]',
                                    'defaults' => array(
                                        'controller' => 'User\Controller\Category',
                                        'action' => 'category',
                                    ),
                                ),
                            ),
                            'save' => array(
                                'type' => 'segment',
                                'description' => 'Route to add category',
                                'options' => array(
                                    'route' => 'save[/]',
                                    'defaults' => array(
                                        'controller' => 'User\Controller\Category',
                                        'action' => 'save',
                                    ),
                                ),
                            ),
                            'delete' => array(
                                'type' => 'segment',
                                'description' => 'Route to delete category',
                                'options' => array(
                                    'route' => 'delete[/]',
                                    'defaults' => array(
                                        'controller' => 'User\Controller\Category',
                                        'action'  => 'delete',
                                    ),
                                ),
                            ),
                        ),
                    ),
                    'account' => array(
                        'type' => 'segment',
                        'description' => 'Route to bank account',
                        'options' => array(
                            'route' => 'accounts[/]',
                            'defaults' => array(
                                'controller' => 'User\Controller\Account',
                                'action' => 'index',
                            ),
                        ),
                        'may_terminate' => true,
                        'child_routes' => array(
                            'add' => array(
                                'type' => 'segment',
                                'description' => 'Route to add bank account',
                                'options' => array(
                                    'route' => 'add[/]',
                                    'defaults' => array(
                                        'controller' => 'User\Controller\Account',
                                        'action' => 'add',
                                    ),
                                ),
                            ),
                            'edit' => array(
                                'type' => 'segment',
                                'description' => 'Route to edit bank account',
                                'options' => array(
                                    'route' => 'edit/:aid',
                                    'constraints' => array(
                                        'type' => '\d+',
                                    ),
                                    'defaults' => array(
                                        'controller' => 'User\Controller\Account',
                                        'action' => 'edit',
                                    ),
                                ),
                            ),
                            'delete' => array(
                                'type' => 'segment',
                                'description' => 'Route to delete bank account',
                                'options' => array(
                                    'route' => 'delete/:aid',
                                    'constraints' => array(
                                        'type' => '\d+',
                                    ),
                                    'defaults' => array(
                                        'controller' => 'User\Controller\Account',
                                        'action' => 'delete',
                                    ),
                                ),
                            ),
                            'default' => array(
                                'type' => 'segment',
                                'description' => 'Route to set default bank account',
                                'options' => array(
                                    'route' => 'default/:aid',
                                    'constraints' => array(
                                        'type' => '\d+',
                                    ),
                                    'defaults' => array(
                                        'controller' => 'User\Controller\Account',
                                        'action' => 'default',
                                    ),
                                ),
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
            'User\Controller\Config' => 'User\Controller\ConfigController',
            'User\Controller\User' => 'User\Controller\UserController',
            'User\Controller\Category' => 'User\Controller\CategoryController',
            'User\Controller\Account' => 'User\Controller\AccountController',
        ),
    ),
    // Services
    'service_manager' => array(
        'invokables' => array(
            'User\CategoryMapper' => 'User\Mapper\CategoryMapper',
            'User\UserMapper' => 'User\Mapper\UserMapper',
            'User\AccountMapper' => 'User\Mapper\AccountMapper',
        ),
    ),
    // View
    'view_manager' => array(
        'template_path_stack' => array(
            __DIR__ . '/../view',
        ),
        'strategies' => array(
                'ViewJsonStrategy',
        ),
    ),
    'view_helpers' => array(
        'invokables' => array(
            'configMenu' => 'User\View\Helper\ConfigMenu',
        ),
    ),
);