<?php

namespace App\Utils;

use Predis\Client;

class RedisClient {
    private static ?Client $instance = null;

    private function __construct() {}

    public static function getInstance(): Client {
        if (self::$instance === null) {
            self::$instance = new Client([
                'scheme' => 'tcp',
                'host'   => env('REDIS_HOST') ?: 'redis',
                'port'   => env('REDIS_PORT') ?: 6379,
                'password' => env('REDIS_PASSWORD') ?: null,
            ]);
        }
        return self::$instance;
    }
}
