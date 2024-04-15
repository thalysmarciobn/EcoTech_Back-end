<?php

namespace App\Controladores;
use App\BaseControlador;
use App\Banco\PDO;

final class SolicitacoesControlador
{
    /**
     * @author: Thalys Márcio
     * @created: 12/04/2024
     * @summary: Lista os um solicitações
     * @roles: Administrador, Funcionário
     */
    public static function listaSolicitacoes()
    {
        $consulta = PDO::preparar("SELECT id_residuo, nm_residuo FROM residuos");
        $consulta->execute();

        return ['code' => 200, 'data' => $consulta->fetchAll()];
    }

    /**
     * @author: Antonio Jorge
     * @created: 15/04/2024
     * @summary: Adicionar Solicitação
     * @roles: Administrador, Funcionário
     */

     public static function adcionarSolicitacoes(){

        if($this->receptaculo->validarAutenticacao(0)){
            
        $quantidade = $this->post('quantidade');
        $id_material = $this->post('id_material');
        $dt_solicitacoes =  new \DateTime();
        $id_usuario = $this->receptaculo->autenticador->usuario()['id_usuario'];
        $vl_status = 0;

        $inserirSolicitacoes = PDO::preparar("INSERT INTO usuario_solicitacoes (id_material,id_usuario,qt_material,vl_status,dt_solicitacoes) VALUES (?,?,?,?,?)");
        if($inserirSolicitacoes = PDO::execute([$id_material,$id_usuario,$id,$quantidade,$vl_status,$dt_solicitacoes])){
            return $this->responder(['codigo' => 'inserido']);
        }
        
        }
        return $this->responder(['codigo' => 'falha']);
    }

    /**
     * @author: Antonio Jorge
     * @created: 15/04/2024
     * @summary: Negar Solicitação
     * @roles: Administrador, Funcionário
     */

    public static function negarSolicitacoes(){
        if($this->receptaculo->validarAutenticacao(1)){
        $id_solicitacoes = $this->post('id_solicitacoes');
        $vl_status = -1;
        $updateSolicitacao = PDO::preparar("UPDATE usuarios_solicitacoes SET vl_status = ?  WHERE id_solicitacoes = ?");

        if($updateSolicitacao ->execute([$vl_status,$id_solicitacoes])){
            return $this->responder(['codigo' => 'atualizado']);
        }
        
        }

        return $this->responder(['codigo' => 'falha']);

    }

    /**
     * @author: Antonio Jorge
     * @created: 15/04/2024
     * @summary: Aceita Solicitação
     * @roles: Administrador, Funcionário
     */

    public static function aceitarSolicitacoes(){
        if($this->receptaculo->validarAutenticacao(1)){
        $id_solicitacoes = $this->post('id_solicitacoes');
        $vl_status = 1;
        $updateSolicitacao = PDO::preparar("UPDATE usuarios_solicitacoes SET vl_status = ?  WHERE id_solicitacoes = ?");

        if($updateSolicitacao ->execute([$vl_status,$id_solicitacoes])){
            return $this->responder(['codigo' => 'atualizado']);
        }

        }
        return $this->responder(['codigo' => 'falha']);
    }
    
}