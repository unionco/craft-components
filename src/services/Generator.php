<?php

namespace unionco\components\services;

use craft\base\Component;
use yii\base\ErrorException;
use craft\helpers\FileHelper;
use craft\helpers\StringHelper;
use unionco\components\Components;
use unionco\components\models\GeneratorOutput;
use Zend\Feed\Writer\Exception\InvalidArgumentException;

class Generator extends Component
{
    /** @var string */
    public static $generatorTemplatesDir = '';

    /** @var string */
    public static $phpClassTemplate = '';

    /** @var string */
    public static $twigEmbedTemplate = '';

    /** @var string */
    public static $twigSystemTemplate = '';

    public function init()
    {
        self::$generatorTemplatesDir = Components::$plugin->getBasePath() . '/generator-templates';
        self::$phpClassTemplate = self::$generatorTemplatesDir . '/Component.php.template';
        self::$twigEmbedTemplate = self::$generatorTemplatesDir . '/component.embed.twig.template';
        self::$twigSystemTemplate = self::$generatorTemplatesDir . '/component.system.twig.template';
        parent::init();
    }

    /**
     * Return the input in camelCase
     * 
     * Full Width Callout -> fullWidthCallout
     * 
     * @param string $name
     * @return string
     */
    public function nameToCamelCase($name)
    {
        $pascalCase = $this->nameToPascalCase($name);
        $camelCase = lcfirst($pascalCase);

        return $camelCase;
    }

    /**
     * Return the input in PascalCase
     * 
     * Full Width Callout -> FullWidthCallout
     * 
     * @param string $name
     * @return string
     */
    public function nameToPascalCase($name)
    {
        $pascalCase = preg_replace('/\-/', ' ', $name);
        $pascalCase = StringHelper::mb_ucwords($pascalCase);
        $pascalCase = preg_replace('/[^A-Za-z]/', '', $pascalCase);
        
        return $pascalCase;
    }

    /**
     * Generate scaffolding for a new component
     * @param string $name
     * @param array $opts
     * @return array
     */
    public function generateComponent($name, $opts = [])
    {
        $useVue = false;
        if (key_exists('vue', $opts)) {
            $useVue = $opts['vue'];
        }

        $output = [];
        $output[] = $this->generateEmptyConfigYaml($name);
        $output[] = $this->generateComponentClass($name);
        $output[] = $this->generateTwigEmbed($name);
        $output[] = $this->generateTwigSystem($name);

        return $output;
    }

    /**
     * @param string $name
     * @return GeneratorOutput
     */
    private function generateEmptyConfigYaml($name)
    {
        $targetDir = Components::$componentsConfigDirectory;
        $pascalCase = $this->nameToPascalCase($name);
        $fileName = $pascalCase . '.yaml';
        $filePath = $targetDir . DIRECTORY_SEPARATOR . $fileName;
        
        $output = new GeneratorOutput();
        $output->action = 'Create component config YAML';
        // $output->relativeDirectory = 
        $output->absoluteDirectory = $targetDir;
        $output->fileName = $fileName;
        
        try {
            FileHelper::writeToFile($filePath, '');
            $output->success = true;
        } catch (InvalidArgumentException $e) {
            $output->errors[] = 'Parent directory does not exist';
        } catch (ErrorException $e) {
            $output->errors[] = 'Generating empty YAML config file failed';
        }

        return $output;
    }

    private function generateComponentClass($name)
    {
        $targetDir = Components::$componentsClassDirectory;
        $pascalCase = $this->nameToPascalCase($name);
        $fileName = $pascalCase . '.php';
        $filePath = $targetDir . DIRECTORY_SEPARATOR . $fileName;

        $output = new GeneratorOutput();
        $output->action = 'Create component PHP class';
        $output->absoluteDirectory = $targetDir;
        $output->fileName = $fileName;

        $template = file_get_contents(self::$phpClassTemplate);
        $template = $this->replaceTemplateName($name, $template);

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

    private function generateTwigEmbed($name)
    {
        $targetDir = Components::$templatesEmbedDirectory;
        $camelCase = $this->nameToCamelCase($name);
        $fileName = $camelCase . '.twig';
        $filePath = $targetDir . DIRECTORY_SEPARATOR . $fileName;

        $output = new GeneratorOutput();
        $output->action = 'Create embed template';
        $output->absoluteDirectory = $targetDir;
        $output->fileName = $fileName;

        $template = file_get_contents(self::$twigEmbedTemplate);
        $template = $this->replaceTemplateName($name, $template);

        $output->warnings[] = 'Testing';
        $output->warnings[] = 'Testing Again';
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

    private function generateTwigSystem($name)
    {
        $targetDir = Components::$templatesSystemDirectory;
        $camelCase = $this->nameToCamelCase($name);
        $fileName = $camelCase . '.twig';
        $filePath = $targetDir . DIRECTORY_SEPARATOR . $fileName;

        $output = new GeneratorOutput();
        $output->action = 'Create system template';
        $output->absoluteDirectory = $targetDir;
        $output->fileName = $fileName;

        $template = file_get_contents(self::$twigSystemTemplate);
        $template = $this->replaceTemplateName($name, $template);

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

    /**
     * @param string $name Component name
     * @param string $template Template contents
     * @return string template contents with replacements
     */
    private function replaceTemplateName($name, $template)
    {
        $template = preg_replace('/{{ComponentPascal}}/', $this->nameToPascalCase($name), $template);
        $template = preg_replace('/{{ComponentCamel}}/', $this->nameToCamelCase($name), $template);
        $template = preg_replace('/{{ComponentName}}/', $name, $template);

        return $template;
    }
}
