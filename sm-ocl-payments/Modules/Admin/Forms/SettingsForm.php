<?php

namespace SM\OclPayments\Modules\Admin\Forms;

use SM\OclPayments\Modules\Admin\DataStores\SettingsDataStore;
use SM\Core\Admin\Forms\AdminForm;

class SettingsForm extends AdminForm
{
  public const DATA_CLASS = SettingsDataStore::class;
  public static function getScheme(): array
  {
    return [
      
    ];
  }
}
