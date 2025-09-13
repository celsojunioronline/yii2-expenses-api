<?php

declare(strict_types=1);

namespace Api;

use ApiTester;

final class ExpenseCest
{
    private int $expenseId;

    public function createExpense(ApiTester $I): void
    {
        // Login
        $I->sendPOST('auth/login', [
            'email'    => 'admin@teste.com',
            'password' => 'admin123'
        ]);
        $token = $I->grabDataFromResponseByJsonPath('$.data.token')[0];
        $I->amBearerAuthenticated($token);

        // Cria despesa
        $I->sendPOST('expense/create', [
            'description'  => 'Supermercado Teste',
            'category_id'  => 1,
            'amount'       => 120.5,
            'expense_date' => date('Y-m-d'),
        ]);

        $I->seeResponseCodeIs(201);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson(['success' => true]);
        $I->seeResponseMatchesJsonType([
            'data' => [
                'id'           => 'integer',
                'user_id'      => 'integer',
                'description'  => 'string',
                'category_id'  => 'integer',
                'amount'       => 'float|string',
                'expense_date' => 'string',
            ]
        ]);


        $this->expenseId = $I->grabDataFromResponseByJsonPath('$.data.id')[0];
    }

    public function listExpenses(ApiTester $I): void
    {
        $I->sendPOST('auth/login', [
            'email'    => 'admin@teste.com',
            'password' => 'admin123'
        ]);
        $token = $I->grabDataFromResponseByJsonPath('$.data.token')[0];
        $I->amBearerAuthenticated($token);

        $I->sendGET('expense/index', ['page' => 1, 'per_page' => 5]);

        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson(['success' => true]);
        $I->seeResponseMatchesJsonType([
            'data' => 'array',
            'meta' => [
                'total'       => 'integer',
                'page'        => 'integer',
                'per_page'    => 'integer',
                'total_pages' => 'integer',
                'sort'        => 'string|null',
            ]
        ]);
    }

    public function viewExpense(ApiTester $I): void
    {
        $I->sendPOST('auth/login', [
            'email'    => 'admin@teste.com',
            'password' => 'admin123'
        ]);
        $token = $I->grabDataFromResponseByJsonPath('$.data.token')[0];
        $I->amBearerAuthenticated($token);

        $I->sendGET("expense/view/{$this->expenseId}");

        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson(['success' => true]);
        $I->seeResponseMatchesJsonType([
            'data' => [
                'id'           => 'integer',
                'user_id'      => 'integer',
                'description'  => 'string',
                'category_id'  => 'integer',
                'amount' => 'float|string',
                'expense_date' => 'string',
            ]
        ]);
    }

    public function updateExpense(ApiTester $I): void
    {
        $I->sendPOST('auth/login', [
            'email'    => 'admin@teste.com',
            'password' => 'admin123'
        ]);
        $token = $I->grabDataFromResponseByJsonPath('$.data.token')[0];
        $I->amBearerAuthenticated($token);

        $I->sendPUT("expense/update/{$this->expenseId}", [
            'description' => 'Supermercado Atualizado',
            'amount'      => 250.0,
        ]);

        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson(['success' => true]);
        $I->seeResponseMatchesJsonType([
            'data' => [
                'id'           => 'integer',
                'user_id'      => 'integer',
                'description'  => 'string',
                'category_id'  => 'integer',
                'amount' => 'float|string',
                'expense_date' => 'string',
            ]
        ]);
    }

    public function deleteExpense(ApiTester $I): void
    {
        $I->sendPOST('auth/login', [
            'email'    => 'admin@teste.com',
            'password' => 'admin123'
        ]);
        $token = $I->grabDataFromResponseByJsonPath('$.data.token')[0];
        $I->amBearerAuthenticated($token);

        $I->sendDELETE("expense/delete/{$this->expenseId}");

        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson(['success' => true]);
    }
}