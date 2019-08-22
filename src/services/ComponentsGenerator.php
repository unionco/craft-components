<?php

namespace unionco\components\services;

use craft\base\Component;
use yii\base\ErrorException;
use craft\helpers\FileHelper;
use craft\helpers\StringHelper;
use Symfony\Component\Yaml\Yaml;
use unionco\components\Components;
use unionco\components\models\FieldPrompt;
use unionco\components\models\GeneratorOutput;
use unionco\components\services\GeneratorInterface;
use Zend\Feed\Writer\Exception\InvalidArgumentException;

class ComponentsGenerator extends Component implements GeneratorInterface
{
    /** @var string */
    public static $generatorTemplatesDir = '';

    /** @var string */
    public static $phpClassTemplate = '';

    /** @var string */
    public static $twigEmbedTemplate = '';

    /** @var string */
    public static $twigSystemTemplate = '';

    /** @var string */
    public static $enumConstTemplate = '';

    /** @var string */
    public static $enumAllTemplate = '';

    /** @var FieldPrompt[] */
    public static $prompts = [];

    /** @var string */
    public $name = '';

    /** @var string */
    public $type = 'basic';

    /** @var string[] */
    public $fields = [];

    /** @var array */
    public $values = [];

    /** @return void */
    public function init()
    {
        self::$generatorTemplatesDir = Components::$plugin->getBasePath() . '/generator-templates/components';
        self::$phpClassTemplate = self::$generatorTemplatesDir . '/Component.php.template';
        self::$twigEmbedTemplate = self::$generatorTemplatesDir . '/component.embed.twig.template';
        self::$twigSystemTemplate = self::$generatorTemplatesDir . '/component.system.twig.template';
        self::$enumConstTemplate = self::$generatorTemplatesDir . '/enum-const.component.php.template';
        self::$enumAllTemplate = self::$generatorTemplatesDir . '/enum-all.component.php.template';

        static::$prompts = [
            $namePrompt = new FieldPrompt([
                'prompt' => 'Component name',
                'handle' => 'name',
                'required' => true,
            ]),
            new FieldPrompt([
                'prompt' => 'Component Handle',
                'handle' => 'handle',
                'required' => true,
                'default' => function () use ($namePrompt) {
                    return StringHelper::toCamelCase($namePrompt->getValue());
                },
            ]),
            new FieldPrompt([
                'prompt' => 'Add fields',
                'handle' => 'fields',
                'multi' => true,
                'options' => function () {
                    return FieldsGenerator::getFields();
                },
            ]),
        ];

        parent::init();
    }

    /**
     * Generate scaffolding for a new component
     * @param array $opts
     * @return GeneratorOutput[]
     */
    public function generate($opts = []): array
    {
        $this->values = $opts['values'];
        $this->name = $this->values['name'];
        $this->fields = $this->values['fields'];

        /** @var GeneratorOutput[] */
        $output = [];
        // $output[] = $this->generateEmptyConfigYaml();
        $output[] = $this->generateConfigYaml();
        $output[] = $this->generateComponentClass();
        $output[] = $this->generateTwigEmbed();
        $output[] = $this->generateTwigSystem();
        $output[] = $this->generateEnumConst();
        $output[] = $this->generateEnumAll();

        return $output;
    }

    /**
     * @return GeneratorOutput
     */
    private function generateConfigYaml()
    {
        $targetDir = Components::$componentsConfigDirectory;
        $pascalCase = StringHelper::toPascalCase($this->name);
        $fileName = $pascalCase . '.yaml';
        $filePath = $targetDir . DIRECTORY_SEPARATOR . $fileName;
        
        $output = new GeneratorOutput();
        $output->action = 'Create component config YAML';
        // $output->relativeDirectory = 
        $output->absoluteDirectory = $targetDir;
        $output->fileName = $fileName;
       
        $template = $this->concatenateFields();
        // $templatePath = self::$generatorTemplatesDir . DIRECTORY_SEPARATOR . $this->type . '.yaml.template';
        // $template = Yaml::parse(file_get_contents($templatePath));//file_get_contents($)

        try {
            FileHelper::writeToFile($filePath, Yaml::dump($template, 20, 2));
            $output->success = true;
        } catch (InvalidArgumentException $e) {
            $output->errors[] = 'Parent directory does not exist';
        } catch (ErrorException $e) {
            $output->errors[] = 'Writing YAML config file failed';
        }

        return $output;
    }

