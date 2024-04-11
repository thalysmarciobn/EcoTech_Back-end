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
        //$consulta = PDO::preparar("SELECT * FROM usuarios WHERE email = ?");
        //$consulta->execute(['test']);

        return ('Isso é um cadastro');
    }
}