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
     * @created: 12/04/2024
     * @summary: Adicionar Solicitação
     * @roles: Administrador, Funcionário
     */

     public static function adcionarSolicitacoes(){

        if($this->receptaculo->validarAutenticacao(0)){
            
        $quantidade = $this->post('quantidade');
        $id_material = $this->post('id_material');
        $dt_solicitacoes =  new \DateTime();
        $id_usuario = $this->receptaculo->autenticador->usuario()['id_usuario'];
        $fl_aprovado = 0;

        $inserirSolicitacoes = PDO::preparar("INSERT INTO usuario_solicitacoes (id_material,id_usuario,qt_material,fl_aprovado,dt_solicitacoes) VALUES (?,?,?,?,?)");
        if($inserirSolicitacoes = PDO::execute([$id_material,$id_usuario,$id,$quantidade,$fl_aprovado,$dt_solicitacoes])){
            return $this->responder(['codigo' => 'inserido']);
        }
        
        }
        return $this->responder(['codigo' => 'falha']);
     }

    public static function negarSolicitacoes(){
        if($this->receptaculo->validarAutenticacao(1)){
        $id_solicitacoes = $this->post('id_solicitacoes');
        $fl_aprovado = -1;
        $updateSolicitacao = PDO::preparar("UPDATE usuarios_solicitacoes SET fl_aprovado = ?  WHERE id_solicitacoes = ?");

        if($updateSolicitacao ->execute([$fl_aprovado,$id_solicitacoes])){
            return $this->responder(['codigo' => 'atualizado']);
        }
        
        }

        return $this->responder(['codigo' => 'falha']);

    }
    public static function aceitarSolicitacoes(){
        if($this->receptaculo->validarAutenticacao(2)){
        $id_solicitacoes = $this->post('id_solicitacoes');
        $fl_aprovado = 1;
        $updateSolicitacao = PDO::preparar("UPDATE usuarios_solicitacoes SET fl_aprovado = ?  WHERE id_solicitacoes = ?");

        if($updateSolicitacao ->execute([$fl_aprovado,$id_solicitacoes])){
            return $this->responder(['codigo' => 'atualizado']);
        }

        }
        return $this->responder(['codigo' => 'falha']);
    }
    
}