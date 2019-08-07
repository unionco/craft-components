<?php

namespace unionco\components\services;

use craft\base\Component;
use craft\helpers\FileHelper;
use craft\helpers\StringHelper;
use Symfony\Component\Yaml\Yaml;
use unionco\components\Components;
use unionco\components\models\GeneratorOutput;
use unionco\components\services\GeneratorInterface;

class FieldsGenerator extends Component implements GeneratorInterface
{
    /** @var string */
    public static $generatorTemplatesDir = '';

    /** @var string */
    public $name = '';

    /** @var string */
    public $type = '';

    /** @var string */
    public $handle = '';

    /** @var string */
    public $instructions = '';

    /** @var string[] */
    public $subFields = [];

    /** @return void */
    public function init()
    {
        self::$generatorTemplatesDir = Components::$plugin->getBasePath() . '/generator-templates/fields';
        parent::init();
    }

    /**
     * Generate scaffolding for a new field
     * @param string $name
     * @param array $opts
     * @return GeneratorOutput[]
     **/
    public function generate($name, $opts = []): array
    {
        $this->name = $name;
        $this->handle = $opts['handle'] ?? '';
        $this->type = $opts['type'] ?? '';
        $this->instructions = $opts['instructions'] ?? '';
        $this->subFields = $opts['subFields'] ?? [];

        /** @var GeneratorOuput[] */
        $output = [];
        if ($this->type === 'supertable' || $this->type === 'matrix') {
            $output[] = $this->generateComplexConfigYaml();
        } else {
            $output[] = $this->generateConfigYaml();
        }

        return $output;
    }

    public function generateConfigYaml()
    {
        $targetDir = Components::$fieldsConfigDirectory;
        $pascalCase = StringHelper::toPascalCase($this->name);
        $fileName = $pascalCase . '.yaml';
        $filePath = $targetDir . DIRECTORY_SEPARATOR . $fileName;
        
        $output = new GeneratorOutput();
        $output->action = 'Create field config YAML';
        $output->absoluteDirectory = $targetDir;
        $output->fileName = $fileName;
        
        $templatePath = self::$generatorTemplatesDir . DIRECTORY_SEPARATOR . $this->type . '.yaml.template';
        if (!file_exists($templatePath)) {
            $output->errors[] = "Template for {$this->type} fields does not exist. Tried {$templatePath}";
        }
        
        $template = file_get_contents($templatePath);
        $template = $this->replaceTemplateName($template);

        try {
            FileHelper::writeToFile($filePath, $template);
            // $output->warnings[] = 'Empty YAML file has been generated. You must add your own config to the file before installing the component';
            $output->success = true;
        } catch (InvalidArgumentException $e) {
            $output->errors[] = 'Parent directory does not exist';
        } catch (ErrorException $e) {
            $output->errors[] = 'Generating field YAML config file failed';
        }

        return $output;
    }

    private function generateComplexConfigYaml()
    {
        $targetDir = Components::$fieldsConfigDirectory;
        $pascalCase = StringHelper::toPascalCase($this->name);
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
        foreach ($this->subFields as $subField) {
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

    /**
     * @param string $template Template contents
     * @return string template contents with replacements
     */
    private function replaceTemplateName($template)
    {
        $template = preg_replace('/{{FieldName}}/', $this->name, $template);
        $template = preg_replace('/{{FieldHandle}}/', $this->handle, $template);
        $template = preg_replace('/{{FieldInstructions}}/', $this->instructions, $template);

        return $template;
    }

    public static function fieldTypes()
    {
        return [
            'plainText' => 'Plain Text',
            'lightswitch' => 'Lightswitch',
            'supertable' => 'Super Table',
            'matrix' => 'Matrix',
        ];
    }

    public static function getFields()
    {
        $files = FileHelper::findFiles(Components::$fieldsConfigDirectory);
        if (!$files) {
            throw new \Exception('You need to create some fields first');
        }
        $fields = [];
        foreach ($files as $file) {
            $name = str_replace(Components::$fieldsConfigDirectory . '/', '', $file);
            $name = str_replace('.yaml', '', $name);
            $fields[$name] = $file;
        }

        return $fields;
    }

    public static function hasInstructions($fieldType): bool
    {
        switch ($fieldType) {
            case 'plainText':
            case 'lightSwitch':
            case 'asset':
                return true;
            default:
                return false;
        }
    }

    public static function isComplex($fieldType): bool
    {
        return $fieldType === 'supertable' || $fieldType === 'matrix';
    }
}
