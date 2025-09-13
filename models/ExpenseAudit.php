<?php

namespace app\models;

use yii\db\ActiveRecord;

/**
 * This is the model class for table "expenses_audit".
 *
 * @property int $id
 * @property int $expense_id
 * @property int $user_id
 * @property string $action
 * @property string|null $old_data
 * @property string|null $new_data
 * @property string $created_at
 *
 * @property Expense $expense
 * @property User $user
 */
class ExpenseAudit extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%expenses_audit}}';
    }

    public function rules(): array
    {
        return [
            [['expense_id', 'user_id', 'action'], 'required'],
            [['expense_id', 'user_id'], 'integer'],
            [['old_data', 'new_data'], 'string'],
            [['created_at'], 'safe'],
            ['action', 'in', 'range' => ['create', 'update', 'delete']],
        ];
    }

    public function getExpense()
    {
        return $this->hasOne(Expense::class, ['id' => 'expense_id']);
    }

    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }
}