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

        $pagina = is_null($this->get('pagina')) ? 1 : $this->get('pagina');
        $pesquisa = is_null($this->get('pesquisa')) ? '' : $this->get('pesquisa');
        $status = is_null($this->get('status')) ? '' : $this->get('status');

        $consultaSolicitacoes = PDO::paginacao("SELECT usuarios_solicitacoes.id_solicitacao, nm_usuario, nm_residuo, nm_material, qt_material, sg_medida, vl_status, dt_solicitacao, nm_codigo, recebimentos.vl_ecorecebido, recebimentos.vl_realrecebido FROM usuarios_solicitacoes 
            JOIN materiais ON materiais.id_material = usuarios_solicitacoes.id_material
            JOIN residuos ON residuos.id_residuo = materiais.id_residuo
            JOIN usuarios ON usuarios_solicitacoes.id_usuario = usuarios.id_usuario
            LEFT JOIN recebimentos ON usuarios_solicitacoes.id_solicitacao = recebimentos.id_solicitacao
            AND (((case when vl_status = 0 then 'Pendente'
                        when vl_status = 1 then 'Aprovado'
                        when vl_status = -1 then 'Negado'
                    end) ilike '%' || :pesquisa || '%')
            OR (nm_codigo ilike '%' || :pesquisa || '%')
            OR (nm_material ilike '%' || :pesquisa || '%')
            OR :pesquisa is null)

            ORDER BY usuarios_solicitacoes.id_solicitacao DESC",
            [
                ':pesquisa' => [$pesquisa, \PDO::PARAM_STR]
            ], $pagina, 15);
            
        return $this->responder([
            'codigo' => 'recebido',
            'dados' => $consultaSolicitacoes
        ]);
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

        if (is_null($idPessoa) || empty($idPessoa) || is_numeric($idPessoa))
        {
            return $this->responder(['codigo' => 'vazio']);
        }
        
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

        $pagina = is_null($this->get('pagina')) ? 1 : $this->get('pagina');
        $pesquisa = is_null($this->get('pesquisa')) ? '' : $this->get('pesquisa');

        $consultaValores = PDO::paginacao("SELECT r.id_usuario, usu.nm_usuario, nm_material, re.nm_residuo, us.vl_status, us.qt_material, us.id_material, m.vl_eco FROM recebimentos r
            JOIN usuarios_solicitacoes  us ON r.id_solicitacao = us.id_solicitacao
            JOIN usuarios  usu ON usu.id_usuario = r.id_usuario
            JOIN materiais m ON m.id_material = us.id_material
            JOIN residuos re ON re.id_residuo = m.id_residuo",
            [
                ':pesquisa' => [$pesquisa, \PDO::PARAM_STR]
            ], $pagina, 15);
        
        return $this->responder($consultaValores);
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

        $idSolicitacao = $this->post('id_solicitacao');

        if (is_null($idSolicitacao) || empty($idSolicitacao))
        {
            return $this->responder(['codigo' => 'vazio']);
        }
        
        $usuario = $this->receptaculo->autenticador->usuario();

        $idFuncionario = $usuario['id_usuario'];

        $consultaSolicitacao = PDO::preparar("SELECT id_solicitacao, id_usuario, vl_status FROM usuarios_solicitacoes WHERE id_solicitacao = ?");
        $consultaSolicitacao->execute([$idSolicitacao]);

        $consultaSolicitacaoUsuario = $consultaSolicitacao->fetch(\PDO::FETCH_ASSOC);

        if (!$consultaSolicitacaoUsuario)
        {
            return $this->responder(['codigo' => 'solicitacao_inexistente']);
        }

        $idUsuarioSolicitacao = $consultaSolicitacaoUsuario['id_usuario'];
        $statusSolicitacao = $consultaSolicitacaoUsuario['vl_status'];

        if ($statusSolicitacao != 0)
        {
            return $this->responder(['codigo' => $statusSolicitacao == 1 ?
                'solicitacao_ja_aprovada' : 'solicitacao_ja_negada']);
        }
        
        $updateSolicitacao = PDO::preparar("UPDATE usuarios_solicitacoes SET vl_status = -1 WHERE id_solicitacao = ?");
        $executarUpdateSolicitacao = $updateSolicitacao->execute([$idSolicitacao]);

        if($executarUpdateSolicitacao){

            $inserirRecebimento = PDO::preparar("INSERT INTO recebimentos (id_solicitacao, id_usuario, id_funcionario, vl_ecorecebido, vl_realrecebido, dt_recebimento) VALUES (?, ?, ?, ?, ?, CURRENT_TIMESTAMP)");
            $inserirRecebimento->execute([$idSolicitacao, $idUsuarioSolicitacao, $idFuncionario, 0, 0]);

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

        $idSolicitacao = $this->post('id_solicitacao');

        if (is_null($idSolicitacao) || empty($idSolicitacao))
        {
            return $this->responder(['codigo' => 'vazio']);
        }

        $usuario = $this->receptaculo->autenticador->usuario();

        $idFuncionario = $usuario['id_usuario'];

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
            $realRecebido = $valorCambioBrl * $ecoRecebido;

            $inserirRecebimento = PDO::preparar("INSERT INTO recebimentos (id_solicitacao, id_usuario, id_funcionario, vl_ecorecebido, vl_realrecebido, dt_recebimento) VALUES (?, ?, ?, ?, ?, CURRENT_TIMESTAMP)");
            $executarInserirRecebimento = $inserirRecebimento->execute([$idSolicitacao, $idUsuario, $idFuncionario, $ecoRecebido, $realRecebido]);

            if($executarInserirRecebimento)
            {
                $atualizarUsuario = PDO::preparar("UPDATE usuarios SET qt_ecosaldo = qt_ecosaldo + ? WHERE id_usuario = ?");
                $atualizarUsuario->execute([$ecoRecebido, $idUsuario]);
                return $this->responder(['codigo' => 'aprovado']);
            }
        }
        return $this->responder(['codigo' => 'falha']);
    }
}