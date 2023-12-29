<?php

namespace SM\OclPayments\Services;

use SM\OclPayments\Config\PluginConfig;
use SMOclPayments;

class SMOclPaymentsDiscountsService
{
  public const AJAX_ACTION = 'check-discount';

  public static function init()
  {
    static::registerAjax();
  }

  public static function registerAjax()
  {
    add_action("wp_ajax_nopriv_" . static::AJAX_ACTION, [static::class, 'handleRequest']);
    add_action("wp_ajax_" . static::AJAX_ACTION, [static::class, 'handleRequest']);
  }

  public static function handleRequest()
  {
    $params = static::getParams();

    if(!$params->code || !$params->amount || !$params->crc) {
      wp_send_json_error([
        'message' => __('Invalid form data', PluginConfig::getTextDomain())
      ]);
      exit;
    }

    $discountTerm = static::getDiscountTerm($params->code);

    if(!$discountTerm) {
      wp_send_json_error([
        'message' => __('Discount code does not exist', PluginConfig::getTextDomain())
      ]);
      exit;
    }

    if(!static::isDiscountActive($discountTerm)) {
      wp_send_json_error([
        'message' => __('Discount code is not active', PluginConfig::getTextDomain())
      ]);
      exit;
    }

    $discount = static::getDiscount($discountTerm);

    if($discount === false) {
      wp_send_json_error([
        'message' => __('Discount code is not valid', PluginConfig::getTextDomain())
      ]);
      exit;
    }

    $newAmount = round($params->amount * (1 - ($discount / 100)), 2);
    $newMd5Sum = SMOclPayments::getMd5Sum($newAmount, $params->crc);

    wp_send_json_success([
      'amount' => $newAmount,
      'fullAmount' => SMOclPayments::formatCurrency($newAmount),
      'md5sum' => $newMd5Sum,
      'message' => __('Discount code applied', PluginConfig::getTextDomain()),
      'code' => $params->code,
      'discount' => $discount,
      'discountTerm' => $discountTerm
    ]);
    exit;
  }

  public static function getParams()
  {
    return (object) [
      'code' => sanitize_text_field($_POST['code'] ?? ''),
      'amount' => sanitize_text_field($_POST['amount'] ?? ''),
      'crc' => sanitize_text_field($_POST['crc'] ?? ''),
    ];
  }

  public static function getDiscountTerm(string $code): int | bool
  {
    if(!$code) {
      return 0;
    }

    $term = get_term_by('name', $code, 'sm-ocl-discounts');

    if(!$term) {
      return false;
    }

    return $term->term_id;
  }

  public static function isDiscountActive(int $discountTermId): bool
  {
    return (bool) (get_field('active', 'term_' . $discountTermId)) ?? false;
  }

  public static function getDiscount(int $discountTermId): int | bool
  {
    if(!$discountTermId) {
      return false;
    }

    return (int) (get_field('discount', 'term_' . $discountTermId)) ?? 0;
  }

  public static function getDiscountCodeById(int $discountTermId): string
  {
    $discount = get_term($discountTermId);

    if(!$discount) {
      return "";
    } 

    return $discount->name ?? "";
  }
}