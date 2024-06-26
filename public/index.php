<?php

require '../vendor/autoload.php';

use App\Aplicacao;
use App\Controladores\TesteControlador;
use App\Controladores\UsuarioControlador;
use App\Controladores\ProdutosControlador;
use App\Controladores\ResiduosControlador;
use App\Controladores\MateriaisControlador;
use App\Controladores\SolicitacoesControlador;

$app = new Aplicacao();

define('API', 'api/');
define('USUARIO', API . 'usuario/');
define('PRODUTOS', API . 'produtos/');
define('RESIDUOS', API . 'residuos/');
define('MATERIAIS', API . 'materiais/');
define('SOLICITACOES', API .'solicitacoes/');

// Rotas para Teste
$app->rota(API . 'teste', 'GET', [TesteControlador::class, 'teste']);
$app->rota(API . 'teste/chave', 'GET', [TesteControlador::class, 'testarChave']);

// Rotas para Usuários
$app->rota(API . 'logar', ['POST', 'OPTIONS'], [UsuarioControlador::class, 'logar']);
$app->rota(API . 'cadastrar', ['POST', 'OPTIONS'], [UsuarioControlador::class, 'cadastrar']);
$app->rota(USUARIO . 'enderecos', ['GET', 'OPTIONS'], [UsuarioControlador::class, 'enderecos']);
$app->rota(USUARIO . 'lista', ['GET', 'OPTIONS'], [UsuarioControlador::class, 'listaUsuarios']);

// Rotas para Produtos
$app->rota(PRODUTOS . 'lista', ['GET', 'OPTIONS'], [ProdutosControlador::class, 'listaProdutos']);
$app->rota(PRODUTOS . 'adicionar', ['POST', 'OPTIONS'], [ProdutosControlador::class, 'adicionarProduto']);
$app->rota(PRODUTOS . 'atualizar', ['POST', 'OPTIONS'], [ProdutosControlador::class, 'atualizarProduto']);
$app->rota(PRODUTOS . 'remover', ['POST', 'OPTIONS'], [ProdutosControlador::class, 'removerProduto']);
$app->rota(PRODUTOS . 'comprar', ['POST', 'OPTIONS'], [ProdutosControlador::class, 'comprarProduto']);

// Rotas para Resíduos
$app->rota(RESIDUOS . 'lista', ['GET', 'OPTIONS'], [ResiduosControlador::class, 'listaResiduos']);

$app->rota(RESIDUOS . 'adicionar', ['POST', 'OPTIONS'], [ResiduosControlador::class, 'adicionarResiduo']);

$app->rota(RESIDUOS . 'remover', ['POST', 'OPTIONS'], [ResiduosControlador::class, 'removerResiduo']);

// Rotas para Materiais
$app->rota(MATERIAIS . 'lista', ['GET', 'OPTIONS'], [MateriaisControlador::class, 'listaMateriais']);
$app->rota(MATERIAIS . 'obter', ['GET', 'OPTIONS'], [MateriaisControlador::class, 'obter']);
$app->rota(MATERIAIS . 'adicionar', ['POST', 'OPTIONS'], [MateriaisControlador::class, 'adicionarMaterial']);
$app->rota(MATERIAIS . 'remover', ['POST', 'OPTIONS'], [MateriaisControlador::class, 'removerMaterial']);

// Rotas para Solicitações
$app->rota(SOLICITACOES . 'adicionar', ['POST', 'OPTIONS'], [SolicitacoesControlador::class, 'adicionarSolicitacao']);
$app->rota(SOLICITACOES . 'listaUsuario', ['GET', 'OPTIONS'], [SolicitacoesControlador::class, 'listaUsuario']);
$app->rota(SOLICITACOES . 'listaUsuario', ['OPTIONS', 'OPTIONS'], [SolicitacoesControlador::class, 'listaUsuario']);

$app->rota(SOLICITACOES . 'lista', ['GET', 'OPTIONS'], [SolicitacoesControlador::class, 'listaSolicitacoes']);
$app->rota(SOLICITACOES . 'aceitar', ['POST', 'OPTIONS'], [SolicitacoesControlador::class, 'aceitarSolicitacao']);
$app->rota(SOLICITACOES . 'negar', ['POST', 'OPTIONS'], [SolicitacoesControlador::class, 'negarSolicitacao']);

$app->rota(SOLICITACOES . 'listaPessoa', ['POST', 'OPTIONS'], [SolicitacoesControlador::class, 'listaPessoaMaterial']);


// Execução da aplicação
$app->rodar();
