<?php

namespace unionco\components\services\fields;

use unionco\components\services\FieldsGenerator;

class PlainTextFieldGenerator extends FieldsGenerator
{
    /** @inheritdoc */
    public function init()
    {
        parent::init();
        $this->type = 'plainText';
    }
}