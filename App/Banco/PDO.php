<?php

namespace App\Banco;

class PDO
{
    private static $pdo = NULL;

    public static function conectar()
    {
        $servidor = '127.0.0.1';
        $banco = 'eco';
        $usuario = 'postgres';
        $senha = '123456';

        $dsn = "pgsql:host=$servidor;port=5432;dbname=$banco;";
        
        try
        {
            self::$pdo = new \PDO($dsn, $usuario, $senha, [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION]);
        }
        catch (PDOException $e)
        {
            die($e->getMessage());
        }
    }

    public static function iniciarTransacao(): void
    {
        self::$pdo->beginTransaction();
    }

    public static function entregarTransacao(): void
    {
        self::$pdo->commit();
    }

    public static function reverterTransacao(): void
    {
        self::$pdo->rollBack();
    }

    public static function ultimaIdInserida()
    {
        return self::$pdo->lastInsertId();
    }
    
    public static function consulta(string $query)
    {
        return self::$pdo->query($query);
    }

    public static function preparar(string $query, array $opcoes = [])
    {
        return self::$pdo->prepare($query, $opcoes);
    }
}