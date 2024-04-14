<?php

namespace App\Controladores;

use App\BaseControlador;
use Banco\PDO;

final class MateriaisControlador extends BaseControlador
{
    /**
     * @author: Thalys Márcio
     * @created: 12/04/2024
     * @summary: Retornar a lista de materiais
     * @roles: Administrador, Funcionário, Usuário
     */
    public function listaMateriais(): array
    {
        $consulta = PDO::preparar("SELECT id_material, nm_material, vl_eco, id_residuo, sg_medida FROM materiais");
        $consulta->execute();

        return $this->responder($consulta->fetchAll());
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
    public function adicionarMaterial(): array
    {
        $nomeMaterial = $this->post('nm_material');
        $valorEco = $this->post('vl_eco');
        $idResiduo = $this->post('id_residuo');
        $siglaMedida = $this->post('sg_medida');

        $consultaMaterialNome = PDO::preparar("SELECT nm_material FROM materiais WHERE nm_material = ?");
        $consultaMaterialNome->execute([$nomeMaterial]);

        if ($consultaMaterialNome->fetch(\PDO::FETCH_ASSOC))
        {
            return $this->responder(['codigo' => 'material_existente']);
        }

        $consultaResiduoId = PDO::preparar("SELECT id_residuo FROM residuos WHERE id_residuo = ?");
        $consultaResiduoId->execute([$idResiduo]);

        if (!$consultaResiduoId->fetch(\PDO::FETCH_ASSOC))
        {
            return $this->responder(['codigo' => 'residuo_inexistente']);
        }

        $inserirMaterial = PDO::preparar("INSERT INTO materiais (nm_material, vl_eco, id_residuo, sg_medida)
            VALUES (?, ?, ?, ?)");
        if ($inserirMaterial->execute([$nomeMaterial, $valorEco, $idResiduo, $siglaMedida]))
        {
            return $this->responder(['codigo' => 'inserido']);
        }
        return $this->responder(['codigo' => 'falha']);
    }

    /**
     * @author: Thalys Márcio
     * @created: 12/04/2024
     * @summary: Remove um material a partir de uma requisição
     * @request: nm_material
     * @roles: Administrador
     */
    public function removerMaterial(): array
    {
        $nomeMaterial = $this->post('nm_material');

        $removerMaterial = PDO::preparar("DELETE FROM materiais WHERE nm_material = ?");
        if ($removerMaterial->execute([$nomeMaterial]))
        {
            return $this->responder(['codigo' => $removerMaterial->rowCount()> 0 ? 'removido' : 'inexistente']);
        }

        return $this->responder(['codigo' => 'inexistente']);
    }
}