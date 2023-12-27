<?php

namespace SM\OclPayments\Modules\PostTypes;

use SM\Core\Types\PostType;
use SM\OclPayments\Config\PluginConfig;
use SM\OclPayments\Services\SMOclPaymentsOrdersService;

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
        'menu_icon' => 'dashicons-cart',
        'public' => false,
        'rewrite' => false
    ];

    public function init()
    {
       parent::init();

       add_filter('manage_sm-ocl-order_posts_columns', function ($columns) {
        $offset = array_search('date', array_keys($columns));
        return array_merge(
            array_slice($columns, 0, $offset),
            [
                'sm-ocl-status' => __('Order status', PluginConfig::getTextDomain()),
                'sm-ocl-crc' => __('CRC', PluginConfig::getTextDomain())
            ],
            array_slice($columns, $offset, null)
        );
       });

       add_action('manage_sm-ocl-order_posts_custom_column', function ($columnName, $postId) {
        if($columnName === 'sm-ocl-status') {
            echo SMOclPaymentsOrdersService::getOrderStatusBadge($postId);
        }

        if($columnName === 'sm-ocl-crc') {
            echo get_post_meta($postId, 'ocl_crc', true);
        }
       }, 10, 2);
    }
}