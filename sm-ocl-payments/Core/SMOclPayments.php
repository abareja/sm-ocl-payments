<?php

use SM\OclPayments\Modules\Admin\DataStores\SettingsDataStore;
use SM\Core\Hooks\HooksManager;
use SM\Core\Admin\ACFBuilder;
use SM\Core\Admin\BlockManager;
use SM\Core\Admin\Metabox;
use SM\Core\Admin\ShortcodeManager;
use SM\Core\Assets\AssetsManager;
use SM\Core\Helpers\Helpers;
use SM\Core\Helpers\PolylangHelpers;
use SM\Core\Types\PostType;
use SM\OclPayments\Config\PluginConfig;
use SM\OclPayments\Modules\Admin\SMOclPaymentsAdminMenu;
use SM\OclPayments\Modules\PostTypes\SMOrderType;
use SM\OclPayments\Services\SMOclPaymentsNotificationsService;
use SM\OclPayments\Services\SMOclPaymentsOrdersService;

class SMOclPayments
{
    protected ?HooksManager $hooksManager = null;
    protected ?PostType $postType = null;
    protected ?AssetsManager $assetsManager = null;
    protected ?AssetsManager $adminAssetsManager = null;
    protected ?SMOclPaymentsNotificationsService $notificationsService = null;

    public const FORM_ACTION = "https://secure.tpay.com";
    public const SANDBOX_FORM_ACTION = "https://secure.sandbox.tpay.com";
    public const PAYMENT_RESULT_ENDPOINT = "ocl-payment-result";

    public function __construct()
    {
        $this->hooksManager = new HooksManager();
        $this->assetsManager = new AssetsManager();
        $this->adminAssetsManager = new AssetsManager();
        $this->adminAssetsManager->setIsAdmin(true);
        $this->init();
    }

    private function init(): void
    {
        (new SMOclPaymentsAdminMenu())->init();
        $this->initFields();
        $this->initBlocks();
        $this->initShortcode();
        $this->loadAssets();
        $this->initLocalization();

        if(static::saveOrders())
        {
            $this->initSavingOrders();
        }
    }

    public function initSavingOrders()
    {
        $this->postType = new SMOrderType();
        $this->postType->init();
        $this->postType->addToPolylang();
        $this->notificationsService = new SMOclPaymentsNotificationsService(self::PAYMENT_RESULT_ENDPOINT);
        $this->initOrdersMetabox();
    }

    public function initFields()
    {
        // $builder = new ACFBuilder('sm-ocl-orders', 'Plugin');
        // $builder->setLocation('post_type', '==', $this->postType->slug);


        // $builder->build();
    }

    public function initBlocks()
    {
        //Init ACF Blocks

        $manager = new BlockManager('sm-ocl-block', 'SM Ocl Card');
        $manager->setBlocksDir(PluginConfig::getPluginDir() . '/blocks');
        
        $manager->addBlock([
            'name' => 'sm-ocl-card',
            'title' => 'SM One-Click Card',
        ]);

        $builder = new ACFBuilder('sm-ocl-block', 'SM Ocl Card');
        $builder->setLocation('block', '==', 'acf/sm-ocl-card');

        $builder->addAccordion('content-accordion', [
            'label' => 'Content',
        ]);

        

        $builder->build();
    }

    public function initShortcode()
    {
        ShortcodeManager::createShortcode('sm-ocl-payments', function($atts) {
            $defaults = [
                'amount' => null,
                'crc' => null,
                'description' => '',
                'label' => __('Purchase', PluginConfig::getTextDomain())
            ];

            $a = shortcode_atts($defaults, $atts);

            if(!isset($a['amount']) || !isset($a['crc'])) {
                return null;
            }

            $instanceId = uniqid('sm-ocl-payment-modal-');

            static::appendModal($instanceId, $a['amount'], $a['description'], $a['crc']);
            return static::getButtonView($instanceId, $a['label']);
        });
    }

    public static function getButtonView(string $instanceId, string $label)
    {
        return Helpers::getView(PluginConfig::getPluginDir() . '/Modules/Views/button.view.php', [
            'instanceId' => $instanceId,
            'label' => $label
        ]);
    }

    public static function getPaymentFormView(
        string $instanceId, 
        float $amount = null, 
        string $description = '', 
        string $crc = ''
    )
    {
        if(!$amount) {
            return null;
        }

        if(empty($description)) {
            $description = "";
        }

        if(empty($crc)) {
            return null;
        }

        return Helpers::getView(PluginConfig::getPluginDir() . '/Modules/Views/form.view.php', [
            'crc' => $crc,
            'amount' => $amount,
            'description' => $description,
            'instanceId' => $instanceId,
            'formAction' => static::isSandboxEnabled() ? static::SANDBOX_FORM_ACTION : static::FORM_ACTION,
            'md5Sum' => static::getMd5Sum($amount, $crc),
            'language' => static::getLanguage(),
            'resultUrl' => home_url('/' . self::PAYMENT_RESULT_ENDPOINT),
            'merchantId' => SettingsDataStore::getOption('sm_ocl_payments_merchant_id'),
            'returnUrl' => SettingsDataStore::getOption('sm_ocl_payments_return_url'),
            'returnErrorUrl' => SettingsDataStore::getOption('sm_ocl_payments_return_err_url'),
            'consent' => SettingsDataStore::getOption('sm_ocl_payments_consent') ?: false,
            'requirePhone' => SettingsDataStore::getOption('sm_ocl_payments_require_phone') ?: false,
        ]);
    }

