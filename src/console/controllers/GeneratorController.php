<?php

namespace unionco\components\console\controllers;

use craft\helpers\Console;
use craft\console\Controller;
use yii\console\widgets\Table;

abstract class GeneratorController extends Controller
{
    protected $opts;
    protected $name;
    protected $generator;
    protected $generateMethod;

    /** @inheritdoc */
    public function init()
    {
        parent::init();
        $this->opts = [
            'values' => [],
        ];
    }

    public function actionGenerate(string $name = '')
    {
        $output = $this->generator->generate($this->opts);
        [$rows, $warnings, $errors] = $this->outputToRows($output);

        echo $this->ansiFormat(PHP_EOL . 'Output' . PHP_EOL, Console::FG_GREEN);
        echo Table::widget([
            'headers' => ['Action', 'File Name', 'Absolute Path', 'Warnings', 'Errors', 'Success'],
            'rows' => $rows,
        ]);

        if ($warnings) {
            echo $this->ansiFormat("\nWarnings\n", Console::FG_YELLOW);
            echo Table::widget([
                'headers' => ['Action', 'Warnings'],
                'rows' => $warnings,
            ]);
        }

        if ($errors) {
            echo $this->ansiFormat("\nErrors\n", Console::FG_RED);
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
