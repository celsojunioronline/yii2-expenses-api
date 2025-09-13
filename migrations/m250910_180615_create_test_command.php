<?php

use yii\db\Migration;

class m250910_180615_create_test_command extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m250910_180615_create_test_command cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m250910_180615_create_test_command cannot be reverted.\n";

        return false;
    }
    */
}
