<?php

namespace App\Transacao\Gerencianet;

use Gerencianet\Exception\GerencianetException;
use Gerencianet\Gerencianet;
use App\Traits\OrdemTrait;

class Operacoes
{
   use OrdemTrait;

   private $options = [];
   private $gerenciaNetDesconto;
   private $ordem;
   private $formaPagamento;

   private $ordemModel;
   private $ordemResponsavelModel;
   private $transacaoModel;
   private $eventoModel;

   public function __construct(object $ordem = null, object $formaPagamento = null)
   {
      $this->options = [
         'client_id'     => env('gerenciaNetClientId'),
         'client_secret' => env('gerenciaNetClientSecret'),
         'sandbox'       => env('gerenciaNetSandbox'),
         'timeout'       => 60, // essa linha para evitar exception por timeout quando formos registrar o Boleto na Gerencianet
      ];

      $this->gerenciaNetDesconto = (int) env('gerenciaNetDesconto'); // 700 = 7% de desconto

      $this->ordem = $ordem;
      $this->formaPagamento = $formaPagamento;

      $this->ordemModel = new \App\Models\OrdemModel();
      $this->ordemResponsavelModel = new \App\Models\OrdemResponsavelModel();
      $this->transacaoModel = new \App\Models\TransacaoModel();
      $this->eventoModel = new \App\Models\EventoModel();
   }

   public function registraBoleto()
   {
      foreach ($this->ordem->itens as $item) {
         $itemBoleto = [
            'name'   => $item->nome,
            'amount' => (int) $item->item_quantidade,
            'value'  => (int) str_replace([',','.'], '', $item->preco_venda),
         ];

         $items[] = $itemBoleto;
      }

      /**
       * $todo mudar para URL do servidor de hospedagem ... localhost não funciona
       */
      // Servidor temporário => https://hookbin.com/7ZmKe2NxNGiWXDmW3jao
      $urlNotificacoes = "https://hookb.in/7ZmKe2NxNGiWXDmW3jao";
      $metadata = array('notification_url' => $urlNotificacoes);

      $customer = [
         'name' => $this->ordem->nome, // nome do cliente
         'cpf' => str_replace(['.','-'], '', $this->ordem->cpf), // cpf válido do cliente
         'phone_number' => str_replace(['(',')', ' ', '-'], '', $this->ordem->telefone), // telefone do cliente
         'email' => $this->ordem->email, // email do cliente
      ];

      $discount = [ // configuração de descontos
         'type'  => 'percentage', // tipo de desconto a ser aplicado
         'value' => $this->gerenciaNetDesconto, // valor de desconto 
      ];

      //$conditional_discount = [ // configurações de desconto condicional
      //   'type' => 'percentage', // seleção do tipo de desconto 
      //   'value' => 500, // porcentagem de desconto
      //   'until_date' => '2019-08-30' // data máxima para aplicação do desconto
      //];

      $configurations = [   // configurações de juros e mora
         'fine'     => 200, // porcentagem de multa
         'interest' => 33   // porcentagem de juros
      ];     

      $bankingBillet = [
         'expire_at' => $this->ordem->data_vencimento, // data de vencimento do titulo
         'message' => "Boleto referente a OS: " . $this->ordem->codigo, // mensagem a ser exibida no boleto
         'customer' => $customer,
         'discount' => $discount,
         //'conditional_discount' => $conditional_discount
      ];

     $payment = [
         'banking_billet' => $bankingBillet // forma de pagamento (banking_billet = boleto)
     ];

     $body = [
         'items' => $items,
         'metadata' =>$metadata,
         'payment' => $payment
     ];

     try {
         $api = new Gerencianet($this->options);
         $pay_charge = $api->createOneStepCharge([],$body);

         if (isset($pay_charge['error'])) {
            
            $this->ordem->erro_transacao = $pay_charge['error_Description'];

            return $this->ordem;
         }
         // Nesse ponto deu tudo certo na gerarção do boleto
         // Transformamos o array $pay_charge em um objeto
         $objetoRetorno = json_decode(json_encode($pay_charge));

         $this->preparaOrdemParaEncerrar($this->ordem, $this->formaPagamento);

         // Salva a Ordem de Serviço
         $this->ordemModel->save($this->ordem);

         // Criamos o objeto Transacao(Entidade)
         $transacao = new \App\Entities\Transacao();

         $transacao->ordem_id = $this->ordem->id;
         $transacao->charge_id = $objetoRetorno->data->charge_id;
         $transacao->barcode = $objetoRetorno->data->barcode;
         $transacao->link = $objetoRetorno->data->link;
         $transacao->pdf = $objetoRetorno->data->pdf->charge;
         $transacao->expire_at = $objetoRetorno->data->expire_at;
         $transacao->status = $objetoRetorno->data->status;
         $transacao->total = $objetoRetorno->data->total / 100; // Dessa forma, pois o retorno vem como inteiro
         // Salva a transação 
         $this->transacaoModel->save($transacao);

         // Crio o atributo transacao, pois precisarei no métido processaEncerramento
         $this->ordem->transacao = $transacao;

         $tituloEvento = "Boleto para OS: " . $this->ordem->codigo . " - Cliente: " . $this->ordem->nome . " - Valor R$ " . number_format($transacao->total,2);
         $dias = $this->ordem->defineDataVencimentoEvento($objetoRetorno->data->expire_at);
         //Salva o evento
         $this->eventoModel->cadastraEvento('ordem_id', $tituloEvento, $this->ordem->id, $dias);

         return $this->ordem;

         // echo '<pre>';
         // print_r($pay_charge);
         // echo '<pre>';       
         // exit;
      } catch (GerencianetException $e) {
         // print_r($e->code);
         // print_r($e->error);
         // print_r($e->errorDescription);
         $this->ordem->erro_transacao = "Erro: " . $e->code . "-" . $e->errorDescription;
         return $this->ordem;
      } catch (\Exception $e) {
         // print_r($e->getMessage());
         $this->ordem->erro_transacao = $e->getMessage();
         return $this->ordem;         
      }
   }

