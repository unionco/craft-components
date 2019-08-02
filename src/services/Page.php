<?php
/**
 * Components plugin for Craft CMS 3.x
 *
 * A component library for Craft cms.
 *
 * @link      https://github.com/unionco
 * @copyright Copyright (c) 2019 Union
 */

namespace unionco\components\services;

use unionco\components\Components;

use Craft;
use craft\base\Component;
use craft\elements\Entry;
use craft\helpers\Template as TemplateHelper;
// use craft\helpers\Html;

/**
 * @author    Union
 * @package   Components
 * @since     0.0.1
 */
class Page extends Component
{
    // Public Methods
    // =========================================================================

    /**
     * 
     */
    public function render(Entry $entry, array $params = [])
    {
        if (!$pageBuilder = $entry->pageBuilder->all()) {
            return '';
        }

        $view = Craft::$app->getView();

        foreach ($pageBuilder as $key => $component) {
            $template = $view->renderTemplate('components/system/'.$component->type->handle, ['component' => $component]);

            echo TemplateHelper::raw($template);
        }
    }
}
