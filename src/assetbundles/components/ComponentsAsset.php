<?php
/**
 * Components plugin for Craft CMS 3.x
 *
 * A component library for Craft cms.
 *
 * @link      https://github.com/unionco
 * @copyright Copyright (c) 2019 Union
 */

namespace unionco\components\assetbundles\Components;

use Craft;
use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;

/**
 * @author    Union
 * @package   Components
 * @since     0.0.1
 */
class ComponentsAsset extends AssetBundle
{
    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->sourcePath = "@unionco/components/assetbundles/components/dist";

        $this->depends = [
            CpAsset::class,
        ];

        $this->js = [
            'js/Components.js',
        ];

        $this->css = [
            'css/Components.css',
        ];

        parent::init();
    }
}