    public static function appendModal(string $instanceId, float $amount, string $description, string $crc)
    {
        add_action('wp_footer', function() use($instanceId, $amount, $description, $crc) {
            echo static::getPaymentFormView($instanceId, $amount, $description, $crc);
        });
    }

    public static function getMd5Sum(float $amount, string $crc): ?string
    {
        $id = SettingsDataStore::getOption('sm_ocl_payments_merchant_id') ?: false;
        $code = SettingsDataStore::getOption('sm_ocl_payments_security_code') ?: false;

        if(!$amount || !$id || !$crc || !$code) {
            return null;
        }

        return md5(implode('&', [$id, $amount, $crc, $code]));
    }

    public static function getLanguage(): string
    {
        return PolylangHelpers::isPolylangActive() ? PolylangHelpers::getCurrentLanguage() : 'pl';
    }

    public static function formatCurrency(float $amount): string
    {
        $formatter = new \NumberFormatter(get_locale(), \NumberFormatter::CURRENCY);
        return $formatter->formatCurrency($amount, 'PLN');
    }

    public function initOrdersMetabox()
    {
        $details = new Metabox('sm-ocl-order', 'Order details', $this->postType->slug);

        $details->setView(function(\WP_Post $post) {
            $textDomain = PluginConfig::getTextDomain();
            $status = SMOclPaymentsOrdersService::getOrderStatus($post->ID);

            echo Helpers::getView(PluginConfig::getPluginDir() . '/Modules/Admin/Views/orderDetails.view.php', [
                'id' => $post->ID,
                'status' => $status,
                'details' => [
                    [
                        'label' => __('Status: ', $textDomain),
                        'value' => SMOclPaymentsOrdersService::getOrderStatusBadge($post->ID)
                    ],
                    [
                        'label' => __('E-mail address: ', $textDomain),
                        'value' => get_post_meta($post->ID, 'ocl_email', true)
                    ],
                    [
                        'label' => __('CRC: ', $textDomain),
                        'value' => get_post_meta($post->ID, 'ocl_crc', true)
                    ],
                    [
                        'label' => __('Amount: ', $textDomain),
                        'value' => static::formatCurrency(get_post_meta($post->ID, 'ocl_amount', true) ?: 0)
                    ],
                    [
                        'label' => __('Description: ', $textDomain),
                        'value' => get_post_meta($post->ID, 'ocl_description', true)
                    ],
                    [
                        'label' => __('Date: ', $textDomain),
                        'value' => get_post_meta($post->ID, 'ocl_date', true)
                    ],
                    [
                        'label' => __('TPay order ID: ', $textDomain),
                        'value' => get_post_meta($post->ID, 'ocl_id', true)
                    ]
                ]
            ]);
        });
    }

    public function loadAssets()
    {
        $this->assetsManager->setVersion('0.1');
        $this->assetsManager->addStyle('sm-ocl-payments', PluginConfig::getPluginUrl() . '/assets/styles/style.css');
        $this->assetsManager->addScript('sm-ocl-payments', PluginConfig::getPluginUrl() . '/assets/js/main.js', ['jquery']);
        $this->hooksManager->addAction('wp_enqueue_scripts', $this, 'initScriptVars');
    
        $this->assetsManager->enqueue();

        $this->adminAssetsManager->setVersion('0.1');
        $this->adminAssetsManager->addStyle('sm-ocl-payments-admin', PluginConfig::getPluginUrl() . '/Modules/Admin/assets/style.css');
        $this->adminAssetsManager->enqueue();

    }

    public function initScriptVars()
    {
        wp_localize_script('sm-ocl-payments', 'oclVars', [
            'lang' => static::getLanguage()
        ]);
    }

    public function initLocalization()
    {
        $locale = apply_filters('plugin_locale', determine_locale(), PluginConfig::getTextDomain());
        load_textdomain(PluginConfig::getTextDomain(), PluginConfig::getPluginDir() . '/languages/' . $locale . '.mo');
    }

    public static function saveOrders(): bool
    {
        return SettingsDataStore::getOption('sm_ocl_payments_save_orders') ?: false;
    }

    public static function isSandboxEnabled(): bool
    {
        return SettingsDataStore::getOption('sm_ocl_payments_sandbox') ?: false;
    }

    public function run()
    {
        if(!$this->hooksManager) {
            return;
        }

        $this->hooksManager->hook();
    }

    public function onActivate(): void {}
    public function onDeactivate(): void {}
}
