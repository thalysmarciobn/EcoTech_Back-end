<?php

namespace App\Controladores;

use App\Banco\PDO;

final class UsuarioControlador
{
    /**
     * @author: Thalys Márcio
     * @created: 12/04/2024
     * @summary: Requisição de logar e criação de sessão
     * @roles:
     */
    public static function logar()
    {
        $emailUsuario = $_POST['nm_email'];
        $senhaUsuario = $_POST['nm_senha'];

        $senhaCriptografada = md5($senhaUsuario);

        $consultaUsuario = PDO::preparar("SELECT * FROM usuarios WHERE nm_email = ? and nm_senha = ?");
        $consultaUsuario->execute([$emailUsuario, $senhaCriptografada]);

        $usuario = $consultaUsuario->fetch(\PDO::FETCH_ASSOC);
        
        if ($usuario)
        {
            $idUsuario = $usuario['id_usuario'];

            $umaHoraFutura = strtotime("+1 hour");
            $dataFutura = date('d/m/Y H:i', $umaHoraFutura);

            $pseudoBytes = openssl_random_pseudo_bytes(15);
            $chaveAleatoria = bin2hex($pseudoBytes);

            $checarSessaoLivre = PDO::preparar("SELECT (id_usuario) FROM sessoes WHERE id_usuario = ? AND dt_expiracao > CURRENT_TIMESTAMP");
            $checarSessaoLivre->execute([$idUsuario]);

            if ($checarSessaoLivre->fetch(\PDO::FETCH_ASSOC))
            {
                // update
                return [
                    'code' => 200,
                    'data' => [
                        'codigo' => 'logado',
                        'chave' => $chaveAleatoria
                    ]
                ];
            }

            $inserirSessao = PDO::preparar("INSERT INTO sessoes (id_usuario, dt_expiracao, nm_chave) VALUES (?, ?, ?)");
            if ($inserirSessao->execute([$idUsuario, $dataFutura, $chaveAleatoria]))
            {
                return [
                    'code' => 200,
                    'data' => [
                        'codigo' => 'logado',
                        'chave' => $chaveAleatoria
                    ]
                ];
            }
        }

        return ['code' => 500];
    }

    /**
     * @author: Thalys Márcio
     * @created: 12/04/2024
     * @summary: Requisição de cadastro de usuário
     * @roles:
     */
    public static function cadastrar()
    {
        $emailUsuario = $_POST['nm_email'];
        $nomeUsuaurio = $_POST['nm_usuario'];
        $senhaUsuario = $_POST['nm_senha'];
        
        $nomeRua = $_POST['nm_rua'];
        $nomeBairro = $_POST['nm_bairro'];
        $nomeCidade = $_POST['nm_cidade'];
        $nomeEstado = $_POST['nm_estado'];
        $numeroCasa = $_POST['nu_casa'];

        $consultaUsuario = PDO::preparar("SELECT * FROM usuarios WHERE nm_email = ?");
        $consultaUsuario->execute([$emailUsuario]);

        if ($consultaUsuario->fetch(\PDO::FETCH_ASSOC))
        {
            return [
                'code' => 200,
                'data' => [
                    'codigo' => 'usuario_existente'
                ]
            ];
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

                    return ['code' => 500];
                }

                PDO::entregarTransacao();
                return [
                    'code' => 200,
                    'data' => [
                        'codigo' => 'inserido'
                    ]
                ];
            }
            PDO::reverterTransacao();
        }
        catch (\Exception $e)
        {
            PDO::reverterTransacao();
        }

        return ['code' => 500];
    }
}