    /**
     * @param string $name
     * @return GeneratorOutput
     */
    private function generateEmptyConfigYaml()
    {
        $targetDir = Components::$componentsConfigDirectory;
        $pascalCase = StringHelper::toPascalCase($this->name);
        $fileName = $pascalCase . '.yaml';
        $filePath = $targetDir . DIRECTORY_SEPARATOR . $fileName;
        
        $output = new GeneratorOutput();
        $output->action = 'Create component config YAML';
        // $output->relativeDirectory = 
        $output->absoluteDirectory = $targetDir;
        $output->fileName = $fileName;
        
        try {
            FileHelper::writeToFile($filePath, '');
            $output->warnings[] = 'Empty YAML file has been generated. You must add your own config to the file before installing the component';
            $output->success = true;
        } catch (InvalidArgumentException $e) {
            $output->errors[] = 'Parent directory does not exist';
        } catch (ErrorException $e) {
            $output->errors[] = 'Generating empty YAML config file failed';
        }

        return $output;
    }

    private function generateComponentClass()
    {
        $targetDir = Components::$componentsClassDirectory;
        $pascalCase = StringHelper::toPascalCase($this->name);
        $fileName = $pascalCase . '.php';
        $filePath = $targetDir . DIRECTORY_SEPARATOR . $fileName;

        $output = new GeneratorOutput();
        $output->action = 'Create component PHP class';
        $output->absoluteDirectory = $targetDir;
        $output->fileName = $fileName;

        $template = file_get_contents(self::$phpClassTemplate);
        $template = $this->replaceTemplateName($this->name, $template);

        try {
            FileHelper::writeToFile($filePath, $template);
            $output->success = true;
        } catch (InvalidArgumentException $e) {
            $output->errors[] = 'Parent directory does not exist';
        } catch (ErrorException $e) {
            $output->errors[] = 'Generating PHP class failed';
        }

        return $output;
    }

    private function generateTwigEmbed()
    {
        $targetDir = Components::$templatesEmbedDirectory;
        $camelCase = StringHelper::toCamelCase($this->name);
        $fileName = $camelCase . '.twig';
        $filePath = $targetDir . DIRECTORY_SEPARATOR . $fileName;

        $output = new GeneratorOutput();
        $output->action = 'Create embed template';
        $output->absoluteDirectory = $targetDir;
        $output->fileName = $fileName;

        $template = file_get_contents(self::$twigEmbedTemplate);
        $template = $this->replaceTemplateName($this->name, $template);

        try {
            FileHelper::writeToFile($filePath, $template);
            $output->success = true;
        } catch (InvalidArgumentException $e) {
            $output->errors[] = 'Parent directory does not exist';
        } catch (ErrorException $e) {
            $output->errors[] = 'Generating Twig embed template failed';
        }

        return $output;
    }

    private function generateTwigSystem()
    {
        $targetDir = Components::$templatesSystemDirectory;
        $camelCase = StringHelper::toCamelCase($this->name);
        $fileName = $camelCase . '.twig';
        $filePath = $targetDir . DIRECTORY_SEPARATOR . $fileName;

        $output = new GeneratorOutput();
        $output->action = 'Create system template';
        $output->absoluteDirectory = $targetDir;
        $output->fileName = $fileName;

        $template = file_get_contents(self::$twigSystemTemplate);
        $template = $this->replaceTemplateName($this->name, $template);

        try {
            FileHelper::writeToFile($filePath, $template);
            $output->success = true;
        } catch (InvalidArgumentException $e) {
            $output->errors[] = 'Parent directory does not exist';
        } catch (ErrorException $e) {
            $output->errors[] = 'Generating Twig system template failed';
        }

        return $output;
    }

