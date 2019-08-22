<?php

namespace unionco\components\services\fields;

use unionco\components\services\fields\AssetFieldGenerator;

class PdfAssetFieldGenerator extends AssetFieldGenerator
{
    /** @inheritdoc */
    static $assetPath = 'pdfs/';
    
    /** @inheritdoc */
    static $assetType = 'pdf';
}
