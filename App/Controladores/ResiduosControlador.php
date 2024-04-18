<?php

namespace App\Controladores;

use App\BaseControlador;
use Banco\PDO;

final class ResiduosControlador extends BaseControlador
{
    /**
     * @author: Thalys Márcio
     * @created: 12/04/2024
     * @summary: Lista os um resíduos
     * @roles: Administrador, Funcionário, Usuário
     */
    public function listaResiduos(): array
    {
        if(!$this->receptaculo->validarAutenticacao(0))
        {
            return $this->responder(['codigo' => 'login_necessario']);
        }
        $consulta = PDO::preparar("SELECT id_residuo, nm_residuo FROM residuos");
        $consulta->execute();

        return $this->responder($consulta->fetchAll(\PDO::FETCH_ASSOC));
    }

    /**
     * @author: Thalys Márcio
     * @created: 12/04/2024
     * @summary: Adiciona um resíduo a partir de uma requisição
     * @request: nm_residuo
     * @roles: Administrador
     */
    public function adicionarResiduo(): array
    {
        if(!$this->receptaculo->validarAutenticacao(1))
        {
            return $this->responder(['codigo' => 'login_necessario']);
        }

        if (empty($this->post('nm_residuo')))
        {
            return $this->responder(['codigo' => 'vazio']);
        }

        $nomeResiduo = $this->post('nm_residuo');

        $consultaResiduo = PDO::preparar("SELECT nm_residuo FROM residuos WHERE nm_residuo = ?");
        $consultaResiduo->execute([$nomeResiduo]);

        if ($consultaResiduo->fetch(\PDO::FETCH_ASSOC))
        {
            return $this->responder(['codigo' => 'existente']);
        }

        $inserirResiduo = PDO::preparar("INSERT INTO residuos (nm_residuo) VALUES (?)");
        if ($inserirResiduo->execute([$nomeResiduo]))
        {
            return $this->responder(['codigo' => 'inserido']);
        }

        return $this->responder(['codigo' => 'falha']);
    }

    /**
     * @author: Thalys Márcio
     * @created: 12/04/2024
     * @summary: Atualiza um resíduo a partir de uma requisição
     * @request: nm_residuo
     * @request: nm_novo
     * @roles: Administrador
     */
    public function atualizarResiduo(): array
    {
        if(!$this->receptaculo->validarAutenticacao(1))
        {
            return $this->responder(['codigo' => 'login_necessario']);
        }

        if (empty($this->post('nm_residuo') || empty($this->post('nm_novo'))))
        {
            return $this->responder(['codigo' => 'vazio']);
        }

        $nome = $this->post('nm_residuo');
        $nomeNovo = $this->post('nm_novo');

        $consultaResiduo = PDO::preparar("SELECT nm_residuo FROM residuos WHERE nm_residuo = ?");
        $consultaResiduo->execute([$nome]);
        $retornoConsultaResiduo = $consultaResiduo->fetch(\PDO::FETCH_ASSOC);

        if (!$retornoConsultaResiduo)
        {
            return $this->responder(['codigo' => 'inexistente']);
        }

        $consultaResiduosNovoExistente = PDO::preparar("SELECT nm_residuo FROM residuos WHERE nm_residuo = ?");
        $consultaResiduosNovoExistente->execute([$nomeNovo]);
        $retornoConsultaResiduosNovoExistente = $consultaResiduosNovoExistente->fetch(\PDO::FETCH_ASSOC);

        if ($retornoConsultaResiduosNovoExistente)
        {
            return $this->responder(['codigo' => 'existente']);
        }

        $atualizarResiduo = PDO::preparar("UPDATE residuos SET nm_residuo = ? WHERE nm_residuo = ?");
        if ($atualizarResiduo->execute([$nomeNovo, $nome]))
        {
            return $this->responder(['codigo' => 'atualizado']);
        }

        return $this->responder(['codigo' => 'falha']);
    }

    /**
     * @author: Thalys Márcio
     * @created: 12/04/2024
     * @summary: Remove um resíduo a partir de uma requisição
     * @request: nm_residuo
     * @roles: Administrador
     */
    public function removerResiduo(): array
    {
        if(!$this->receptaculo->validarAutenticacao(1))
        {
            return $this->responder(['codigo' => 'login_necessario']);
        }

        if (empty($this->post('nm_residuo')))
        {
            return $this->responder(['codigo' => 'vazio']);
        }

        $nome = $this->post('nm_residuo');

        $removerResiduo = PDO::preparar("DELETE FROM residuos WHERE nm_residuo = ?");
        if ($removerResiduo->execute([$nome]))
        {
            return $this->responder(['codigo' => $removerResiduo->rowCount()> 0 ? 'removido' : 'inexistente']);
        }

        return $this->responder(['codigo' => 'falha']);
    }
}