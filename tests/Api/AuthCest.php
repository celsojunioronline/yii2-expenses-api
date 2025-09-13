<?php

declare(strict_types=1);

namespace Api;

use ApiTester;

final class AuthCest
{
    public function registerUser(ApiTester $I): void
    {
        $email = 'user_' . uniqid() . '@example.com';

        $I->sendPOST('auth/register', [
            'name'     => 'User Test',
            'email'    => $email,
            'password' => '123456'
        ]);

        $I->seeResponseCodeIs(201);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson(['success' => true]);
        $I->seeResponseMatchesJsonType([
            'data' => [
                'id'    => 'integer',
                'name'  => 'string',
                'email' => 'string',
            ],
            'message' => 'string',
        ]);
    }

    public function loginUser(ApiTester $I): void
    {
        $I->sendPOST('auth/login', [
            'email'    => 'admin@teste.com',
            'password' => 'admin123'
        ]);

        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson(['success' => true]);
        $I->seeResponseMatchesJsonType([
            'data' => [
                'token' => 'string',
                'user'  => [
                    'id'    => 'integer',
                    'name'  => 'string',
                    'email' => 'string',
                ]
            ],
            'message' => 'string',
        ]);

        $token = $I->grabDataFromResponseByJsonPath('$.data.token')[0];
        $I->assertNotEmpty($token);

        $I->amBearerAuthenticated($token);
    }

    public function loginWithInvalidPassword(ApiTester $I): void
    {
        $I->sendPOST('auth/login', [
            'email'    => 'admin@teste.com',
            'password' => 'senhaErrada123'
        ]);

        $I->seeResponseCodeIs(401);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson(['success' => false]);
        $I->seeResponseMatchesJsonType([
            'message' => 'string',
            'errors'  => 'array',
        ]);
    }
}
