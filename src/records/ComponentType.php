<?php
/**
 * Components plugin for Craft CMS 3.x
 *
 * A component library for Craft cms.
 *
 * @link      https://github.com/unionco
 * @copyright Copyright (c) 2019 Union
 */

namespace unionco\components\records;

use craft\db\ActiveRecord;
use unionco\components\db\Table;
use craft\records\Field;
use yii\db\ActiveQueryInterface;

/**
 * Class ComponentType record.
 */
class ComponentType extends ActiveRecord
{
    /**
     * @inheritdoc
     * @return string
     */
    public static function tableName(): string
    {
        return Table::TYPES;
    }

    /**
     * Returns the entry type’s fieldLayout.
     *
     * @return ActiveQueryInterface The relational query object.
     */
    public function getFields(): ActiveQueryInterface
    {
        return $this->hasMany(Field::class, ['id' => 'fieldId'])
            ->viaTable(Table::FIELDS, ['typeId' => 'id']);
    }

}