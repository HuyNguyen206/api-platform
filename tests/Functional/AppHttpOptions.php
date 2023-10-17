<?php
namespace App\Tests\Functional;

use Zenstruck\Browser\HttpOptions;

class AppHttpOptions extends HttpOptions
{
    public static function api(string $token, $json = null): self
    {
        return self::json($json)
            ->withHeader('Authorization', "Bearer $token");
    }
}