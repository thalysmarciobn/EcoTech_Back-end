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
                'chamadas' => new ChamadaDeRotas()
            ]);
        }
    }

    private function chamarMetodoInterno($parametros)
    {
        $classe = $parametros[0];
        $funcao = $parametros[1];
        
        $chamada = new \ReflectionMethod($classe, $funcao);
        
        return $chamada;
    } 

    public function rota($rota, $metodo, $parametros): void
    {
        $this->checarChamada($rota);

        $indiceChamada = -1;

        for ($indice = 0; $indice < count($this->chamadas); $indice++)
        {
            $chamada = $this->chamadas[$indice];

            if ($chamada['rota']) {
                $indiceChamada = $indice;
            }
        }

        $retorno = $this->chamadas[$indiceChamada];

        $chamada = $retorno['chamadas'];
        
        $metodoInterno = $this->chamarMetodoInterno($parametros);

        switch ($metodo)
        {
            case "GET":
                if (!$chamada->atualizarGet($metodoInterno))
                    throw new \Exception("Rota já existente.", 1);
                break;
            case "POST":
                if (!$chamada->atualizarPost($metodoInterno))
                    throw new \Exception("Rota já existente.", 1);
                break;
            case "PUT":
                if (!$chamada->atualizarPut($metodoInterno))
                    throw new \Exception("Rota já existente.", 1);
                break;
            case "DELETE":
                if (!$chamada->atualizarDelete($metodoInterno))
                    throw new \Exception("Rota já existente.", 1);
                break;
        }
    }

    private function liberarOrigem(): void
    {
        header("Allow-Control-Access-Origin: *");
    }

    private function renderizar($metodo, $chamadas)
    {
        switch ($metodo)
        {
            case "GET":
                return json_encode($chamadas->get()?->invoke(null));
            case "POST":
                return json_encode($chamadas->post()?->invoke(null));
            case "DELETE":
                return json_encode($chamadas->delete()?->invoke(null));
            default:
                return json_encode();
        }
    }
    
    public function rodar(): void
    {
        $this->liberarOrigem();

        $metodo = $_SERVER['REQUEST_METHOD'];
        $requisicao = substr($_SERVER['REQUEST_URI'], 1);

        for ($indice = 0; $indice < count($this->chamadas); $indice++)
        {
            $chamada = $this->chamadas[$indice];

            $rota = $chamada['rota'];
            $chamadas = $chamada['chamadas'];

            if ($rota == $requisicao)
            {
                print $this->renderizar($metodo, $chamadas);
                break;
            }
        }
    }
}