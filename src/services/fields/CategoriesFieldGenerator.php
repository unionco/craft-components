<?php

// group:ad63504b-2947-4c28-b6c2-8b99ed2b774a

namespace unionco\components\services\fields;

use Craft;
use unionco\components\models\FieldPrompt;
use unionco\components\services\FieldsGenerator;

class CategoriesFieldGenerator extends FieldsGenerator
{
    /** @inheritdoc */
    public function init()
    {
        parent::init();
        $this->type = 'categories';

        static::$prompts = array_merge(static::$prompts, [
            new FieldPrompt([
                'prompt' => 'Source',
                'handle' => 'source',
                'multi' => false,
                'options' => function () {
                    $categories = Craft::$app->getCategories()->getAllGroups();
                    $sources = [];
                    foreach ($categories as $cat) {
                        $sources[$cat->handle] = $cat->name;
                    }
                    return $sources;
                },
                'transform' => function ($val) {
                    $cat = Craft::$app->getCategories()->getGroupByHandle($val);
                    $uid = $cat->uid;
                    return "group:{$uid}";
                },
            ]),
        ]);
    }

    /** @inheritdoc */
    public function replaceTemplateName($template): string
    {
        $template = parent::replaceTemplateName($template);
        $template = preg_replace('/{{FieldSource}}/', $this->values['source'], $template);
        
        return $template;
    }
}
