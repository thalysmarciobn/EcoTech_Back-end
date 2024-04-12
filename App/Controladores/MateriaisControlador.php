<?php

namespace App\Controladores;

use App\Banco\PDO;

final class MateriaisControlador
{
    /**
     * @author: Thalys Márcio
     * @created: 12/04/2024
     * @summary: Retornar a lista de materiais
     * @roles: Administrador, Funcionário, Usuário
     */
    public static function listaMateriais()
    {
        $consulta = PDO::preparar("SELECT id_material, nm_material, vl_eco, id_residuo, sg_medida FROM materiais");
        $consulta->execute();

        return ['code' => 200, 'data' => $consulta->fetchAll()];
    }

    /**
     * @author: Thalys Márcio
     * @created: 12/04/2024
     * @summary: Adicionar material a partir de uma requisição
     * @request: nm_material
     * @request: vl_eco
     * @request: id_residuo
     * @request: sg_medida
     * @roles: Administrador
     */
    public static function adicionarMaterial()
    {
        $nomeMaterial = $_POST['nm_material'];
        $valorEco = $_POST['vl_eco'];
        $idResiduo = $_POST['id_residuo'];
        $siglaMedida = $_POST['sg_medida'];

        $consultaMaterialNome = PDO::preparar("SELECT nm_material FROM materiais WHERE nm_material = ?");
        $consultaMaterialNome->execute([$nomeMaterial]);

        if ($consultaMaterialNome->fetch(\PDO::FETCH_ASSOC))
        {
            return [
                'code' => 200,
                'data' => [
                    'codigo' => 'material_existente'
                ]
            ];
        }

        $consultaResiduoId = PDO::preparar("SELECT id_residuo FROM residuos WHERE id_residuo = ?");
        $consultaResiduoId->execute([$idResiduo]);

        if (!$consultaResiduoId->fetch(\PDO::FETCH_ASSOC))
        {
            return [
                'code' => 200,
                'data' => [
                    'codigo' => 'residuo_inexistente'
                ]
            ];
        }

        $inserirMaterial = PDO::preparar("INSERT INTO materiais (nm_material, vl_eco, id_residuo, sg_medida)
            VALUES (?, ?, ?, ?)");
        if ($inserirMaterial->execute([$nomeMaterial, $valorEco, $idResiduo, $siglaMedida]))
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
     * @summary: Remove um material a partir de uma requisição
     * @request: nm_material
     * @roles: Administrador
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