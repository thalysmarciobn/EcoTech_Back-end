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

        $indiceChamada = array_search('chamadas', $this->chamadas);

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
                switch ($metodo)
                {
                    case "GET":
                        $chamadas->get()?->invoke(null);
                        break;
                    case "POST":
                        $chamadas->post()?->invoke(null);
                        break;
                    case "DELETE":
                        $chamadas->delete()?->invoke(null);
                        break;
                    default:
                        break;
                }
                break;
            }
        }
    }
}