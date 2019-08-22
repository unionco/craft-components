<?php

namespace unionco\components\services\fields;

use unionco\components\services\fields\ComplexFieldGenerator;

class SuperTableFieldGenerator extends ComplexFieldGenerator
{
    /** @inheritdoc */
    public function init()
    {
        parent::init();
        $this->type = 'supertable';
    }
}
