<?php

namespace SM\OclPayments\Services;

use SM\OclPayments\Services\SMOclPaymentsOrdersService as OrdersService;
use SMOclPayments;

class SMOclPaymentsNotificationsService
{
  private string $endpoint = '';
  private const QUERY_VAR = 'ocl-payment-result';
  private const SUCCESS_MSG = 'TRUE';
  private const ERROR_MSG = 'FALSE';

  public function __construct(string $endpoint)
  {
    $this->endpoint = $endpoint;
    $this->initRules();
    $this->addQueryVars();
    $this->parseRequest();

    flush_rewrite_rules();
  }

  public function initRules()
  {
    add_action('init', function() {
      add_rewrite_rule($this->endpoint, 'index.php? ' . self::QUERY_VAR . '=true', 'top');
    });
  }

  public function addQueryVars()
  {
    add_filter('query_vars', function ($queryVars) {
      $queryVars = array_merge(
        $queryVars,
        [
          self::QUERY_VAR
        ]
      );
  
      return $queryVars;
    });
  }

  public function parseRequest()
  {
    add_action('template_redirect', function($template) {
      if(get_query_var(self::QUERY_VAR) === 'true') 
      {
        $result = $this->getNotification();
        exit($result);
      }

      return $template;
    });
  }

  public function getNotification()
  {
    if(!$this->checkPayment()) {
      return self::ERROR_MSG;
    }

    $status = $_POST['tr_status'] ?? self::ERROR_MSG;

    if($status === self::SUCCESS_MSG) {
      $this->successAction();
    } else {
      $this->failureAction();
    }

    return $status;
  }

  public function checkPayment(): bool
  {
    $jws = isset($_SERVER['HTTP_X_JWS_SIGNATURE']) ? $_SERVER['HTTP_X_JWS_SIGNATURE'] : null;

    if (null === $jws) {
      error_log('FALSE - Missing JWS header');
      exit('FALSE - Missing JWS header');
    }

    // Extract JWS header properties
    $jwsData = explode('.', $jws);
    $headers = isset($jwsData[0]) ? $jwsData[0] : null;
    $signature = isset($jwsData[2]) ? $jwsData[2] : null;
    if (null === $headers || null === $signature) {
      error_log('FALSE - Invalid JWS header');
      exit('FALSE - Invalid JWS header');
    }

    // Decode received headers json string from base64_url_safe
    $headersJson = base64_decode(strtr($headers, '-_', '+/'));

    // Get x5u header from headers json
    $headersData = json_decode($headersJson, true);
    $x5u = isset($headersData['x5u']) ? $headersData['x5u'] : null;
    if (null === $x5u) {
      error_log('FALSE - Missing x5u header');
      exit('FALSE - Missing x5u header');
    }

    // Check certificate url
    $prefix = SMOclPayments::isSandboxEnabled() ? SMOclPayments::SANDBOX_FORM_ACTION : SMOclPayments::FORM_ACTION;
    if (substr($x5u, 0, strlen($prefix)) !== $prefix) {
      error_log('FALSE - Wrong x5u url');
      exit('FALSE - Wrong x5u url');
    }

    // Get JWS sign certificate from x5u uri
    $certificate = file_get_contents($x5u);

    // Get request body
    $body = file_get_contents('php://input');
    // Encode body to base46_url_safe
    $payload = str_replace('=', '', strtr(base64_encode($body), '+/', '-_'));

    // Decode received signature from base64_url_safe
    $decodedSignature = base64_decode(strtr($signature, '-_', '+/'));

    if(function_exists('openssl_pkey_get_public') && function_exists('openssl_verify')) {
      // Verify RFC 7515: JSON Web Signature (JWS) with ext-openssl
      // Get public key from certificate
      $publicKey = openssl_pkey_get_public($certificate);
      if (1 !== openssl_verify($headers . '.' . $payload, $decodedSignature, $publicKey, OPENSSL_ALGO_SHA256)) {
        error_log('FALSE - Invalid JWS signature');
        exit('FALSE - Invalid JWS signature');
      }
    }
    
    return true;
  }

  public function getActionData()
  {
    return [
      'id' => sanitize_text_field($_POST['tr_id'] ?? 0),
      'date' => sanitize_text_field($_POST['tr_date'] ?? ''),
      'crc' => sanitize_text_field($_POST['tr_crc'] ?? ''),
      'amount' => sanitize_text_field($_POST['tr_amount'] ?? 0),
      'email' => sanitize_text_field($_POST['tr_email'] ?? ''),
      'md5sum' => sanitize_text_field($_POST['md5sum'] ?? ''),
      'description' => sanitize_text_field($_POST['tr_desc'] ?? ''),
      'discount' => sanitize_text_field($_GET['discount'] ?? 0)
    ];
  }

  public function successAction()
  {
    $data = $this->getActionData();
    $orderId = OrdersService::createOrder('success', $data);

    if(!$orderId) {
      return false;
    }

    if(SMOclPayments::sendEmailsEnabled()) {
      $send = SMOclPaymentsMailerService::sendSuccess($data['email'] ?? '', $data);
      update_post_meta($orderId, 'ocl_email_send', $send);
    }
  }

  public function failureAction()
  {
    return OrdersService::createOrder('failure', $this->getActionData());
  }
}
