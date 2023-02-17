<?php

namespace App\Traits;

trait OrdemTrait
{
   public function preparaItensDaOrdem(object $ordem) : object
   {
      $ordemItemModel = new \App\Models\OrdemItemModel();

      if ($ordem->situacao === 'aberta') {
         
         $ordemItens = $ordemItemModel->recuperaItensDaOrdem($ordem->id);

         $ordem->itens = (empty($ordemItens) ? null : $ordemItens);

         return $ordem;
      }

      if ($ordem->itens !== null) {
         
         $ordem->itens = unserialize($ordem->itens);
      }

      return $ordem;
   }

   public function preparaOrdemParaEncerrar(object $ordem, object $formaPagamento) : object
   {
      $ordem->situacao = ($formaPagamento->id === "1" ? "aguardando" : "encerrada");

      if ($ordem->itens === null) {
         
         $ordem->forma_pagamento = "Cortesia";
         $ordem->valor_produtos = null;
         $ordem->valor_servicos = null;
         $ordem->valor_desconto = null;
         $ordem->valor_ordem = null;

         return $ordem;
      }

      // Nesse ponto a ordem tem ao menos 1 item ... tem valor 
      $ordem->forma_pagamento = esc($formaPagamento->nome);

      // Desclaro 2 variaveis para receber os valores
      $valorProdutos = null;
      $valorServicos = null;

      // Receberá o push dos itens produto para baixa de estoque
      $produtos = [];

      foreach ($ordem->itens as $item) {
         
         if ($item->tipo === 'produto') {
            
            $valorProdutos += $item->preco_venda * $item->item_quantidade;

            if ($item->controla_estoque == true) {
               
               array_push($produtos, [
                  'id' => $item->id,
                  'quantidade' => (int) $item->item_quantidade,
               ]);
            }
         }else{

            $valorServicos += $item->preco_venda * $item->item_quantidade;
         }
      }

      if (! empty($produtos)) {
         
         $ordem->produtos = $produtos;
      }

      $ordem->valor_produtos = str_replace(',', '', number_format($valorProdutos,2));
      $ordem->valor_servicos = str_replace(',', '', number_format($valorServicos,2));


      if ($formaPagamento->id === "1") {
         
         $valor = $valorProdutos + $valorServicos;

         $porcentagem = (int) env('gerenciaNetDesconto') / 100;

         // Sobrescrevemos o valor de desconto, caso exista para a forma de pagamento em Boleto
         $ordem->valor_desconto = number_format(($valor * ($porcentagem / 100)),2);
      }

      $valorOrdem = number_format((($valorProdutos + $valorServicos) - $ordem->valor_desconto),2);

      $ordem->valor_ordem = str_replace(',', '', $valorOrdem);

      //echo '<pre>';
      //print_r($ordem);      
      //exit;

      // Serializamos os itens da ordem
      $ordem->itens = serialize($ordem->itens);

      // Retorno o objeto $ordem totalmente pronto para encerrar
      return $ordem;
   }

   /**
    * Método responsável por realizar a baixa no estoque de produtos quando necessário.
    *
    * @param object $ordem
    * @return void
    */
   public function gerenciaEstoqueProduto(object $ordem)
   {
      $produtos = [];

      $ordem->itens = unserialize($ordem->itens);

      foreach ($ordem->itens as $item) {         
         if ($item->tipo === 'produto') {            
            if ($item->controla_estoque == true) {               
               array_push($produtos, [
                  'id' => $item->id,
                  'quantidade' => (int) $item->item_quantidade,
               ]);
            }
         }
      }

      if (! empty($produtos)) {         
         $itemModel = new \App\Models\ItemModel();
         $itemModel->realizaBaixaNoEstoqueDeProdutos($produtos);
      }

   }

}
