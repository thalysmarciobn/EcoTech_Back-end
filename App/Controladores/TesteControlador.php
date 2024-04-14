<?php

namespace App\Controladores;

use App\BaseControlador;

final class TesteControlador extends BaseControlador
{
    public function teste(): array
    {
        $this->receptaculo->autenticador->chaveAleatoria(10);
        return $this->responder(['text' => 'Isso é um teste']);
    }
}