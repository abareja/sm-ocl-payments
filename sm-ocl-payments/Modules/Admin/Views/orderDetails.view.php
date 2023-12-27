<?php if(is_array($details) && !empty($details)): ?>

<div class="sm-ocl-order-details">
  <table>
    <?php foreach($details as $key => $detail): ?>
      <?php if(isset($detail['label']) && isset($detail['value']) && !empty($detail['value'])): ?>
        <tr>
          <td style="padding: 0.25rem 0.5rem 0.25rem 0"><strong><?php echo $detail['label'] ?? ''; ?></strong></td>
          <td style="paddong: 0.25rem 0"><?php echo $detail['value'] ?? ''; ?></td>
        </tr>
      <?php endif; ?>
    <?php endforeach; ?>
  </table>
</div>

<?php endif; ?>