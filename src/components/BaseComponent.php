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
use unionco\components\helpers\ConfigHelper;

/**
 * @author    Union
 * @package   Components
 * @since     0.0.1
 */
abstract class BaseComponent implements ComponentInterface
{
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