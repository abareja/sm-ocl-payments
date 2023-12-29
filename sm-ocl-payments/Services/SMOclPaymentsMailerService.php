<?php

namespace SM\OclPayments\Services;

use SM\OclPayments\Config\PluginConfig;
use SM\OclPayments\Modules\Admin\DataStores\SettingsDataStore;
use SMOclPayments;

class SMOclPaymentsMailerService
{
  public static function sendSuccess($to, array $data = []): bool
  {
    if(empty($to)) {
      return false;
    }

    $receivers[] = $to;
    $templateId = SettingsDataStore::getOption('sm_ocl_payments_success_template') ?: false;

    if(!$templateId) {
      return false;
    }

    $subject = get_field('subject', $templateId) ?: __('Success', PluginConfig::getTextDomain());
    $content = static::getTemplate($templateId, $data);
    $from = get_bloginfo('name');
    $headers = array('From: ' . $from, 'Content-Type: text/html; charset=UTF-8', 'Reply-To: ' . $to);
    $attachments = get_field('attachments', $templateId) ?: [];
    $attachments = array_map(function ($item) {
      if(!isset($item['item']['id'])) {
        return '';
      }

      return get_attached_file($item['item']['id']);
    }, $attachments);

    $adminEmail = SettingsDataStore::getOption('sm_ocl_payments_admin_email') ?: '';

    if(!empty($adminEmail)) {
      $receivers[] = $adminEmail;
    }

    return wp_mail($receivers, $subject, $content, $headers, $attachments);
  }

  public static function getTemplate($templateId, array $data = [])
  {
    $template = get_field('template', $templateId);

    // [client_email]
    if(isset($data['email'])) {
      $template = str_replace('[client_email]', $data['email'], $template);
    }

    // [amount]
    if(isset($data['amount'])) {
      $template = str_replace('[amount]', SMOclPayments::formatCurrency($data['amount']), $template);
    }

    // [description]
    if(isset($data['description'])) {
      $template = str_replace('[description]', $data['description'], $template);
    }

    // [order_date]
    if(isset($data['date'])) {
      $template = str_replace('[order_date]', $data['date'], $template);
    }

    // [crc]
    if(isset($data['crc'])) {
      $template = str_replace('[crc]', $data['crc'], $template);
    }

    // [id]
    if(isset($data['id'])) {
      $template = str_replace('[id]', $data['id'], $template);
    }

    return $template;
  }
}