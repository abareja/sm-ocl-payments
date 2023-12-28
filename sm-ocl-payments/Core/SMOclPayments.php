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
use SM\OclPayments\Modules\PostTypes\SMEmailTemplateType;
use SM\OclPayments\Modules\PostTypes\SMOrderType;
use SM\OclPayments\Services\SMOclPaymentsDiscountsService;
use SM\OclPayments\Services\SMOclPaymentsNotificationsService;
use SM\OclPayments\Services\SMOclPaymentsOrdersService;

class SMOclPayments
{
    protected ?HooksManager $hooksManager = null;
    protected ?PostType $orderType = null;
    protected ?PostType $templateType = null;
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
        $this->initBlocks();
        $this->initShortcode();
        $this->loadAssets();
        $this->initLocalization();

        if(static::saveOrders())
        {
            $this->initSavingOrders();
        }

        if(static::isDiscountsEnabled())
        {
            $this->initDiscountsFields();
            SMOclPaymentsDiscountsService::init();
        }
    }

    public function initSavingOrders()
    {
        $this->orderType = new SMOrderType();
        $this->orderType->init();
        $this->orderType->addToPolylang();

        $this->templateType = new SMEmailTemplateType();
        $this->templateType->init();
        $this->templateType->addToPolylang();

        $this->notificationsService = new SMOclPaymentsNotificationsService(self::PAYMENT_RESULT_ENDPOINT);
        $this->initOrdersMetabox();
        $this->initTemplateFields();
    }

    public function initTemplateFields()
    {
        $builder = new ACFBuilder('sm-ocl-template', 'E-mail template details');
        $builder->setLocation('post_type', '==', $this->templateType->slug);

        $builder->addText('subject', [
           'label' => 'E-mail subject'
        ]);

        $builder->addTextarea('template', [
            'label' => 'E-mail template',
            'instructions' => 'Possible shortcodes: [client_email], [amount], [description], [order_date], [crc], [id]',
            'rows' => 30
        ]);

        $builder->addRepeater('attachments', [
            'label' => 'Attachments'
        ], [
            [
                'name' => 'item',
                'label' => 'File',
                'type' => 'file'
            ]
        ]);

        $builder->build();
    }

    public function initDiscountsFields()
    {
        $builder = new ACFBuilder('sm-ocl-discounts', 'Discounts');
        $builder->setLocation('taxonomy', '==', 'sm-ocl-discounts');

        $builder->addNumber('discount', [
           'label' => 'Discount',
           'min' => 1,
           'max' => 100,
           'append' => '%'
        ]);
        $builder->addTrueFalse('active', [
            'label' => 'Is active?',
            'default_value' => true
        ]);

        $builder->build();
    }

    public function initBlocks()
    {
        //Init ACF Block
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
        $builder->addText('title', [
            'label' => 'Title'
        ]);
        $builder->addImage('image', [
            'label' => 'Product image'
        ]);
        $builder->addTextarea('description', [
            'label' => 'Description'
        ]);
        $builder->addNumber('amount', [
            'label' => 'Amount',
            'min' => 1,
            'append' => 'PLN'
        ]);
        $builder->addText('crc', [
            'label' => 'CRC',
            'maxlength' => 128
        ]);
        $builder->addText('label', [
            'label' => 'Button label'
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
            'enableDiscounts' => SettingsDataStore::getOption('sm_ocl_payments_enable_discounts') ?: false
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
        $details = new Metabox('sm-ocl-order', 'Order details', $this->orderType->slug);

        $details->setView(function(\WP_Post $post) {
            $textDomain = PluginConfig::getTextDomain();
            $status = SMOclPaymentsOrdersService::getOrderStatus($post->ID);

            $details = [
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
            ];

            if(get_post_meta($post->ID, 'ocl_email_send', true) !== null) {
                $details[] = [
                    'label' => __('Email status: ', $textDomain),
                    'value' => boolval(get_post_meta($post->ID, 'ocl_email_send', true)) ? __('Sent', $textDomain) : __('Failure', $textDomain)
                ];
            }

            echo Helpers::getView(PluginConfig::getPluginDir() . '/Modules/Admin/Views/orderDetails.view.php', [
                'id' => $post->ID,
                'status' => $status,
                'details' => $details
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
            'lang' => static::getLanguage(),
            'ajaxURL' => admin_url('admin-ajax.php')
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

    public static function sendEmailsEnabled(): bool
    {
        return SettingsDataStore::getOption('sm_ocl_send_mails') ?: false;
    }

    public static function isDiscountsEnabled(): bool
    {
        return SettingsDataStore::getOption('sm_ocl_payments_enable_discounts') ?: false;
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
