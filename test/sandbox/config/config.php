<?php

chdir(dirname(__DIR__));
return [
    'modules' => [
        'Core',
        'Cv',
        'Auth',
        'Jobs',
        'Applications',
        'Settings',
        'Organizations',
        'Geo',
        'Jobboard',
    ],
    'core_options' => [
        'systemMessageEmail' => 'developer@yawik.org',
    ],
];
