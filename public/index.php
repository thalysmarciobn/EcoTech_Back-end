<?php

require '../vendor/autoload.php';

use App\Aplicacao;

$app = new Aplicacao();

$app->rota('test', 'GET', [App\Controladores\TesteControlador::class, 'teste']);

$app->rodar();