<?php

namespace App;

use App\Intermediario\Receptaculo;

abstract class BaseControlador
{
    protected Receptaculo $receptaculo;
    
    public function __construct(Receptaculo $receptaculo)
    {
        $this->receptaculo = $receptaculo;
    }

    protected function post(string $nome): ?string
    {
        return $_POST[$nome] ?? null;
    }

    protected function get(string $nome): ?string
    {
        return $_GET[$nome] ?? null;
    }

    protected function responder(array $data, int $code = 200): array
    {
        http_response_code($code);
        return $data;
    }
}