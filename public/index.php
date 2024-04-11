<?php

require '../vendor/autoload.php';

use App\Aplicacao;

$app = new Aplicacao();

$app->rotaGet('test', [App\Controladores\TesteControlador::class, 'teste']);

$app->rodar();