<?php

namespace App\Controladores;

use App\BaseControlador;
use Banco\PDO;

final class UsuarioControlador extends BaseControlador
{
    
    
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
        if (empty($this->post('nm_email')) || empty($this->post('nm_senha')))
        {
            return $this->responder(['codigo' => 'vazio']);
        }

        $emailUsuario = $this->post('nm_email');
        $senhaUsuario = $this->post('nm_senha');

        $senhaCriptografada = md5($senhaUsuario);

        $consultaUsuario = PDO::preparar("SELECT * FROM usuarios WHERE nm_email = ? and nm_senha = ?");
        $consultaUsuario->execute([$emailUsuario, $senhaCriptografada]);

        $usuario = $consultaUsuario->fetch(\PDO::FETCH_ASSOC);
        
        if ($usuario)
        {
            $idUsuario = $usuario['id_usuario'];
            $nomeUsuario = $usuario['nm_usuario'];
            $cargoUsuario = $usuario['nu_cargo'];
            $quantidadeEcoSaldo = $usuario['qt_ecosaldo'];

            $umaHoraFutura = strtotime("+1 hour");
            $dataFutura = date('d/m/Y H:i', $umaHoraFutura);
            
            [$chaveAleatoria, $chave] = $this->receptaculo->autenticador->gerarChaveAutenticacao($idUsuario, $cargoUsuario, $nomeUsuario, $emailUsuario);

            $checarSessaoLivre = PDO::preparar("SELECT (id_usuario) FROM sessoes WHERE id_usuario = ? AND dt_expiracao > CURRENT_TIMESTAMP");
            $checarSessaoLivre->execute([$idUsuario]);

            if ($checarSessaoLivre->fetch(\PDO::FETCH_ASSOC))
            {
                $atualizarSessao = PDO::preparar("UPDATE sessoes SET nm_chave = ?, dt_expiracao = ? WHERE id_usuario = ?");
                $atualizarSessao->execute([$chaveAleatoria, $dataFutura, $idUsuario]);

                return $this->responder(['codigo' => 'logado',
                    'nm_usuario' => $nomeUsuario,
                    'nu_cargo' => $cargoUsuario,
                    'qt_ecosaldo' => $quantidadeEcoSaldo,
                    'chave' => $chave]);
            }

            $inserirSessao = PDO::preparar("INSERT INTO sessoes (id_usuario, dt_expiracao, nm_chave) VALUES (?, ?, ?)");
            if ($inserirSessao->execute([$idUsuario, $dataFutura, $chaveAleatoria]))
            {
                return $this->responder(['codigo' => 'logado',
                    'nm_usuario' => $nomeUsuario,
                    'nu_cargo' => $cargoUsuario,
                    'qt_ecosaldo' => $quantidadeEcoSaldo,
                    'chave' => $chave]);
            }
        }

        return $this->responder(['codigo' => 'falha']);
    }

    public function enderecos(): array
    {
        if(!$this->receptaculo->validarAutenticacao(0))
        {
            return $this->responder(['codigo' => 'login_necessario']);
        }

        $usuario = $this->receptaculo->autenticador->usuario();

        $usuarioId = $usuario['id'];

        $enderecos = PDO::preparar("SELECT id_endereco, id_usuario, nm_rua, nm_bairro, nm_cidade, nm_estado, nu_casa, nm_complemento FROM usuarios_enderecos WHERE id_usuario = ?");
        if ($enderecos->execute([$usuarioId]))
        {
            return $this->responder([
                'codigo' => 'sucesso',
                'enderecos' => $enderecos->fetchAll(\PDO::FETCH_ASSOC)
            ]);
        }

        return $this->responder(['codigo' => 'falha']);
    }

    /**
     * @author: Thalys Márcio
     * @created: 12/04/2024
     * @summary: Requisição de cadastro de usuário
     * @roles:
     */
    public function cadastrar(): array
    {
        if (empty($this->post('nm_email')) ||
            empty($this->post('nm_usuario')) ||
            empty($this->post('nm_senha')))
        {
            return $this->responder(['codigo' => 'vazio']);
        }

        $enderecos = $this->post('lista_enderecos');

        $consultaUsuario = PDO::preparar("SELECT * FROM usuarios WHERE nm_email = ?");
        $consultaUsuario->execute([$emailUsuario]);

        if ($consultaUsuario->fetch(\PDO::FETCH_ASSOC))
        {
            return $this->responder(['codigo' => 'usuario_existente']);
        }

        try
        {
            PDO::iniciarTransacao();
            
            $inserirUsuario = PDO::preparar("INSERT INTO usuarios (nm_email, nm_usuario, nm_senha, qt_ecosaldo, nu_cargo) VALUES (?, ?, ?, ?, ?)");
            
            $emailUsuario = $this->post('nm_email');
            $nomeUsuaurio = $this->post('nm_usuario');
            $senhaCriptografada = md5($senhaUsuario);
            
            if ($inserirUsuario->execute([$emailUsuario, $nomeUsuaurio, $senhaCriptografada, 0, 0]))
            {
                
                $jsonLista = json_decode($enderecos, true);

                foreach ($jsonLista as $endereco) {
                    $inserirUsuarioEndereco = PDO::preparar("INSERT INTO usuarios_enderecos (id_usuario, nm_rua, nm_bairro, nm_cidade, nm_estado, nu_casa) VALUES (?, ?, ?, ?, ?, ?)");
                    
                    $idUsuario = PDO::ultimaIdInserida();

                    if (!$inserirUsuarioEndereco->execute([$idUsuario, $endereco['rua'], $endereco['bairro'], $endereco['cidade'], $endereco['estado'], 0]))
                    {
                        PDO::reverterTransacao();

                        return $this->responder(['codigo' => 'falha']);
                    }
                }

                PDO::entregarTransacao();
                return $this->responder(['codigo' => 'inserido']);
            }
            PDO::reverterTransacao();
        }
        catch (\Exception $e)
        {
            PDO::reverterTransacao();
        }

        return $this->responder(['codigo' => 'falha']);
    }
}