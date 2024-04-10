<?php

use App\Aplicacao;

require '../App/Aplicacao.php';

$app = new Aplicacao();

$app->adicionarRota('test', [TesteControlador::class, 'teste']);

$app->run();