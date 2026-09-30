<?php return array(
    'root' => array(
        'name' => 'instawp/migration',
        'pretty_version' => 'dev-main',
        'version' => 'dev-main',
        'reference' => 'd0199ae760f7ee8bdc93d2babfeae1bd62e61346',
        'type' => 'wordpress-plugin',
        'install_path' => __DIR__ . '/../../',
        'aliases' => array(),
        'dev' => false,
    ),
    'versions' => array(
        'instawp/connect-helpers' => array(
            'pretty_version' => 'dev-main',
            'version' => 'dev-main',
            'reference' => '671c15743066b54cb77396641ec4a9c877ee4177',
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
            'reference' => 'd0199ae760f7ee8bdc93d2babfeae1bd62e61346',
            'type' => 'wordpress-plugin',
            'install_path' => __DIR__ . '/../../',
            'aliases' => array(),
            'dev_requirement' => false,
        ),
        'wp-cli/wp-config-transformer' => array(
            'pretty_version' => 'v1.4.6',
            'version' => '1.4.6.0',
            'reference' => '1ef18784990b85b35202c2d68dbbc4a8fe6615b6',
            'type' => 'library',
            'install_path' => __DIR__ . '/../wp-cli/wp-config-transformer',
            'aliases' => array(),
            'dev_requirement' => false,
        ),
    ),
);
