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
        if(!$this->receptaculo->validarAutenticacao(0))
        {
            return $this->responder(['codigo' => 'login_necessario']);
        }
        $consulta = PDO::preparar("SELECT id_material, nm_material, vl_eco, id_residuo, sg_medida FROM materiais WHERE fl_inativo = false ORDER BY nm_material ASC");
        $consulta->execute();

        return $this->responder($consulta->fetchAll(\PDO::FETCH_ASSOC));
    }

    /**
     * @author: Thalys Márcio
     * @created: 12/04/2024
     * @summary: Retornar a lista de materiais
     * @roles: Administrador, Funcionário, Usuário
     */
    public function obter(): array
    {
        $idResiduo = $this->get('id_residuo');

        $consulta = PDO::preparar("SELECT id_material, nm_material, vl_eco, id_residuo, sg_medida FROM materiais WHERE id_residuo = ?");
        $consulta->execute([$idResiduo]);

        return $this->responder($consulta->fetchAll(\PDO::FETCH_ASSOC));
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
        if(!$this->receptaculo->validarAutenticacao(1))
        {
            return $this->responder(['codigo' => 'login_necessario']);
        }
        $nomeMaterial = $this->post('nm_material');
        $valorEco = $this->post('vl_eco');
        $idResiduo = $this->post('id_residuo');
        $siglaMedida = $this->post('sg_medida');

        if (is_null($nomeMaterial) || empty($nomeMaterial) ||
            is_null($valorEco) || empty($valorEco) || !is_numeric($valorEco) ||
            is_null($idResiduo) || empty($idResiduo) || !is_numeric($idResiduo) ||
            is_null($siglaMedida) || empty($siglaMedida)
        )
        {
            return $this->responder(['codigo' => 'vazio']);
        }

        $consultaMaterialNome = PDO::preparar("SELECT nm_material FROM materiais WHERE nm_material = ?");
        $consultaMaterialNome->execute([$nomeMaterial]);

        if ($consultaMaterialNome->fetch(\PDO::FETCH_ASSOC))
        {
            return $this->responder(['codigo' => 'material_existente']);
        }

        $consultaResiduoId = PDO::preparar("SELECT id_residuo FROM residuos WHERE id_residuo = ?");
        $consultaResiduoId->execute([$idResiduo]);
        $resultadoResiduo = $consultaResiduoId->fetch(\PDO::FETCH_ASSOC);

        if (!$resultadoResiduo)
        {
            return $this->responder(['codigo' => 'residuo_inexistente']);
        }

        $inserirMaterial = PDO::preparar("INSERT INTO materiais (nm_material, vl_eco, id_residuo, sg_medida)
            VALUES (?, ?, ?, ?)");
        $executarInserirMaterial = $inserirMaterial->execute([$nomeMaterial, $valorEco, $idResiduo, $siglaMedida]);
        if ($executarInserirMaterial)
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
        if(!$this->receptaculo->validarAutenticacao(1))
        {
            return $this->responder(['codigo' => 'login_necessario']);
        }

        $idMaterial = $this->post('id_material');

        if (is_null($idMaterial) || empty($idMaterial) || !is_numeric($idMaterial))
        {
            return $this->responder(['codigo' => 'vazio']);
        }

        $removerMaterial = PDO::preparar("UPDATE materiais SET fl_inativo = true WHERE id_material = ?");
        if ($removerMaterial->execute([$idMaterial]))
        {
            return $this->responder(['codigo' => $removerMaterial->rowCount()> 0 ? 'removido' : 'inexistente']);
        }

        return $this->responder(['codigo' => 'inexistente']);
    }

    /**
     * @author: Thalys Márcio
     * @created: 23/04/2024
     * @summary: Atualiza um material a partir de uma requisição
     * @roles: Administrador
     */
    public function editar(): array
    {
        if(!$this->receptaculo->validarAutenticacao(1))
        {
            return $this->responder(['codigo' => 'login_necessario']);
        }

        $idMaterial = $this->post('id_material');
        $nomeMaterial = $this->post('nm_material');
        $valorEco = $this->post('vl_eco');
        $siglaMedida = $this->post('sg_medida');
        
        if (is_null($idMaterial) || empty($idMaterial) || !is_numeric($idMaterial) ||
            is_null($nomeMaterial) || empty($nomeMaterial) ||
            is_null($valorEco) || empty($valorEco) || !is_numeric($valorEco) ||
            is_null($siglaMedida) || empty($siglaMedida)
        )
        {
            return $this->responder(['codigo' => 'vazio']);
        }

        $consultaResiduo = PDO::preparar("SELECT nm_material FROM materiais WHERE id_material = ?");
        $consultaResiduo->execute([$idMaterial]);
        $retornoConsultaResiduo = $consultaResiduo->fetch(\PDO::FETCH_ASSOC);

        if (!$retornoConsultaResiduo)
        {
            return $this->responder(['codigo' => 'inexistente']);
        }

        $atualizarResiduo = PDO::preparar("UPDATE materiais SET nm_material = ?, vl_eco = ?, sg_medida = ? WHERE id_material = ?");
        $executarAtualizarResiduo = $atualizarResiduo->execute([$nomeMaterial, $valorEco, $siglaMedida, $idMaterial]);

        if ($executarAtualizarResiduo)
        {
            return $this->responder(['codigo' => 'atualizado']);
        }

        return $this->responder(['codigo' => 'falha']);
    }
}