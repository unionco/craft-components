<?php

namespace unionco\components\services;

use craft\base\Component;
use craft\helpers\FileHelper;
use craft\helpers\StringHelper;
use unionco\components\Components;
use unionco\components\models\FieldPrompt;
use unionco\components\models\GeneratorOutput;
use unionco\components\services\GeneratorInterface;

class FieldsGenerator extends Component implements GeneratorInterface
{
    /** @var string */
    public static $generatorTemplatesDir = '';

    /** @var FieldPrompt[] */
    public static $prompts = [];

    public static $fieldTypes = [
        'categories' => 'Categories',
        'dateTime' => 'DateTime',
        'email' => 'Email',
        'entries' => 'Entries',
        'imageAsset' => 'ImageAsset',
        'lightswitch' => 'Lightswitch',
        'matrix' => 'Matrix',
        'number' => 'Number',
        'pdfAsset' => 'PdfAsset',
        'plainText' => 'Plain Text',
        'supertable' => 'Super Table',
        'tags' => 'Tags',
        'url' => 'Url',
    ];

    /** @var string */
    public $name = '';

    /** @var string */
    public $type = 'base';

    /** @var string */
    public $handle = '';

    /** @var string */
    public $instructions = '';

    /** @var array */
    public $values = [];

    /** @inheritdoc */
    public function init()
    {
        // Set dynamic static properties
        self::$generatorTemplatesDir = Components::$plugin->getBasePath() . '/generator-templates/fields';

        static::$prompts = [
            $name = new FieldPrompt([
                'prompt' => 'Enter a name for the field',
                'handle' => 'name',
                'required' => true,
            ]),
            new FieldPrompt([
                'prompt' => 'Enter a handle for the field',
                'handle' => 'handle',
                'required' => true,
                'default' => function () use ($name) {
                    return StringHelper::toCamelCase($name->getValue());
                },
            ]),
            new FieldPrompt([
                'prompt' => 'Instructions',
                'handle' => 'instructions',
            ]),
        ];

        parent::init();
    }

    /**
     * Generate scaffolding for a new field
     * @param array $opts
     * @return GeneratorOutput[]
     **/
    public function generate($opts = []): array
    {
        $this->values = $opts['values'];

        /** @var GeneratorOuput[] */
        $output = [$this->generateConfigYaml()];

        return $output;
    }

    public function generateConfigYaml()
    {
        /** @var string */
        $name = $this->values['name'];

        $targetDir = Components::$fieldsConfigDirectory;
        $pascalCase = StringHelper::toPascalCase($name);
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


    /**
     * Replace variables in the template, returning a string template
     * 
     * @param string $template Template contents
     * @return string template contents with replacements
     */
    public function replaceTemplateName($template): string
    {
        $template = preg_replace('/{{FieldName}}/', $this->values['name'], $template);
        $template = preg_replace('/{{FieldHandle}}/', $this->values['handle'], $template);
        $template = preg_replace('/{{FieldInstructions}}/', $this->values['instructions'], $template);

        return $template;
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
}
