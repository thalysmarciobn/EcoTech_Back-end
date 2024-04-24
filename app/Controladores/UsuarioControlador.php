<?php

namespace App\Controladores;

use App\BaseControlador;
use Banco\PDO;

final class UsuarioControlador extends BaseControlador
{
    public function checar(): array
    {
        $usuario = $this->receptaculo->autenticador->usuario();
        $idUsuario = $usuario['id_usuario'];
        $chave = $usuario['chave'];

        $checarSessaoLivre = PDO::preparar("SELECT * FROM sessoes WHERE id_usuario = ? AND nm_chave = ? AND dt_expiracao > CURRENT_TIMESTAMP");
        $checarSessaoLivre->execute([$idUsuario, $chave]);

        $contemSessao = $checarSessaoLivre->fetch();

        if (!$contemSessao)
        {
            return $this->responder(['codigo' => 'deslogado']);
        }

        $usuario = $this->receptaculo->autenticador->usuario();

        $idUsuario = $usuario['id_usuario'];

        $consultaUsuario = PDO::preparar("SELECT id_usuario, nm_usuario, nm_email, nu_cargo, qt_ecosaldo FROM usuarios WHERE id_usuario = ?");
        $consultaUsuario->execute([$idUsuario]);

        $resultadoUsuario = $consultaUsuario->fetch(\PDO::FETCH_ASSOC);

        if (!$resultadoUsuario) {
            return $this->responder(['codigo' => 'usuario_inexistente']);
        }

        return $this->responder(['codigo' => 'logado', 'usuario' => $resultadoUsuario]);
    }
    
