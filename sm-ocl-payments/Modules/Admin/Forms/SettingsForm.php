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
        'type' => 'Text',
        'label' => __('CRC', $textDomain),
        'id' => 'sm_ocl_payments_crc',
        'name' => 'sm_ocl_payments_crc',
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
}
