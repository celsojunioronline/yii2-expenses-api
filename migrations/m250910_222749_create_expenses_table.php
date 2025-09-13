<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%expenses}}`.
 */
class m250910_222749_create_expenses_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%expenses}}', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer()->notNull(),
            'category_id' => $this->integer()->notNull(),
            'description' => $this->string(255)->notNull(),
            'amount' => $this->decimal(12,2)->notNull(),
            'expense_date' => $this->date()->notNull(),
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
            'deleted_at' => $this->timestamp()->null(),
        ]);

        $this->addForeignKey(
            'fk_expenses_user_id',
            '{{%expenses}}',
            'user_id',
            '{{%users}}',
            'id',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk_expenses_category_id',
            '{{%expenses}}',
            'category_id',
            '{{%categories}}',
            'id',
            'RESTRICT'
        );

        $this->createIndex('idx_expenses_date', '{{%expenses}}', 'expense_date');
        $this->createIndex('idx_expenses_deleted', '{{%expenses}}', 'deleted_at');
        $this->createIndex('idx_expenses_user_category', '{{%expenses}}', ['user_id', 'category_id']);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk_expenses_user_id', '{{%expenses}}');
        $this->dropForeignKey('fk_expenses_category_id', '{{%expenses}}');
        $this->dropTable('{{%expenses}}');
    }
}
