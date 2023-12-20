<?php

use SM\OclPayments\Modules\Admin\DataStores\SettingsDataStore;
use SM\Core\Hooks\HooksManager;
use SM\Core\Admin\ACFBuilder;
use SM\Core\Admin\BlockManager;
use SM\Core\Admin\Metabox;
use SM\Core\Admin\ShortcodeManager;
use SM\Core\Assets\AssetsManager;
use SM\Core\Helpers\Helpers;
use SM\Core\Types\PostType;
use SM\OclPayments\Modules\Admin\SMPluginAdminMenu;
use SM\OclPayments\Modules\PostTypes\SMPluginType;
use SM\OclPayments\Config\PluginConfig;

class SMPlugin
{
    protected ?HooksManager $hooksManager = null;
    protected ?PostType $postType = null;
    protected ?AssetsManager $assetsManager = null;

    public function __construct()
    {
        $this->hooksManager = new HooksManager();
        $this->postType = new SMPluginType();
        $this->assetsManager = new AssetsManager();
        $this->init();
    }

    private function init(): void
    {
        (new SMPluginAdminMenu())->init();
        $this->postType->init();
        $this->postType->addToPolylang();
        $this->initFields();
        $this->initBlocks();
        $this->initShortcode();
        $this->initMetabox();
        $this->loadAssets();
        $this->initLocalization();
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
        //Init Shortcodes

        // ShortcodeManager::createShortcode('sm-plugin', function($atts) {
        //     $defaults = [
        //         'id' => 0
        //     ];
        // });
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
        $this->assetsManager->addStyle('sm-plugin', PluginConfig::getPluginUrl() . '/assets/styles/style.css');
        $this->assetsManager->addScript('sm-plugin', PluginConfig::getPluginUrl() . '/assets/js/main.js', ['jquery']);

        $this->assetsManager->enqueue();
    }

    public static function getSettings()
    {
        // return [
        //     'setting' => SettingsDataStore::getOption('sm_setting'),
        // ];
    }

    public function initLocalization()
    {
        // $locale = apply_filters('plugin_locale', determine_locale(), PluginConfig::getTextDomain());
        // load_textdomain(PluginConfig::getTextDomain(), PluginConfig::getPluginDir() . '/languages/' . $locale . '.mo');
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
