<?php

namespace unionco\components\console\controllers;

use craft\console\Controller;
use unionco\components\Components;
use unionco\components\console\controllers\GeneratorController;

class FieldController extends GeneratorController
{
    public function init()
    {
        $this->generator = Components::$plugin->fieldsGenerator;
        parent::init();
    }

    public function actionGenerate($name)
    {
        parent::actionGenerate($name);
    }
}
