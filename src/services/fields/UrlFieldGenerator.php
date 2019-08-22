<?php

namespace unionco\components\services\fields;

use unionco\components\services\FieldsGenerator;

class UrlFieldGenerator extends FieldsGenerator
{
    /** @inheritdoc */
    public function init()
    {
        parent::init();
        $this->type = 'url';
    }
}