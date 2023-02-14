<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

class Item extends Entity
{
    protected $dates =
    [
        'criado_em',
        'atualizado_em',
        'deletado_em',
    ];
    
    public function exibeSituacao() 
    {
        if($this->deletado_em != null){
            $icone = '<span class="text-white">Excluído</span>&nbsp<i class="fa fa-undo"></i>&nbspRecuperar';

            $situacao = anchor("itens/recuperar/$this->id", $icone, ['class' => 'btn btn-outline-success btn-sm']);

            return $situacao;
        }
        
        if($this->ativo == true) {
            return '<i class="fa fa-unlock text-success"></i>&nbsp;Ativo';
        }else{
            return '<i class="fa fa-lock text-warning"></i>&nbsp;Inativo';
        }

    }

    public function exibeTipo() 
    {
        $tipoItem = "";

        if($this->tipo === 'produto') {
            $tipoItem = '<i class="fa fa-archive text-success" aria-hidden="true"></i>&nbsp;Produto';
        }else{
            $tipoItem = '<i class="fa fa-wrench text-warning" aria-hidden="true"></i>&nbsp;Serviço';
        }

        return $tipoItem;
    }

    public function exibeEstoque() 
    {     
        if($this->tipo === 'produto') {
            return $this->estoque;
        }else{
            return 'Não se aplica';
        }
    }

    public function recuperaAtributosAlterados() : string
    {
        $atributosAlterados = [];

        if ($this->hasChanged('nome')) {
            $atributosAlterados['nome'] = "O nome foi alterado para $this->nome";
        }

        if ($this->hasChanged('descricao')) {
            $atributosAlterados['descricao'] = "A descrição foi alterada para $this->descricao";
        }

        if ($this->hasChanged('marca')) {
            $atributosAlterados['marca'] = "A marca foi alterada para $this->marca";
        }

        if ($this->hasChanged('modelo')) {
            $atributosAlterados['modelo'] = "O modelo foi alterado para $this->modelo";
        }

        if ($this->hasChanged('preco_custo')) {
            $atributosAlterados['preco_custo'] = "O preço de custo foi alterado para $this->preco_custo";
        }
        
        if ($this->hasChanged('preco_venda')) {
            $atributosAlterados['preco_venda'] = "O preço de venda foi alterado para $this->preco_venda";
        }
        
        if ($this->hasChanged('estoque')) {
            $atributosAlterados['estoque'] = "O estoque foi alterado para $this->estoque";
        }

        if ($this->hasChanged('controla_estoque')) {
            if ($this->controla_estoque == true) {
                $atributosAlterados['controla_estoque'] = "O controle de estoque foi ativado";
            } else {
                $atributosAlterados['controla_estoque'] = "O controle de estoque foi inativado";
            }
        }

        if ($this->hasChanged('ativo')) {
            if ($this->controla_estoque == true) {
                $atributosAlterados['ativo'] = "O item foi ativado";
            } else {
                $atributosAlterados['ativo'] = "O item foi inativado";
            }
        }

        return serialize($atributosAlterados);
    }

}
