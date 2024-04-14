<?php

namespace App;

use App\Rotas\ChamadaDeRotas;
use ReflectionMethod;
use Exception;

class Aplicacao
{
    private array $chamadas = [];

    private function checarChamada($rota): void
    {
        if (!in_array($rota, array_column($this->chamadas, 'rota'))) {
            array_push($this->chamadas, [
                'rota' => $rota,
                'chamadas' => new ChamadaDeRotas()
            ]);
        }
    }

    private function chamarMetodoInterno($parametros): ReflectionMethod
    {
        [$classe, $funcao] = $parametros;
        return new ReflectionMethod($classe, $funcao);
    }

    public function rota($rota, $metodo, $parametros): void
    {
        $this->checarChamada($rota);

        $indiceChamada = array_search($rota, array_column($this->chamadas, 'rota'));

        if ($indiceChamada === false) {
            throw new Exception("Rota não encontrada.", 1);
        }

        $retorno = $this->chamadas[$indiceChamada]['chamadas'];

        $metodoInterno = $this->chamarMetodoInterno($parametros);

        $metodosValidos = [
            "GET" => "atualizarGet",
            "POST" => "atualizarPost",
            "PUT" => "atualizarPut",
            "DELETE" => "atualizarDelete"
        ];
        
        if (isset($metodosValidos[$metodo])) {
            $funcaoMetodo = $metodosValidos[$metodo];
            if (!$retorno->$funcaoMetodo($metodoInterno)) {
                throw new Exception("Rota já existente.", 1);
            }
        } else {
            throw new Exception("Método HTTP inválido.", 1);
        }
    }

    private function liberarOrigem(): void
    {
        header("Access-Control-Allow-Origin: *");
    }

    private function renderizar($metodo, $chamadas): string
    {
        $metodosValidos = [
            "GET" => "get",
            "POST" => "post",
            "PUT" => "put",
            "DELETE" => "delete"
        ];

        if (isset($metodosValidos[$metodo])) {
            $funcaoMetodo = $metodosValidos[$metodo];
            $funcao = $chamadas->$funcaoMetodo();

            if ($funcao !== null) {
                return $this->retorno($funcao->invoke(null));
            }
        }

        throw new Exception("Método HTTP inválido.", 1);
    }

    private function retorno($objeto): string
    {
        http_response_code($objeto['code']);
        return json_encode($objeto['data']);
    }

    private function buscarChamada($rota): ?array
    {
        foreach ($this->chamadas as $chamada) {
            if ($chamada['rota'] === $rota) {
                return $chamada;
            }
        }
        return null;
    }
    
    public function rodar(): void
    {
        $this->liberarOrigem();

        $metodo = $_SERVER['REQUEST_METHOD'];
        $requisicao = substr($_SERVER['REQUEST_URI'], 1);

        $chamada = $this->buscarChamada($requisicao);

        if ($chamada !== null) {
            print $this->renderizar($metodo, $chamada['chamadas']);
        } else {
            throw new Exception("Rota não encontrada.", 404);
        }
    }
}
