<?php

namespace App\Controladores;

use App\Banco\PDO;

final class SolicitacoesControlador
{
    /**
     * @author: Thalys Márcio
     * @created: 12/04/2024
     * @summary: Lista os um solicitações
     * @roles: Administrador, Funcionário
     */
    public static function listaSolicitacoes()
    {
        $consulta = PDO::preparar("SELECT id_residuo, nm_residuo FROM residuos");
        $consulta->execute();

        return ['code' => 200, 'data' => $consulta->fetchAll()];
    }
}