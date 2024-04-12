<?php

require '../vendor/autoload.php';

use App\Aplicacao;
use App\Banco\PDO;

PDO::conectar();

$app = new Aplicacao();

$app->rota('test', 'GET', [App\Controladores\TesteControlador::class, 'teste']);

$app->rota('api/logar','POST',[App\Controladores\UsuarioControlador::class,'logar']);

$app->rota('api/cadastrar', 'POST', [App\Controladores\UsuarioControlador::class, 'cadastrar']);

// Produtos
$app->rota('api/produtos/lista', 'GET', [App\Controladores\ProdutosControlador::class, 'listaProdutos']);
$app->rota('api/produtos/adicionar', 'POST', [App\Controladores\ProdutosControlador::class, 'adicionarProduto']);
$app->rota('api/produtos/atualizar', 'POST', [App\Controladores\ProdutosControlador::class, 'atualizarProduto']);
$app->rota('api/produtos/remover', 'POST', [App\Controladores\ProdutosControlador::class, 'removerProduto']);

// Resíduos
$app->rota('api/residuos/lista', 'GET', [App\Controladores\ResiduosControlador::class, 'listaResiduos']);
$app->rota('api/residuos/adicionar', 'POST', [App\Controladores\ResiduosControlador::class, 'adicionarResiduo']);
$app->rota('api/residuos/atualizar', 'POST', [App\Controladores\ResiduosControlador::class, 'atualizarResiduo']);
$app->rota('api/residuos/remover', 'POST', [App\Controladores\ResiduosControlador::class, 'removerResiduo']);

// Materiais
$app->rota('api/materiais/lista', 'GET', [App\Controladores\MateriaisControlador::class, 'listaMateriais']);
$app->rota('api/materiais/adicionar', 'POST', [App\Controladores\MateriaisControlador::class, 'adicionarMaterial']);
$app->rota('api/materiais/remover', 'POST', [App\Controladores\MateriaisControlador::class, 'removerMaterial']);

$app->rodar();