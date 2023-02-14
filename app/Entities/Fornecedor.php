<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

class Fornecedor extends Entity
{
    protected $dates   = [
        'criado_em',
        'atualizado_em',
        'deletado_em'
    ];

    public function exibeSituacao() {
        
        if($this->deletado_em != null){
            
            $icone = '<span class="text-white">Excluído</span>&nbsp<i class="fa fa-undo"></i>&nbspRecuperar';

            $situacao = anchor("fornecedores/recuperar/$this->id", $icone, ['class' => 'btn btn-outline-success btn-sm']);

            return $situacao;
        }
        
        if($this->ativo == true) {
            return '<i class="fa fa-unlock text-success"></i>&nbsp;Ativo';
        }else{
            return '<i class="fa fa-lock text-warning"></i>&nbsp;Inativo';
        }

    }
}
