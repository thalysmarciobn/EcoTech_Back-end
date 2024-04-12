<?php

namespace App\Controladores;

use App\Banco\PDO;

final class UsuarioControlador
{

    public static function logar()
    {
        $email = $_POST['email'];
        $senha = $_POST['senha'];

        $consultaFuncionario = PDO::preparar("SELECT * FROM funcionario WHERE email = ? and senha = ?");
        $consultaFuncionario->execute([$email,$senha]);

        return $consultaFuncionario->fetch();


    }

    public static function cadastrar()
    {
        $email = $_POST['email'];
        $senha = $_POST['senha'];

        $consulta = PDO::preparar("SELECT * FROM usuarios WHERE email = ?");
        $consulta->execute([$email]);

        return $consulta->fetch();
    }
}