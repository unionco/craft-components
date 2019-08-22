<?php

namespace unionco\components\console\controllers;

use craft\console\Controller;
use craft\helpers\StringHelper;
use unionco\components\Components;
use unionco\components\services\FieldsGenerator;
use unionco\components\console\controllers\GeneratorController;
use craft\helpers\Console;
use ReflectionClass;

class FieldController extends GeneratorController
{
    public function init()
    {
        $this->generator = null; //Components::$plugin->fieldsGenerator;
        parent::init();
    }

    /**
     * Extends the yii select method to allow multiple selections
     */
    public function multi($prompt, $options)
    {
        $values = [];
        $available = $options;
        $selection = null;
        while ($selection != 'done') {
            if ($selection) {
                unset($available[$selection]);
                echo "Selected:\n";
                $values[] = $selection;
                foreach ($values as $value) {
                    echo "\t$value\n";
                }
            }

            $selection = $this->select(
                $prompt,
                array_merge($available, ['done' => 'Finished'])
            );
        }
        return $values;
    }

    public function actionGenerate(string $name = null)
    {
        echo $this->ansiFormat('Field Generator', Console::FG_GREEN);
        echo PHP_EOL;

        // Field Type
        $this->opts['type'] = $this->select("Select a field type: ", FieldsGenerator::fieldTypes());

        // See if a custom generator exists for this field type
        try {
            $fieldGeneratorClassName = '\\unionco\\components\\services\\fields\\' . ucFirst($this->opts["type"]) . 'FieldGenerator';
            $fieldGeneratorClass = new ReflectionClass($fieldGeneratorClassName);
            $this->generator = $fieldGeneratorClass->newInstance();
            $prompts = $this->generator::$prompts;
            // var_dump($methods); die;

        } catch (\Throwable $e) {
            var_dump($e);
            die;
        }

        foreach ($prompts as $prompt) {
            $options = $prompt->getOptions();
            $value = null;
            if ($options) {
                if ($prompt->getMulti()) {
                    $value = $this->multi($prompt->getPrompt(), $options());
                } else {
                    $value = $this->select($prompt->getPrompt(), $options());
                }
            } else {
                $value = $this->prompt($prompt->getPrompt(), [
                    'required' => $prompt->getRequired(),
                    'default' => $prompt->getDefault(),
                ]);
            }
            $prompt->setValue($value);
            $this->opts['values'][$prompt->getHandle()] = $prompt->getValue();
        }

        echo PHP_EOL . PHP_EOL;
        echo $this->ansiFormat('Preview', Console::FG_GREEN) . PHP_EOL . PHP_EOL;

        foreach ($this->generator::$prompts as $prompt) {
            echo $this->ansiFormat($prompt->getPrompt(), Console::FG_CYAN) . PHP_EOL;
            echo "\t" . $prompt->getValue() . PHP_EOL . PHP_EOL;
        }

        echo PHP_EOL . PHP_EOL;
        if (!$this->confirm('Proceed?')) {
            exit(1);
        }

        parent::actionGenerate();
    }
}
