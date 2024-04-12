<?php

require '../vendor/autoload.php';

use App\Aplicacao;
use App\Banco\PDO;

PDO::conectar();

$app = new Aplicacao();

$app->rota('test', 'GET', [App\Controladores\TesteControlador::class, 'teste']);

$app->rota('api/logar','POST',[App\Controladores\UsuarioControlador::class,'logar']);

$app->rota('api/cadastrar', 'POST', [App\Controladores\UsuarioControlador::class, 'cadastrar']);

$app ->rota('api/CadastrarProduto','POST',[App\Controladores\ProdutoControlador::class,'CadastrarProduto']);

// Resíduos
$app->rota('api/residuos/lista', 'GET', [App\Controladores\ResiduosControlador::class, 'listaResiduos']);
$app->rota('api/residuos/adicionar', 'POST', [App\Controladores\ResiduosControlador::class, 'adicionarResiduo']);
$app->rota('api/residuos/atualizar', 'POST', [App\Controladores\ResiduosControlador::class, 'atualizarResiduo']);
$app->rota('api/residuos/remover', 'POST', [App\Controladores\ResiduosControlador::class, 'removerResiduo']);

// Materiais
$app->rota('api/materiais/lista', 'GET', [App\Controladores\MaterialControlador::class, 'listaMateriais']);
$app->rota('api/materiais/adicionar', 'POST', [App\Controladores\MaterialControlador::class, 'adicionarMaterial']);

$app->rodar();