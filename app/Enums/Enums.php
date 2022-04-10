<?php

namespace App\Enums;

abstract class Enums
{
    const MONTH_NAMES = [
        'Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez',
    ];

    const MASKS = [
        'cnpj'     => "##.###.###/####-##",
        'cpf'      => "###.###.###-##",
        'zip_code' => "#####-###",
        'phone'    => "(##)#####-####",
    ];
}
