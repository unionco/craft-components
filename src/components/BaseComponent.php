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
use craft\base\Field;
use craft\base\FieldInterface;
use craft\elements\Entry;
use craft\helpers\StringHelper;
use craft\models\EntryType;
use craft\models\FieldLayout;
use craft\models\FieldLayoutTab;
use craft\models\Section;
use unionco\components\helpers\ConfigHelper;

/**
 * @author    Union
 * @package   Components
 * @since     0.0.1
 */
abstract class BaseComponent implements ComponentInterface
{
    /**
     * @var array config
     */
    protected $config;

    // Static
    // =========================================================================

    /**
     * Returns the display name of this class.
     *
     * @return string The display name of this class.
     */
    public static function displayName(): string
    {
        $classNameParts = explode('\\', static::class);
        return array_pop($classNameParts);
    }

    /**
     * Returns the ref used for field handles
     *
     * @return string The ref handle
     */
    public static function ref(): string
    {
        return self::displayName();
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

    /**
     *
     */
    public function saveField(array $fieldConfig, int $fieldId = null): FieldInterface
    {
        $fieldsService = Craft::$app->getFields();

        // apply replace
        $fieldConfig = ConfigHelper::resolve($fieldConfig);

        // upadte supertable ids
        if ($fieldConfig['type'] === "verbb\\supertable\\fields\\SuperTableField") {
            $fieldConfig = ConfigHelper::supertable($fieldConfig, $fieldId);
        }

        $field = $fieldsService->createField([
            'id' => $fieldId ?? null,
            'type' => $fieldConfig['type'],
            'groupId' => $fieldConfig['fieldGroup'],
            'name' => $fieldConfig['name'],
            'handle' => $fieldConfig['handle'],
            'instructions' => $fieldConfig['instructions'],
            'searchable' => (bool)$fieldConfig['searchable'] ?? true,
            'translationMethod' => $fieldConfig['translationMethod'] ?? Field::TRANSLATION_METHOD_NONE,
            'translationKeyFormat' => $fieldConfig['translationKeyFormat'],
            'settings' => $fieldConfig['settings'],
        ]);

        try {
            //code...
            $fieldsService->saveField($field);
        } catch (\Throwable $th) {
            throw $th;
        }

        return $field;
    }
}
