<?php
/**
 * Components plugin for Craft CMS 3.x
 *
 * A component library for Craft cms.
 *
 * @link      https://github.com/unionco
 * @copyright Copyright (c) 2019 Union
 */

namespace unionco\components\helpers;

use Craft;
use craft\helpers\Json;
use craft\helpers\ArrayHelper;

class ConfigHelper
{
    /**
     * 
     */
    public static function resolve(array $fieldConfig)
    {
        $string = Json::encode($fieldConfig);

        $re = '/{{([\w\:]+)}}/i';

        $replaced = preg_replace_callback(
            $re,
            function ($matches) {
                if ($matches) {
                    [$type, $handle] = explode(":", $matches[1]);
                    switch ($type) {
                        case 'volume':
                            $volume = Craft::$app->getVolumes()->getVolumeByHandle($handle);
                            return implode(":", [$type, $volume->uid]);
                    }
                }
            },
            $string
        );

        return Json::decode($replaced);
    }

    /**
     * 
     */
    public static function supertable(array $fieldConfig, int $fieldId = null)
    {
        // this is a new field - bounce
        if (!$fieldId) {
            return $fieldConfig;
        }

        //
        $fieldsService = Craft::$app->getFields();
        $field = $fieldsService->getFieldById($fieldId);

        $blockTypes = $field->getBlockTypes();
        $blockType = array_pop($blockTypes);
        $blockTypeFields = $field->getBlockTypeFields();
        $fieldConfig['settings']['blockTypes'][$blockType->id] = $fieldConfig['settings']['blockTypes']['new'];
        unset($fieldConfig['settings']['blockTypes']['new']);

        foreach ($fieldConfig['settings']['blockTypes'][$blockType->id]['fields'] as $key => $field) {
            $blockTypeField = ArrayHelper::where($blockTypeFields, 'handle', $field['handle']);

            if ($blockTypeField) {
                $fieldId = array_pop($blockTypeField)->id;
                ArrayHelper::rename(
                    $fieldConfig['settings']['blockTypes'][$blockType->id]['fields'],
                    $key,
                    $fieldId
                );
            } else {
                $keepField = $fieldConfig['settings']['blockTypes'][$blockType->id]['fields'][$key];
                $newKey = explode("-", $key)[1];
                $fieldConfig['settings']['blockTypes'][$blockType->id]['fields']["new".$newKey] = $keepField;
                unset($fieldConfig['settings']['blockTypes'][$blockType->id]['fields'][$key]);
            }
        }

        return $fieldConfig;
    }
}