<?php
namespace App;

class Aplicacao
{
    private $rotas = array();

    public function adicionarRota($local, $parametros): void
    {
        $classe = $parametros[0];
        $chamada = $parametros[1];
    }
    
    public function run(): void
    {
        $requisicao = substr($_SERVER['REQUEST_URI'], 1);
        print $requisicao;
    }
}