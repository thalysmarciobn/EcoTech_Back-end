<?php

namespace App;

class Aplicacao
{
    private $rotas = array();

    private $diretorioControladores = '\App\Controladores';

    public function adicionarRota($rota, $parametros): void
    {
        $classe = $parametros[0];
        $funcao = $parametros[1];
        
        require('../App/Controladores/' . $classe. '.php');
        
        $construtor = new \ReflectionMethod($this->diretorioControladores . '\\' . $classe, $funcao);
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
    
    public function run(): void
    {
        $this->liberarOrigem();

        $requisicao = substr($_SERVER['REQUEST_URI'], 1);


    }
}