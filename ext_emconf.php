<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'Content element registry',
    'description' => 'Helper for creating typo3 content elements',
    'category' => 'fe',
    'author' => 'Ondrej Grosko',
    'author_email' => 'ondrej@digitalwerk.agency',
    'author_company' => 'Digitalwerk',
    'state' => 'stable',
    'version' => '0.0.6',
    'constraints' => [
        'depends' => [
            'typo3' => '8.7.0-10.4.999',
            'php' => '7.0.0-7.4.999',
        ],
        'conflicts' => [
        ],
        'suggests' => [
        ],
    ],
    'autoload' => [
        'psr-4' => [
            'Digitalwerk\\ContentElementRegistry\\' => 'Classes'
        ]
    ],
];
