<?php

namespace unionco\components\console\controllers;

use craft\helpers\Console;
use craft\console\Controller;
use craft\helpers\StringHelper;
use craft\records\Field;
use yii\console\widgets\Table;
use unionco\components\Components;
use unionco\components\models\GeneratorOutput;
use unionco\components\services\FieldsGenerator;
use unionco\components\console\controllers\GeneratorController;
use unionco\components\models\FieldPrompt;

class ComponentController extends GeneratorController
{
    public function init()
    {
        $this->generator = Components::$plugin->componentsGenerator;

        parent::init();
    }

    public function actionGenerate(string $name = null)
    {
        $this->opts = [];
        foreach ($this->generator::$prompts as $i => $prompt) {
            if ($name && $i === 0) {
                $prompt->setValue($name);
                $this->opts['values']['name'] = $name;
                continue;
            }

            $value = null;
            $options = $prompt->getOptions();
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
            $val = $prompt->getValue();
            if (is_array($val)) {
                foreach ($val as $line) {
                    echo "\t$line\n";
                }
            } else {
                echo "\t" . $val . PHP_EOL . PHP_EOL;
            }
        }

        echo PHP_EOL . PHP_EOL;
        if (!$this->confirm('Proceed?')) {
            exit(1);
        }

        parent::actionGenerate($name);
    }
}
