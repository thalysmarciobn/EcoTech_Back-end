<?php

namespace App\Rotas;

class ChamadaDeRotas
{
    private $get = NULL;

    private $post = NULL;

    private $put = NULL;

    private $delete = NULL;

    public function get()
    {
        return $this->get;
    }
    
    public function atualizarGet($metodo): bool
    {
        if ($this->get == NULL)
        {
            $this->get = $metodo;
            return true;
        }
        return false;
    }

    public function post()
    {
        return $this->post;
    }
    
    public function atualizarPost($metodo): bool
    {
        if ($this->post == NULL)
        {
            $this->post = $metodo;
            return true;
        }
        return false;
    }

    public function put()
    {
        return $this->put;
    }
    
    public function atualizarPut($metodo): bool
    {
        if ($this->put == NULL)
        {
            $this->put = $metodo;
            return true;
        }
        return false;
    }

    public function delete()
    {
        return $this->delete;
    }
    
    public function atualizarDelete($metodo): bool
    {
        if ($this->delete == NULL)
        {
            $this->delete = $metodo;
            return true;
        }
        return false;
    }
}