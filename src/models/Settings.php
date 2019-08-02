<?php
/**
 * Components plugin for Craft CMS 3.x
 *
 * A component library for Craft cms.
 *
 * @link      https://github.com/unionco
 * @copyright Copyright (c) 2019 Union
 */

namespace unionco\components\models;

use Craft;
use craft\base\Model;
use unionco\components\Components;

/**
 * @author    Union
 * @package   Components
 * @since     0.0.1
 */
class Settings extends Model
{
    // Public Properties
    // =========================================================================

    /**
     * @var string
     */
    public $pluginName = 'Components';

    /**
     * @var string
     */
    public $installedComponents = [];

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [];
    }

    /**
     * 
     */
    public function getPluginName()
    {
        return $this->pluginName;
    }
}
