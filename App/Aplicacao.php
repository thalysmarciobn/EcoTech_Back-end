<?php

namespace App;

use App\Rotas\ChamadaDeRotas;

class Aplicacao
{
    private $chamadas = array();

    private function checarChamada($rota)
    {
        if (!in_array($rota, $this->chamadas))
        {  
            array_push($this->chamadas, [
                'rota' => $rota,
                'chamada' => new ChamadaDeRotas()
            ]);
        }
    }

    private function chamarMetodo($parametros)
    {
        $classe = $parametros[0];
        $funcao = $parametros[1];
        
        $chamada = new \ReflectionMethod($classe, $funcao);
        
        return $chamada;
    } 

    public function rotaGet($rota, $parametros): void
    {
        $this->checarChamada($rota);

        $chamada = $this->chamarMetodo($parametros);

        var_dump($this->chamadas);
    }

    private function liberarOrigem(): void
    {
        header("Allow-Control-Access-Origin: *");
    }
    
    public function rodar(): void
    {
        $this->liberarOrigem();

        $remoto = $_SERVER['REQUEST_METHOD'];
        $requisicao = substr($_SERVER['REQUEST_URI'], 1);

        switch ($metodo)
        {
            case "GET":
                break;
            case "POST":
                break;
            case "DELETE":
                break;
            default:
                break;
        }
    }
}