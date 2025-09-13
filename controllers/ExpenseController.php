<?php

namespace app\controllers;

use app\helpers\ApiResponse;
use app\helpers\ApiStatus;
use app\middleware\JwtAuthMiddleware;
use app\models\Expense;
use app\services\ExpenseService;
use Yii;
use yii\rest\Controller;

class ExpenseController extends Controller
{
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['authenticator'] = [
            'class' => JwtAuthMiddleware::class,
        ];
        return $behaviors;
    }

    public function verbs()
    {
        return [
            'create' => ['POST'],
            'index'  => ['GET'],
            'view'   => ['GET'],
            'update' => ['PUT', 'PATCH'],
            'delete' => ['DELETE'],
        ];
    }

    public function actionCreate()
    {
        $userId = Yii::$app->user->id;
        $data = Yii::$app->request->post();

        $service = (new ExpenseService())
            ->comUsuario($userId)
            ->comDados($data);

        $result = $service->criar();

        return ApiResponse::json($result, ApiStatus::CREATED);
    }

    public function actionIndex()
    {
        $userId = Yii::$app->user->id;
        $filters = Yii::$app->request->get();

        $service = (new ExpenseService())
            ->comUsuario($userId)
            ->comFiltros($filters);

        $result = $service->listar();

        return ApiResponse::json($result, ApiStatus::OK);
    }

    public function actionView($id)
    {
        $userId = Yii::$app->user->id;
        $service = (new ExpenseService())->comUsuario($userId);

        $expense = $service->detalhar($id);

        if (!$expense) {
            return ApiResponse::json(
                ['success' => false, 'message' => 'Despesa não encontrada ou acesso negado'],
                ApiStatus::NOT_FOUND
            );
        }

        return ApiResponse::json(['success' => true, 'data' => $expense], ApiStatus::OK);
    }

    public function actionUpdate($id)
    {
        $userId = Yii::$app->user->id;
        $expense = Expense::findOne(['id' => $id, 'deleted_at' => null]);

        if (!$expense) {
            return ApiResponse::json(
                ['success' => false, 'message' => 'Despesa não encontrada'],
                ApiStatus::NOT_FOUND
            );
        }

        $data = Yii::$app->request->post();

        $service = (new ExpenseService())
            ->comUsuario($userId)
            ->comDespesa($expense)
            ->comDados($data);

        $result = $service->atualizar();

        return ApiResponse::json($result, ApiStatus::OK);
    }

    public function actionDelete($id)
    {
        $userId = Yii::$app->user->id;
        $expense = Expense::findOne(['id' => $id, 'deleted_at' => null]);

        if (!$expense) {
            return ApiResponse::json(
                ['success' => false, 'message' => 'Despesa não encontrada'],
                ApiStatus::NOT_FOUND
            );
        }

        $service = (new ExpenseService())
            ->comUsuario($userId)
            ->comDespesa($expense);

        $result = $service->excluir();

        return ApiResponse::json($result, ApiStatus::OK);
    }
}
