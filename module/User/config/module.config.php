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
                            'add' => array(
                                'type' => 'segment',
                                'description' => 'Route to add category',
                                'options' => array(
                                    'route' => 'add[/]',
                                    'defaults' => array(
                                        'controller' => 'User\Controller\Category',
                                        'action' => 'add',
                                    ),
                                ),
                            ),
                            'edit' => array(
                                'type' => 'segment',
                                'description' => 'Route to edit category',
                                'options' => array(
                                    'route' => 'edit/:cid',
                                    'defaults' => array(
                                        'controller' => 'User\Controller\Category',
                                        'action' => 'edit',
                                    ),
                                ),
                            ),
                            'delete' => array(
                                'type' => 'segment',
                                'description' => 'Route to delete category',
                                'options' => array(
                                    'route' => 'delete/:cid',
                                    'defaults' => array(
                                        'controller' => 'User\Controller\Category',
                                        'action'  => 'delete',
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
        ),
    ),
    // View
    'view_manager' => array(
        'template_path_stack' => array(
            __DIR__ . '/../view',
        ),
    ),
);