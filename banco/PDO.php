<?php

namespace Banco;

use PDO as NativoPDO;
use PDOException;

class PDO
{
    private static ?NativoPDO $pdo = null;

    private static function conectar(): void
    {
        $servidor = '127.0.0.1';
        $banco = 'eco';
        $usuario = 'postgres';
        $senha = '123456';

        $dsn = "pgsql:host=$servidor;port=5432;dbname=$banco;";
        
        try {
            self::$pdo = new NativoPDO($dsn, $usuario, $senha, [NativoPDO::ATTR_ERRMODE => NativoPDO::ERRMODE_EXCEPTION]);
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
