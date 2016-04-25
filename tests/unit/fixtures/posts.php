<?php

global $mockMedia;

$mockMedia = [
    'one' => (object)[
        'ID'    => 100,
        'post_title' => '',
        'post_name' => 'mediaOne',
        'post_type' => 'attachment',
        'post_parent' => 47,
        'post_status' => 'inherit',
        'post_mime_type' => 'image/jpeg',
        'guid' => 'http://www.example.com/foo/mediaOne',
        'post_meta' => [
            '_wp_attached_file' => ["/some/path/mediaOne.jpg"]
        ],
    ],
];
