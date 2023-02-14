<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

class Grupo extends Entity
{
    protected $dates   = [
        'criado_em',
        'atualizado_em',
        'deletado_em'
    ];

    public function exibeSituacao() {
        
        if($this->deletado_em != null){
            //Se excluído
            $icone = '<span class="text-white">Excluído</span>&nbsp<i class="fa fa-undo"></i>&nbspRecuperar';

            $situacao = anchor("grupos/recuperar/$this->id", $icone, ['class' => 'btn btn-outline-success btn-sm']);

            return $situacao;
        }
        
        if($this->exibir == true) {
            return '<i class="fa fa-eye text-secundary"></i>&nbsp;Exibir grupo';
        }else{
            return '<i class="fa fa-eye-slash text-danger"></i>&nbsp;Não exibir grupo';
        }

    }
}
