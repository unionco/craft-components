<?php

namespace unionco\components\services\fields;

use Craft;
use unionco\components\models\FieldPrompt;
use unionco\components\services\FieldsGenerator;

class TagsFieldGenerator extends FieldsGenerator
{
    /** @inheritdoc */
    public function init()
    {
        parent::init();
        $this->type = 'tags';

        static::$prompts = array_merge(static::$prompts, [
            new FieldPrompt([
                'prompt' => 'Source',
                'handle' => 'source',
                'multi' => false,
                'options' => function () {
                    $tags = Craft::$app->getTags()->getAllTagGroups();
                    $sources = [];
                    foreach ($tags as $tag) {
                        $sources[$tag->handle] = $tag->name;
                    }
                    return $sources;
                },
                'transform' => function ($val) {
                    $tag = Craft::$app->getTags()->getTagGroupByHandle($val);
                    $uid = $tag->uid;
                    return "taggroup:{$uid}";
                },
            ]),
        ]);
    // taggroup:956130f3-1fac-46ef-b2a4-a4d932c022ad
    }

    /** @inheritdoc */
    public function replaceTemplateName($template): string
    {
        $template = parent::replaceTemplateName($template);
        $template = preg_replace('/{{FieldSource}}/', $this->values['source'], $template);
        
        return $template;
    }
}
