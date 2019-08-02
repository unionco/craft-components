<?php
/**
 * Components plugin for Craft CMS 3.x
 *
 * A component library for Craft cms.
 *
 * @link      https://github.com/unionco
 * @copyright Copyright (c) 2019 Union
 */

namespace unionco\components\twigextensions;

use unionco\components\Components;

use Craft;

/**
 * @author    Union
 * @package   Components
 * @since     0.0.1
 */
class ComponentsTwigExtension extends \Twig_Extension
{
    // Public Methods
    // =========================================================================

    /**
     * Init
     *
     * @return void
     **/
    public function __construct()
    {
        $env = Craft::$app->getView()->getTwig();
        $env->addGlobal('comps', Components::$plugin);
    }

    /**
     * @inheritdoc
     */
    public function getName()
    {
        return 'Components';
    }

    /**
     * @inheritdoc
     */
    public function getFilters()
    {
        // new \Twig_SimpleFilter('someFilter', [$this, 'someInternalFunction'])
        return [];
    }

    /**
     * @inheritdoc
     */
    public function getFunctions()
    {
        // new \Twig_SimpleFunction('someFunction', [$this, 'someInternalFunction']),
        return [];
    }
}
