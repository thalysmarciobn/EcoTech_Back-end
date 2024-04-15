<?php

namespace App;

use App\Rotas\Chamadas;
use App\Intermediario\Receptaculo;
use ReflectionMethod;
use Exception;

class Aplicacao
{
    private array $controladores = [];
    private array $chamadas = [];
    private array $metodosValidosDeChamadas = [
        "GET" => "get",
        "POST" => "post",
        "PUT" => "put",
        "DELETE" => "delete"
    ];

    private ?Receptaculo $receptaculo = NULL;

    public function __construct()
    {
        $this->receptaculo = new Receptaculo();
    }
    
    /**
     * @author: Thalys Márcio
     * @created: 11/04/2024
     * @summary: Inicia o processo de roteamento com os dados de rota, método HTTP e parâmetros
     */
    public function rota($rota, $metodo, $parametros): void
    {
        [$classe, $funcao] = $parametros;
        
        $this->verificarEAdicionarClasse($classe);
        $this->verificarEAdicionarChamada($rota);
        
        $classeInstanciada = $this->obterClasseInstanciada($classe);
        $retorno = $this->obterRetornoChamada($rota);
        
        $this->processarMetodo($metodo, $retorno, $classeInstanciada, $funcao);
    }
    
    /**
     * @author: Thalys Márcio
     * @created: 11/04/2024
     * @summary: Verifica a nescessidade de adicionar uma classe instanciada
     */
    private function verificarEAdicionarClasse(string $classe): void
    {
        $classeExistente = array_filter($this->controladores, function($controlador) use ($classe)
        {
            return $controlador['classe'] instanceof $classe;
        });

        if (empty($classeExistente))
        {
            $classeInstanciada = new $classe($this->receptaculo);
            $this->controladores[] = [
                'nome' => get_class($classeInstanciada),
                'classe' => $classeInstanciada
            ];
        }
    }
    
    /**
     * @author: Thalys Márcio
     * @created: 11/04/2024
     * @summary: Verifica a nescessidade de adicionar uma Chamada baseada na rota
     */
    private function verificarEAdicionarChamada(string $rota): void
    {
        if (!in_array($rota, array_column($this->chamadas, 'rota')))
        {
            $this->chamadas[] = [
                'rota' => $rota,
                'chamadas' => new Chamadas()
            ];
        }
    }
    
    /**
     * @author: Thalys Márcio
     * @created: 11/04/2024
     * @summary: Obtem uma classe instanciada dos Controladores
     */
    private function obterClasseInstanciada(string $classe): object
    {
        $indiceControlador = array_search($classe, array_column($this->controladores, 'nome'));

        if ($indiceControlador === false)
        {
            throw new Exception("Controlador não encontrado.", 1);
        }

        return $this->controladores[$indiceControlador]['classe'];
    }
    
    /**
     * @author: Thalys Márcio
     * @created: 11/04/2024
     * @summary: Retorna uma Chamada baseada na rota acessada
     */
    private function obterRetornoChamada(string $rota): object
    {
        $indiceChamada = array_search($rota, array_column($this->chamadas, 'rota'));

        if ($indiceChamada === false)
        {
            throw new Exception("Rota não encontrada.", 1);
        }

        return $this->chamadas[$indiceChamada]['chamadas'];
    }
    
    /**
     * @author: Thalys Márcio
     * @created: 11/04/2024
     * @summary: Processa o método nas Chamadas para registrar qual método HTTP ele é processado
     */
    private function processarMetodo(string $metodo, Chamadas $retorno, object $classeInstanciada, string $funcao): void
    {
        if (isset($this->metodosValidosDeChamadas[$metodo]))
        {
            $funcaoMetodo = $this->metodosValidosDeChamadas[$metodo];
            $retorno->$funcaoMetodo = ['classe' => $classeInstanciada, 'metodo' => $funcao];
        }
        else
        {
            throw new Exception("Método HTTP inválido.", 1);
        }
    }
    
    /**
     * @author: Thalys Márcio
     * @created: 11/04/2024
     * @summary: Libera a origem CORS
     */
    private function liberarOrigem(): void
    {
        header("Access-Control-Allow-Origin: *");
    }

    /**
     * @author: Thalys Márcio
     * @created: 11/04/2024
     * @summary: Renderiza um JSON a partir de uma busca de método buscado na lista de Controladores
     */
    private function renderizar($metodo, $chamadas): string
    {
        if (isset($this->metodosValidosDeChamadas[$metodo]))
        {
            $retornoMetodo = $this->metodosValidosDeChamadas[$metodo];
            $retornoRota = $chamadas->$retornoMetodo;

            $classeInstanciada = $retornoRota['classe'];
            $metodoInstanciado = $retornoRota['metodo'];

            $funcaoMetodo = new ReflectionMethod($classeInstanciada, $metodoInstanciado);

            if ($funcaoMetodo == NULL)
            {
                throw new Exception('Método não pode ser instanciado: ' . $metodoInstanciado, 1);
            }
            
            $retorno = $funcaoMetodo->invoke($classeInstanciada);

            if (is_array($retorno))
            {
                return $this->retorno($retorno);
            }

            throw new Exception('Retorno inválido.', 1);
        }

        throw new Exception('Método HTTP inválido.', 1);
    }
    
    /**
     * @author: Thalys Márcio
     * @created: 11/04/2024
     * @summary: Cria um retorno em JSON para o navegador.
     */
    private function retorno($objeto): string
    {
        return json_encode($objeto, false);
    }
    
    /**
     * @author: Thalys Márcio
     * @created: 11/04/2024
     * @summary: Busca uma chamada baseada na rota
     */
    private function buscarChamada($rota): ?array
    {
        foreach ($this->chamadas as $chamada)
        {
            if ($chamada['rota'] === $rota)
            {
                return $chamada;
            }
        }
        return null;
    }
    
    /**
     * @author: Thalys Márcio
     * @created: 11/04/2024
     * @summary: Executa a chamada buscada para renderizar
     */
    public function rodar(): void
    {
        $this->liberarOrigem();

        $metodo = $_SERVER['REQUEST_METHOD'];
        $requisicao = substr($_SERVER['REQUEST_URI'], 1);
        $rota = explode('?', $requisicao);

        $chamada = $this->buscarChamada($rota[0]);

        if ($chamada !== null)
        {
            print $this->renderizar($metodo, $chamada['chamadas']);
        }
        else
        {
            throw new Exception("Rota não encontrada.", 404);
        }
    }
}
