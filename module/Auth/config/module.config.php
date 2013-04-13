<?php

return array(
    // Routing
    'router' => array(
        'routes' => array(
            'access-denied' => array(
                'type' => 'segment',
                'description' => 'Route to access denied page',
                'options' => array(
                    'route' => '/access-denied[/]',
                    'defaults' => array(
                        'controller' => 'Auth\Controller\Index',
                        'action' => 'denied',
                    ),
                ),
            ),
        ),
    ),
    'controllers' => array(
        'invokables' => array(
            'Auth\Controller\Index' => 'Auth\Controller\IndexController',
        ),
    ),
    'service_manager' => array(
        'invokables' => array(
            'Auth\UserAuthentication' => 'Auth\Service\UserAuthentication',
            'Auth\Authorization' => 'Auth\Service\Authorization',
        ),
    ),
    'view_manager' => array(
        'template_path_stack' => array(
            __DIR__ . '/../view',
        ),
    ),
);