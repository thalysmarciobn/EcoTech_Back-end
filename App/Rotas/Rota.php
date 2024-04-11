<?php

namespace App\Rotas;

class Rota
{
    protected $rota = [];

    protected $chamada = [];
    
    public function __construct($rota, $chamada)
    {
        $this->rota = $rota;
        $this->chamada = $chamada;
    }
}