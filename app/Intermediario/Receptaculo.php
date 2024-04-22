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

    public function gerarCodigo() {
        $timestampPart = substr(time(), -4);

        $letras = array_map(fn() => chr(rand(65, 90)), range(1, 4));
        $numeros = array_map(fn() => rand(0, 9), range(1, 4));

        $codigo = '';
        $totalElementos = count($letras) + count($numeros);

        for ($i = 0; $i < $totalElementos; $i++) {
            if ($i % 2 == 0) {
                $codigo .= array_shift($letras);
            } else {
                $codigo .= array_shift($numeros);
            }

            if (($i + 1) % 4 == 0 && $i < $totalElementos - 1) {
                $codigo .= '-';
            }
        }
        
        $codigo .= '-' . $timestampPart;

        return $codigo;
    }    
    
    public function validarAutenticacao(int $cargo = 0): bool
    {
        $usuario = $this->autenticador->usuario();

        $usuarioId = $usuario['id_usuario'];
        $usuarioCargo = $usuario['nu_cargo'];
        $usuarioChave = $usuario['chave'];

        $validarSessao = PDO::preparar("SELECT id_usuario, nm_chave FROM sessoes WHERE id_usuario = ? AND dt_expiracao > CURRENT_TIMESTAMP");
        $validarSessao->execute([$usuarioId]);

        $sessaoBanco = $validarSessao->fetch(\PDO::FETCH_ASSOC);
        if ($sessaoBanco)
        {
            $sessaoIdUsuario = $sessaoBanco['id_usuario'];
            $sessaoChaveUsuario = $sessaoBanco['nm_chave'];

            return $usuarioId == $sessaoIdUsuario &&
                $usuarioCargo >= $cargo &&
                $usuarioChave == $sessaoChaveUsuario;
        }
        return false;
    }

    public function teste(): string
    {
        return "aa";
    }
}