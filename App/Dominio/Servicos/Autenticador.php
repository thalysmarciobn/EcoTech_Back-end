<?php

namespace App\Dominio\Servicos;

class Autenticador
{
    /**
     * @author: Thalys Márcio
     * @created: 14/04/2024
     * @summary: Cria uma chave aleatória em bytes basseada em um tamanho
     */
    public function chaveAleatoria(int $tamanho): string
    {
        return openssl_random_pseudo_bytes($tamanho);
    }

    /**
     * @author: Thalys Márcio
     * @created: 14/04/2024
     * @summary: Converte bytes para HEX
     */
    public function converterHex($dado): string
    {
        return bin2hex($pseudoBytes);
    }
}