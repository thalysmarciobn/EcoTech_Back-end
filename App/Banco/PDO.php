<?php

namespace App\Banco;

class PDO
{
    private static $pdo = NULL;

    public static function conectar()
    {
        $servidor = 'localhost';
        $banco = 'eco';
        $usuario = 'postgres';
        $senha = '123456';

        $dsn = "pgsql:host=$servidor;port=5432;dbname=$banco;";
        
        try
        {
            $pdo = new \PDO($dsn, $usuario, $senha, [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION]);
        }
        catch (PDOException $e)
        {
            die($e->getMessage());
        }
    }
    
    public static function consulta($query)
    {
        return $this->pdo->query($query);
    }
}