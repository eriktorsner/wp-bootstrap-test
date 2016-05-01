<?php

global $mockPosts;

$mockPosts = [
    'testpost1' => [
        'ID'    => 10,
        'post_title' => 'testpost1_title',
        'post_name' => 'testpost1',
        'post_type' => 'post',
        'post_parent' => 12,
        'post_status' => 'publish',
        'post_mime_type' => 'text',
        'post_content' => 'testpost1 content',
        'post_excerpt' => 'testpost1',
        'ping_status' => 1,
        'pinged' => 1,
        'comment_status' => 'comment_status',
        'post_date' => '2016-01-01 12:34:10',
        'post_date_gmt' => '2016-01-01 11:34:10',
        'post_modified' => '2016-01-01 12:34:10',
        'post_modified_gmt' => '2016-01-01 11:34:10',
        'guid' => 'http://www.example.com/foo/testpost1',
        'post_meta' => [
            'somemeta' => ["someMetaValue"],
            'serializedMeta' => ['s:6:"foobar";'],
        ],
    ],
    'testpost2' => [
        'ID'    => 12,
        'post_title' => 'testpost2_title',
        'post_name' => 'testpost2',
        'post_type' => 'post',
        'post_parent' => 0,
        'post_status' => 'publish',
        'post_mime_type' => 'text',
        'post_content' => 'testpost2 content',
        'post_excerpt' => 'testpost2',
        'ping_status' => 1,
        'pinged' => 1,
        'comment_status' => 'comment_status',
        'post_date' => '2016-01-01 12:34:10',
        'post_date_gmt' => '2016-01-01 11:34:10',
        'post_modified' => '2016-01-01 12:34:10',
        'post_modified_gmt' => '2016-01-01 11:34:10',
        'guid' => 'http://www.example.com/foo/testpost2',
        'post_meta' => [
            'somemeta' => ["someMetaValue2"],
            'othermeta' => ["someOtherMetaValue"],
            '_thumbnail_id' => [41],
        ],
    ],
    'testimage1' => [
        'ID'    => 41,
        'post_title' => 'testimage1_title',
        'post_name' => 'testimage1',
        'post_type' => 'attachment',
        'post_parent' => 10,
        'post_status' => 'inherit',
        'post_mime_type' => 'image/jpeg',
        'guid' => 'http://www.example.com/foo/testimage1',
        'post_meta' => [
            '_wp_attached_file' => ["/some/path/testimage1.jpg"]
        ],
    ],
    'testimage2' => [
        'ID'    => 42,
        'post_title' => 'testimage2_title',
        'post_name' => 'testimage2',
        'post_type' => 'attachment',
        'post_parent' => 0,
        'post_status' => 'inherit',
        'post_mime_type' => 'image/jpeg',
        'guid' => 'http://www.example.com/foo/testimage2',
        'post_meta' => [
            '_wp_attached_file' => ["/some/path/testimage2.jpg"]
        ],
    ],
];
