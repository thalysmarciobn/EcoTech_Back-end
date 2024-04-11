<?php

namespace App;

class Aplicacao
{
    private $rotas = array();

    public function adicionarRota($rota, $parametros): void
    {
        $classe = $parametros[0];
        $funcao = $parametros[1];
        
        $construtor = new \ReflectionMethod($classe, $funcao);
        $chamada = [$rota, $construtor];
        
        if (!in_array($chamada, $this->rotas))
        {
            array_push($this->rotas, [$rota, $construtor]);
        }

        $construtor->invoke(null);
    }

    private function liberarOrigem(): void
    {
        header("Allow-Control-Access-Origin: *");
    }
    
    public function rodar(): void
    {
        $this->liberarOrigem();

        $requisicao = substr($_SERVER['REQUEST_URI'], 1);


    }
}