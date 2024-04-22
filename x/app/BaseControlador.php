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

    /**
     * @author: Thalys Márcio
     * @created: 14/04/2024
     * @summary: Retorna um dado POST de uma chave específica do navegador
     */
    protected function post(string $nome): ?string
    {
        return $_POST[$nome] ?? null;
    }

    /**
     * @author: Thalys Márcio
     * @created: 14/04/2024
     * @summary: Obtêm um dado GET de uma chave específica do navegador
     */
    protected function get(string $nome): ?string
    {
        return $_GET[$nome] ?? null;
    }

    /**
     * @author: Thalys Márcio
     * @created: 14/04/2024
     * @summary: Retorna as informações a serem exibidas pela API
     */
    protected function responder(array $data, int $code = 200): array
    {
        http_response_code($code);
        return $data;
    }
}