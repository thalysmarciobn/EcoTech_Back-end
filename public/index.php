<?php

require '../vendor/autoload.php';

use App\Aplicacao;
use App\Controladores\TesteControlador;
use App\Controladores\UsuarioControlador;
use App\Controladores\ProdutosControlador;
use App\Controladores\ResiduosControlador;
use App\Controladores\MateriaisControlador;

$app = new Aplicacao();

define('API', 'api/');
define('PRODUTOS', API . 'produtos/');
define('RESIDUOS', API . 'residuos/');
define('MATERIAIS', API . 'materiais/');
define('SOLICITACOES',API .'solicitacoes/');

// Rotas para Teste
$app->rota(API . 'teste', 'GET', [TesteControlador::class, 'teste']);
$app->rota(API . 'teste/chave', 'GET', [TesteControlador::class, 'testarChave']);

// Rotas para Usuários
$app->rota(API . 'logar', 'POST', [UsuarioControlador::class, 'logar']);
$app->rota(API . 'cadastrar', 'POST', [UsuarioControlador::class, 'cadastrar']);

// Rotas para Produtos
$app->rota(PRODUTOS . 'lista', 'GET', [ProdutosControlador::class, 'listaProdutos']);
$app->rota(PRODUTOS . 'adicionar', 'POST', [ProdutosControlador::class, 'adicionarProduto']);
$app->rota(PRODUTOS . 'atualizar', 'POST', [ProdutosControlador::class, 'atualizarProduto']);
$app->rota(PRODUTOS . 'remover', 'POST', [ProdutosControlador::class, 'removerProduto']);

// Rotas para Resíduos
$app->rota(RESIDUOS . 'lista', 'GET', [ResiduosControlador::class, 'listaResiduos']);
$app->rota(RESIDUOS . 'adicionar', 'POST', [ResiduosControlador::class, 'adicionarResiduo']);
$app->rota(RESIDUOS . 'atualizar', 'POST', [ResiduosControlador::class, 'atualizarResiduo']);
$app->rota(RESIDUOS . 'remover', 'POST', [ResiduosControlador::class, 'removerResiduo']);

// Rotas para Materiais
$app->rota(MATERIAIS . 'lista', 'GET', [MateriaisControlador::class, 'listaMateriais']);
$app->rota(MATERIAIS . 'adicionar', 'POST', [MateriaisControlador::class, 'adicionarMaterial']);
$app->rota(MATERIAIS . 'remover', 'POST', [MateriaisControlador::class, 'removerMaterial']);

$app->rota(SOLICITACOES . 'adiconar','POST',[SolicitacoesControlador::class],'adcionarSolicitacoes');

// Execução da aplicação
$app->rodar();
