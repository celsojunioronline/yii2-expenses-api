<?php

namespace app\services;

use app\models\User;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Yii;

class AuthService
{
    private string $jwtKey;
    private string $jwtAlgo = 'HS256';
    private int $jwtExpire;

    public function __construct()
    {
        $this->jwtKey = Yii::$app->params['jwt_key'] ?? 'chave_default_insegura';
        $this->jwtExpire = Yii::$app->params['jwt_expire'] ?? 3600;
    }

    private function mapUser(User $user): array
    {
        return [
            'id'    => (int) $user->id,
            'name'  => (string) $user->name,
            'email' => (string) $user->email,
        ];
    }

    public function register(array $data): array
    {
        if (User::findByEmail($data['email'] ?? '')) {
            return [
                'success' => false,
                'message' => 'Email já cadastrado',
                'errors'  => ['email' => ['Este email já está em uso']]
            ];
        }

        $user = new User();
        $user->name  = $data['name'] ?? null;
        $user->email = $data['email'] ?? null;

        if (empty($data['password'])) {
            return [
                'success' => false,
                'message' => 'Senha obrigatória',
                'errors'  => ['password' => ['A senha é obrigatória']]
            ];
        }

        $user->setPassword($data['password']);
        $user->generateAuthKey();

        if ($user->save()) {
            return [
                'success' => true,
                'data'    => $this->mapUser($user),
                'message' => 'Usuário registrado com sucesso'
            ];
        }

        return [
            'success' => false,
            'errors'  => $user->errors,
            'message' => 'Falha ao registrar usuário'
        ];
    }

    public function login(string $email, string $password): array
    {
        $user = User::findByEmail($email);

        if (!$user || !$user->validatePassword($password)) {
            return [
                'success' => false,
                'message' => 'Credenciais inválidas',
                'errors'  => ['login' => ['Email ou senha incorretos']]
            ];
        }

        $payload = [
            'iss'   => 'yii2-expenses',
            'sub'   => $user->id,
            'email' => $user->email,
            'iat'   => time(),
            'exp'   => time() + $this->jwtExpire,
        ];

        $jwt = JWT::encode($payload, $this->jwtKey, $this->jwtAlgo);

        return [
            'success' => true,
            'data'    => [
                'token' => $jwt,
                'user'  => $this->mapUser($user),
            ],
            'message' => 'Login realizado com sucesso.'
        ];
    }

    public function validateToken(string $token): ?User
    {
        try {
            $decoded = JWT::decode($token, new Key($this->jwtKey, $this->jwtAlgo));
            return User::findOne($decoded->sub);
        } catch (\Exception $e) {
            return null;
        }
    }
}
