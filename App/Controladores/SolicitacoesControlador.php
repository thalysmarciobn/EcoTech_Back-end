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
        $quantidade = $this->post('quantidade');
        $id_material = $this->post('id_material');
        $dt_solicitacoes =  new \DateTime();
        $id_usuario = $this->receptaculo->autenticador->usuario()['id_usuario'];
        $aprovado = false;

        $inserirSolicitacoes = PDO::preparar("INSERT INTO usuario_solicitacoes (id_material,id_usuario,qt_material,fl_aprovado,dt_solicitacoes) VALUES (?,?,?,?,?");
        if($inserirSolicitacoes = PDO::execute([$id_material,$id_usuario,$id,$quantidade,$aprovado,$dt_solicitacoes])){
            return $this->responder(['codigo' => 'inserido']);
        }

        return $this->responder(['codigo' => 'falha']);
     }

    public static function deletarSolicitacoes(){

        $id_solicitacoes = $this->post('id_solicitacoes');

        $deletarSolicitacao = PDO::preparar("DELETE FROM usuarios_solicitacoes WHERE id_solicitacoes = ?");

        if($deletarSolicitacao ->execute([$id_solicitacoes])){
            return $this->responder(['codigo' => $deletarSolicitacao->rowCount()> 0 ? 'removido' : 'inexistente']);
        }
        
        return $this->responder(['codigo' => 'falha']);
    }
}