<?php

namespace App\Controladores;

final class TesteControlador
{

    public static function teste()
    {
        return ['code' => 200, 'data' => ['data' => 'Isso é um teste']];
    }
}