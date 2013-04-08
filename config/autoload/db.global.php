<?php

/**
 * Database configuration
 */
return array(
        'db' => array(
            'driver'         => 'Pdo',
            'dsn'            => 'mysql:dbname=database_name;host=localhost',
            'driver_options' => array(
                    PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'UTF8\''
            ),
            'username' => 'user_name',
            'password' => 'user_password',
        )
);
