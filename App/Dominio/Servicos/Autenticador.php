<?php

namespace App\Dominio\Servicos;

class Autenticador
{
    private const METODO = 'aes-256-cbc';

    private const CHAVE_SECRETA = 'chavesecretaaqui';

    private const FORCA_CHAVE = 16;

    public function __construct()
    {
        $bearer = $_SERVER['HTTP_AUTHORIZATION'];
        
        if (isset($bearer))
        {
            $chaveCriptada = explode('Bearer ', $bearer);
            $chave = $chaveCriptada[1];
        
            var_dump($this->obterDadosChaveAutenticacao($chaveCriptada[1]));
        }
    }

    /**
     * @author: Thalys Márcio
     * @created: 14/04/2024
     * @summary: Cria uma chave aleatória em bytes basseada em um tamanho
     */
    private function chaveAleatoria(int $tamanho): string
    {
        return openssl_random_pseudo_bytes($tamanho);
    }

    /**
     * @author: Thalys Márcio
     * @created: 14/04/2024
     * @summary: Converte bytes para HEX
     */
    private function converterHex($dado): string
    {
        return bin2hex($dado);
    }

    public function criptarChave($dado): string
    {
        $bytes = openssl_random_pseudo_bytes(self::FORCA_CHAVE);
        $encriptar = openssl_encrypt($dado, self::METODO, self::CHAVE_SECRETA, 0, $bytes);
        $result = base64_encode($bytes . $encriptar);

        return $result;
    }

    public function decriptarChave($dado): string
    {
        $decodificado = base64_decode($dado);
        $bytes = substr($decodificado, 0, self::FORCA_CHAVE);
        $dadoEncriptado = substr($decodificado, self::FORCA_CHAVE);
        $dadoDecriptografrado = openssl_decrypt($dadoDecriptografrado, self::METODO, self::CHAVE_SECRETA, 0, $bytes);

        return $dadoDecriptografrado;
    }

    public function gerarChave(): string
    {
        $pseudoBytes = $this->chaveAleatoria(15);
        $dadoHex = $this->converterHex($pseudoBytes);

        return $dadoHex;
    }

    public function gerarChaveAutenticacao(int $id, int $cargo, string $nome, string $email, $chave): string
    {
        $dados = json_encode(['id' => $id, 'cargo' => $cargo, 'nome' => $nome, 'email' => $email, 'chave' => $chave]);
        $criptografado = $this->criptarChave($dados);

        return $criptografado;
    }
    
    public function obterDadosChaveAutenticacao($dado)
    {
        $chaveDecriptada = $this->decriptarChave($dado);

        return $chaveDecriptada;
    }
}