    public function listaUsuarios(): array
    {
        if(!$this->receptaculo->validarAutenticacao(1))
        {
            return $this->responder(['codigo' => 'login_necessario']);
        }

        $consultaUsuario = PDO::preparar("SELECT usuarios.id_usuario, nm_usuario, 
                COUNT(DISTINCT recebimentos.id_recebimento) AS qt_recebimentos,
                COUNT(DISTINCT usuarios_solicitacoes.id_solicitacao) AS qt_solicitacoes,
                SUM(recebimentos.vl_ecorecebido) AS total_ecorecebido,
                SUM(recebimentos.vl_realrecebido) AS total_realrecebido
            FROM 
                usuarios
            LEFT JOIN 
                recebimentos ON recebimentos.id_usuario = usuarios.id_usuario
            LEFT JOIN 
                usuarios_solicitacoes ON usuarios_solicitacoes.id_usuario = usuarios.id_usuario
            WHERE 
                nu_cargo = 0
            GROUP BY 
                usuarios.id_usuario, nm_usuario
            ORDER BY 
                usuarios.id_usuario DESC");
        $consultaUsuario->execute();

        return $this->responder($consultaUsuario->fetchAll(\PDO::FETCH_ASSOC));
    }

    /**
     * @author: Thalys Márcio
     * @created: 12/04/2024
     * @summary: Requisição de logar e criação de sessão
     * @roles:
     */
    public function logar(): array
    {
        $emailUsuario = $this->post('nm_email');
        $senhaUsuario = $this->post('nm_senha');

        if (is_null($emailUsuario) || empty($emailUsuario) ||
        is_null($senhaUsuario) || empty($senhaUsuario))
        {
            return $this->responder(['codigo' => 'vazio']);
        }

        $senhaCriptografada = md5($senhaUsuario);

        $consultaUsuario = PDO::preparar("SELECT id_usuario, nm_usuario, nm_email, nu_cargo, qt_ecosaldo FROM usuarios WHERE nm_email = ? and nm_senha = ?");
        $consultaUsuario->execute([$emailUsuario, $senhaCriptografada]);

        $usuario = $consultaUsuario->fetch(\PDO::FETCH_ASSOC);
        
        if ($usuario)
        {
            $idUsuario = $usuario['id_usuario'];
            $nomeUsuario = $usuario['nm_usuario'];
            $cargoUsuario = $usuario['nu_cargo'];
            $saldoEco = $usuario['qt_ecosaldo'];

            $umaHoraFutura = strtotime("+1 hour");
            $dataFutura = date('d/m/Y H:i', $umaHoraFutura);
            
            [$chaveAleatoria, $chave] = $this->receptaculo->autenticador->gerarChaveAutenticacao($idUsuario, $cargoUsuario, $nomeUsuario, $emailUsuario, $saldoEco);

            $checarSessaoLivre = PDO::preparar("SELECT (id_usuario) FROM sessoes WHERE id_usuario = ? AND dt_expiracao > CURRENT_TIMESTAMP");
            $checarSessaoLivre->execute([$idUsuario]);
            $resultadoChecarSessaoLivre = $checarSessaoLivre->fetch(\PDO::FETCH_ASSOC);

            if ($resultadoChecarSessaoLivre)
            {
                $atualizarSessao = PDO::preparar("UPDATE sessoes SET nm_chave = ?, dt_expiracao = ? WHERE id_usuario = ?");
                $atualizarSessao->execute([$chaveAleatoria, $dataFutura, $idUsuario]);

                return $this->responder(['codigo' => 'logado',
                    'usuario' => $usuario,
                    'chave' => $chave]);
            }

            $inserirSessao = PDO::preparar("INSERT INTO sessoes (id_usuario, dt_expiracao, nm_chave) VALUES (?, ?, ?)");
            if ($inserirSessao->execute([$idUsuario, $dataFutura, $chaveAleatoria]))
            {
                return $this->responder(['codigo' => 'logado',
                    'usuario' => $usuario,
                    'chave' => $chave]);
            }
        }

        return $this->responder(['codigo' => 'falha']);
    }

    /**
     * @author: Thalys Márcio
     * @created: 22/04/2024
     * @summary: Retorna uma lista de endereços do usuário
     * @roles: Usuário
     */
    public function enderecos(): array
    {
        if(!$this->receptaculo->validarAutenticacao(0))
        {
            return $this->responder(['codigo' => 'login_necessario']);
        }

        $usuario = $this->receptaculo->autenticador->usuario();

        $usuarioId = $usuario['id_usuario'];

        $enderecos = PDO::preparar("SELECT id_endereco, id_usuario, nm_rua, nm_bairro, nm_cidade, nm_estado, nu_casa, nm_complemento, nm_cep FROM usuarios_enderecos WHERE fl_desativado = false AND id_usuario = ?");
        $executarEnderecos = $enderecos->execute([$usuarioId]);
        if ($executarEnderecos)
        {
            return $this->responder([
                'codigo' => 'enviado',
                'enderecos' => $enderecos->fetchAll(\PDO::FETCH_ASSOC)
            ]);
        }

        return $this->responder(['codigo' => 'falha']);
    }

    /**
     * @author: Thalys Márcio
     * @created: 23/04/2024
     * @summary: Edita um endereço do usuário logado
     * @roles: Usuário
     */
    public function editarEndereco(): array
    {
        if(!$this->receptaculo->validarAutenticacao(0))
        {
            return $this->responder(['codigo' => 'login_necessario']);
        }

        $idEndereco = $this->post('id_endereco');
        $nomeEstado = $this->post('nm_estado');
        $nomeCidade = $this->post('nm_cidade');
        $cep = $this->post('nm_cep');
        $nomeBairro = $this->post('nm_bairro');
        $nomeRua = $this->post('nm_rua');
        $complemento = $this->post('nm_complemento');
        $numeroCasa = $this->post('nu_casa');

        if (is_null($idEndereco) || empty($idEndereco) || !is_numeric($idEndereco) ||
        is_null($nomeEstado) || empty($nomeEstado) ||
        is_null($nomeCidade) || empty($nomeCidade) ||
        is_null($cep) || empty($cep) ||
        is_null($nomeBairro) || empty($nomeBairro) ||
        is_null($nomeRua) || empty($nomeRua) ||
        is_null($numeroCasa) || empty($numeroCasa) || !is_numeric($numeroCasa))
        {
            return $this->responder(['codigo' => 'vazio']);
        }

        $usuario = $this->receptaculo->autenticador->usuario();

        $usuarioId = $usuario['id_usuario'];

        $consultaEndereco = PDO::preparar("SELECT id_endereco, id_endereco FROM usuarios_enderecos WHERE id_endereco = ? AND id_usuario = ?");
        $consultaEndereco->execute([$idEndereco, $usuarioId]);
        $resultadoEndereco = $consultaEndereco->fetch(\PDO::FETCH_ASSOC);

        if (!$resultadoEndereco) {
            return $this->responder(['codigo' => 'endereco_nao_encontrado']);
        }

        try {
            $atualizarEndereco = PDO::preparar("UPDATE usuarios_enderecos SET nm_estado = ?, nm_cidade = ?, nm_cep = ?, nm_bairro = ?, nm_rua = ?, nm_complemento = ?, nu_casa = ? WHERE id_endereco = ? AND id_usuario = ?");
            $atualizarEndereco->execute([$nomeEstado, $nomeCidade, $cep, $nomeBairro, $nomeRua, $complemento, $numeroCasa, $idEndereco, $usuarioId]);
            return $this->responder(['codigo' => 'atualizado']);
        } catch (\Exception $e) {
            return $this->responder(['codigo' => 'falha']);
        }
    }

    /**
     * @author: Thalys Márcio
     * @created: 22/04/2024
     * @summary: Adiciona o endereço do usuário logado
     * @roles: Usuário
     */
    public function adicionarEndereco(): array
    {
        if(!$this->receptaculo->validarAutenticacao(0))
        {
            return $this->responder(['codigo' => 'login_necessario']);
        }

        $nomeEstado = $this->post('nm_estado');
        $nomeCidade = $this->post('nm_cidade');
        $cep = $this->post('nm_cep');
        $nomeBairro = $this->post('nm_bairro');
        $nomeRua = $this->post('nm_rua');
        $complemento = $this->post('nm_complemento');
        $numeroCasa = $this->post('nu_casa');

        if (is_null($nomeEstado) || empty($nomeEstado) ||
        is_null($nomeCidade) || empty($nomeCidade) ||
        is_null($cep) || empty($cep) ||
        is_null($nomeBairro) || empty($nomeBairro) ||
        is_null($nomeRua) || empty($nomeRua) ||
        is_null($numeroCasa) || empty($numeroCasa) || !is_numeric($numeroCasa))
        {
            return $this->responder(['codigo' => 'vazio']);
        }

        $usuario = $this->receptaculo->autenticador->usuario();

        $usuarioId = $usuario['id_usuario'];

        $consultaEndereco = PDO::preparar("INSERT INTO usuarios_enderecos (id_usuario, nm_rua, nm_bairro, nm_cidade, nm_estado, nu_casa, nm_complemento, nm_cep, fl_desativado)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, false)");
        $executarEndereco = $consultaEndereco->execute([$usuarioId, $nomeRua, $nomeBairro, $nomeCidade, $nomeEstado, $numeroCasa, $complemento, $cep]);

        if (!$executarEndereco) {
            return $this->responder(['codigo' => 'falha']);
        }
        return $this->responder(['codigo' => 'inserido']);
    }

    /**
     * @author: Thalys Márcio
     * @created: 22/04/2024
     * @summary: Remove o endereço do usuário logado
     * @roles: Usuário
     */
    public function removerEndereco(): array
    {
        $idEndereco = $this->post('id_endereco');

        if (is_null($idEndereco) || empty($idEndereco) || !is_numeric($idEndereco))
        {
            return $this->responder(['codigo' => 'vazio']);
        }

        if(!$this->receptaculo->validarAutenticacao(0))
        {
            return $this->responder(['codigo' => 'login_necessario']);
        }

        $usuario = $this->receptaculo->autenticador->usuario();

        $usuarioId = $usuario['id_usuario'];

        $consultaEndereco = PDO::preparar("SELECT id_endereco FROM usuarios_enderecos WHERE id_endereco = ? AND id_usuario = ?");
        $consultaEndereco->execute([$idEndereco, $usuarioId]);
        $resultadoEndereco = $consultaEndereco->fetch(\PDO::FETCH_ASSOC);

        if (!$resultadoEndereco)
        {
            return $this->responder(['codigo' => 'falha']);
        }

        try {
            $atualizarEndereco = PDO::preparar("UPDATE usuarios_enderecos SET fl_desativado = true WHERE id_endereco = ? AND id_usuario = ?");
            $atualizarEndereco->execute([$idEndereco, $usuarioId]);
            return $this->responder(['codigo' => 'atualizado']);
        } catch (\Exception $e) {
            return $this->responder(['codigo' => 'falha']);
        }
    }

    /**
     * @author: Thalys Márcio
     * @created: 12/04/2024
     * @summary: Requisição de cadastro de usuário
     * @roles:
     */
    public function cadastrar(): array
    {
        if (is_null($this->post('nm_email')) || empty($this->post('nm_email')) ||
            is_null($this->post('nm_usuario')) || empty($this->post('nm_usuario')) ||
            is_null($this->post('nm_senha')) || empty($this->post('nm_senha')))
        {
            return $this->responder(['codigo' => 'vazio']);
        }

        $nomeCompleto = $this->post('nm_usuario');
        $email = $this->post('nm_email');
        $senha = $this->post('nm_senha');
        
        $cep = $this->post('nm_cep');
        $bairro = $this->post('nm_bairro');
        $cidade = $this->post('nm_cidade');
        $estado = $this->post('nm_estado');
        $rua = $this->post('nm_rua');
        $casa = $this->post('nu_casa');
        $complemento = $this->post('nm_complemento');

        PDO::iniciarTransacao();
        try
        {
            $senhaCriptografada = md5($senha);

            $inserirUsuario = PDO::preparar("INSERT INTO usuarios (nm_email, nm_usuario, nm_senha, qt_ecosaldo, nu_cargo) VALUES (?, ?, ?, ?, ?)");
            $executarInserirUsuario = $inserirUsuario->execute([$email, $nomeCompleto, $senhaCriptografada, 0, 0]);

            if (!$executarInserirUsuario)
            {
                PDO::reverterTransacao();
                return $this->responder(['codigo' => 'falha_inserir_usuario']);
            }

            $inserirUsuarioEndereco = PDO::preparar("INSERT INTO usuarios_enderecos (id_usuario, nm_rua, nm_bairro, nm_cidade, nm_estado, nu_casa, nm_complemento, nm_cep, fl_desativado) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $idUsuario = PDO::ultimaIdInserida();

            var_dump("aa");
            $resultadoInserirEnderecoUsuario = $inserirUsuarioEndereco->execute([$idUsuario, $rua, $bairro, $cidade, $estado, $casa, $complemento, $cep, false]);
            if (!$resultadoInserirEnderecoUsuario)
            {
                PDO::reverterTransacao();
                return $this->responder(['codigo' => 'falha_inserir_endereco']);
            }

            $umaHoraFutura = strtotime("+1 hour");
            $dataFutura = date('d/m/Y H:i', $umaHoraFutura);

            $consultaUsuario = PDO::preparar("SELECT id_usuario, nm_usuario, nm_email, nu_cargo, qt_ecosaldo FROM usuarios WHERE nm_email = ? and nm_senha = ?");
            $consultaUsuario->execute([$email, $senhaCriptografada]);

            $usuario = $consultaUsuario->fetch(\PDO::FETCH_ASSOC);

            [$chaveAleatoria, $chave] = $this->receptaculo->autenticador->gerarChaveAutenticacao($idUsuario, 0, $nomeCompleto, $email, 0);

            $checarSessaoLivre = PDO::preparar("SELECT (id_usuario) FROM sessoes WHERE id_usuario = ? AND dt_expiracao > CURRENT_TIMESTAMP");
            $checarSessaoLivre->execute([$idUsuario]);
            $resultadoChecarSessaoLivre = $checarSessaoLivre->fetch(\PDO::FETCH_ASSOC);

            if ($resultadoChecarSessaoLivre)
            {
                $atualizarSessao = PDO::preparar("UPDATE sessoes SET nm_chave = ?, dt_expiracao = ? WHERE id_usuario = ?");
                $atualizarSessao->execute([$chaveAleatoria, $dataFutura, $idUsuario]);

                return $this->responder(['codigo' => 'logado',
                    'usuario' => $usuario,
                    'chave' => $chave]);
            }

            $inserirSessao = PDO::preparar("INSERT INTO sessoes (id_usuario, dt_expiracao, nm_chave) VALUES (?, ?, ?)");
            if ($inserirSessao->execute([$idUsuario, $dataFutura, $chaveAleatoria]))
            {
                return $this->responder(['codigo' => 'logado',
                    'usuario' => $usuario,
                    'chave' => $chave]);
            }

            PDO::entregarTransacao();
        }
        catch (\Exception $e)
        {
            var_dump($e->getMessage());
            PDO::reverterTransacao();
            return $this->responder(['codigo' => 'falha']);
        }
    }

    /**
     * @author: Antonio Jorge
     * @created: 15/04/2024
     * @summary: Lista de Solicitação do usuario
     * @roles: Usuário
     */
    public function solicitacoes(): array
    {
        if(!$this->receptaculo->validarAutenticacao(0))
        {
            return $this->responder(['codigo' => 'login_necessario']);
        }

        $pagina = is_null($this->get('pagina')) ? 1 : $this->get('pagina');
        $porPagina = is_null($this->get('porPagina')) ? 15 : $this->get('porPagina');
        $pesquisa = is_null($this->get('pesquisa')) ? '' : $this->get('pesquisa');
            
        $usuario = $this->receptaculo->autenticador->usuario();

        $usuarioId = $usuario['id_usuario'];

        $consultaSolicitacoesUsuarios = PDO::paginacao("SELECT id_solicitacao, nm_residuo, nm_material, qt_material, sg_medida, vl_status, dt_solicitacao, nm_codigo FROM usuarios_solicitacoes 
            JOIN materiais ON materiais.id_material = usuarios_solicitacoes.id_material
            JOIN residuos ON residuos.id_residuo = materiais.id_residuo
            WHERE usuarios_solicitacoes.id_usuario = :usuario
            AND (((case when vl_status = 0 then 'Pendente'
                        when vl_status = 1 then 'Aprovado'
                        when vl_status = -1 then 'Negado'
                    end) ilike '%' || :pesquisa || '%')
            OR (nm_codigo ilike '%' || :pesquisa || '%')
            OR (nm_material ilike '%' || :pesquisa || '%')
            OR :pesquisa is null)

            ORDER BY usuarios_solicitacoes.id_solicitacao DESC",
            [
                ':usuario' => [$usuarioId, \PDO::PARAM_INT],
                ':pesquisa' => [$pesquisa, \PDO::PARAM_STR]
            ], $pagina, 15);

            //AND (nm_residuo LIKE ? OR nm_material LIKE ?) [$usuarioId, "%$procurar%", "%$procurar%"]);
            
        return $this->responder([
            'codigo' => 'recebido',
            'dados' => $consultaSolicitacoesUsuarios
        ]);
    }

    /**
     * @author: Thalys Márcio
     * @created: 19/04/2024
     * @summary: Lista de Solicitação do usuario
     * @roles: Usuário
     */
    public function dados(): array
    {
        if(!$this->receptaculo->validarAutenticacao(0))
        {
            return $this->responder(['codigo' => 'login_necessario']);
        }
            
        $usuario = $this->receptaculo->autenticador->usuario();

        $usuarioId = $usuario['id_usuario'];

        $consultarDados = PDO::preparar("SELECT
                usuarios.id_usuario,
                nm_usuario,
                COALESCE(recebimentos.qt_recebimentos, 0) AS qt_recebimentos,
                COALESCE(solicitacoes.qt_solicitacoes, 0) AS qt_solicitacoes,
                COALESCE(recebimentos.total_ecorecebido, 0) AS total_ecorecebido,
                COALESCE(recebimentos.total_realrecebido, 0) AS total_realrecebido
            FROM
                usuarios
            LEFT JOIN (
                SELECT
                    id_usuario,
                    COUNT(DISTINCT id_recebimento) AS qt_recebimentos,
                    COALESCE(SUM(vl_ecorecebido), 0) AS total_ecorecebido,
                    COALESCE(SUM(vl_realrecebido), 0) AS total_realrecebido
                FROM
                    recebimentos
                GROUP BY
                    id_usuario
            ) AS recebimentos ON recebimentos.id_usuario = usuarios.id_usuario
            LEFT JOIN (
                SELECT
                    id_usuario,
                    COUNT(DISTINCT id_solicitacao) AS qt_solicitacoes
                FROM
                    usuarios_solicitacoes
                GROUP BY
                    id_usuario
            ) AS solicitacoes ON solicitacoes.id_usuario = usuarios.id_usuario
            WHERE
                usuarios.id_usuario = ?");
        $consultarDados->execute([$usuarioId]);

        $retornoDados = $consultarDados->fetch(\PDO::FETCH_ASSOC);
        if ($retornoDados)
        {
            return $this->responder([
                'codigo' => 'recebido',
                'dados' => $retornoDados
            ]);
        }
        return $this->responder([
            'codigo' => 'falha'
        ]);
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

       $listaMateriais = $this->post('lista_materiais');

       if (is_null($listaMateriais) || empty($listaMateriais))
       {
           return $this->responder(['codigo' => 'vazio']);
       }

       $usuario = $this->receptaculo->autenticador->usuario();

       $usuarioId = $usuario['id_usuario'];

       $lista = $this->post('lista_materiais');
       $jsonLista = json_decode($lista, true);

       $chaveAleatoria = $this->receptaculo->gerarCodigo();

       PDO::iniciarTransacao();
       try {
           foreach ($jsonLista as $material)
           {
               if (!isset($material['nm_material']) || !$material['qt_material'])
               {
                   PDO::reverterTransacao();
                   return $this->responder(['codigo' => 'falha']);
               }

               $nomeMaterial = $material['nm_material'];
               $quantidadeMaterial = $material['qt_material'];

               if (!is_numeric($quantidadeMaterial))
               {
                   PDO::reverterTransacao();
                   return $this->responder(['codigo' => 'falha']);
               }
               
               $dataSolicitacao = date('d/m/Y H:i');

               $consultaMaterial = PDO::preparar("SELECT * FROM materiais WHERE nm_material = ?");
               $consultaMaterial->execute([$nomeMaterial]);

               $consultaSolicitacaoMaterial = $consultaMaterial->fetch(\PDO::FETCH_ASSOC);

               if (!$consultaSolicitacaoMaterial)
               {
                   PDO::reverterTransacao();
                   return $this->responder(['codigo' => 'material_inexistente']);
               }

               $idMaterial = $consultaSolicitacaoMaterial['id_material'];

               $inserirSolicitacoes = PDO::preparar("INSERT INTO usuarios_solicitacoes (id_material, id_usuario, qt_material, vl_status, dt_solicitacao, nm_codigo) VALUES (?, ?, ?, 0, ?, ?)");
               $resultadoInsercao = $inserirSolicitacoes->execute([$idMaterial, $usuarioId, $quantidadeMaterial, $dataSolicitacao, $chaveAleatoria]);
               if (!$resultadoInsercao)
               {
                   PDO::reverterTransacao();
                   return $this->responder(['codigo' => 'falha']);
               }
           }
           PDO::entregarTransacao();
           return $this->responder(['codigo' => 'inserido']);
       }
       catch (\Exception $e)
       {
           PDO::reverterTransacao();
           return $this->responder(['codigo' => 'falha']);
       }
   }
}