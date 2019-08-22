<?php

namespace unionco\components\services\fields;

use Craft;
use unionco\components\models\FieldPrompt;
use unionco\components\services\FieldsGenerator;

class UsersFieldGenerator extends FieldsGenerator
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
                    $groups = Craft::$app->getUserGroups()->getAllGroups();
                    $sources = [
                        'all' => '*',
                        'admins' => 'admins',
                    ];
                    foreach ($groups as $group) {

                        $sources[$group->handle] = "section:{$group->uid}";
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
                    foreach ($val as $groupHandle) {
                        $group = Craft::$app->getUserGroups()->getGroupByHandle($groupHandle);
                        $output .= "\n  - 'group:{$group->uid}'";
                    }
                    return $output;
                },
            ]),
            new FieldPrompt([
                'prompt' => 'Limit number of users',
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

// - admins
// - 'group:10942228-e351-4009-b2d9-c3496e684008'