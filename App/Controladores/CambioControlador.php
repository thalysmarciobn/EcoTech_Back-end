<?php

namespace App\Controladores;

namespace App\Controladores;
use App\BaseControlador;
use Banco\PDO;


final class CambioControlador extends BaseControlador
{

    public  function atualizarEco()
    {
        
       
        
        $eco =  $this->post('eco');
        if($eco > 0 ){
        
            $atualizarEco = PDO::preparar("UPDATE cambio SET eco = ?");
            if($atualizarEco->execute([$eco]))
            {
                return $this->responder(['codigo' => 'atualizado']);
            
            }
            else
            {
                return $this->responder(['codigo' => 'Valor Invalido']);
            }

            return $this->responder(['codigo' => 'falha']);
        
        }
    }

}