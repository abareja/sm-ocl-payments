<?php

namespace SM\OclPayments\Services;

use SM\OclPayments\Config\PluginConfig;

class SMOclPaymentsOrdersService
{
  public const ORDER_TYPE = 'sm-ocl-order';
  public const ORDER_STATUSES = [
    'success',
    'failure',
  ];

  public static function createOrder(
    string $status = 'success',
    array $orderData = []
  ): int
  {
    if(!in_array($status, static::ORDER_STATUSES)) {
      return 0;
    }

    if(!isset($orderData['id'])) {
      return 0;
    }

    return wp_insert_post([
      'post_title' => sprintf('%1$s %2$s', __('Order', PluginConfig::getTextDomain()), $orderData['id'] ?? 0),
      'post_status' => 'publish',
      'post_type' => static::ORDER_TYPE,
      'meta_input' => array_merge([
        'ocl_id' => $orderData['id'] ?? 0,
        'ocl_date' => $orderData['date'] ?? '',
        'ocl_crc' => $orderData['crc'] ?? '',
        'ocl_amount' => $orderData['amount'] ?? 0,
        'ocl_email' => $orderData['email'] ?? '',
        'ocl_md5sum' => $orderData['md5sum'] ?? '',
        'ocl_description' => $orderData['dedscription'] ?? '',
      ], [
        'ocl_status' => $status
      ])
    ]);
  }

  public static function getOrderStatus(int $orderId)
  {
    return get_post_meta($orderId, 'ocl_status', true) ?: false;
  }

  public static function getOrderStatusLabel(int $orderId, string $status = 'success')
  {
    $labels = [
      'success' => __('Success', PluginConfig::getTextDomain()),
      'failure' => __('Failure', PluginConfig::getTextDomain()),
    ];

    return $labels[$status] ?? '';
  }

  public static function getOrderStatusBadge(int $orderId)
  {
    $status = static::getOrderStatus($orderId);
    $label = static::getOrderStatusLabel($orderId, $status);

    return "<span class='sm-ocl-status sm-ocl-status--$status'>$label</span>";
  }
}