<?php

namespace App\Intermediario;

use App\Dominio\Servicos\Autenticador;
use Banco\PDO;

class Receptaculo
{
    public ?Autenticador $autenticador = NULL;

    public function __construct()
    {
        $this->autenticador = new Autenticador();
    }

    public function validarAutenticacao(int $cargo = 0): bool
    {
        $usuario = $this->autenticador->usuario();

        $usuarioId = $usuario['id'];
        $usuarioCargo = $usuario['cargo'];
        $usuarioChave = $usuario['chave'];

        $validarSessao = PDO::preparar("SELECT id_usuario, nm_chave FROM sessoes WHERE id_usuario = ? AND dt_expiracao > CURRENT_TIMESTAMP");
        $validarSessao->execute([$usuarioId]);

        $sessaoBanco = $validarSessao->fetch(\PDO::FETCH_ASSOC);
        if ($sessaoBanco)
        {
            $sessaoIdUsuario = $sessaoBanco['id_usuario'];
            $sessaoChaveUsuario = $sessaoBanco['nm_chave'];

            return $usuarioId == $sessaoIdUsuario &&
            $usuarioCargo == $cargo &&
            $usuarioChave == $sessaoChaveUsuario;
        }
        return false;
    }

    public function teste(): string
    {
        return "aa";
    }
}