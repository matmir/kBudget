<?php

return array(
    'router' => array(
        'routes' => array(
            'admin' => array(
                'type' => 'segment',
                'description' => 'Route to admin menu',
                'options' => array(
                    'route' => '/admin[/]',
                    'defaults' => array(
                        'controller' => 'Admin\Controller\Index',
                        'action' => 'index',
                    ),
                ),
                'may_terminate' => true,
                'child_routes' => array(
                    'users' => array(
                        'type' => 'segment',
                        'description' => 'Route to user admin page',
                        'options' => array(
                            'route' => 'users[/]',
                            'defaults' => array(
                                'controller' => 'Admin\Controller\Users',
                                'action' => 'index',
                            ),
                        ),
                        'may_terminate' => true,
                        'child_routes' => array(
                            'list' => array(
                                'type' => 'segment',
                                'description' => 'Route to user list',
                                'options' => array(
                                    'route' => 'list/:page',
                                    'constraints' => array(
                                        'page' => '\d+',
                                    ),
                                    'defaults' => array(
                                        'controller' => 'Admin\Controller\Users',
                                        'action' => 'list',
                                        'page' => 1
                                    ),
                                ),
                            ),
                            'activate' => array(
                                'type' => 'segment',
                                'description' => 'Route to activate/deactivate user',
                                'options' => array(
                                    'route' => 'activate/:uid/:active/:page',
                                    'constraints' => array(
                                        'uid' => '\d+',
                                        'active' => '\d+',
                                        'page' => '\d+',
                                    ),
                                    'defaults' => array(
                                        'controller' => 'Admin\Controller\Users',
                                        'action' => 'activate',
                                    ),
                                ),
                            ),
                            'password' => array(
                                'type'    => 'segment',
                                'description' => 'Route to change user password form',
                                'options' => array(
                                    'route' => 'password/:uid/:page',
                                    'constraints' => array(
                                        'uid' => '\d+',
                                        'page' => '\d+',
                                    ),
                                    'defaults' => array(
                                        'controller' => 'Admin\Controller\Users',
                                        'action'     => 'password',
                                    ),
                                ),
                            ),
                        ),
                    ),
                ),
            ),
        ),
    ),
    // Controllers in module
    'controllers' => array(
        'invokables' => array(
            'Admin\Controller\Index' => 'Admin\Controller\IndexController',
            'Admin\Controller\Users' => 'Admin\Controller\UsersController',
        ),
    ),
    // View
    'view_manager' => array(
        'template_path_stack' => array(
            __DIR__ . '/../view',
        ),
    ),
);
