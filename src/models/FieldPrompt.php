<?php

namespace unionco\components\models;

use Craft;
use craft\helpers\StringHelper;

class FieldPrompt
{
    public $prompt;
    public $handle;
    public $templateSymbol;
    public $default;
    public $required;
    public $value;
    public $options;
    public $multi;
    public $transform;

    public function __construct($opts)
    // $prompt, $handle, $templateSymbol = null, $required = false, $default = null, $options = null)
    {
        Craft::configure($this, $opts);
        // $this->prompt = $opts['prompt'];
        // $this->handle = $opts['handle'];
        // $this->templateSymbol = $opts['templateSymbol'] ?? null;
        // $this->default = $opts['default'] ?? null;
        // $this->required = $opts['required'] ?? false;
        // $this->value = $opts['value'] ?? null;
        // $this->options = $opts['options'] ?? [];
        // $this->multi = $opts['multi'] ?? false;
        // $this->transform = 
    }

    public function getPrompt()
    {
        return $this->prompt;
    }

    public function getHandle()
    {
        return $this->handle;
    }

    public function getTemplateSymbol()
    {
        if (!$this->templateSymbol) {
            return StringHelper::toPascalCase($this->handle);
        }
        return $this->templateSymbol;
    }

    public function getDefault()
    {
        if ($this->default instanceof \Closure) {
            return call_user_func($this->default);
        }

        return $this->default;
    }

    public function getRequired() {
        return $this->required;
    }

    public function setValue($val) {
        $this->value = $val;
    }

    public function getValue() {
        if ($this->transform) {
            return call_user_func($this->transform, $this->value);
        }
        return $this->value;
    }

    public function getOptions()
    {
        return $this->options;
    }

    public function getMulti()
    {
        return $this->multi;
    }
}
