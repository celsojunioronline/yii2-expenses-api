<?php

namespace app\controllers;

use app\helpers\ApiResponse;
use app\helpers\ApiStatus;
use app\middleware\RateLimiter;
use app\services\AuthService;
use Yii;
use yii\rest\Controller;

class AuthController extends Controller
{
    public $enableCsrfValidation = false;

    public function behaviors()
    {
        $behaviors = parent::behaviors();
        // Garante que sempre responda em JSON
        $behaviors['contentNegotiator']['formats']['application/json'] = \yii\web\Response::FORMAT_JSON;
        return $behaviors;
    }

    public function verbs()
    {
        return [
            'register' => ['POST'],
            'login'    => ['POST'],
        ];
    }

    public function actionRegister()
    {
        $data = Yii::$app->request->post();

        $service = new AuthService();
        $result = $service->register($data);

        $status = $result['success'] ? ApiStatus::CREATED : ApiStatus::BAD_REQUEST;
        return ApiResponse::json($result, $status);
    }

    public function actionLogin()
    {
        $data = Yii::$app->request->post();
        $ip = Yii::$app->request->userIP;

        if (empty($data['email']) || empty($data['password'])) {
            return ApiResponse::json([
                'success' => false,
                'message' => 'Email e senha são obrigatórios'
            ], ApiStatus::BAD_REQUEST);
        }

        $limiter = new RateLimiter('login:' . $ip . ':' . $data['email'], 5, 900);

        if ($limiter->hasTooManyAttempts()) {
            return ApiResponse::json([
                'success' => false,
                'message' => 'Muitas tentativas de login. Tente novamente mais tarde.'
            ], ApiStatus::TOO_MANY_REQUESTS);
        }

        $service = new AuthService();
        $result = $service->login($data['email'], $data['password']);

        if ($result['success']) {
            $limiter->reset();
        } else {
            $limiter->hit();
        }

        $status = $result['success'] ? ApiStatus::OK : ApiStatus::UNAUTHORIZED;
        return ApiResponse::json($result, $status);
    }
}