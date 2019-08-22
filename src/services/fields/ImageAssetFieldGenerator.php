<?php

namespace unionco\components\services\fields;

use unionco\components\services\fields\AssetFieldGenerator;

class ImageAssetFieldGenerator extends AssetFieldGenerator
{
    /** @inheritdoc */
    static $assetPath = 'images/';
    
    /** @inheritdoc */
    static $assetType = 'image';
}
