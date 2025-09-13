<?php

namespace app\middleware;

use Yii;
use yii\filters\auth\AuthMethod;
use app\services\AuthService;
use yii\web\UnauthorizedHttpException;

class JwtAuthMiddleware extends AuthMethod
{

    /**
     * @inheritDoc
     */
    public function authenticate($user, $request, $response)
    {
        $authHeader = $request->getHeaders()->get('Authorization');

        if ($authHeader && preg_match('/^Bearer\\s+(.*?)$/', $authHeader, $matches)) {
            $token = $matches[1];
            $authService = new AuthService();
            $identity = $authService->validateToken($token);

            if ($identity) {
                $user->setIdentity($identity);
                return $identity;
            }
        }

        $this->handleFailure($response);
    }

    public function handleFailure($response)
    {
        throw new UnauthorizedHttpException('Token inválido ou ausente.');
    }
}