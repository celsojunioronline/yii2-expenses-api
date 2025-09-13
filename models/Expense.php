<?php

namespace app\models;

use yii\db\ActiveRecord;
use yii\db\Expression;

class Expense extends ActiveRecord
{

    public static function tableName(): string
    {
        return '{{%expenses}}';
    }

    public function rules(): array
    {
        return [
            [['user_id', 'category_id', 'description', 'amount', 'expense_date'], 'required'],
            ['user_id', 'integer'],
            ['category_id', 'integer'],
            ['description', 'string', 'max' => 255],
            ['amount', 'number', 'min' => 0],
            ['expense_date', 'date', 'format' => 'php:Y-m-d'],
        ];
    }

    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    public function getCategory()
    {
        return $this->hasOne(Category::class, ['id' => 'category_id']);
    }

    public function softDelete(): bool
    {
        $this->deleted_at = new Expression('CURRENT_TIMESTAMP');
        return $this->save(false, ['deleted_at']);
    }

    public function isOwner(int $userId): bool
    {
        return $this->user_id === $userId;
    }

}