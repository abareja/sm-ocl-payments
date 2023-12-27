<?php

namespace SM\OclPayments\Modules\PostTypes;

use SM\Core\Types\PostType;

class SMEmailTemplateType extends PostType
{
    public string $slug = 'sm-ocl-template';
    protected array $args = [
        'labels' => [
            'name' => 'SM OCL Email Template',
            'singular_name' => 'SM OCL Email Template',
            'menu_name' => 'SM One-Click Email Templates'
        ],
        'supports' => ['title'],
        'menu_icon' => 'dashicons-email',
        'public' => false,
        'show_in_rest' => false,
    ];
}