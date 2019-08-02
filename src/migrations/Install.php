<?php

namespace unionco\components\migrations;

use craft\db\Migration;

/**
 * Install migration.
 */
class Install extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        // Place installation code here...
        if ($this->createTables()) {}

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        // Place uninstallation code here...
        return true;
    }

    /**
     * 
     */
    protected function createTables()
    {
        //         
        return true;
    }

    /**
     * 
     */
    protected function addForeignKeys()
    {
        //
        return true;
    }
}
