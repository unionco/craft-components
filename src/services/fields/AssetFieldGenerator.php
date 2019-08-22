<?php

namespace unionco\components\services\fields;

use Craft;
use unionco\components\models\FieldPrompt;
use unionco\components\services\FieldsGenerator;

abstract class AssetFieldGenerator extends FieldsGenerator
{
    /** @var string */
    static $assetPath = '';

    /** @var string */
    static $assetType = '';


    /** @inheritdoc */
    public function init()
    {
        parent::init();
        $this->type = 'asset';
        static::$prompts = array_merge(static::$prompts, [
            new FieldPrompt([
                'prompt' => 'Volume',
                'handle' => 'volume',
                'required' => true,
                // 'multi' => true, // Limited to one volume for now
                'options' => function () {
                    $available = Craft::$app->getVolumes()->getAllVolumes();
                    if (!$available) {
                        echo "You must create volumes before creating an Asset Field";
                        die;
                    }
                    $volumes = [];

                    foreach ($available as $volume) {
                        $volumes[$volume->handle] = 'volume:' . $volume->uid;
                    }
                    return $volumes;
                },
                'transform' => function ($handle) {
                    return 'volume:' . Craft::$app->getVolumes()->getVolumeByHandle($handle)->uid;
                }
            ]),
            new FieldPrompt([
                'prompt' => 'Limit number of assets',
                'handle' => 'limit',
            ]),
        ]);
    }

    /**
     * @inheritdoc
     */
    public function replaceTemplateName($template): string
    {
        // var_dump($this->values); die;
        $template = parent::replaceTemplateName($template);
        $template = preg_replace('/{{FieldPath}}/', static::$assetPath, $template);
        $template = preg_replace('/{{FieldAssetType}}/', static::$assetType, $template);
        $template = preg_replace('/{{FieldVolume}}/', $this->values['volume'], $template);
        $template = preg_replace('/{{FieldLimit}}/', $this->values['limit'], $template);

        return $template;
    }
}
