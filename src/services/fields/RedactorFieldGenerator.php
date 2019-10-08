<?php

namespace unionco\components\services\fields;

use Craft;
use craft\helpers\FileHelper;
use craft\helpers\Html;
use craft\helpers\StringHelper;
use unionco\components\models\FieldPrompt;
use unionco\components\services\FieldsGenerator;

class RedactorFieldGenerator extends FieldsGenerator
{
    /** @inheritdoc */
    public function init()
    {
        parent::init();
        $this->type = 'redactor';

        static::$prompts = array_merge(static::$prompts, [
            new FieldPrompt([
                'prompt' => 'Redactor Config',
                'handle' => 'redactorConfig',
                'options' => function () {
                    return $this->_getCustomConfigOptions('redactor');
                },
                'transform' => function ($val) {
                    return empty($val) ? 'default' : $val[1];
                },
            ]),
            new FieldPrompt([
                'prompt' => 'Available Volumes',
                'handle' => 'availableVolumes',
                'options' => function () {
                    $sources = [
                        'all' => '*',
                    ];
                    foreach ($this->_getVolumeOptions() as $key => $volume) {
                        $sources[$key] = $volume;
                    }
                    return $sources;
                },
                'multi' => true,
                'transform' => function ($val) {
                    if (!is_array($val)) {
                        $val = [$val];
                    }

                    if (in_array('all', $val)) {
                        return "'*'";
                    }

                    $output = '';

                    $volumeService = Craft::$app->getVolumes();
                    foreach ($val as $volume) {
                        $uid = $volumeService->getVolumeByHandle(StringHelper::camelCase($volume))->uid;
                        $output .= "\n  - {$uid}";
                    }
                    return $output;
                },
            ]),
            new FieldPrompt([
                'prompt' => 'Available Transforms',
                'handle' => 'availableTransforms',
                'options' => function () {
                    $sources = [
                        'all' => '*',
                    ];
                    foreach ($this->_getTransformOptions() as $key => $volume) {
                        $sources[$key] = $volume;
                    }
                    return $sources;
                },
                'multi' => true,
                'transform' => function ($val) {
                    if (!is_array($val)) {
                        $val = [$val];
                    }

                    if (in_array('all', $val)) {
                        return "'*'";
                    }

                    $output = '';
                    $transformService = Craft::$app->getAssetTransforms();
                    foreach ($val as $transform) {
                        $uid = $transformService->getTransformByHandle(StringHelper::camelCase($transform))->uid;
                        $output .= "\n  - {$uid}";
                    }
                    return $output;
                },
            ])
        ]);
    }

    /** @inheritdoc */
    public function replaceTemplateName($template): string
    {
        $template = parent::replaceTemplateName($template);
        $template = preg_replace('/{{FieldRedactorConfig}}/', $this->values['redactorConfig'], $template);
        $template = preg_replace('/{{FieldAvailableVolumes}}/', $this->values['availableVolumes'], $template);
        $template = preg_replace('/{{FieldAvailableTransforms}}/', $this->values['availableTransforms'], $template);

        return $template;
    }

    /**
     * Returns the available Redactor config options.
     *
     * @param string $dir The directory name within the config/ folder to look for config files
     * @return array
     */
    private function _getCustomConfigOptions(string $dir): array
    {
        $options = ['' => Craft::t('redactor', 'Default')];
        $path = Craft::$app->getPath()->getConfigPath() . DIRECTORY_SEPARATOR . $dir;

        if (is_dir($path)) {
            $files = FileHelper::findFiles($path, [
                'only' => ['*.json'],
                'recursive' => false
            ]);

            foreach ($files as $file) {
                $options[str_replace(".json", "", pathinfo($file, PATHINFO_BASENAME))] = pathinfo($file, PATHINFO_FILENAME);
            }
        }

        return $options;
    }

    /**
     *
     */
    private function _getVolumeOptions(): array
    {
        $volumeOptions = [];
        /** @var $volume Volume */
        foreach (Craft::$app->getVolumes()->getPublicVolumes() as $volume) {
            if ($volume->hasUrls) {
                $name = Html::encode($volume->name);
                $volumeOptions[$name] = $volume->uid;
            }
        }
        return $volumeOptions;
    }

    /**
     *
     */
    private function _getTransformOptions(): array
    {
        $transformOptions = [];
        foreach (Craft::$app->getAssetTransforms()->getAllTransforms() as $transform) {
            $name = Html::encode($transform->name);
            $transformOptions[$name] = $transform->uid;
        }
        return $transformOptions;
    }
}
