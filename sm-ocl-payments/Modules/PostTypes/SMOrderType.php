<?php

namespace SM\OclPayments\Modules\PostTypes;

use SM\Core\Types\PostType;

class SMOrderType extends PostType
{
    public string $slug = 'sm-ocl-order';
    protected array $args = [
        'labels' => [
            'name' => 'SM One-Click Orders',
            'singular_name' => 'SM Ocl Orders',
            'menu_name' => 'SM One-Click Orders'
        ],
        'supports' => ['title'],
        'menu_icon' => 'dashicons-cart'
    ];
}