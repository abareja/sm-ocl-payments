<?php 

use SM\OclPayments\Modules\Admin\Forms\SettingsForm;
use SM\Core\Admin\Forms\AdminNotifications;
use SM\Core\Helpers\Helpers;
use SM\Core\Helpers\PolylangHelpers;
use SM\OclPayments\Config\PluginConfig;
?>

<div class="sm-form-header">
  <h1><?php echo $title; ?></h1>
</div>

<?php if(Helpers::isACFActive()): ?>
  <?php SettingsForm::generate(); ?>

  <?php if(PolylangHelpers::isPolylangActive()): ?>
    <div class="sm-form-alert">
      <h3><?php _e('Polylang is active', PluginConfig::getTextDomain()); ?></h3>
      <p><?php printf('%1$s: <span style="text-transform: uppercase;">%2$s</span>', __('This settings are applied for', PluginConfig::getTextDomain()), PolylangHelpers::getCurrentLanguage()); ?>  </div>
  <?php endif; ?>

<?php else: 
  AdminNotifications::error(__('ACF Pro is required for this plugin. Enable it on plugins page!', PluginConfig::getTextDomain())); 
endif; ?>