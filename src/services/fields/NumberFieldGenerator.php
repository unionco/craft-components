<?php

namespace unionco\components\services\fields;

use unionco\components\models\FieldPrompt;
use unionco\components\services\FieldsGenerator;

class NumberFieldGenerator extends FieldsGenerator
{
    /** @inheritdoc */
    public function init()
    {
        parent::init();
        $this->type = 'number';
        static::$prompts = array_merge(static::$prompts, [
            new FieldPrompt([
                'prompt' => 'Default value',
                'handle' => 'default',
            ]),
            new FieldPrompt([
                'prompt' => 'Minimum (empty for none)',
                'handle' => 'min',
                'transform' => function ($val) {
                    return empty($val) ? null : $val;
                },
            ]),
            new FieldPrompt([
                'prompt' => 'Maximum (empty for none)',
                'handle' => 'max',
                'transform' => function ($val) {
                    return empty($val) ? null : $val;
                },
            ]),
            new FieldPrompt([
                'prompt' => 'Decimals (empty for none)',
                'handle' => 'decimals',
                'transform' => function ($val) {
                    return empty($val) ? null : $val;
                },
            ]),
        ]);
    }

    /** @inheritdoc */
    public function replaceTemplateName($template): string
    {
        $template = parent::replaceTemplateName($template);
        $template = preg_replace('/{{FieldDefault}}/', $this->values['default'], $template);
        $template = preg_replace('/{{FieldMin}}/', $this->values['min'], $template);
        $template = preg_replace('/{{FieldMax}}/', $this->values['max'], $template);
        $template = preg_replace('/{{FieldDecimals}}/', $this->values['decimals'], $template);

        return $template;
    }
}
