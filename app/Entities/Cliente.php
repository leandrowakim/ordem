<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

class Cliente extends Entity
{
    protected $dates   = [
        'criado_em',
        'atualizado_em',
        'deletado_em'
    ];

    public function exibeSituacao() 
    {
        
        if($this->deletado_em != null){
            
            $icone = '<span class="text-white">Excluído</span>&nbsp<i class="fa fa-undo"></i>&nbspRecuperar';

            $situacao = anchor("clientes/recuperar/$this->id", $icone, ['class' => 'btn btn-outline-success btn-sm']);

            return $situacao;
        }

        $situacao = '<span class="text-white"><i class="fa fa-thumbs-up"></i>&nbsp;Disponivel';
        return $situacao;
    }
}
