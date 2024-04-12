<?php

require '../vendor/autoload.php';

use App\Aplicacao;
use App\Banco\PDO;

PDO::conectar();

$app = new Aplicacao();

$app->rota('test', 'GET', [App\Controladores\TesteControlador::class, 'teste']);

$app->rota('api/logar','POST',[App\Controladores\UsuarioControlador::class,'logar']);

$app->rota('api/cadastrar', 'POST', [App\Controladores\UsuarioControlador::class, 'cadastrar']);

$app->rodar();