<?php

namespace SM\OclPayments\Modules\PostTypes;

use SM\Core\Types\PostType;

class SMPluginType extends PostType
{
    public string $slug = 'sm-plugin-post';
    protected array $args = [
        'labels' => [
            'name' => 'SM Plugin',
            'singular_name' => 'SM Plugin',
            'menu_name' => 'SM Plugin'
        ],
        'supports' => ['title'],
        'menu_icon' => 'dashicons-wordpress'
    ];
}