    private function generateEnumConst()
    {
        $targetDir = Components::$enumDirectory;
        $fileName = 'Components.php';
        $filePath = $targetDir . DIRECTORY_SEPARATOR . $fileName;
        
        $output = new GeneratorOutput();
        $output->action = 'Add new component to enum';
        $output->absoluteDirectory = $targetDir;
        $output->fileName = $fileName;

        $template = file_get_contents(self::$enumConstTemplate);
        $template = $this->replaceTemplateName($this->name, $template);

        $hostTemplate = file_get_contents($filePath);
        $anchor = '    //{{enum-const.component.php.template}}';
        $newTemplate = $this->insertIntoTemplate($hostTemplate, $anchor, $template);
        if (!$newTemplate) {
            $output->warnings[] = 'Const already exists in Components enum';
            $newTemplate = $hostTemplate;
        }

        try {
            FileHelper::writeToFile($filePath, $newTemplate);
            $output->success = true;
        } catch (InvalidArgumentException $e) {
            $output->errors[] = 'Parent directory does not exist';
        } catch (ErrorException $e) {
            $output->errors[] = 'Adding component to enum failed';
        }

        return $output;
    }

    private function generateEnumAll()
    {
        $targetDir = Components::$enumDirectory;
        $fileName = 'Components.php';
        $filePath = $targetDir . DIRECTORY_SEPARATOR . $fileName;
        
        $output = new GeneratorOutput();
        $output->action = 'Add enum const enum ALL';
        $output->absoluteDirectory = $targetDir;
        $output->fileName = $fileName;

        $template = file_get_contents(self::$enumAllTemplate);
        $template = $this->replaceTemplateName($template);

        $hostTemplate = file_get_contents($filePath);
        $anchor = '    //{{enum-all.component.php.template}}';
        $newTemplate = $this->insertIntoTemplate($hostTemplate, $anchor, $template);
        if (!$newTemplate) {
            $output->warnings[] = 'Const already exists in Components enum ALL';
            $newTemplate = $hostTemplate;
        }

        try {
            FileHelper::writeToFile($filePath, $newTemplate);
            $output->success = true;
        } catch (InvalidArgumentException $e) {
            $output->errors[] = 'Parent directory does not exist';
        } catch (ErrorException $e) {
            $output->errors[] = 'Adding component to enum failed';
        }

        return $output;
    }

    /**
     * @param string $template Template contents
     * @return string template contents with replacements
     */
    private function replaceTemplateName($template)
    {
        $template = preg_replace('/{{ComponentPascal}}/', StringHelper::toPascalCase($this->name), $template);
        $template = preg_replace('/{{ComponentCamel}}/', StringHelper::toCamelCase($this->name), $template);
        $template = preg_replace('/{{ComponentName}}/', $this->name, $template);

        return $template;
    }

    private function insertIntoTemplate($hostTemplate, $anchor, $content)
    {
        // Don't add it again if it is already included
        if (strpos($hostTemplate, $content) !== false) {
            return false;
        }
        return str_replace($anchor, "$content\n$anchor", $hostTemplate);
    }

    private function concatenateFields()
    {
        $baseConfigTemplatePath = self::$generatorTemplatesDir . DIRECTORY_SEPARATOR . $this->type . '.yaml.template';
        $baseConfigTemplate = file_get_contents($baseConfigTemplatePath);
        $baseConfig = Yaml::parse($baseConfigTemplate);

        $fieldsDir = Components::$fieldsConfigDirectory;
        foreach ($this->fields as $i => $fieldPascal) {
            $fieldTemplatePath = $fieldsDir . DIRECTORY_SEPARATOR . $fieldPascal . '.yaml';
            $fieldTemplate = file_get_contents($fieldTemplatePath);
            $fieldData = Yaml::parse($fieldTemplate);
            $baseConfig['tabs'][0]['fields'][] = $fieldData;
        }

        return $baseConfig;
    }
}
