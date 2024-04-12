<?php

namespace App\Controladores;

use App\Banco\PDO;

final class ResiduosControlador
{
    /**
     * @author: Thalys Márcio
     * @created: 12/04/2024
     * @summary: Lista os um resíduos
     * @roles: Administrador, Funcionário, Usuário
     */
    public static function listaResiduos()
    {
        $consulta = PDO::preparar("SELECT id_residuo, nm_residuo FROM residuos");
        $consulta->execute();

        return ['code' => 200, 'data' => $consulta->fetchAll()];
    }

    /**
     * @author: Thalys Márcio
     * @created: 12/04/2024
     * @summary: Adiciona um resíduo a partir de uma requisição
     * @request: nm_residuo
     * @roles: Administrador
     */
    public static function adicionarResiduo()
    {
        $nomeResiduo = $_POST['nm_residuo'];

        $consultaResiduo = PDO::preparar("SELECT nm_residuo FROM residuos WHERE nm_residuo = ?");
        $consultaResiduo->execute([$nomeResiduo]);

        if ($consultaResiduo->fetch(\PDO::FETCH_ASSOC))
        {
            return [
                'code' => 200,
                'data' => [
                    'codigo' => 'existente'
                ]
            ];
        }

        $inserirResiduo = PDO::preparar("INSERT INTO residuos (nm_residuo) VALUES (?)");
        if ($inserirResiduo->execute([$nomeResiduo]))
        {
            return [
                'code' => 200,
                'data' => [
                    'codigo' => 'inserido'
                ]
            ];
        }

        return [
            'code' => 500
        ];
    }

    /**
     * @author: Thalys Márcio
     * @created: 12/04/2024
     * @summary: Atualiza um resíduo a partir de uma requisição
     * @request: nm_residuo
     * @request: nm_novo
     * @roles: Administrador
     */
    public static function atualizarResiduo()
    {
        $nome = $_POST['nm_residuo'];
        $nomeNovo = $_POST['nm_novo'];

        $consultaResiduo = PDO::preparar("SELECT nm_residuo FROM residuos WHERE nm_residuo = ?");
        $consultaResiduo->execute([$nome]);
        $retornoConsultaResiduo = $consultaResiduo->fetch(\PDO::FETCH_ASSOC);

        if (!$retornoConsultaResiduo)
        {
            return [
                'code' => 200,
                'data' => [
                    'codigo' => 'inexistente'
                ]
            ];
        }

        $consultaResiduosNovoExistente = PDO::preparar("SELECT nm_residuo FROM residuos WHERE nm_residuo = ?");
        $consultaResiduosNovoExistente->execute([$nomeNovo]);
        $retornoConsultaResiduosNovoExistente = $consultaResiduosNovoExistente->fetch(\PDO::FETCH_ASSOC);

        if ($retornoConsultaResiduosNovoExistente)
        {
            return [
                'code' => 200,
                'data' => [
                    'codigo' => 'existente'
                ]
            ];
        }

        $atualizarResiduo = PDO::preparar("UPDATE residuos SET nm_residuo = ? WHERE nm_residuo = ?");
        if ($atualizarResiduo->execute([$nomeNovo, $nome]))
        {
            return [
                'code' => 200,
                'data' => [
                    'codigo' => 'atualizado'
                ]
            ];
        }

        return [
            'code' => 500
        ];
    }

    /**
     * @author: Thalys Márcio
     * @created: 12/04/2024
     * @summary: Remove um resíduo a partir de uma requisição
     * @request: nm_residuo
     * @roles: Administrador
     */
    public static function removerResiduo()
    {
        $nome = $_POST['nm_residuo'];

        $removerResiduo = PDO::preparar("DELETE FROM residuos WHERE nm_residuo = ?");
        if ($removerResiduo->execute([$nome]))
        {
            return [
                'code' => 200,
                'data' => [
                    'codigo' => $removerResiduo->rowCount()> 0 ? 'removido' : 'inexistente'
                ]
            ];
        }

        return [
            'code' => 200,
            'data' => [
                'codigo' => 'inexistente'
            ]
        ];
    }
}