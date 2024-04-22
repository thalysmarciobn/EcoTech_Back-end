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
        $senha = '12345';

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

    public static function paginacao($sql, $parametros = [], $pagina = 1, $porPagina = 10)
    {
        $offset = ($pagina - 1) * $porPagina;
    
        $queryTotal = self::preparar("SELECT COUNT(*) FROM ($sql) AS total");
        $queryTotal->execute($parametros);
        $quantidadeItens = $queryTotal->fetchColumn();
    
        if ($quantidadeItens <= $offset) {
            return [
                'lista' => [],
                'pagina' => $pagina,
                'total_paginas' => 0,
                'proxima_pagina' => null,
                'pagina_anterior' => ($pagina > 1) ? $pagina - 1 : null
            ];
        }
        $sql .= " LIMIT $porPagina OFFSET $offset";
    
        $query = self::preparar($sql);
        $query->execute($parametros);
    
        $dados = $query->fetchAll(\PDO::FETCH_ASSOC);
    
        $totalPaginas = ceil($quantidadeItens / $porPagina);
    
        $proximaPagina = ($pagina < $totalPaginas) ? $pagina + 1 : null;
        $paginaAnterior = ($pagina > 1) ? $pagina - 1 : null;
    
        return [
            'lista' => $dados,
            'pagina' => $pagina,
            'total_paginas' => $totalPaginas,
            'proxima_pagina' => $proximaPagina,
            'pagina_anterior' => $paginaAnterior
        ];
    }

    private static function verificarConexao(): void
    {
        if (self::$pdo === null) {
            self::conectar();
        }
    }
}
