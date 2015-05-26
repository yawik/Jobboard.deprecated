<?php

/**
 * create a config/autoload/YawikDemoJobboard.local.php and put modifications there.
 */

\YawikDemoJobboard\Module::$isLoaded = true;

return array('view_manager' => array(
    'template_map' => array(
        'layout/layout'        => __DIR__ . '/../view/layout.phtml',
        'core/index/index'     => __DIR__ . '/../view/index.phtml',
        'piwik'                => __DIR__ . '/../view/piwik.phtml',
        'content/about'       => __DIR__ . '/../view/about.phtml',
        'main-navigation'      => __DIR__ . '/../view/main-navigation.phtml',
        'jobs/form/list-filter' => __DIR__ . '/../view/search-for-jobs.phtml',
        'templates/default/index' => __DIR__ . '/../view/templates/default/index.phtml'
    ),
),
             'translator'   => array(
                 'translation_file_patterns' => array(
                     array(
                         'type'     => 'gettext',
                         'base_dir' => __DIR__ . '/../language',
                         'pattern'  => '%s.mo',
                     ),
                 ),
             ),
             'router'       => array(
                 'routes' => array(
                     'lang' => array(
                         'options' => array(
                             'defaults' => array(
                                 'controller' => 'Jobs/Jobboard', //Overwrites the route of the start Page
                                 'action'     => 'index',
                             ),
                         ),
                     ),
                 ),
             ),
             'acl' => array(
                 'rules' => array(
                     // guests are allowed to see a list of companies.
                     'guest' => array(
                         'allow' => array(
                             'route/lang/organizations',
                         ),
                     ),
                 ),
             ),
             'navigation' => array(
                 'default' => array(
                     'about'    => array(
                         'label' => 'About',
                         'order' => 200,
                         'uri'   => '/de/content/about'
                     ),
                 ),
             ),
);