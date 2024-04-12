<?php

namespace App\Controladores;

use App\Banco\PDO;

final class MaterialControlador
{
    /**
     * @author: Thalys Márcio
     * @created: 12/04/2024
     * @summary: Retornar a lista de materiais
     */
    public static function listaMateriais()
    {
        $consulta = PDO::preparar("SELECT id_material, nm_material, qt_eco, id_residuo, sg_medida FROM materiais");
        $consulta->execute();

        return ['code' => 200, 'data' => $consulta->fetchAll()];
    }

    /**
     * @author: Thalys Márcio
     * @created: 12/04/2024
     * @summary: Adicionar material à parti de uma requisição
     */
    public static function adicionarMaterial()
    {
        $nomeMaterial = $_POST['nm_material'];
        $quantidadeEco = $_POST['qt_eco'];
        $idResiduo = $_POST['id_residuo'];
        $siglaMedida = $_POST['sg_medida'];

        $consultaResiduo = PDO::preparar("SELECT id_residuo FROM residuos WHERE id_residuo = ?");
        $consultaResiduo->execute([$idResiduo]);

        if (!$consultaResiduo->fetch(\PDO::FETCH_ASSOC))
        {
            return [
                'code' => 200,
                'data' => [
                    'codigo' => 'residuo_existente'
                ]
            ];
        }

        $inserirMaterial = PDO::preparar("INSERT INTO materiais (nm_material, qt_eco, id_residuo, sg_medida)
            VALUES (?, ?, ?, ?)");
        if ($inserirMaterial->execute([$nomeMaterial, $quantidadeEco, $idResiduo, $siglaMedida]))
        {
            return [
                'code' => 200,
                'data' => [
                    'codigo' => 'inserido'
                ]
            ];
        }
        return ['code' => 200, 'data' => [
            'codigo' => 'falha'
        ]];
    }

    /**
     * @author: Thalys Márcio
     * @created: 12/04/2024
     * @summary: Remove um material à parti de uma requisição
     */
    public static function removerMaterial()
    {
        $nomeMaterial = $_POST['nm_material'];

        $removerMaterial = PDO::preparar("DELETE FROM materiais WHERE nm_material = ?");
        if ($removerMaterial->execute([$nomeMaterial]))
        {
            return [
                'code' => 200,
                'data' => [
                    'codigo' => $removerMaterial->rowCount()> 0 ? 'removido' : 'inexistente'
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