<?php

namespace App\Dominio\Servicos;

class Autenticador
{
    public function chaveAleatoria(int $tamanho): string
    {
        return openssl_random_pseudo_bytes($tamanho);
    }

    public function converterHex($dado): string
    {
        return bin2hex($pseudoBytes);
    }
}