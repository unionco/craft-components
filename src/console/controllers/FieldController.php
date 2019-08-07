<?php

namespace unionco\components\console\controllers;

use craft\console\Controller;
use craft\helpers\StringHelper;
use unionco\components\Components;
use unionco\components\services\FieldsGenerator;
use unionco\components\console\controllers\GeneratorController;

class FieldController extends GeneratorController
{
    public function init()
    {
        $this->generator = Components::$plugin->fieldsGenerator;
        parent::init();
    }

    public function actionGenerate(string $name = null)
    {
        $this->opts = [];
        if (!$name) {
            $name = $this->prompt("Give the field a name: ", [
                'required' => true,
            ]);
        }

        // Field Handle
        $this->opts['handle'] = $this->prompt('Give the field a handle: ', [
            'required' => true,
            'default' => StringHelper::toCamelCase($name),
        ]);

        // Field Type
        $this->opts['type'] = $this->select("Select a field type: ", FieldsGenerator::fieldTypes());

        // Field Instructions
        if (FieldsGenerator::hasInstructions($this->opts['type'])) {
            $this->opts['instructions'] = $this->prompt("Instructions for field: ");
        }

        if ($this->opts['type'] === 'supertable' || $this->opts['type'] === 'matrix') {
            $availableFields = FieldsGenerator::getFields();
            $this->opts['subFields'] = [];
            $field = null;
            while ($field != 'done') {
                if ($field) {
                    unset($availableFields[$field]);
                    echo "Selected fields:\n";
                    $this->opts['subFields'][] = $field;
                    foreach ($this->opts['subFields'] as $f) {
                        echo "\t$f\n";
                    }
                }
    
                $field = $this->select(
                    'Add fields: ',
                    array_merge($availableFields, ['done' => 'Finished adding sub-fields'])
                );
            }
        }

        parent::actionGenerate($name);
    }
}
