<?php

namespace App\Controladores;

use App\BaseControlador;

final class TesteControlador extends BaseControlador
{
    public function teste(): array
    {
        return $this->responder(['text' => 'Isso é um teste']);
    }

    public function testarChave(): array
    {
        $this->receptaculo->validarAutenticacao(0);
        
        $usuario = $this->receptaculo->autenticador->usuario();

        return $this->responder(['usuario' => $usuario]);
    
    }
    
}