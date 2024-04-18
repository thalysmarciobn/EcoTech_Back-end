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
    public function listaSolicitacoes(): array
    {
        if(!$this->receptaculo->validarAutenticacao(1))
        {
            return $this->responder(['codigo' => 'login_necessario']);
        }
        $consultaSolicitacoes = PDO::paginacao("SELECT id_solicitacao, nm_usuario, nm_residuo, nm_material, qt_material, sg_medida, vl_status, dt_solicitacao FROM usuarios_solicitacoes 
            JOIN materiais ON materiais.id_material = usuarios_solicitacoes.id_material
            JOIN usuarios ON usuarios.id_usuario = usuarios_solicitacoes.id_usuario
            JOIN residuos ON residuos.id_residuo = materiais.id_residuo
            WHERE vl_status = 0 ORDER BY usuarios_solicitacoes.dt_solicitacao DESC");
            
        return $this->responder([
            'codigo' => 'recebido',
            'dados' => $consultaSolicitacoes
        ]);

        return $this->responder($consulta->fetchAll(\PDO::FETCH_ASSOC));
    }

    /**
     * @author: Antonio Jorge
     * @created: 17/04/2024
     * @summary: Retornar a lista de pessoa especifica e seus materias entregue
     * @roles: Administrador, Funcionário, Usuário
     */
    public function listaPessoaMaterial()
    {   
        if(!$this->receptaculo->validarAutenticacao(0))
        {
            return $this->responder(['codigo' => 'login_necessario']);
        }
        $idPessoa = $this->post('id_usuario');
        $consultaValores = PDO::preparar("SELECT r.id_usuario, nm_material, re.nm_residuo, us.vl_status, us.qt_material, us.id_material, m.vl_eco FROM recebimentos r
            JOIN usuarios_solicitacoes  us ON r.id_solicitacao = us.id_solicitacao
            JOIN materiais m ON m.id_material = us.id_material
            JOIN residuos re ON re.id_residuo = m.id_residuo
            WHERE r.id_usuario = ?");
        $consultaValores->execute([$idPessoa]);
        
        return $this->responder($consultaValores->fetchAll(\PDO::FETCH_ASSOC));
    }
    
    /**
     * @author: Antonio Jorge
     * @created: 17/04/2024
     * @summary: Retornar a lista de todas as pessoas e seus materias entregue
     * @roles: Administrador, Funcionário
     */
    public function listaPessoaFuncionarioMaterial()
    {
        if(!$this->receptaculo->validarAutenticacao(1))
        {
            return $this->responder(['codigo' => 'login_necessario']);
        }
        $consultaValores = PDO::preparar("SELECT r.id_usuario, nm_material, re.nm_residuo, us.vl_status, us.qt_material, us.id_material, m.vl_eco FROM recebimentos r
            JOIN usuarios_solicitacoes  us ON r.id_solicitacao = us.id_solicitacao
            JOIN materiais m ON m.id_material = us.id_material
            JOIN residuos re ON re.id_residuo = m.id_residuo");
        $consultaValores->execute([]);
        
        return $this->responder($consultaValores->fetchAll(\PDO::FETCH_ASSOC));
    }

    /**
     * @author: Antonio Jorge
     * @created: 15/04/2024
     * @summary: Adicionar Solicitação
     * @roles: Usuário
     */
     public function adicionarSolicitacao(): array
     {
        if(!$this->receptaculo->validarAutenticacao(0))
        {
            return $this->responder(['codigo' => 'login_necessario']);
        }

        if (empty($this->post('lista_materiais')))
        {
            return $this->responder(['codigo' => 'vazio']);
        }

        $usuario = $this->receptaculo->autenticador->usuario();

        $usuarioId = $usuario['id'];

        $lista = $this->post('lista_materiais');
        $jsonLista = json_decode($lista, true);
        
        $inserirMateriais = [];

        foreach ($jsonLista as $material)
        {
            if (!isset($material['nm_material']) || !$material['qt_material'])
            {
                return $this->responder(['codigo' => 'falha']);
            }

            $nomeMaterial = $material['nm_material'];
            $quantidadeMaterial = $material['qt_material'];

            if (!is_numeric($quantidadeMaterial))
            {
                return $this->responder(['codigo' => 'falha']);
            }
            
            $dataSolicitacao = date('d/m/Y H:i');

            $consultaMaterial = PDO::preparar("SELECT * FROM materiais WHERE nm_material = ?");
            $consultaMaterial->execute([$nomeMaterial]);

            $consultaSolicitacaoMaterial = $consultaMaterial->fetch(\PDO::FETCH_ASSOC);

            if (!$consultaSolicitacaoMaterial)
            {
                return $this->responder(['codigo' => 'material_inexistente']);
            }

            $idMaterial = $consultaSolicitacaoMaterial['id_material'];
            array_push($inserirMateriais, [$idMaterial, $usuarioId, $quantidadeMaterial, $dataSolicitacao]);
        }

        $insercoes = 0;
        PDO::iniciarTransacao();
        foreach ($inserirMateriais as $inserir)
        {
            $inserirSolicitacoes = PDO::preparar("INSERT INTO usuarios_solicitacoes (id_material, id_usuario, qt_material, vl_status, dt_solicitacao) VALUES (?, ?, ?, 0, ?)");
            if($inserirSolicitacoes->execute($inserir))
            {
                $insercoes++;
            }
        }
        if ($insercoes == count($inserirMateriais))
        {
            PDO::entregarTransacao();
            return $this->responder(['codigo' => 'inserido']);
        }
        PDO::reverterTransacao();
        
        return $this->responder(['codigo' => 'falha']);
    }

    /**
     * @author: Antonio Jorge
     * @created: 15/04/2024
     * @summary: Negar Solicitação
     * @roles: Administrador, Funcionário
     */
    public function negarSolicitacao(): array
    {
        if (!$this->receptaculo->validarAutenticacao(1))
        {
            return $this->responder(['codigo' => 'cargo_insuficiente']);
        }

        if (empty($this->post('id_solicitacao')))
        {
            return $this->responder(['codigo' => 'vazio']);
        }
        
        $usuario = $this->receptaculo->autenticador->usuario();

        $idFuncionario = $usuario['id'];
        $idSolicitacao = $this->post('id_solicitacao');

        $consultaSolicitacao = PDO::preparar("SELECT id_solicitacao, vl_status FROM usuarios_solicitacoes WHERE id_solicitacao = ?");
        $consultaSolicitacao->execute([$idSolicitacao]);

        $consultaSolicitacaoUsuario = $consultaSolicitacao->fetch(\PDO::FETCH_ASSOC);

        if (!$consultaSolicitacaoUsuario)
        {
            return $this->responder(['codigo' => 'solicitacao_inexistente']);
        }

        $statusSolicitacao = $consultaSolicitacaoUsuario['vl_status'];

        if ($statusSolicitacao != 0)
        {
            return $this->responder(['codigo' => $statusSolicitacao == 1 ?
                'solicitacao_ja_aprovada' : 'solicitacao_ja_negada']);
        }
        
        $updateSolicitacao = PDO::preparar("UPDATE usuarios_solicitacoes SET vl_status = -1 WHERE id_solicitacao = ?");
        if($updateSolicitacao->execute([$idSolicitacao])){
            return $this->responder(['codigo' => 'atualizado']);
        }

        return $this->responder(['codigo' => 'falha']);
    }

    /**
     * @author: Antonio Jorge
     * @created: 15/04/2024
     * @summary: Aceita Solicitação
     * @roles: Administrador, Funcionário
     */
    public function aceitarSolicitacao(): array
    {
        if (!$this->receptaculo->validarAutenticacao(1))
        {
            return $this->responder(['codigo' => 'cargo_insuficiente']);
        }

        if (empty($this->post('id_solicitacao')))
        {
            return $this->responder(['codigo' => 'vazio']);
        }

        $usuario = $this->receptaculo->autenticador->usuario();

        $idFuncionario = $usuario['id'];
        $idSolicitacao = $this->post('id_solicitacao');

        $consultaSolicitacao = PDO::preparar("SELECT id_solicitacao, usuarios.id_usuario, nm_usuario, nm_residuo, nm_material, qt_material, vl_status, vl_eco, dt_solicitacao FROM usuarios_solicitacoes 
            JOIN materiais ON materiais.id_material = usuarios_solicitacoes.id_material
            JOIN usuarios ON usuarios.id_usuario = usuarios_solicitacoes.id_usuario
            JOIN residuos ON residuos.id_residuo = materiais.id_residuo
            WHERE id_solicitacao = ?");
        $consultaSolicitacao->execute([$idSolicitacao]);
        $consultaSolicitacaoUsuario = $consultaSolicitacao->fetch(\PDO::FETCH_ASSOC);

        if (!$consultaSolicitacaoUsuario)
        {
            return $this->responder(['codigo' => 'solicitacao_inexistente']);
        }

        $statusSolicitacao = $consultaSolicitacaoUsuario['vl_status'];

        if ($statusSolicitacao != 0)
        {
            return $this->responder(['codigo' => $statusSolicitacao == 1 ?
                'solicitacao_ja_aprovada' : 'solicitacao_ja_negada']);
        }

        $updateSolicitacao = PDO::preparar("UPDATE usuarios_solicitacoes SET vl_status = 1  WHERE id_solicitacao = ?");
        if ($updateSolicitacao->execute([$idSolicitacao]))
        {
            $idSolicitacao = $consultaSolicitacaoUsuario['id_solicitacao'];

            $idUsuario = $consultaSolicitacaoUsuario['id_usuario'];
            $quantidadeMaterial = $consultaSolicitacaoUsuario['qt_material'];
            $valorEco = $consultaSolicitacaoUsuario['vl_eco'];

            $consultaCambio = PDO::preparar("SELECT vl_brl FROM cambio");
            $consultaCambio->execute();
            $resultadoCambio = $consultaCambio->fetch(\PDO::FETCH_ASSOC);

            $valorCambioBrl = $resultadoCambio['vl_brl'];

            $ecoRecebido = $valorEco * $quantidadeMaterial;
            $realRecebido = $valorCambioBrl * $valorEco;

            $inserirRecebimento = PDO::preparar("INSERT INTO recebimentos (id_solicitacao, id_usuario, id_funcionario, vl_ecorecebido, vl_realrecebido, dt_recebimento) VALUES (?, ?, ?, ?, ?, CURRENT_TIMESTAMP)");
            
            if($inserirRecebimento->execute([$idSolicitacao, $idUsuario, $idFuncionario, $ecoRecebido, $realRecebido]))
            {
                return $this->responder(['codigo' => 'aprovado']);
            }
        }
        return $this->responder(['codigo' => 'falha']);
    }
    

     /**
     * @author: Antonio Jorge
     * @created: 15/04/2024
     * @summary: Lista de Solicitação do usuario
     * @roles: Usuário
     */
    public function listaUsuario(): array
    {
        if(!$this->receptaculo->validarAutenticacao(0))
        {
            return $this->responder(['codigo' => 'login_necessario']);
        }
            
        $usuario = $this->receptaculo->autenticador->usuario();

        $usuarioId = $usuario['id'];

        $consultaSolicitacoesUsuarios = PDO::paginacao("SELECT id_solicitacao, nm_residuo, nm_material, qt_material, sg_medida, vl_status, dt_solicitacao FROM usuarios_solicitacoes 
            JOIN materiais ON materiais.id_material = usuarios_solicitacoes.id_material
            JOIN residuos ON residuos.id_residuo = materiais.id_residuo
            WHERE usuarios_solicitacoes.id_usuario = ? ORDER BY usuarios_solicitacoes.dt_solicitacao DESC",
            [$usuarioId]);
            
        return $this->responder([
            'codigo' => 'recebido',
            'dados' => $consultaSolicitacoesUsuarios
        ]);
    }
}