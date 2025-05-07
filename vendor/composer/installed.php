<?php return array(
    'root' => array(
        'name' => 'instawp/migration',
        'pretty_version' => 'dev-main',
        'version' => 'dev-main',
        'reference' => '27187d08b89cffde9ab2f57d265042f8aa0a4751',
        'type' => 'wordpress-plugin',
        'install_path' => __DIR__ . '/../../',
        'aliases' => array(),
        'dev' => true,
    ),
    'versions' => array(
        'instawp/connect-helpers' => array(
            'pretty_version' => 'dev-main',
            'version' => 'dev-main',
            'reference' => '7d59e8a9bf40d0bd81a9cabc69f28671d0a6c6d2',
            'type' => 'library',
            'install_path' => __DIR__ . '/../instawp/connect-helpers',
            'aliases' => array(
                0 => '9999999-dev',
            ),
            'dev_requirement' => false,
        ),
        'instawp/migration' => array(
            'pretty_version' => 'dev-main',
            'version' => 'dev-main',
            'reference' => '27187d08b89cffde9ab2f57d265042f8aa0a4751',
            'type' => 'wordpress-plugin',
            'install_path' => __DIR__ . '/../../',
            'aliases' => array(),
            'dev_requirement' => false,
        ),
        'wp-cli/wp-config-transformer' => array(
            'pretty_version' => 'v1.4.2',
            'version' => '1.4.2.0',
            'reference' => 'b78cab1159b43eb5ee097e2cfafe5eab573d2a8a',
            'type' => 'library',
            'install_path' => __DIR__ . '/../wp-cli/wp-config-transformer',
            'aliases' => array(),
            'dev_requirement' => false,
        ),
    ),
);
