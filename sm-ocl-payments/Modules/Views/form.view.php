<?php use SM\OclPayments\Config\PluginConfig; ?>

<div class="sm-ocl-modal" data-ocl-modal id="<?php echo $instanceId; ?>">
  <div class="sm-ocl-modal__bg" data-ocl-modal-close></div>
  <div class="sm-ocl-modal__body">
    <div class="sm-ocl-modal__header">
      <div class="sm-ocl-modal__title"><?php _e('Order', PluginConfig::getTextDomain()); ?></div>
      <div class="sm-ocl-modal__close" data-ocl-modal-close>&times;</div>
    </div>
    <div class="sm-ocl-modal__content">
      <form 
        action="<?php echo $formAction; ?>" 
        method="POST" 
        name="payment" 
        target="_blank" 
        data-ocl-order-form
        class="sm-ocl-form"
      >
        <p class="sm-ocl-parsley-validate">
          <input 
            type="text" 
            name="name" 
            class="sm-ocl-input" 
            required 
            placeholder="<?php _e('* Firstname and lastname', PluginConfig::getTextDomain()); ?>" 
          />
        </p>
        <p class="sm-ocl-parsley-validate">
          <input 
            type="email" 
            name="email" 
            class="sm-ocl-input" 
            required 
            placeholder="<?php _e('* E-mail address', PluginConfig::getTextDomain()); ?>" 
          />
        </p>

        <?php if(isset($requirePhone) && $requirePhone): ?>
          <p class="sm-ocl-parsley-validate">
            <input 
              type="text" 
              name="phone" 
              class="sm-ocl-input" 
              required 
              placeholder="<?php _e('* Phone number', PluginConfig::getTextDomain()); ?>" 
            />
          </p>
        <?php endif; ?>

        <input type="hidden" name="id" value="<?php echo $merchantId; ?>"/>
        <input type="hidden" name="crc" value="<?php echo $crc; ?>"/>
        <input type="hidden" name="amount" value="<?php echo $amount; ?>" />
        <input type="hidden" name="description" value="<?php echo $description ?: 'test'; ?>"/>
        <input type="hidden" name="md5sum" value="<?php echo $md5Sum; ?>"/>
        <input type="hidden" name="language" value="<?php echo $language; ?>" />

        <?php if(isset($returnUrl) && $returnUrl): ?>
          <input type="hidden" name="return_url" value="<?php echo $returnUrl; ?>" />
        <?php endif; ?>

        <?php if(isset($returnErrorUrl) && $returnErrorUrl): ?>
          <input type="hidden" name="return_error_url" value="<?php echo $returnErrorUrl; ?>" />
        <?php endif; ?>

        <?php if(isset($consent) && $consent): ?>
          <div class="sm-ocl-checkbox sm-ocl-parsley-validate sm-ocl-parsley-validate--checkbox">
              <label>
                  <input 
                    type="checkbox" 
                    name="consent" 
                    required 
                    data-parsley-error-message="<?php _e('You have to agree to consent.', PluginConfig::getTextDomain()); ?>"
                  >
                  <span><?php echo $consent; ?></span>
              </label>
          </div>
        <?php endif; ?>

        <div class="sm-ocl-form__summary">
          <p><strong><?php _e('Amount', PluginConfig::getTextDomain()); ?></strong>:
          <?php echo SMOclPayments::formatCurrency($amount); ?></p>
        </div>

        <input 
          type="submit" 
          class="sm-ocl-button sm-ocl-button--black"
          value="<?php _e('Pay', PluginConfig::getTextDomain()); ?>"
        />
      </form>
    </div>
  </div>
</div>
