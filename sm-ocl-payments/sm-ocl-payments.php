<?php

/**
 * Plugin Name:     See-me one-click payments plugin
 * Version:         1.0.1
 * Description:     See-me plugin for one-click payments.
 * Author:          See-Me
 * Author URI:      https://see-me.pl
 * License:         GPL2
 * License URI:     https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:     sm-ocl-payments
 * Domain Path:     /languages/
 */

use SM\OclPayments\Config\PluginConfig;

if (!defined('WPINC')) {
    die;
}

// Composer autoloader
if (is_readable(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
}

//Core
require_once PluginConfig::getPluginDir() . 'Core/SMOclPayments.php';
$plugin = new SMOclPayments();
$plugin->run();

register_activation_hook(__FILE__, function() use($plugin) {
    $plugin->onActivate();
});

register_deactivation_hook(__FILE__, function() use($plugin) {
    $plugin->onDeactivate();
});

add_action('deactivate_plugin', function($plugin) {
    if($plugin === 'advanced-custom-fields-pro/acf.php') {
        deactivate_plugins([
            'advanced-custom-fields-pro/acf.php',
            plugin_basename(__FILE__)
        ], true);
        wp_safe_redirect($_SERVER['HTTP_REFERER']);
        exit;
    }
}, 1000, 1);