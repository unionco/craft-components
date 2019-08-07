<?php

namespace unionco\components\console\controllers;

use craft\console\Controller;
use craft\helpers\StringHelper;
use unionco\components\Components;
use unionco\components\services\FieldsGenerator;
use unionco\components\console\controllers\GeneratorController;
use craft\helpers\Console;

class FieldController extends GeneratorController
{
    public function init()
    {
        $this->generator = Components::$plugin->fieldsGenerator;
        parent::init();
    }

    public function actionGenerate(string $name = null)
    {
        echo $this->ansiFormat('Field Generator', Console::FG_GREEN);
        echo PHP_EOL;
        
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
        $hasInstructions = FieldsGenerator::hasInstructions($this->opts['type']);
        if ($hasInstructions) {
            $this->opts['instructions'] = $this->prompt("Instructions for field: ");
        }

        $isComplex = FieldsGenerator::isComplex($this->opts['type']);
        if ($isComplex) {
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
        echo PHP_EOL . PHP_EOL;
        echo $this->ansiFormat('Preview', Console::FG_GREEN) . PHP_EOL . PHP_EOL;
        
        echo $this->ansiFormat('Field Name:', Console::FG_CYAN) . PHP_EOL;
        echo "\t$name\n";
        
        echo $this->ansiFormat('Field Handle:', Console::FG_CYAN) . PHP_EOL;
        echo "\t" . $this->opts['handle'] . PHP_EOL;

        echo $this->ansiFormat('Field Type:', Console::FG_CYAN) . PHP_EOL;
        echo "\t" . $this->opts['handle'] . PHP_EOL;

        if ($hasInstructions) {
            echo $this->ansiFormat('Field Instructions:', Console::FG_CYAN) . PHP_EOL;
            echo "\t" . $this->opts['instructions'] . PHP_EOL;
        }

        if ($isComplex) {
            echo $this->ansiFormat('Sub-Fields:', Console::FG_CYAN) . PHP_EOL;
            foreach ($this->opts['subFields'] as $sub) {
                echo "\t$sub\n";
            }
        }

        echo PHP_EOL . PHP_EOL;
        if (!$this->confirm('Proceed?')) {
            exit(1);
        }

        parent::actionGenerate($name);
    }
}
