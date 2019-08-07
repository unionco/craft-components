<?php

namespace unionco\components\console\controllers;

use yii\helpers\ArrayHelper;
use craft\console\Controller;
use yii\helpers\StringHelper;
use yii\console\widgets\Table;
use unionco\components\Components;
use unionco\components\models\GeneratorOutput;
use craft\helpers\Console;

class ComponentController extends Controller
{
    public $vue = false;

    public function options($actionId)
    {
        $options = parent::options($actionId);

        // Remove options we end up overriding
        // ArrayHelper::removeValue($options, 'vue');

        $options[] = 'vue';

        return $options;
    }

    public function actionGenerate($name)
    {
        // echo "name: $name\n";
        // echo "vue: " . ($this->vue ? "true" : "false") . PHP_EOL;
        $generator = Components::$plugin->generator;
        // $camelCase = $generator->nameToCamelCase($name);
        $output = $generator->generateComponent($name, []);
        // echo "camelCase: $camelCase" . PHP_EOL;

        [$rows, $warnings, $errors] = $this->outputToRows($output);
        echo Table::widget([
            'headers' => ['Action', 'File Name', 'Absolute Path', 'Errors', 'Warnings', 'Success'],
            'rows' => $rows,
        ]);

        if ($warnings) {
            echo $this->ansiFormat("Warnings\n", Console::FG_YELLOW);
            echo Table::widget([
                'headers' => ['Action', 'Warnings'],
                'rows' => $warnings,
            ]);
        }

        if ($errors) {
            echo $this->ansiFormat("Errors\n", Console::FG_RED);
            echo Table::widget([
                'headers' => ['Action', 'Errors'],
                'rows' => $errors,
            ]);
        }
    }

    /**
     * @param GeneratorOutput[] $output
     * @return array
     */
    protected function outputToRows(array $output)
    {
        $rows = [];
        $warnings = [];
        $errors = [];
        foreach ($output as $row) {
            $rowWarnings = '';
            if (count($row->warnings)) {
                $rowWarnings = count($row->warnings); //$this->ansiFormat(count($row->warnings), Console::FG_YELLOW);
                // $warnings[$row->action] = $row->warnings;
                foreach ($row->warnings as $w) {
                    $warnings[] = [$row->action, $w];
                }
            }
            $rowErrors = '';
            if (count($row->errors)) {
                $rowErrors = count($row->errors); //$this->ansiFormat(count($row->errors), Console::FG_RED);
                foreach ($row->errors as $e) {
                    $errors[] = [$row->action, $e];
                }
            }

            $success = $row->success ? 'Y' : 'N';

            $rows[] = [
                $row->action,
                $row->fileName,
                $row->absoluteDirectory,
                $rowWarnings,
                $rowErrors,
                $success,
            ];
        }
        return [
            $rows,
            $warnings,
            $errors,
        ];
    }
}
