<div class="sm-modal">
  <div class="sm-modal__bg"></div>
  <div class="sm-modal__body">
    <div class="sm-modal__header">
      <div class="sm-modal__title"></div>
      <div class="sm-modal__close">&times;</div>
    </div>
    <div class="sm-modal__content">
      <form id="tPay" action="<?php echo $formAction; ?>" method="POST" name="payment" target="_blank" class="">
        <input type="text" name="name" class="" required placeholder="<?php _e('* Imię i nazwisko', PluginConfig::getTextDomain()); ?>" />
        <input type="email" name="email" class="" required placeholder="<?php _e('* Adres e-mail', PluginConfig::getTextDomain()); ?>" />
        <input type="hidden" name="id" value="<?php echo $merchantId; ?>"/>
        <input type="hidden" name="crc" value="<?php echo $crc; ?>"/>
        <input type="hidden" name="amount" value="<?php echo $amount; ?>" />
        <input type="hidden" name="description" value="Payment for order X"/>
        <input type="hidden" name="md5sum" value="<?php echo $md5Sum; ?>"/>
        <input type="hidden" name="language" value="PL" />

        <input type="submit" value="<?php _e('Pay', PluginConfig::getTextDomain()); ?>"/>
      </form>
    </div>
  </div>
</div>
