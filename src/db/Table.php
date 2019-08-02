<?php
/**
 * Components plugin for Craft CMS 3.x
 *
 * A component library for Craft cms.
 *
 * @link      https://github.com/unionco
 * @copyright Copyright (c) 2019 Union
 */

namespace unionco\components\db;

/**
 * This class provides constants for defining plugins database table names.
 *
 * @author    Union
 * @package   Ticketmaster
 * @since     1.0.0
 */
abstract class Table
{
    const BASE = '{{%components}}';
    const TYPES = '{{%component_types}}';
    const FIELDS = '{{%component_type_fields}}';
}