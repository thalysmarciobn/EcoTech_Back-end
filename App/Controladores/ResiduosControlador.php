<?php

namespace App\Controladores;

use App\Banco\PDO;

final class ResiduosControlador
{

    public static function listaResiduos()
    {
        $consulta = PDO::preparar("SELECT idResiduo, nome FROM residuos");
        $consulta->execute();

        return ['code' => 200, 'data' => $consulta->fetchAll()];
    }



    public static function adicionarResiduo()
    {
        $nome = $_POST['nome'];

        $consultaResiduos = PDO::preparar("SELECT * FROM residuos WHERE nome = ?");
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
        $nome = $_POST['nome'];
        $novoNome = $_POST['novoNome'];

        $consultaResiduos = PDO::preparar("SELECT * FROM residuos WHERE nome = ?");
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

        $atualizarResiduo = PDO::preparar("UPDATE residuos SET nome = ? WHERE nome = ?");
        if ($atualizarResiduo->execute([$novoNome, $nome]))
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
        $nome = $_POST['nome'];

        $removerResiduo = PDO::preparar("DELETE FROM residuos WHERE nome = ?");
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