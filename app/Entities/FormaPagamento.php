<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

class FormaPagamento extends Entity
{
    protected $dates   = 
    [
        'criado_em',
        'atualizado_em',
    ];

    public function exibeSituacao() 
    {
        if($this->ativo == true) {
            return '<i class="fa fa-unlock text-success"></i>&nbsp;Ativa';
        }else{
            return '<i class="fa fa-lock text-warning"></i>&nbsp;Inativa';
        }
    }

}
