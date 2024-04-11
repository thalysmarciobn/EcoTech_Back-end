<?php

namespace App\Controladores;

use App\Banco\PDO;

final class UsuarioControlador
{

    public static function logar()
    {
        print('aaadfjhoisdfjiojhf ');
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