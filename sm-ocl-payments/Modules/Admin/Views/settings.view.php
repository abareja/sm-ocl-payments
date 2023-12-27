<?php 

use SM\OclPayments\Modules\Admin\Forms\SettingsForm;
use SM\Core\Admin\Forms\AdminNotifications;
use SM\Core\Helpers\Helpers;
use SM\OclPayments\Config\PluginConfig;
?>

<div class="sm-form-header">
  <h1><?php echo $title; ?></h1>
</div>

<?php if(Helpers::isACFActive()): ?>
  <?php SettingsForm::generate(); ?>

  <div class="sm-form-alert">
    <h3><?php _e('Shortcode exmaples', PluginConfig::getTextDomain()); ?></h3>
    <h4><?php _e('Basic:', PluginConfig::getTextDomain()); ?></h4>
    <pre>[sm-ocl-payments amount="10.0" crc="Product-1"]</pre>
    <hr />
    <h4><?php _e('With description:', PluginConfig::getTextDomain()); ?></h4>
    <pre>[sm-ocl-payments amount="10.0" crc="Product-1" description="Sample payment"]</pre>
  </div>

  <hr />
  <h3><?php _e('Mandatory parameters:', PluginConfig::getTextDomain()); ?></h3>
  <ul style="list-style: disc; padding-left: 16px;">
    <li><?php _e('Amount', PluginConfig::getTextDomain()); ?></li>
    <li><?php _e('CRC - alphanumeric value, up to 128 characters, identyfing transaction or product', PluginConfig::getTextDomain()); ?></li>
  </ul>

  <hr />
  <h3><?php _e('Additional parameters:', PluginConfig::getTextDomain()); ?></h3>
  <ul style="list-style: disc; padding-left: 16px;">
    <li><?php _e('Description', PluginConfig::getTextDomain()); ?></li>
    <li><?php _e('Label - text displayed on button - default to "Purchase"', PluginConfig::getTextDomain()); ?></li>
  </ul>

<?php else: 
  AdminNotifications::error(__('ACF Pro is required for this plugin. Enable it on plugins page!', PluginConfig::getTextDomain())); 
endif; ?>