<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%expenses_audit}}`.
 */
class m250910_234810_create_expenses_audit_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%expenses_audit}}', [
            'id' => $this->primaryKey(),
            'expense_id' => $this->integer()->notNull(),
            'user_id' => $this->integer()->notNull(),
            'action' => "ENUM('create', 'update', 'delete') NOT NULL",
            'old_data' => $this->json()->null(),
            'new_data' => $this->json()->null(),
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
        ]);

        $this->addForeignKey(
            'fk_audit_expense_id',
            '{{%expenses_audit}}',
            'expense_id',
            '{{%expenses}}',
            'id',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk_audit_user_id',
            '{{%expenses_audit}}',
            'user_id',
            '{{%users}}',
            'id',
            'CASCADE'
        );

        $this->createIndex('idx_audit_action', '{{%expenses_audit}}', 'action');
        $this->createIndex('idx_audit_created_at', '{{%expenses_audit}}', 'created_at');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk_audit_expense_id', '{{%expenses_audit}}');
        $this->dropForeignKey('fk_audit_user_id', '{{%expenses_audit}}');
        $this->dropTable('{{%expenses_audit}}');
    }
}
