<?php

namespace App\Intermediario;

use App\Dominio\Servicos\Autenticador;

class Receptaculo
{
    public ?Autenticador $autenticador = NULL;

    public function __construct()
    {
        $this->autenticador = new Autenticador();
    }

    public function teste(): string
    {
        return "aa";
    }
}