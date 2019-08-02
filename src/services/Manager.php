<?php
/**
 * Components plugin for Craft CMS 3.x
 *
 * A component library for Craft cms.
 *
 * @link      https://github.com/unionco
 * @copyright Copyright (c) 2019 Union
 */

namespace unionco\components\services;

use Craft;
use craft\base\Component;
use craft\base\Field;
use craft\fields\Entries;
use craft\helpers\StringHelper;
use craft\models\Section;
use craft\models\EntryType;
use craft\models\FieldLayoutTab;
use craft\models\Section_SiteSettings;
use unionco\components\Components;
use unionco\components\enum\Components as Comps;
use unionco\components\ComponentInterface;

/**
 * @author    Union
 * @package   Components
 * @since     0.0.1
 */
class Manager extends Component
{
    /**
     * @var ComponentInterface[]
     */
    private $_components = [];

    /**
     * @var Section|null
     */
    private $_section;

    /**
     * @var EntryType[]
     */
    private $_types = [];

    /**
     * @var Entries
     */
    private $_pageBuilder;

    // Public Methods
    // =========================================================================
    
    /**
     * 
     */
    public function init()
    {
        $this->_section = Craft::$app->getSections()->getSectionByHandle('components');
        
        if ($this->_section) {
            $this->_types = $this->_section->getEntryTypes();   
        }
    }

    /**
     * 
     */
    public function install(): bool
    {
        if (!$this->_section) {
            if (!$this->createSection()) {
                return false;
            }
        }

        if (!$this->createPageBuilder()) {
            return false;
        }

        if (!$this->addPageBuilderToSections()) {
            return false;
        }

        return true;
    }

    /**
     * 
     */
    public function installComponents()
    {
        $settings = Components::$plugin->getSettings();
        $toInstall = $settings->installedComponents;
        $allComps = $this->getAllComponents();

        foreach ($toInstall as $key => $component) {
            if ((bool) $component['installed']) {
                echo "Install: " . $allComps[$key]['namespace'] . "<br/>";
                $this->installComponent($allComps[$key]);
            } else {
                // todo check usage before uninstall
                echo "Uninstall: " . $allComps[$key]['namespace'] . "<br/>";
            }
        }

        // var_dump($toInstall);die;
    }

    /**
     * 
     */
    public function installComponent($component)
    {
        $entryType = array_filter($this->_types, function ($type) use($component) {
            return $type->handle === $component['handle'];
        });
        
        if (!$entryType) {
            $comp = new $component['namespace']();
            
            try {
                $result = $comp->install($this->_section);
            } catch (\Throwable $th) {
                throw $th;
            }

            return $result;
        } else {
            $comp = new $component['namespace']();
            
            try {
                $result = $comp->install($this->_section, array_pop($entryType));
            } catch (\Throwable $th) {
                throw $th;
            }

            return $result;
        }

        return true;
    }

    /**
     * 
     */
    public function getAllComponents(): array
    {
        if (!$this->_components) {
            // cache it
            $components = Comps::ALL;

            foreach ($components as $key => $component) {
                $this->_components[] = $this->getComponentData($component);
            }
        }

        return $this->_components;
    }

    /**
     * 
     */
    public function getComponentData(string $name): array
    {
        // is the component installed
        $handle = StringHelper::toCamelCase($name);

        $entryType = array_filter($this->_types, function ($type) use($handle) {
            return $type->handle === $handle;
        });

        $component = [
            'id' => null,
            'name' => $name,
            'handle' => $handle,
            'namespace' => "unionco\\components\\components\\" . ucfirst($handle),
            'installed' => false
        ];

        if ($entryType) {
            $entryType = array_pop($entryType);
            $component['id'] = $entryType->id;
            $component['installed'] = true;
        }

        return $component;
    }

    /**
     * 
     */
    private function createSection(): bool
    {
        $section = new Section([
            'name' => 'Components',
            'handle' => 'components',
            'type' => Section::TYPE_CHANNEL,
            'propagationMethod' => 'none',
            'siteSettings' => array_map(function($site) {
                return new Section_SiteSettings([
                    'siteId' => $site->id,
                    'enabledByDefault' => true,
                    'hasUrls' => false,
                    'uriFormat' => '',
                    'template' => '',
                ]);
            }, Craft::$app->sites->getAllSites())
        ]);

        try {
            Craft::$app->sections->saveSection($section);
            $this->_section = $section;
        } catch (\Throwable $th) {
            //throw $th;
            return false;
        }
        
        return true;
    }

    /**
     * 
     */
    private function createPageBuilder(): bool
    {
        $fieldsService = Craft::$app->getFields();
        $field = $fieldsService->getFieldByHandle('pageBuilder');

        if ($field) {
            $this->_pageBuilder = $field;
            return true;
        }

        $field = $fieldsService->createField([
            'id' => null,
            'type' => "craft\\fields\\Entries",
            'groupId' => 1,
            'name' => "Page Builder",
            'handle' => "pageBuilder",
            'instructions' => Craft::t('components', 'Use this field to build out your page from prebuilt components.'),
            'searchable' => false,
            'translationMethod' => Field::TRANSLATION_METHOD_SITE,
            'translationKeyFormat' => null,
            'settings' => [
                'limit' => 1,
                'localizeRelations' => true,
                'selectionLabel' => 'Choose Component',
                'source' => null,
                'allowMultipleSources' => false,
                'sources' => ["section:{$this->_section->uid}"],
                'targetSiteId' => null,
                'viewMode' => null,
            ],
        ]);

        try {
            //code...
            $fieldsService->saveField($field);
            $this->_pageBuilder = $field;
        } catch (\Throwable $th) {
            // throw $th;
            return false;
        }
        
        return true;
    }

    /**
     * 
     */
    private function addPageBuilderToSections(): bool
    {
        $sectionService = Craft::$app->getSections();
        $sections = $sectionService->getAllSections();

        foreach ($sections as $key => $section) {
            $siteSettings = $section->getSiteSettings();
            $hasUrls = array_filter($siteSettings, function ($setting) {
                return $setting->hasUrls;
            });

            if ($hasUrls) {
                $entryTypes = $section->getEntryTypes();
    
                foreach ($entryTypes as $key => $entryType) {
                    // add field to entry type
                    $fieldLayout = $entryType->getFieldLayout();
                    $tabs = $fieldLayout->getTabs() ?? [];
                    
                    $hasPageBuilder = array_filter($tabs, function ($tab) {
                        return $tab->name === 'Page Builder';
                    });

                    if (!$hasPageBuilder) {
                        // create a new tab for page builder field
                        $tab = new FieldLayoutTab();
                        $tab->name = 'Page Builder';
                        $tab->sortOrder = count($tabs) > 1 ? count($tabs) - 1 : 1;
                        $tab->setFields([$this->_pageBuilder]);
                        $tabs[] = $tab;

                        $fieldLayout->setTabs($tabs);
                        $fieldLayout->setFields([$this->_pageBuilder]);

                        $entryType->setFieldLayout($fieldLayout);

                        $sectionService->saveEntryType($entryType);
                    }
                }
            }
        }

        return true;

    }
}
