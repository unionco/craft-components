<?php
/**
 * Components plugin for Craft CMS 3.x
 *
 * A component library for Craft cms.
 *
 * @link      https://github.com/unionco
 * @copyright Copyright (c) 2019 Union
 */

namespace unionco\components;

use Craft;
use craft\web\View;
use yii\base\Event;
use craft\base\Plugin;
use craft\fields\Assets;
use craft\fields\Matrix;
use craft\web\UrlManager;
use craft\services\Fields;
use craft\services\Plugins;
use craft\events\FieldEvent;
use craft\services\Elements;
use craft\events\PluginEvent;
use unionco\components\services\Page;
use craft\events\RegisterUrlRulesEvent;
use unionco\components\models\Settings;
use unionco\components\services\Manager;
use unionco\components\services\Generator;
use craft\web\twig\variables\CraftVariable;
use craft\events\RegisterTemplateRootsEvent;
use unionco\components\helpers\ConfigHelper;
use craft\events\RegisterComponentTypesEvent;
use craft\console\Application as ConsoleApplication;
use unionco\components\services\Base as BaseService;
use unionco\components\variables\ComponentsVariable;
use unionco\components\twigextensions\ComponentsTwigExtension;

/**
 * Class Components
 *
 * @author    Union
 * @package   Components
 * @since     0.0.1
 *
 * @property  BaseService $base
 */
class Components extends Plugin
{
    // Static Properties
    // =========================================================================

    /**
     * @var Components
     */
    public static $plugin;

    /** @var string */
    public static $componentsConfigDirectory = '';

    /** @var string */
    public static $componentsClassDirectory = '';

    /** @var string */
    public static $templatesEmbedDirectory = '';

    /** @var string */
    public static $templatesSystemDirectory = '';

    // Public Properties
    // =========================================================================

    /**
     * @var string
     */
    public $schemaVersion = '0.0.1';

    /**
     * @var string
     */
    public $hasCpSection = true;
    
    /**
     * @var string
     */
    public $hasCpSettings = true;

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        self::$plugin = $this;
        self::$componentsConfigDirectory = $this->getBasePath() . '/components/configs';
        self::$componentsClassDirectory = $this->getBasePath() . '/components';
        self::$templatesEmbedDirectory = $this->getBasePath() . '/templates/embed';
        self::$templatesSystemDirectory = $this->getBasePath() . '/templates/system';

        // Add in our console commands
        if (Craft::$app instanceof ConsoleApplication) {
            $this->controllerNamespace = 'unionco\components\console\controllers';
        }

        Craft::$app->view->registerTwigExtension(new ComponentsTwigExtension());

        $this->name = $this->settings->getPluginName();

        $this->setComponents([
            'manager' => Manager::class,
            'page' => Page::class,
            'generator' => Generator::class,
        ]);
        
        // Base template directory
        Event::on(
            View::class, 
            View::EVENT_REGISTER_SITE_TEMPLATE_ROOTS, 
            function (RegisterTemplateRootsEvent $e) {
                if (is_dir($baseDir = $this->getBasePath().DIRECTORY_SEPARATOR.'templates')) {
                    $e->roots[$this->id] = $baseDir;
                }
            }
        );

        Event::on(
            UrlManager::class,
            UrlManager::EVENT_REGISTER_CP_URL_RULES,
            function (RegisterUrlRulesEvent $event) {
                //
            }
        );

        Event::on(
            Elements::class,
            Elements::EVENT_REGISTER_ELEMENT_TYPES,
            function (RegisterComponentTypesEvent $event) {
                // 
            }
        );

        // Event::on(
        //     Fields::class,
        //     Fields::EVENT_BEFORE_SAVE_FIELD,
        //     function (FieldEvent $event) {
        //         $field = $event->field;

        //         if ($field instanceof Assets) {
        //             // 
        //             die('asset');
        //         }

        //         if ($field instanceof Matrix) {
        //             foreach ($field->getBlockTypes() as $blockType) {
        //                 foreach ($blockType->getFields() as $blockTypeField) {
        //                     if ($blockTypeField instanceof Assets) {
        //                         $blockTypeField = ConfigHelper::asset($blockTypeField);
        //                     }
        //                 }
        //             }
        //         }
        //     }
        // );

        Event::on(
            CraftVariable::class,
            CraftVariable::EVENT_INIT,
            function (Event $event) {
                /** @var CraftVariable $variable */
                $variable = $event->sender;
                $variable->set('components', ComponentsVariable::class);
            }
        );

        Event::on(
            Plugins::class,
            Plugins::EVENT_BEFORE_INSTALL_PLUGIN,
            function (PluginEvent $event) {
                if ($event->plugin === $this) {
                    if (!$this->manager->install()) {
                        throw new Exception("Error installing plugin. Components channel already exists", 1);
                    }
                }
            }
        );

        Event::on(
            Plugins::class,
            Plugins::EVENT_AFTER_SAVE_PLUGIN_SETTINGS,
            function (PluginEvent $event) {
                if ($event->plugin === $this) {
                    $this->manager->installComponents();
                }
            }
        );

        Craft::info(
            Craft::t(
                'components',
                '{name} plugin loaded',
                ['name' => $this->name]
            ),
            __METHOD__
        );
    }

    // Protected Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    protected function createSettingsModel()
    {
        return new Settings();
    }

    /**
     * @inheritdoc
     */
    protected function settingsHtml(): string
    {
        return Craft::$app->view->renderTemplate(
            'components/settings',
            [
                'settings' => $this->getSettings()
            ]
        );
    }
}
