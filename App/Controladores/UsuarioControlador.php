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
        $email = $_POST['email'];
        $senha = $_POST['senha'];

        $consultaFuncionario = PDO::preparar("SELECT * FROM usuarios WHERE email = ? and senha = ?");
        $consultaFuncionario->execute([$email, $senha]);

        return ['code' => 200, 'data' => $consultaFuncionario->fetch()];
    }

    /**
     * @author: Thalys Márcio
     * @created: 12/04/2024
     * @summary: Requisição de cadastro de usuário
     * @roles: Administrador, Funcionário
     */
    public static function cadastrar()
    {
        $email = $_POST['email'];
        $senha = $_POST['senha'];

        $consulta = PDO::preparar("SELECT * FROM usuarios WHERE email = ?");
        $consulta->execute([$email]);

        return ['code' => 200, 'data' => $consulta->fetch()];
    }
}