<?php

namespace unionco\components\services;

use craft\base\Component;
use unionco\components\Components;
use unionco\components\services\GeneratorInterface;

class FieldsGenerator extends Component implements GeneratorInterface
{
    /** @var string */
    public static $generatorTemplatesDir = '';

    public function init()
    {
        self::$generatorTemplatesDir = Components::$plugin->getBasePath() . '/generator-templates/fields';
    }

    public function generate($name, $opts = []): array
    {
        return [];
    }
}
