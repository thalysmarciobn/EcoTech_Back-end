<?php

namespace App\Controladores;

use App\BaseControlador;
use Banco\PDO;

final class TesteControlador extends BaseControlador
{
    public function teste(): array
    {
        $consulta = PDO::preparar("SELECT vl_brl FROM cambio");
        $consulta->execute();
        return $this->responder(['text' => $consulta->fetch(\PDO::FETCH_ASSOC)]);
    }

    public function testarChave(): array
    {
        $this->receptaculo->validarAutenticacao(0);
        
        $usuario = $this->receptaculo->autenticador->usuario();

        return $this->responder(['usuario' => $usuario]);
    
    }
    
}