<?php if(isset($instanceId)): ?>
  <?php
    $style = [
      '--bg-color: ' . $buttonBackground ?? '',
      '--font-color: ' . $buttonFontColor ?? ''
    ];
  ?>

  <a 
    style="<?php echo implode('; ', $style); ?>"
    class="sm-ocl-button sm-ocl-button--<?php echo $buttonStyle; ?>" 
    href="#<?php echo $instanceId; ?>" 
    data-ocl-toggle-modal
  >
    <?php echo $label ?? ''; ?>
  </a>
<?php endif; ?>