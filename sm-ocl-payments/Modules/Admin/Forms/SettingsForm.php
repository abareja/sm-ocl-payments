<?php

namespace SM\OclPayments\Modules\Admin\Forms;

use SM\OclPayments\Modules\Admin\DataStores\SettingsDataStore;
use SM\Core\Admin\Forms\AdminForm;
use SM\OclPayments\Config\PluginConfig;

class SettingsForm extends AdminForm
{
  public const DATA_CLASS = SettingsDataStore::class;
  public static function getScheme(): array
  {
    $textDomain = PluginConfig::getTextDomain();

    return [
      [
        'type' => 'Heading',
        'label' => __('Global settings', $textDomain)
      ],
      [
        'type' => 'Text',
        'label' => __('Merchant ID', $textDomain),
        'id' => 'sm_ocl_payments_merchant_id',
        'name' => 'sm_ocl_payments_merchant_id',
        'value' => ''
      ],
      [
        'type' => 'Text',
        'label' => __('Security code', $textDomain),
        'id' => 'sm_ocl_payments_security_code',
        'name' => 'sm_ocl_payments_security_code',
        'value' => ''
      ],
      [
        'type' => 'Checkbox',
        'label' => __('Save orders', $textDomain),
        'checked' => true,
        'id' => 'sm_ocl_payments_save_orders',
        'name' => 'sm_ocl_payments_save_orders',
        'value' => 'true'
      ],
      [
        'type' => 'Checkbox',
        'label' => __('Enable sandbox mode', $textDomain),
        'checked' => true,
        'id' => 'sm_ocl_payments_sandbox',
        'name' => 'sm_ocl_payments_sandbox',
        'value' => 'true'
      ],
      [
        'type' => 'Checkbox',
        'label' => __('Require phone number', $textDomain),
        'checked' => true,
        'id' => 'sm_ocl_payments_require_phone',
        'name' => 'sm_ocl_payments_require_phone',
        'value' => 'true'
      ],
      [
        'type' => 'Checkbox',
        'label' => __('Send e-mails (requires option "Save orders" to be enabled)', $textDomain),
        'checked' => true,
        'id' => 'sm_ocl_send_mails',
        'name' => 'sm_ocl_send_mails',
        'value' => 'true'
      ],
      [
        'type' => 'Text',
        'label' => __('Return URL', $textDomain),
        'id' => 'sm_ocl_payments_return_url',
        'name' => 'sm_ocl_payments_return_url',
        'value' => home_url('/')
      ], 
      [
        'type' => 'Text',
        'label' => __('Return Error URL', $textDomain),
        'id' => 'sm_ocl_payments_return_err_url',
        'name' => 'sm_ocl_payments_return_err_url',
        'value' => home_url('/')
      ],
      [
        'type' => 'Textarea',
        'label' => __('Consent text', $textDomain),
        'id' => 'sm_ocl_payments_consent',
        'name' => 'sm_ocl_payments_consent',
        'value' => ''
      ],
      [
        'type' => 'Select',
        'label' => __('Successful e-mail template', $textDomain),
        'id' => 'sm_ocl_payments_success_template',
        'name' => 'sm_ocl_payments_success_template',
        'choices' => static::getTemplates(),
        'value' => ''
      ],
      [
        'type' => 'Text',
        'label' => __('Admin e-mail', $textDomain),
        'id' => 'sm_ocl_payments_admin_email',
        'name' => 'sm_ocl_payments_admin_email',
        'value' => '',
      ],
      [
        'type' => 'Buttons',
        'buttons' => [
          [
            'type' => 'submit',
            'label' => __('Save changes', $textDomain)
          ]
        ]
      ]
    ];
  }

  public static function getTemplates()
  {
    $query = new \WP_Query([
      'post_type' => 'sm-ocl-template',
      'posts_per_page' => -1,
      'fields' => 'ids'
    ]);

    if(!$query->have_posts()) {
      return [];
    }

    $result = [];
    foreach($query->posts ?? [] as $postId) {
      $result[$postId] = get_the_title($postId);
    }

    return $result;
  }
}
