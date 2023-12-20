<?php

namespace SM\OclPayments\Modules\Admin;

use SM\Core\Admin\Menu\MenuManager;
use SM\OclPayments\Config\PluginConfig;

class SMOclPaymentsAdminMenu extends MenuManager
{
    public function getSchema(): array
    {
        return [
            'menu' => [
                [
                    'page_title' => 'SM One-Click Payments',
                    'menu_title' => 'SM One-Click Payments',
                    'capability' => 'manage_options',
                    'menu_slug' => 'sm-ocl-payments',
                    'icon_url' => PluginConfig::getPluginUrl() . '/assets/images/menu-icon.png',
                    'position' => 100
                ]
            ],
            'routing' => [
                'sm-ocl-payments' => [
                    'controller' => 'SM\OclPayments\Modules\Admin\Controllers\SettingsController',
                ],
            ]
        ];
    }
}
