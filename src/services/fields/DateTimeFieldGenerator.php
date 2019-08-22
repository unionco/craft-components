<?php

namespace unionco\components\services\fields;

use unionco\components\models\FieldPrompt;
use unionco\components\services\FieldsGenerator;

class DateTimeFieldGenerator extends FieldsGenerator
{
    const DATETIME = 'datetime';
    const DATE = 'date';
    const TIME = 'time';
    
    public static $displays = [
        self::DATETIME => 'Show Date & Time',
        self::DATE => 'Only show date',
        self::TIME => 'Only show time',
    ];

    public static $increments = [
        '15' => '15',
        '30' => '30',
        '60' => '60',
    ];

    /** @inheritdoc */
    public function init()
    {
        parent::init();
        $this->type = 'dateTime';

        static::$prompts = array_merge(static::$prompts, [
            new FieldPrompt([
                'prompt' => 'Select Display Type',
                'handle' => 'display',
                'multi' => false,
                'options' => function () { return static::$displays; },
            ]),
            new FieldPrompt([
                'prompt' => 'Minute Increment',
                'handle' => 'minInc',
                'multi' => false,
                'options' => function () { return static::$increments; },
            ]),
        ]);
    }

    /** @inheritdoc */
    public function replaceTemplateName($template): string
    {
        $template = parent::replaceTemplateName($template);
        $template = preg_replace('/{{FieldShowDate}}/', $this->showDate() ? 'true' : 'false', $template);
        $template = preg_replace('/{{FieldShowTime}}/', $this->showTime() ? 'true' : 'false', $template);
        $template = preg_replace('/{{FieldMinInc}}/', $this->values['minInc'], $template);

        return $template;
    }

    private function showDate(): bool
    {
        $display = $this->values['display'];
        return ($display === self::DATETIME || $display === self::DATE);
    }

    private function showTime(): bool
    {
        $display = $this->values['display'];
        return ($display === self::DATETIME || $display === self::TIME);
    }
}
