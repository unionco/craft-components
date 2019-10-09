<?php
/**
 * NOTE: This file was automatically generated, update values accordingly
 */
namespace unionco\components\components;

use Symfony\Component\Yaml\Yaml;

/**
 * @author    Union
 * @package   Components
 * @since     0.0.1
 */
class FlexibleContent extends BaseComponent implements ComponentInterface
{
    /**
     * @var array config
     */
    protected $config;

    // Static
    // =========================================================================

    /**
     * Returns the display name of this class.
     *
     * @return string The display name of this class.
     */
    public static function displayName(): string
    {
        return 'Flexible Content';
    }

    /**
     * Returns the ref used for field handles
     *
     * @return string The ref handle
     */
    public static function ref(): string
    {
        return 'flexibleContent';
    }

    // Public
    // =========================================================================

    /**
     *
     */
    public function __construct()
    {
        $file = __DIR__ . "/configs/FlexibleContent.yaml";
        $this->config = Yaml::parse(file_get_contents($file));
    }
}
