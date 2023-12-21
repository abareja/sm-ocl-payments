<?php if(isset($instanceId)): ?>
  <a 
    class="sm-ocl-button sm-ocl-button--outline" 
    href="#<?php echo $instanceId; ?>" 
    data-ocl-toggle-modal
  >
    <?php echo $label ?? ''; ?>
  </a>
<?php endif; ?>