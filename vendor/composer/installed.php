<?php return array(
    'root' => array(
        'name' => 'instawp/migration',
        'pretty_version' => 'dev-main',
        'version' => 'dev-main',
        'reference' => '19c6c53e928dc0d7275db390c7654c65cd470d4e',
        'type' => 'wordpress-plugin',
        'install_path' => __DIR__ . '/../../',
        'aliases' => array(),
        'dev' => true,
    ),
    'versions' => array(
        'instawp/connect-helpers' => array(
            'pretty_version' => 'dev-main',
            'version' => 'dev-main',
            'reference' => '7178cccefc15607880fd8376d95db74a4fb13ed1',
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
            'reference' => '19c6c53e928dc0d7275db390c7654c65cd470d4e',
            'type' => 'wordpress-plugin',
            'install_path' => __DIR__ . '/../../',
            'aliases' => array(),
            'dev_requirement' => false,
        ),
        'wp-cli/wp-config-transformer' => array(
            'pretty_version' => 'v1.4.4',
            'version' => '1.4.4.0',
            'reference' => 'b0fda07aac51317404f5e56dc8953ea899bc7bce',
            'type' => 'library',
            'install_path' => __DIR__ . '/../wp-cli/wp-config-transformer',
            'aliases' => array(),
            'dev_requirement' => false,
        ),
    ),
);
