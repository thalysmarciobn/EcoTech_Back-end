<?php

namespace App\Controladores;
use App\BaseControlador;
use Banco\PDO;

final class SolicitacoesControlador extends BaseControlador
{
    /**
     * @author: Thalys Márcio
     * @created: 12/04/2024
     * @summary: Lista os um solicitações
     * @roles: Administrador, Funcionário
     */
    public static function listaSolicitacoes(): array
    {
        $consulta = PDO::preparar("SELECT id_residuo, nm_residuo FROM residuos");
        $consulta->execute();

        return $this->responder($consulta->fetchAll());
    }

    /**
     * @author: Antonio Jorge
     * @created: 15/04/2024
     * @summary: Adicionar Solicitação
     * @roles: Administrador, Funcionário
     */

     public function adcionarSolicitacoes(): array
     {

        //if($this->receptaculo->validarAutenticacao(0)){
        $id_usuario = 1;
        $quantidade = $this->post('quantidade');
        $id_material = $this->post('id_material');
        $dt_solicitacoes = date('d/m/Y H:i');
       // $id_usuario = $this->receptaculo->autenticador->usuario()['id_usuario'];
        $vl_status = 0;

        $inserirSolicitacoes = PDO::preparar("INSERT INTO usuarios_solicitacoes (id_material,id_usuario,qt_material,vl_status,dt_solicitacao) VALUES (?,?,?,?,?)");
        if($inserirSolicitacoes -> execute([$id_material,$id_usuario,$quantidade,$vl_status,$dt_solicitacoes])){
            return $this->responder(['codigo' => 'inserido']);
        }
        
       // }
        return $this->responder(['codigo' => 'falha']);
    }

    /**
     * @author: Antonio Jorge
     * @created: 15/04/2024
     * @summary: Negar Solicitação
     * @roles: Administrador, Funcionário
     */

    public function negarSolicitacoes(): array
    {
        //if($this->receptaculo->validarAutenticacao(1)){
        $id_usuario = 1;
        $id_solicitacoes = $this->post('id_solicitacoes');
        $vl_status = -1;
        $updateSolicitacao = PDO::preparar("UPDATE usuarios_solicitacoes SET vl_status = ?  WHERE id_solicitacoes = ?");

        if($updateSolicitacao ->execute([$vl_status,$id_solicitacoes])){
            return $this->responder(['codigo' => 'atualizado']);
        }
        
        //}

        return $this->responder(['codigo' => 'falha']);

    }

    /**
     * @author: Antonio Jorge
     * @created: 15/04/2024
     * @summary: Aceita Solicitação
     * @roles: Administrador, Funcionário
     */

    public function aceitarSolicitacoes(): array
    {
       // if($this->receptaculo->validarAutenticacao(1)){
        $id_usuario = 1;
        $id_solicitacoes = $this->post('id_solicitacoes');
        $vl_status = 1;
        $updateSolicitacao = PDO::preparar("UPDATE usuarios_solicitacoes SET vl_status = ?  WHERE id_solicitacoes = ?");

        if($updateSolicitacao ->execute([$vl_status,$id_solicitacoes])){
            return $this->responder(['codigo' => 'atualizado']);
        }

       // }
        return $this->responder(['codigo' => 'falha']);
    }
    

     /**
     * @author: Antonio Jorge
     * @created: 15/04/2024
     * @summary: lista de Solicitação do usuario
     * @roles: Administrador, Funcionário
     */
    public function listaSolicitacoesUsuario(): array
    {
        //if($this->receptaculo->validarAutenticacao(0)){
            $id_usuario = 1;//$this->receptaculo->autenticador->usuario()['id_usuario'];

            $consultaSolicitacoesUsuarios = PDO::preparar("SELECT nm_residuo,nm_material,qt_material,vl_status,dt_solicitacao FROM usuarios_solicitacoes 
            JOIN materiais ON materiais.id_material = usuarios_solicitacoes.id_material
            JOIN residuos ON residuos.id_residuo = materiais.id_residuo WHERE usuarios_solicitacoes.id_usuario = ?");
            $consultaSolicitacoesUsuarios -> execute([$id_usuario]);
            
            return $this->responder($consultaSolicitacoesUsuarios->fetchAll());
        //}
    }
}