   public function alteraVencimentoTransacao()
   {
      // $charge_id refere-se ao ID da transação gerada anteriormente
      $params = [
         'id' => $this->ordem->transacao->charge_id
      ];
      
      $body = [
         'expire_at' => $this->ordem->transacao->expire_at //'2020-12-20'
      ];
      
      try {
         $api = new Gerencianet($this->options);
         $charge = $api->updateBillet($params, $body);

         if ($charge['code'] != 200)  {
            
            $this->ordem->erro_transacao = $charge['error_Description'];
            return $this->ordem;
         }

         $dias = $this->ordem->defineDataVencimentoEvento($this->ordem->transacao->expire_at);
         
         $this->transacaoModel->save($this->ordem->transacao);
         
         $this->eventoModel->atualizaEvento('ordem_id', $this->ordem->id, $dias);
         
         $this->marcaOrdemComoAtualizada();
         
         return $this->ordem;
      } catch (GerencianetException $e) {
         // print_r($e->code);
         // print_r($e->error);
         // print_r($e->errorDescription);
         $this->ordem->erro_transacao = "Erro: " . $e->code . "-" . $e->errorDescription;
         return $this->ordem;
      } catch (\Exception $e) {
         // print_r($e->getMessage());
         $this->ordem->erro_transacao = $e->getMessage();
         return $this->ordem;         
      }
   }

   public function cancelarTransacao()
   {
      // $charge_id refere-se ao ID da transação gerada anteriormente
      $params = [
         'id' => $this->ordem->transacao->charge_id
      ];
      
      try {
         $api = new Gerencianet($this->options);
         $charge = $api->cancelCharge($params, []);

         if ($charge['code'] != 200)  {
            
            $this->ordem->erro_transacao = $charge['error_Description'];
            return $this->ordem;
         }

         $this->ordem->transacao->status = 'canceled';

         $this->transacaoModel->save($this->ordem->transacao);
         
         $this->ordem->situacao = 'cancelada';
         $this->ordemModel->save($this->ordem);

         $this->eventoModel->where('ordem_id', $this->ordem->id)->delete();
         
         return $this->ordem;         
      } catch (GerencianetException $e) {
         // print_r($e->code);
         // print_r($e->error);
         // print_r($e->errorDescription);
         $this->ordem->erro_transacao = "Erro: " . $e->code . "-" . $e->errorDescription;
         return $this->ordem;
      } catch (\Exception $e) {
         // print_r($e->getMessage());
         $this->ordem->erro_transacao = $e->getMessage();
         return $this->ordem;         
      }
   }

   public function reenviarBoleto()
   {
      // $charge_id refere-se ao ID da transação gerada anteriormente
      $params = [
         'id' => $this->ordem->transacao->charge_id
      ];
      
      $body = [
         'email' => $this->ordem->email
      ];
      
      try {
         $api = new Gerencianet($this->options);
         $charge = $api->sendBilletEmail($params, $body);

         if ($charge['code'] != 200)  {
            
            $this->ordem->erro_transacao = $charge['error_Description'];
            return $this->ordem;
         }
         
         return $this->ordem;
      } catch (GerencianetException $e) {
         // print_r($e->code);
         // print_r($e->error);
         // print_r($e->errorDescription);
         $this->ordem->erro_transacao = "Erro: " . $e->code . "-" . $e->errorDescription;
         return $this->ordem;
      } catch (\Exception $e) {
         // print_r($e->getMessage());
         $this->ordem->erro_transacao = $e->getMessage();
         return $this->ordem;         
      }
   }

   public function consultarTransacao()
   {
      // $charge_id refere-se ao ID da transação gerada anteriormente
      $params = [
         'id' => $this->ordem->transacao->charge_id
      ];
      
      try {
         $api = new Gerencianet($this->options);
         $charge = $api->detailCharge($params);

         if ($charge['code'] != 200)  {
            
            $this->ordem->erro_transacao = $charge['error_Description'];
            return $this->ordem;
         }

         $this->ordem->historico = $charge['data']['history'];
         
         return $this->ordem;
      } catch (GerencianetException $e) {
         // print_r($e->code);
         // print_r($e->error);
         // print_r($e->errorDescription);
         $this->ordem->erro_transacao = "Erro: " . $e->code . "-" . $e->errorDescription;
         return $this->ordem;
      } catch (\Exception $e) {
         // print_r($e->getMessage());
         $this->ordem->erro_transacao = $e->getMessage();
         return $this->ordem;         
      }
   }

