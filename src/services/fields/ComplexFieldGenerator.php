<?php

namespace unionco\components\services\fields;

use ErrorException;
use craft\helpers\FileHelper;
use InvalidArgumentException;
use craft\helpers\StringHelper;
use Symfony\Component\Yaml\Yaml;
use unionco\components\Components;
use unionco\components\models\FieldPrompt;
use unionco\components\models\GeneratorOutput;
use unionco\components\services\FieldsGenerator;

abstract class ComplexFieldGenerator extends FieldsGenerator
{
    /** @var string[] */
    public $subFields = [];

    public $availableFields = [];

    /** @inheritdoc */
    public function init()
    {
        parent::init();
        $this->type = 'matrix';

        static::$prompts = array_merge(static::$prompts, [
            new FieldPrompt([
                'prompt' => 'Select subfields',
                'handle' => 'subfields',
                'multi' => true,
                'options' => function () {
                    return FieldsGenerator::getFields();
                }
            ]),
        ]);
    }

    /** @inheritdoc */
    public function generate($opts = []): array
    {
        $this->values = $opts['values'];

        $output = [$this->generateConfigYaml()];
        return $output;
    }

    public function generateConfigYaml()
    {
        /** @var string|null */
        $name = $this->values['name'];

        $targetDir = Components::$fieldsConfigDirectory;
        $pascalCase = StringHelper::toPascalCase($name);
        $fileName = $pascalCase . '.yaml';
        $filePath = $targetDir . DIRECTORY_SEPARATOR . $fileName;

        $output = new GeneratorOutput();
        $output->action = 'Create field complex config YAML';
        $output->absoluteDirectory = $targetDir;
        $output->fileName = $fileName;

        $templatePath = self::$generatorTemplatesDir . DIRECTORY_SEPARATOR . $this->type . '.yaml.template';
        if (!file_exists($templatePath)) {
            $output->errors[] = "Template for {$this->type} fields does not exist. Tried {$templatePath}";
        }

        $template = file_get_contents($templatePath);
        $template = $this->replaceTemplateName($template);
        $baseTemplateData = Yaml::parse($template);

        // Handle subfields
        $i = 1;
        foreach ($this->values['subfields'] as $subField) {
            $subTemplatePath = $targetDir . DIRECTORY_SEPARATOR . $subField . '.yaml';
            $subTemplate = file_get_contents($subTemplatePath);
            $subTemplateData = Yaml::parse($subTemplate);
            $baseTemplateData['settings']['blockTypes']['new']['fields']['new' . $i] = $subTemplateData;
            $i++;
        }

        // Write that shit
        try {
            FileHelper::writeToFile($filePath, Yaml::dump($baseTemplateData, 20, 2));
            // $output->warnings[] = 'Empty YAML file has been generated. You must add your own config to the file before installing the component';
            $output->success = true;
        } catch (InvalidArgumentException $e) {
            $output->errors[] = 'Parent directory does not exist';
        } catch (ErrorException $e) {
            $output->errors[] = 'Generating field YAML config file failed';
        }

        return $output;
    }
}
