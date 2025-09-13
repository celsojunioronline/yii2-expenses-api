<?php

namespace app\middleware;

use Yii;

class RateLimiter
{
    private string $key;
    private int $maxAttempts;
    private int $decaySeconds;

    public function __construct(string $key, int $maxAttempts = 5, int $decaySeconds = 900)
    {
        $this->key = 'ratelimit:' . md5($key);
        $this->maxAttempts = $maxAttempts;
        $this->decaySeconds = $decaySeconds;
    }


    public function hasTooManyAttempts(): bool
    {
        $attempts = Yii::$app->cache->get($this->key) ?? 0;
        return $attempts >= $this->maxAttempts;
    }

    public function hit(): void
    {
        $attempts = Yii::$app->cache->get($this->key) ?? 0;
        Yii::$app->cache->set($this->key, $attempts + 1, $this->decaySeconds);
    }

    public function remainingAttempts(): int
    {
        $attempts = Yii::$app->cache->get($this->key) ?? 0;
        return max(0, $this->maxAttempts - $attempts);
    }

    public function reset(): void
    {
        Yii::$app->cache->delete($this->key);
    }
}