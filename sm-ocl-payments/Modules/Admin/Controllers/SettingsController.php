<?php

namespace SM\OclPayments\Modules\Admin\Controllers;

use SM\Core\Admin\BaseAdminController;
use SM\OclPayments\Modules\Admin\Forms\SettingsForm;
use SM\OclPayments\Config\PluginConfig;

final class SettingsController extends BaseAdminController
{
    public const TITLE = 'SM Plugin';
    public const FORM_CLASS = SettingsForm::class;
    // public const DATA_CLASS = SettingsDataStore::class;
    public const VIEW = '/Modules/Admin/Views/settings.view.php';

    public function getViewPath(): ?string
    {
        return PluginConfig::getPluginDir() . static::VIEW;
    }
}
