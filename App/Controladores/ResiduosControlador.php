<?php

namespace App\Controladores;

use App\Banco\PDO;

final class ResiduosControlador
{

    public static function listaResiduos()
    {
        $consulta = PDO::preparar("SELECT id_residuo, nm_residuo FROM residuos");
        $consulta->execute();

        return ['code' => 200, 'data' => $consulta->fetchAll()];
    }



    public static function adicionarResiduo()
    {
        $nome = $_POST['nm_pessoa'];

        $consultaResiduos = PDO::preparar("SELECT * FROM residuos WHERE nm_residuo = ?");
        $consultaResiduos->execute([$nome]);

        if ($consultaResiduos->fetch(\PDO::FETCH_ASSOC))
        {
            return [
                'code' => 200,
                'data' => [
                    'codigo' => 'existente'
                ]
            ];
        }

        $inserirResiduo = PDO::preparar("INSERT INTO residuos (nome) VALUES (?)");
        if ($inserirResiduo->execute([$nome]))
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

    public static function atualizarResiduo()
    {
        $nome = $_POST['nm_pessoa'];
        $nomeNovo = $_POST['nm_novo'];

        $consultaResiduos = PDO::preparar("SELECT * FROM residuos WHERE nm_residuo = ?");
        $consultaResiduos->execute([$nome]);

        if (!$consultaResiduos->fetch(\PDO::FETCH_ASSOC))
        {
            return [
                'code' => 200,
                'data' => [
                    'codigo' => 'inexistente'
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

    public static function removerResiduo()
    {
        $nome = $_POST['nm_pessoa'];

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