<?php

namespace app\helpers;

class ApiStatus
{
    public const OK = 200;
    public const CREATED = 201;
    public const BAD_REQUEST = 400;
    public const UNAUTHORIZED = 401;
    public const FORBIDDEN = 403;
    public const NOT_FOUND = 404;
    public const TOO_MANY_REQUESTS = 429;
    public const INTERNAL_ERROR = 500;
    public const GATEWAY_TIMEOUT = 504;

}