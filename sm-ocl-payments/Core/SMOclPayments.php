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

class SMOclPayments
{
    protected ?HooksManager $hooksManager = null;
    protected ?PostType $postType = null;
    protected ?AssetsManager $assetsManager = null;
    
    public const FORM_ACTION = "https://secure.tpay.com";
    public const SANDBOX_FORM_ACTION = "https://secure.sandbox.tpay.com";

    public function __construct()
    {
        $this->hooksManager = new HooksManager();
        $this->assetsManager = new AssetsManager();
        $this->init();
    }

    private function init(): void
    {
        (new SMOclPaymentsAdminMenu())->init();
        $this->initFields();
        $this->initBlocks();
        $this->initShortcode();
        $this->initMetabox();
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
    }

    public function initFields()
    {
        //Init ACF Fields for post types

        // $builder = new ACFBuilder('sm-plugin', 'Plugin');
        // $builder->setLocation('post_type', '==', $this->postType->slug);

        // $builder->addRepeater('items', [
        //     'label' => 'Elementy',
        //     'layout' => 'row'
        // ], [
        //     [
        //         'name' => 'heading',
        //         'label' => 'Nagłówek',
        //         'type' => 'text'
        //     ],
        //     [
        //         'name' => 'content',
        //         'label' => 'Treść',
        //         'type' => 'wysiwyg'
        //     ]
        // ]);

        // $builder->build();
    }

    public function initBlocks()
    {
        //Init ACF Blocks

        // $manager = new BlockManager('sm-plugin-blocks', 'SM Plugin');
        // $manager->setBlocksDir(PluginConfig::getPluginDir() . '/blocks');
        
        // $manager->addBlock([
        //     'name' => 'sm-plugin',
        //     'title' => 'SM Plugin',
        //     'icon' => 'arrow-down-alt2'
        // ]);

        // $builder = new ACFBuilder('sm-plugin-block', 'Plugin block');
        // $builder->setLocation('block', '==', 'acf/sm-plugin');

        // $builder->addAccordion('content-accordion', [
        //     'label' => 'Content',
        // ]);

        // $builder->build();
    }

    public function initShortcode()
    {
        ShortcodeManager::createShortcode('sm-ocl-payments', function($atts) {
            $defaults = [
                'amount' => null,
                'description' => ""
            ];

            $a = shortcode_atts($defaults, $atts);

            if(!isset($a['amount'])) {
                return null;
            }

            $instanceId = uniqid('sm-ocl-payment-modal-');

            static::appendModal($instanceId, $a['amount'], $a['description']);
            return static::getButtonView($instanceId);
        });
    }

    public static function getButtonView(string $instanceId)
    {
        return Helpers::getView(PluginConfig::getPluginDir() . '/Modules/Views/button.view.php', [
            'instanceId' => $instanceId,
            'label' => __('Purchase', PluginConfig::getTextDomain())
        ]);
    }

    public static function getPaymentFormView(string $instanceId, float $amount = null, string $description = "")
    {
        if(!$amount) {
            return null;
        }

        if(empty($description)) {
            $description = "";
        }

        return Helpers::getView(PluginConfig::getPluginDir() . '/Modules/Views/form.view.php', [
            'merchantId' => SettingsDataStore::getOption('sm_ocl_payments_merchant_id'),
            'crc' => SettingsDataStore::getOption('sm_ocl_payments_crc'),
            'returnUrl' => SettingsDataStore::getOption('sm_ocl_payments_return_url'),
            'returnErrorUrl' => SettingsDataStore::getOption('sm_ocl_payments_return_err_url'),
            'formAction' => static::isSandboxEnabled() ? static::SANDBOX_FORM_ACTION : static::FORM_ACTION,
            'md5Sum' => static::getMd5Sum($amount),
            'amount' => $amount,
            'description' => $description,
            'language' => static::getLanguage(),
            'instanceId' => $instanceId,
            'consent' => SettingsDataStore::getOption('sm_ocl_payments_consent') ?: false,
            'requirePhone' => SettingsDataStore::getOption('sm_ocl_payments_require_phone') ?: false
        ]);
    }

    public static function appendModal(string $instanceId, float $amount, string $description)
    {
        add_action('wp_footer', function() use($instanceId, $amount, $description) {
            echo static::getPaymentFormView($instanceId, $amount, $description);
        });
    }

    public static function getMd5Sum(float $amount): ?string
    {
        $id = SettingsDataStore::getOption('sm_ocl_payments_merchant_id') ?: false;
        $crc = SettingsDataStore::getOption('sm_ocl_payments_crc') ?: false;
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
        return $amount . ' zł';
    }

    public function initMetabox()
    {
        //Init metaboxes 

        // $metabox = new Metabox('sm-plugin-shortcode', 'Shortcode', $location, 'side');

        // $metabox->setView(function(\WP_Post $post) {
        //     echo $view;
        // });
    }

    public function loadAssets()
    {
        $this->assetsManager->setVersion('0.1');
        $this->assetsManager->addStyle('sm-ocl-payments', PluginConfig::getPluginUrl() . '/assets/styles/style.css');
        $this->assetsManager->addScript('sm-ocl-payments', PluginConfig::getPluginUrl() . '/assets/js/main.js', ['jquery']);
        $this->hooksManager->addAction('wp_enqueue_scripts', $this, 'initScriptVars');
    
        $this->assetsManager->enqueue();

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
        return SettingsDataStore::getOption('sm_ocl_payments_save_orders');
    }

    public static function isSandboxEnabled(): bool
    {
        return SettingsDataStore::getOption('sm_ocl_payments_sandbox');
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
