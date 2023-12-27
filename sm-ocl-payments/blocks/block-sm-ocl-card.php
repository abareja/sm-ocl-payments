<?php

use SM\OclPayments\Config\PluginConfig;

?>

<section class="sm-ocl-card">
  <div class="sm-ocl-card__wrapper">
    <div class="sm-ocl-card__grid">
      <div class="sm-ocl-card__left">
        <?php if(isset($fields['image']) && $fields['image']): ?>
          <?php echo wp_get_attachment_image($fields['image']['id'], 'large', false, ['class' => 'sm-ocl-card__image']); ?>
        <?php endif; ?>
      </div>
      <div class="sm-ocl-card__right">
        <?php if(isset($fields['title']) && $fields['title']): ?>
          <h2 class="sm-ocl-card__title"><?php echo $fields['title']; ?></h2>
        <?php endif; ?> 

        <?php if(isset($fields['description']) && $fields['description']): ?>
          <p class="sm-ocl-card__description"><?php echo $fields['description']; ?></p>
        <?php endif; ?>

        <?php if(isset($fields['amount']) && $fields['amount']): ?>
          <p class="sm-ocl-card__price"><?php echo SMOclPayments::formatCurrency($fields['amount']); ?></p>
        <?php endif; ?>

        <?php
          $amount = $fields['amount'] ?: false;
          $crc = $fields['crc'] ?: false;
          $description = $fields['description'] ?: '';
          $label = $fields['label'] ?: __('Purchase', PluginConfig::getTextDomain());
        ?>

        <?php if($amount && $crc && !empty($label)): ?>
          <p class="sm-ocl-card__buy">
            <?php echo do_shortcode("[sm-ocl-payments 
                amount='" . $amount .  "' 
                crc='" . $crc . "' 
                description='" . $description .  "'
                label='" . $label . "'
            ]"); ?>
          </p>
          <?php endif; ?>
      </div>  
    </div>
  </div>
</section>
