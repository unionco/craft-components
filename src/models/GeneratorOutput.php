<?php

namespace unionco\components\models;

class GeneratorOutput
{
    /**
     * @var string Description of the generator action
     **/
    public $action = '';

    /** @var string[] List of warnings generated */
    public $warnings = [];

    /** @var string[] List of errors generated */
    public $errors = [];

    /** @var string Name of the generated file */
    public $fileName = '';
    // public $relativeDirectory = '';

    /** @var string Path to the generated file */
    public $absoluteDirectory = '';

    /** @var bool Did generation succeed */
    public $success = false;
}