   public function marcarComoPaga()
   {
      // $charge_id refere-se ao ID da transação gerada anteriormente
      $params = [
         'id' => $this->ordem->transacao->charge_id
      ];
      
      try {
         $api = new Gerencianet($this->options);
         $charge = $api->settleCharge($params);

         if ($charge['code'] != 200)  {
            $this->ordem->erro_transacao = $charge['error_Description'];
            return $this->ordem;
         }

         $this->ordem->transacao->status = 'settled';
         $this->encerrarOrdemServico();
         
         return $this->ordem;
      } catch (GerencianetException $e) {
         // print_r($e->code);
         // print_r($e->error);
         // print_r($e->errorDescription);
         $this->ordem->erro_transacao = "Erro: " . $e->code . "-" . $e->errorDescription;
         return $this->ordem;
      } catch (\Exception $e) {
         // print_r($e->getMessage());
         $this->ordem->erro_transacao = $e->getMessage();
         return $this->ordem;         
      }
   }

   
   public function consultaNotificacao($tokenNotificacao)
   {
      $params = [
        'token' => $tokenNotificacao
      ];
       
      try {
         $api = new Gerencianet($this->options);
         $chargeNotification = $api->getNotification($params, []);
         // Para identificar o status atual da sua transação você deverá contar o número de situações contidas no array, pois a última posição guarda sempre o último status. Veja na um modelo de respostas na seção "Exemplos de respostas" abaixo.
        
         // Veja abaixo como acessar o ID e a String referente ao último status da transação.
              
         // Conta o tamanho do array data (que armazena o resultado)
         $i = count($chargeNotification["data"]);
         // Pega o último Object chargeStatus
         $ultimoStatus = $chargeNotification["data"][$i-1];
         // Acessando o array Status
         $status = $ultimoStatus["status"];
         // Obtendo o ID da transação        
         $charge_id = $ultimoStatus["identifiers"]["charge_id"];
         // Obtendo a String do status atual
         $statusAtual = $status["current"];
              
         // Com estas informações, você poderá consultar sua base de dados e atualizar o status da transação especifica, uma vez que você possui o "charge_id" e a String do STATUS
        
         $transacao = $this->transacaoModel->where('charge_id', $charge_id)->first();

         if ($transacao != null) {
            $transacao->status = $statusAtual;
            if ($transacao->hasChanged()) {
               
               echo "Transação alterada!";
               $this->ordem = $this->ordemModel->find($transacao->ordem_id);

               if ($this->ordem != null) {
                  
                  $this->ordem->transacao = $transacao;

                  if ($this->ordem->trasacao->status === 'canceled') {                     

                     $this->transacaoModel->save($this->ordem->transacao);
                     
                     $this->ordem->situacao = 'cancelada';
                     $this->ordemModel->save($this->ordem);
            
                     $this->eventoModel->where('ordem_id', $this->ordem->id)->delete();
                  }

                  if ($this->ordem->trasacao->status === 'paid' || $this->ordem->trasacao->status === 'settled') {
                     $this->encerrarOrdemServico();
                  }

                  if ($this->ordem->trasacao->status === 'unpaid') {
                     $this->transacaoModel->save($this->ordem->transacao);
                     
                     $this->ordem->situacao = 'nao_pago';
                     $this->ordemModel->save($this->ordem);
                  }

               }
            }
         }
         // echo '<hr>';
         // echo "O id da transação é: ".$charge_id." seu novo status é: ".$statusAtual;         

      } catch (GerencianetException $e) {
         // print_r($e->code);
         // print_r($e->error);
         // print_r($e->errorDescription);
         $this->ordem->erro_transacao = "Erro: " . $e->code . "-" . $e->errorDescription;
         return $this->ordem;         
      } catch (\Exception $e) {
         // print_r($e->getMessage());
         $this->ordem->erro_transacao = $e->getMessage();
      }      
   }

   // Métodos privados //
   private function marcaOrdemComoAtualizada()
   {
      unset($this->ordem->transacao);
      $this->ordem->atualizado_em = date('Y/m/d H:i:s');
      $this->ordemModel->protect(false)->save($this->ordem);
   }

   private function encerrarOrdemServico()
   {
      $this->transacaoModel->save($this->ordem->transacao);

      $this->ordem->situacao = "encerrada";
      $this->ordemModel->save($this->ordem);

      $this->ordemResponsavelModel->defineUsuarioEncerramento($this->ordem->id, usuario_logado()->id);

      $this->gerenciaEstoqueProduto($this->ordem);

      $this->eventoModel->where('ordem_id', $this->ordem->id)->delete();
   }

}