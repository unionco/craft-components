<?php

namespace unionco\components\services\fields;

use unionco\components\services\FieldsGenerator;

class EmailFieldGenerator extends FieldsGenerator
{
    /** @inheritdoc */
    public function init()
    {
        parent::init();
        $this->type = 'email';
    }
}