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

     public function adcionarSolicitacao(): array
     {
        if(!$this->receptaculo->validarAutenticacao(0))
        {
            return $this->responder(['codigo' => 'login_necessario']);
        }

        $usuario = $this->receptaculo->autenticador->usuario();

        $usuarioId = $usuario['id'];

        $idMaterial = $this->post('id_material');
        $idEndereco = $this->post('id_endereco');
        $quantidadeMaterial = $this->post('qt_material');
        $dataSolicitacao = date('d/m/Y H:i');

        $inserirSolicitacoes = PDO::preparar("INSERT INTO usuarios_solicitacoes (id_material, id_usuario, id_endereco, qt_material, vl_status, dt_solicitacao) VALUES (?, ?, ?, ?, 0, ?)");
        if($inserirSolicitacoes->execute([$idMaterial, $usuarioId, $idEndereco, $quantidadeMaterial, $dataSolicitacao])){
            return $this->responder(['codigo' => 'inserido']);
        }
        
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
       // if($this->receptaculo->validarAutenticacao(1)){
        $id_usuario = 1;
        $id_solicitacao = $this->post('id_solicitacao');
        $vl_status = -1;
        $updateSolicitacao = PDO::preparar("UPDATE usuarios_solicitacoes SET vl_status = ? WHERE id_solicitacao = ?");

        if($updateSolicitacao ->execute([$vl_status,$id_solicitacao])){
            return $this->responder(['codigo' => 'atualizado']);
        }

       // }
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
        $id_funcionario = 2;
        $id_solicitacao = $this->post('id_solicitacao');
        $vl_status = 1;
        $updateSolicitacao = PDO::preparar("UPDATE usuarios_solicitacoes SET vl_status = ?  WHERE id_solicitacao = ?");

        
        if($updateSolicitacao ->execute([$vl_status,$id_solicitacao])){

            $ConsultaSolicitacaoVl_status1 = PDO::preparar("SELECT * FROM usuarios_solicitacoes WHERE vl_status = ?");
        
            $ConsultaSolicitacaoVl_status1 -> execute([$vl_status]);
            $solicitacao = $ConsultaSolicitacaoVl_status1 -> fetch(\PDO::FETCH_ASSOC);
            $ConsultarValorMaterial = PDO::preparar("SELECT * FROM materiais WHERE id_material = ?");
            $ConsultarValorMaterial ->execute([$solicitacao['id_material']]);

            $material = $ConsultarValorMaterial ->fetch(\PDO::FETCH_ASSOC);



            $vl_ecorecebido = $solicitacao['qt_material']  * $material['vl_eco'];
            $id_materialRecebimento = $solicitacao['id_material'];
            $id_usuarioRecebimento = $solicitacao['id_usuario'];
            $qt_materialRecebimento = $solicitacao['qt_material'];
            $dt_recebimentos = date('d/m/Y H:i');

           
            
            $inserirRecebimento = PDO::preparar("INSERT INTO recebimentos (id_material,id_usuario,id_funcionario,qt_material,vl_ecorecebido,dt_recebimento) VALUES (?,?,?,?,?,?)");

            var_dump($solicitacao);
            var_dump($material);
            if ($inserirRecebimento->execute([$id_materialRecebimento, $id_usuarioRecebimento, $id_funcionario, $qt_materialRecebimento, $vl_ecorecebido, $dt_recebimentos])) {
                return $this->responder(['codigo' => 'aprovado']);
            }



           
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
    public function listaUsuario(): array
    {
        if(!$this->receptaculo->validarAutenticacao(0))
        {
            return $this->responder(['codigo' => 'login_necessario']);
        }
            
        $usuario = $this->receptaculo->autenticador->usuario();

        $usuarioId = $usuario['id'];

        $consultaSolicitacoesUsuarios = PDO::paginacao("SELECT id_solicitacao,nm_residuo,nm_material,qt_material,vl_status,dt_solicitacao FROM usuarios_solicitacoes 
            JOIN materiais ON materiais.id_material = usuarios_solicitacoes.id_material
            JOIN residuos ON residuos.id_residuo = materiais.id_residuo WHERE usuarios_solicitacoes.id_usuario = ?",
            [$usuarioId]);
            
            return $this->responder([
                'codigo' => 'recebido',
                'dados' => $consultaSolicitacoesUsuarios
            ]);

        return $this->responder(['codigo' => 'falha']);
    }
}