<?php

namespace app\helpers;

use Yii;
use yii\web\Response;

class ApiResponse
{
    public static function json(array $data, int $status = 200): array
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        Yii::$app->response->statusCode = $status;

        return [
            'success' => $data['success'] ?? true,
            'data'    => $data['data']    ?? null,
            'errors'  => $data['errors']  ?? [],
            'message' => $data['message'] ?? null,
            'meta'    => $data['meta']    ?? null,
        ];
    }
}