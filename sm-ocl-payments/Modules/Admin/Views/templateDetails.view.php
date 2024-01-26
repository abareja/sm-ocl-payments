<?php

use SM\OclPayments\Config\PluginConfig;

?>

<p><?php _e('To use this template either choose it in the settings for all orders or add this phrase to shortcode: ', PluginConfig::getTextDomain()); ?></p>
<pre>template="<?php echo $id; ?>"</pre>
<p><?php _e('For example: ', PluginConfig::getTextDomain()); ?></p>
<pre style="text-wrap: wrap;">[sm-ocl-payments amount="10.0" crc="Product-1" template="<?php echo $id; ?>"]</pre>