<?php

namespace app\services;

use app\models\Expense;
use app\models\ExpenseAudit;
use app\models\User;
use yii\db\Expression;

class ExpenseService
{
    private int $userId;
    private ?array $filters = null;
    private ?Expense $expense = null;
    private ?array $data = null;

    public function comUsuario(int $userId): self
    {
        $this->userId = $userId;
        return $this;
    }

    public function comDados(array $data): self
    {
        $this->data = $data;
        return $this;
    }

    public function comDespesa(Expense $expense): self
    {
        $this->expense = $expense;
        return $this;
    }

    public function comFiltros(array $filters): self
    {
        $this->filters = $filters;
        return $this;
    }

    public function criar(): array
    {
        $expense = new Expense();
        $expense->user_id = $this->userId;
        $expense->load($this->data, '');

        if ($expense->save()) {
            $this->registrarAuditoria($expense, 'create');
            return ['success' => true, 'data' => $this->mapExpense($expense)];
        }

        return ['success' => false, 'errors' => $expense->errors];
    }

    public function listar(): array
    {
        $query = Expense::find()->andWhere(['deleted_at' => null]);

        if (!$this->isAdmin()) {
            $query->andWhere(['user_id' => $this->userId]);
        }

        if (!empty($this->filters['category_id'])) {
            $query->andWhere(['category_id' => (int) $this->filters['category_id']]);
        }

        if (!empty($this->filters['start_date']) && !empty($this->filters['end_date'])) {
            $query->andWhere([
                'between',
                'expense_date',
                $this->filters['start_date'],
                $this->filters['end_date']
            ]);
        } elseif (!empty($this->filters['month']) && !empty($this->filters['year'])) {
            $month = str_pad((int) $this->filters['month'], 2, '0', STR_PAD_LEFT);
            $year = max((int) $this->filters['year'], 2000);

            $start = "$year-$month-01";
            $end   = date("Y-m-t", strtotime($start));
            $query->andWhere(['between', 'expense_date', $start, $end]);
        }

        $sort = strtolower($this->filters['sort'] ?? 'desc');
        $query->orderBy(['expense_date' => $sort === 'asc' ? SORT_ASC : SORT_DESC]);

        $page = max((int)($this->filters['page'] ?? 1), 1);
        $perPage = min(max((int)($this->filters['per_page'] ?? 10), 1), 100);
        $total = $query->count();

        $items = $query->offset(($page - 1) * $perPage)
            ->limit($perPage)
            ->all();

        $data = array_map(fn(Expense $exp) => $this->mapExpense($exp), $items);

        return [
            'success' => true,
            'data' => $data,
            'meta' => [
                'total' => $total,
                'page' => $page,
                'per_page' => $perPage,
                'total_pages' => (int) ceil($total / $perPage),
                'sort' => $sort,
            ]
        ];
    }

    public function detalhar(int $id): ?array
    {
        $query = Expense::find()->where(['id' => $id, 'deleted_at' => null]);

        if (!$this->isAdmin()) {
            $query->andWhere(['user_id' => $this->userId]);
        }

        $expense = $query->one();

        return $expense ? $this->mapExpense($expense) : null;
    }

    public function atualizar(): array
    {
        if (!$this->expense) {
            return ['success' => false, 'message' => 'Despesa não encontrada'];
        }

        if (!$this->isAdmin() && !$this->expense->isOwner($this->userId)) {
            return ['success' => false, 'message' => 'Você não tem permissão para editar esta despesa'];
        }

        $oldData = $this->expense->attributes;
        $this->expense->load($this->data, '');

        if ($this->expense->save()) {
            $this->registrarAuditoria($this->expense, 'update', $oldData, $this->expense->attributes);
            return ['success' => true, 'data' => $this->mapExpense($this->expense)];
        }

        return ['success' => false, 'errors' => $this->expense->errors];
    }

    public function excluir(): array
    {
        if (!$this->expense) {
            return ['success' => false, 'message' => 'Despesa não encontrada'];
        }

        if (!$this->isAdmin() && !$this->expense->isOwner($this->userId)) {
            return ['success' => false, 'message' => 'Você não tem permissão para excluir esta despesa'];
        }

        $this->expense->deleted_at = new Expression('CURRENT_TIMESTAMP');

        if ($this->expense->save(false, ['deleted_at'])) {
            $this->registrarAuditoria($this->expense, 'delete');
            return ['success' => true];
        }

        return ['success' => false, 'message' => 'Falha ao excluir despesa'];
    }

    private function mapExpense(Expense $expense): array
    {
        return [
            'id'          => (int) $expense->id,
            'user_id'     => (int) $expense->user_id,
            'description' => (string) $expense->description,
            'category_id' => (int) $expense->category_id,
            'amount' => number_format((float)$expense->amount, 2, '.', ''),
            'expense_date'=> (string) $expense->expense_date,
        ];
    }

    private function registrarAuditoria(Expense $expense, string $action, array $oldData = null, array $newData = null): void
    {
        $audit = new ExpenseAudit();
        $audit->expense_id = $expense->id;
        $audit->user_id = $this->userId;
        $audit->action = $action;
        $audit->old_data = $oldData ? json_encode($oldData) : null;
        $audit->new_data = $newData ? json_encode($newData) : json_encode($expense->attributes);
        $audit->save(false);
    }

    private function isAdmin(): bool
    {
        $user = User::findOne($this->userId);
        return $user && $user->isAdmin();
    }
}
