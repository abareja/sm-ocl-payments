<?php

namespace SM\OclPayments\Modules\PostTypes;

use SM\Core\Types\PostType;
use SM\OclPayments\Config\PluginConfig;
use SM\OclPayments\Services\SMOclPaymentsOrdersService;
use SMOclPayments;

class SMOrderType extends PostType
{
    public string $slug = 'sm-ocl-order';
    public string $taxonomy = 'sm-ocl-discounts';
    protected array $args = [
        'labels' => [
            'name' => 'SM One-Click Orders',
            'singular_name' => 'SM OCL Orders',
            'menu_name' => 'SM One-Click Orders'
        ],
        'supports' => ['title'],
        'menu_icon' => 'dashicons-cart',
        'public' => false,
        'rewrite' => false
    ];

    public function initAdmin()
    {
        add_filter('manage_sm-ocl-order_posts_columns', function ($columns) {
            $offset = array_search('date', array_keys($columns));
            return array_merge(
                array_slice($columns, 0, $offset),
                [
                    'sm-ocl-status' => __('Order status', PluginConfig::getTextDomain()),
                    'sm-ocl-crc' => __('CRC', PluginConfig::getTextDomain()),
                    'sm-ocl-amount' => __('Amount', PluginConfig::getTextDomain())
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

        if($columnName === 'sm-ocl-amount') {
            echo SMOclPayments::formatCurrency(get_post_meta($postId, 'ocl_amount', true));
        }
        }, 10, 2);
    }

    public function registerTaxonomy()
    {
        add_action('init', function() {
            register_taxonomy(
                $this->taxonomy,
                $this->slug,
                [
                    'hierarchical' => false,
                    'label' => __('Discount codes', PluginConfig::getTextDomain()),
                    'publicly_queryable' => false,
                    'show_in_rest' => false,
                    'query_var' => false,
                    'rewrite' => false,
                    'meta_box_cb' => false
                ]
            );
        });
    }

    public function init()
    {
       parent::init();

        $this->initAdmin();

        if(SMOclPayments::isDiscountsEnabled()) {
            $this->registerTaxonomy();
        }
    }
}