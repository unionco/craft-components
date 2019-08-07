<?php

namespace unionco\components\console\controllers;

use craft\helpers\Console;
use craft\console\Controller;
use yii\console\widgets\Table;
use unionco\components\Components;
use unionco\components\models\GeneratorOutput;
use unionco\components\console\controllers\GeneratorController;

class ComponentController extends GeneratorController
{
    public $vue = false;

    public function init()
    {
        $this->generator = Components::$plugin->componentsGenerator;
        parent::init();
    }

    public function actionGenerate($name)
    {
        parent::actionGenerate($name);
    }
}
