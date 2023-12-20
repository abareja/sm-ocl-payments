<?php

namespace SM\OclPayments\Config;

use SM\Core\Config;

class PluginConfig extends Config {
  public const PLUGIN_DIR = "sm-ocl-payments/";
  public const PLUGIN_URL = "sm-ocl-payments/";
  public const TEXT_DOMAIN = "sm-ocl-payments";

  public static function getPluginDir()
  {
    return plugin_dir_path(dirname(__FILE__)) . static::PLUGIN_DIR;
  }

  public static function getPluginUrl()
  {
    return plugin_dir_url(dirname(__FILE__)) . static::PLUGIN_URL;
  }

  public static function getTextDomain()
  {
    return static::TEXT_DOMAIN;
  }
}