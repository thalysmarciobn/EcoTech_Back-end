<?php

namespace App;

use App\Rotas\ChamadaDeRotas;
use App\Intermediario\Receptaculo;
use ReflectionMethod;
use Exception;

class Aplicacao
{
    private array $controladores = [];
    private array $chamadas = [];
    
    public function rota($rota, $metodo, $parametros): void
    {
        [$classe, $funcao] = $parametros;
        
        $this->verificarEAdicionarClasse($classe);
        $this->verificarEAdicionarChamada($rota);
        
        $classeInstanciada = $this->obterClasseInstanciada($classe);
        $retorno = $this->obterRetornoChamada($rota);
        
        $this->processarMetodo($metodo, $retorno, $classeInstanciada, $funcao);
    }
    
    private function verificarEAdicionarClasse(string $classe): void
    {
        $classeExistente = array_filter($this->controladores, function($controlador) use ($classe) {
            return $controlador['classe'] instanceof $classe;
        });

        if (empty($classeExistente)) {
            $classeInstanciada = new $classe(new Receptaculo());
            $this->controladores[] = [
                'nome' => get_class($classeInstanciada),
                'classe' => $classeInstanciada
            ];
        }
    }
    
    private function verificarEAdicionarChamada(string $rota): void
    {
        if (!in_array($rota, array_column($this->chamadas, 'rota'))) {
            $this->chamadas[] = [
                'rota' => $rota,
                'chamadas' => new ChamadaDeRotas()
            ];
        }
    }
    
    private function obterClasseInstanciada(string $classe): object
    {
        $indiceControlador = array_search($classe, array_column($this->controladores, 'nome'));

        if ($indiceControlador === false) {
            throw new Exception("Controlador não encontrado.", 1);
        }

        return $this->controladores[$indiceControlador]['classe'];
    }
    
    private function obterRetornoChamada(string $rota): object
    {
        $indiceChamada = array_search($rota, array_column($this->chamadas, 'rota'));

        if ($indiceChamada === false) {
            throw new Exception("Rota não encontrada.", 1);
        }

        return $this->chamadas[$indiceChamada]['chamadas'];
    }
    
    private function processarMetodo(string $metodo, ChamadaDeRotas $retorno, object $classeInstanciada, string $funcao): void
    {
        $metodosValidos = [
            "GET" => "atualizarGet",
            "POST" => "atualizarPost",
            "PUT" => "atualizarPut",
            "DELETE" => "atualizarDelete"
        ];

        if (isset($metodosValidos[$metodo])) {
            $funcaoMetodo = $metodosValidos[$metodo];
            if (!$retorno->$funcaoMetodo(['classe' => $classeInstanciada, 'metodo' => $funcao])) {
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

        if (isset($metodosValidos[$metodo]))
        {
            $retornoMetodo = $metodosValidos[$metodo];
            $retornoRota = $chamadas->$retornoMetodo();

            $classeInstanciada = $retornoRota['classe'];
            $metodoInstanciado = $retornoRota['metodo'];

            $funcaoMetodo = new ReflectionMethod($classeInstanciada, $metodoInstanciado);

            if ($funcaoMetodo == NULL)
            {
                exit('Metódo não pode ser instanciado: ' . $metodoInstanciado);
            }
            
            return $this->retorno($funcaoMetodo->invoke($classeInstanciada));
        }

        throw new Exception("Método HTTP inválido.", 1);
    }
    
    private function retorno($objeto): string
    {
        return json_encode($objeto);
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
