<?php

global $mockTerms;

$mockTerms = [
    'catTerm1' => [
        'term_id' => 21,
        'name' => 'catTerm1',
        'slug' => 'catTerm1',
        'term_taxonomy_id' => 21,
        'taxonomy' => 'category',
        'description' => 'a test term',
        'term_group' => 0,
        'parent' => 0,
        'count' => 10,
        'filter' => 'raw'
    ],
    'catTerm2' => [
        'term_id' => 22,
        'name' => 'catTerm2',
        'slug' => 'catTerm2',
        'term_taxonomy_id' => 22,
        'taxonomy' => 'category',
        'description' => 'a test term2',
        'term_group' => 0,
        'parent' => 21,
        'count' => 1,
        'filter' => 'raw'
    ],
];
