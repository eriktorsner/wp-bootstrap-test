<?php

class WP_Query
{
    private $args;
    private $posts;
    public function __construct($args)
    {
        global $mockPosts;
        $this->args = $args;
        $this->posts = [];
        foreach ($mockPosts as $name => $post) {
            if ($name == $args['name']) {
                $this->posts[] = $post;
            }
        }
        $this->current = 0;
    }

    public function have_posts()
    {
        return count($this->posts) > 0;
    }

    public function __get($name)
    {
        switch ($name) {
            case 'post':
                return $this->posts[$this->current];
                break;
        }
    }
}