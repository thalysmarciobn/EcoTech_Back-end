<?php

require '../vendor/autoload.php';

use App\Aplicacao;
use App\Banco\PDO;

PDO::conectar();

$app = new Aplicacao();

$app->rota('test', 'GET', [App\Controladores\TesteControlador::class, 'teste']);

$app->rota('api/cadastrar', 'GET', [App\Controladores\UsuarioControlador::class, 'cadastrar']);

$app->rodar();