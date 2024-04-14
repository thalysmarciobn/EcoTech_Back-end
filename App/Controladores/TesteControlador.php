<?php

namespace App\Controladores;

use App\BaseControlador;

final class TesteControlador extends BaseControlador
{
    public function teste(): array
    {
        $encrypt = $this->receptaculo->autenticador->encrypt("aaaaaa");
        $decrypt = $this->receptaculo->autenticador->decrypt($encrypt);
        
        return $this->responder(['text' => 'Isso é um teste', 'chave' => $encrypt, 'dsd' => $decrypt]);
    }

    public function testarChave(): array
    {
        $chave = $this->post('chave');

        return $this->responder(['dado' => $chave]);
    }
}