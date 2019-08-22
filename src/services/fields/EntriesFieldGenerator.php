<?php

namespace unionco\components\services\fields;

use Craft;
use unionco\components\models\FieldPrompt;
use unionco\components\services\FieldsGenerator;

class EntriesFieldGenerator extends FieldsGenerator
{
    /** @inheritdoc */
    public function init()
    {
        parent::init();
        $this->type = 'entries';

        static::$prompts = array_merge(static::$prompts, [
            new FieldPrompt([
                'prompt' => 'Sources',
                'handle' => 'sources',
                'multi' => true,
                'options' => function () {
                    $sections = Craft::$app->getSections()->getAllSections();
                    $sources = [
                        'all' => '*',
                    ];
                    foreach ($sections as $section) {
                        $sources[$section->handle] = "section:$section->uid";
                    }
                    return $sources;
                },
                'transform' => function ($val) {
                    if (!is_array($val)) {
                        $val = [$val];
                    }
                    
                    if (in_array('all', $val)) {
                        return "'*'";
                    }
                    $output = '';
                    foreach ($val as $sectionHandle) {
                        $section = Craft::$app->getSections()->getSectionByHandle($sectionHandle);
                        $output .= "\n  - 'section:{$section->uid}'";
                    }
                    return $output;
                },
            ]),
            new FieldPrompt([
                'prompt' => 'Limit number of assets',
                'handle' => 'limit',
            ]),
        ]);
    }

    /** @inheritdoc */
    public function replaceTemplateName($template): string
    {
        $template = parent::replaceTemplateName($template);
        $template = preg_replace('/{{FieldSources}}/', $this->values['sources'], $template);
        $template = preg_replace('/{{FieldLimit}}/', $this->values['limit'], $template);

        return $template;
    }
}
