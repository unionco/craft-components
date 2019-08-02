<?php
/**
 * Components plugin for Craft CMS 3.x
 *
 * A component library for Craft cms.
 *
 * @link      https://github.com/unionco
 * @copyright Copyright (c) 2019 Union
 */
namespace unionco\components\components;

/**
 * @author    Union
 * @package   Components
 * @since     0.0.1
 */
interface ComponentInterface
{
    /**
     * Returns the display name of this class.
     *
     * @return string The display name of this class.
     */
    public static function displayName(): string;

    /**
     * Returns the ref used for field handles
     *
     * @return string The ref handle
     */
    public static function ref(): string;
}