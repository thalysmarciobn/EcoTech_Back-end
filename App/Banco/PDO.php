<?php

namespace App\Banco;

use PDO as ConexaoPDO;
use PDOException;

class PDO
{
    private static ?ConexaoPDO $pdo = null;

    public static function conectar(): void
    {
        $servidor = '127.0.0.1';
        $banco = 'eco';
        $usuario = 'postgres';
        $senha = '123456';

        $dsn = "pgsql:host=$servidor;port=5432;dbname=$banco;";
        
        try {
            self::$pdo = new BasePDO($dsn, $usuario, $senha, [BasePDO::ATTR_ERRMODE => BasePDO::ERRMODE_EXCEPTION]);
        } catch (PDOException $e) {
            die($e->getMessage());
        }
    }

    public static function iniciarTransacao(): void
    {
        self::verificarConexao();
        self::$pdo->beginTransaction();
    }

    public static function entregarTransacao(): void
    {
        self::verificarConexao();
        self::$pdo->commit();
    }

    public static function reverterTransacao(): void
    {
        self::verificarConexao();
        self::$pdo->rollBack();
    }

    public static function ultimaIdInserida()
    {
        self::verificarConexao();
        return self::$pdo->lastInsertId();
    }
    
    public static function consulta(string $query)
    {
        self::verificarConexao();
        return self::$pdo->query($query);
    }

    public static function preparar(string $query, array $opcoes = [])
    {
        self::verificarConexao();
        return self::$pdo->prepare($query, $opcoes);
    }

    private static function verificarConexao(): void
    {
        if (self::$pdo === null) {
            self::conectar();
        }
    }
}
