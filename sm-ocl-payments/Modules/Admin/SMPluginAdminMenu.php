<?php

namespace SM\OclPayments\Modules\Admin;

use SM\Core\Admin\Menu\MenuManager;
use SM\OclPayments\Config\PluginConfig;

class SMPluginAdminMenu extends MenuManager
{
    public function getSchema(): array
    {
        return [
            'menu' => [
                [
                    'page_title' => 'SM Plugin',
                    'menu_title' => 'SM Plugin',
                    'capability' => 'manage_options',
                    'menu_slug' => 'sm-plugin',
                    'icon_url' => PluginConfig::getPluginUrl() . '/assets/images/menu-icon.png',
                    'position' => 100
                ]
            ],
            'routing' => [
                'sm-plugin' => [
                    'controller' => 'SM\OclPayments\Modules\Admin\Controllers\SettingsController',
                ],
            ]
        ];
    }
}
