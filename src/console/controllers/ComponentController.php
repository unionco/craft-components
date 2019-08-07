<?php

namespace unionco\components\console\controllers;

use craft\helpers\Console;
use craft\console\Controller;
use yii\console\widgets\Table;
use unionco\components\Components;
use unionco\components\models\GeneratorOutput;
use unionco\components\services\FieldsGenerator;
use unionco\components\console\controllers\GeneratorController;

class ComponentController extends GeneratorController
{
    // public $fields = [];

    public function init()
    {
        $this->generator = Components::$plugin->componentsGenerator;
        parent::init();
    }

    public function actionGenerate(string $name = null)
    {
        $this->opts = [];
        if (!$name) {
            $name = $this->prompt("Give the component a name: ", [
                'required' => true,
            ]);
        }

        $availableFields = FieldsGenerator::getFields();
        $this->opts['fields'] = [];
        $field = null;
        while ($field != 'done') {
            if ($field) {
                unset($availableFields[$field]);
                echo "Selected fields:\n";
                $this->opts['fields'][] = $field;
                foreach ($this->opts['fields'] as $f) {
                    echo "\t$f\n";
                }
            }

            $field = $this->select('Add fields: ', array_merge($availableFields, ['done' => 'Finished adding fields']));
        }

        echo PHP_EOL . PHP_EOL;
        echo $this->ansiFormat('Preview', Console::FG_GREEN) . PHP_EOL . PHP_EOL;
        
        echo $this->ansiFormat('Component Name:', Console::FG_CYAN) . PHP_EOL;
        echo "\t$name\n";

        echo $this->ansiFormat('Component Fields:', Console::FG_CYAN) . PHP_EOL;
        foreach ($this->opts['fields'] as $field) {
            echo "\t$field\n";
        }

        echo PHP_EOL . PHP_EOL;
        if (!$this->confirm('Proceed?')) {
            exit(1);
        }

        parent::actionGenerate($name);
    }
}
