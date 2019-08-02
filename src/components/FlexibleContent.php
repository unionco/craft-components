<?php
/**
 * Components plugin for Craft CMS 3.x
 *
 * A component library for Craft cms.
 *
 * @link      https://github.com/unionco
 * @copyright Copyright (c) 2019 Union
 */
namespace unionco\components\components;

use Craft;
// use craft\base\FieldInterface;
use craft\elements\Entry;
use craft\helpers\StringHelper;
use craft\models\EntryType;
use craft\models\FieldLayout;
use craft\models\FieldLayoutTab;
use craft\models\Section;
use Symfony\Component\Yaml\Yaml;

/**
 * @author    Union
 * @package   Components
 * @since     0.0.1
 */
class FlexibleContent extends BaseComponent implements ComponentInterface
{
    /**
     * @var array config
     */
    private $config;

    // Static
    // =========================================================================

    /**
     * Returns the display name of this class.
     *
     * @return string The display name of this class.
     */
    public static function displayName(): string
    {
        return 'Flexible Content';
    }
    
    /**
     * Returns the ref used for field handles
     *
     * @return string The ref handle
     */
    public static function ref(): string
    {
        return 'fc';
    }

    // Public
    // =========================================================================

    /**
     * 
     */
    public function __construct()
    {
        $file = __DIR__ . "/configs/FlexibleContent.yaml";
        $this->config = Yaml::parse(file_get_contents($file));
    }

    /**
     * 
     */
    public function install(Section $section, EntryType $entryType = null): bool
    {
        $sectionService = Craft::$app->getSections();
        $fieldService = Craft::$app->getFields();

        if (!$entryType) {
            $entryType = new EntryType();
            $entryType->sectionId = $section->id;
            $entryType->name = $this->displayName();
            $entryType->handle = StringHelper::toCamelCase($this->displayName());
            $entryType->hasTitleField = true;
            $entryType->titleLabel = Craft::t('components', 'Title');
            $entryType->titleFormat = null;
            
            $fieldLayout = new FieldLayout();
            $fieldLayout->type = Entry::class;
        } else {
            $fieldLayout = $entryType->getFieldLayout();
        }

        $tabs = [];
        $fields = [];

        foreach ($this->config['tabs'] as $tabConfig) {
            // create a tab for the field layout
            $tabFields = [];
            foreach ($tabConfig['fields'] as $fieldHandle => $fieldConfig) {
                // create the field if it doesnt exist
                if ($field = $fieldService->getFieldByHandle($fieldConfig['handle'])) {
                    // upgrade the field
                    $field = $this->saveField($fieldConfig, $field->id);
                } else {
                    // create the field depending on the type
                    $field = $this->saveField($fieldConfig);
                }
                $tabFields[] = $field;
                $fields[] = $field;
            }

            // add field to entry type
            $tabs = $fieldLayout->getTabs() ?? [];

            // add the field to the tab
            $tab = array_search($tabConfig['name'], array_column($tabs, 'name'));
            if (!$tab) {
                $tab = new FieldLayoutTab();
                $tab->name = $tabConfig['name'];
                $tab->sortOrder = $tabConfig['sortOrder'];
            }
            
            $tab->setFields($tabFields);
            $tabs[] = $tab;
        }

        $fieldLayout->setTabs($tabs);
        $fieldLayout->setFields($fields);
        $entryType->setFieldLayout($fieldLayout);

        $sectionService->saveEntryType($entryType);

        return true;
    }
}