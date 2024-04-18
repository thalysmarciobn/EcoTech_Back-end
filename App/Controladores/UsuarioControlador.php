<?php

namespace App\Controladores;

use App\BaseControlador;
use Banco\PDO;

final class UsuarioControlador extends BaseControlador
{
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

        $emailUsuario = $this->post('nm_email');
        $nomeUsuaurio = $this->post('nm_usuario');
        $senhaUsuario = $this->post('nm_senha');
        
        $nomeRua = $this->post('nm_rua');
        $nomeBairro = $this->post('nm_bairro');
        $nomeCidade = $this->post('nm_cidade');
        $nomeEstado = $this->post('nm_estado');
        $numeroCasa = $this->post('nu_casa');

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
            
            $senhaCriptografada = md5($senhaUsuario);
            
            if ($inserirUsuario->execute([$emailUsuario, $nomeUsuaurio, $senhaCriptografada, 0, 0]))
            {
                $inserirUsuarioEndereco = PDO::preparar("INSERT INTO usuarios_enderecos (id_usuario, nm_rua, nm_bairro, nm_cidade, nm_estado, nu_casa) VALUES (?, ?, ?, ?, ?, ?)");
                
                $idUsuario = PDO::ultimaIdInserida();

                if (!$inserirUsuarioEndereco->execute([$idUsuario, $nomeRua, $nomeBairro, $nomeCidade, $nomeEstado, $numeroCasa]))
                {
                    PDO::reverterTransacao();

                    return $this->responder(['codigo' => 'falha']);
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