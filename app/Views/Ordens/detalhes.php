<?php echo $this->extend('Layout/principal'); ?>

<?php echo $this->section('titulo'); ?> <?php echo $titulo; ?> <?php echo $this->endSection(); ?>

<?php echo $this->section('estilos'); ?>

<?php echo $this->endSection(); ?>

<?php echo $this->section('conteudo'); ?>

<div class="row">

   <div class='col-lg-12'>
      <div class="block">

         <ul class="nav nav-pills mb-3" id="pills-tab" role="tablist">
            <li class="nav-item">
               <a class="nav-link <?php echo (session()->has('transacao') ? '' : 'active') ?>" id="pills-home-tab" data-toggle="pill" href="#pills-home" role="tab"
                  aria-controls="pills-home" aria-selected="true">Detalhes da OS</a>
            </li>
            <?php if(isset($ordem->transacao)): ?>
               
               <li class="nav-item">
                  <a class="nav-link <?php echo (session()->has('transacao') ? 'active' : '') ?>" id="pills-profile-tab" data-toggle="pill" href="#pills-profile" role="tab"
                     aria-controls="pills-profile" aria-selected="false">Transações da OS</a>
               </li>
            <?php endif; ?>
         </ul>
         <div class="tab-content mb-3" id="pills-tabContent">
            <div class="tab-pane fade <?php echo (session()->has('transacao') ? '' : 'show active') ?>" id="pills-home" role="tabpanel" aria-labelledby="pills-home-tab">

               <div class="user-block text-center">

                  <div class="user-title mb-2">

                     <h5 class="card-title mt=2"><?php echo esc($ordem->nome); ?></h5>
                     <span>OS: <?php echo esc($ordem->codigo) ;?></span>
                  </div>

                  <p class="contributions"><?php echo $ordem->exibeSituacao(); ?></p>
                  <p class="contributions">Aberta por: <?php echo esc($ordem->usuario_abertura); ?></p>
                  <p class="contributions">Técnico responsável: <?php echo esc($ordem->usuario_responsavel !== null ? $ordem->usuario_responsavel : 'Não definido'); ?></p>

                  <?php if($ordem->situacao === 'encerrada') :?>
                     <p class="contributions">Encerrada por: <?php echo esc($ordem->usuario_encerramento); ?></p>
                  <?php endif; ?>
                  
                  <p class="card-text">Criado <?php echo esc($ordem->criado_em->humanize()); ?></p>
                  <p class="card-text">Atualizado <?php echo esc($ordem->atualizado_em->humanize()); ?></p>

                  <hr class="border-secondary">

                  <?php if($ordem->itens === null) :?>

                     <div class="contributions py-3">

                        <p>Nenhum item foi adicionado</p>

                        <?php if($ordem->situacao === 'aberta') :?>
                           <a class="btn btn-outline-info btn-sm" href="<?php echo site_url("ordensitens/itens/$ordem->codigo") ?>">Adicionar item</a>
                        <?php endif; ?>                        
                     </div>
                  <?php else: ?>

                     <div class="table-responsive my-4">

                        <table class="table table-bordered text-left">
                           <thead>
                              <tr>
                                 <th scope="col">Item</th>
                                 <th scope="col" class="text-center">Tipo</th>
                                 <th scope="col" class="text-center">Preço</th>
                                 <th scope="col" class="text-center">Qtde</th>
                                 <th scope="col" class="text-center">Subtotal</th>
                              </tr>
                           </thead>
                           <tbody>
                              <?php 
                                 $valorProdutos = 0;
                                 $valorServicos = 0;
                              ?>
                              <?php foreach($ordem->itens as $item): ?>

                                 <?php
                                    if ($item->tipo === 'produto') {
                                       $valorProdutos += $item->preco_venda * $item->item_quantidade;
                                    } else {
                                       $valorServicos += $item->preco_venda * $item->item_quantidade;
                                    }
                                 ?>

                                 <tr>                     
                                    <th scope="row"><?php echo ellipsize($item->nome, 32, .5); ?></th>
                                    <td class="text-center"><?php echo esc(ucfirst($item->tipo)); ?></td>
                                    <td class="text-right">R$ <?php echo esc(number_format($item->preco_venda, 2)); ?></td>
                                    <td class="text-right"><?php echo $item->item_quantidade; ?></td>
                                    <td class="text-right">R$ <?php echo esc(number_format($item->item_quantidade * $item->preco_venda, 2)); ?></td>
                                 </tr>
                              <?php endforeach; ?>
                           </tbody>

                           <tfoot>
                              <tr>
                                 <td class="text-right font-weight-bold" colspan="4">
                                    <label>Valor dos produtos:</label>
                                 </td>
                                 <td class="text-right font-weight-bold">R$ <?php echo esc(number_format($valorProdutos, 2)); ?></td>
                              </tr>
                              <tr>
                                 <td class="text-right font-weight-bold" colspan="4">
                                    <label>Valor dos serviços:</label>
                                 </td>
                                 <td class="text-right font-weight-bold">R$ <?php echo esc(number_format($valorServicos, 2)); ?></td>
                              </tr>
                              <tr>
                                 <td class="text-right font-weight-bold" colspan="4">
                                    <label>Valor total sem desconto:</label>
                                 </td>
                                 <td class="text-right font-weight-bold">R$ <?php echo esc(number_format($valorProdutos + $valorServicos, 2)); ?></td>
                              </tr>
                              <tr>
                                 <td class="text-right font-weight-bold" colspan="4">
                                    <label>Valor do desconto:</label>
                                 </td>
                                 <td class="text-right font-weight-bold">R$ <?php echo esc(number_format($ordem->valor_desconto, 2)); ?></td>
                              </tr>                              
                              <tr>
                                 <td class="text-right font-weight-bold" colspan="4">
                                    <label>Valor total da OS:</label>
                                 </td>
                                 <td class="text-right font-weight-bold">R$ <?php echo esc(number_format(($valorProdutos + $valorServicos) - $ordem->valor_desconto, 2)); ?></td>
                              </tr>
                           </tfoot>

                        </table>

                     </div>

                  <?php endif; ?>

               </div>

            </div>
            <?php if(isset($ordem->transacao)): ?>
               
               <div class="tab-pane fade <?php echo (session()->has('transacao') ? 'show active' : '') ?>" id="pills-profile" role="tabpanel" aria-labelledby="pills-profile-tab">
               
                  <div class="row">

                     <div class="col-sm-4">
                        <div class="card border-secondary">
                           <div class="card-body">
                              <h5 class="card-title">
                                 Alterar data de vencimento
                              </h5>
                              <p class="card-text">
                                 Possibilita alterar a data de vecimento de uma transação com boleto bancário que ainda não foi pago.
                              </p>
                              <a href="<?php echo site_url("transacoes/editar/$ordem->codigo"); ?>" class="btn btn-dark btn-sm text-secondary">Alterar data de vecimento</a>
                           </div>
                        </div>
                     </div>

                     <div class="col-sm-4">
                        <div class="card border-secondary">
                           <div class="card-body">
                              <h5 class="card-title">
                                 Cancelar boleto
                              </h5>
                              <p class="card-text">
                                 Possibilita efetuar o cancelamento do boleto bancário se a OS estiver com o status [Aguardando] ou [Não paga].
                              </p>
                              <a href="<?php echo site_url("transacoes/cancelar/$ordem->codigo"); ?>" class="btn-get-gerencianet btn btn-dark btn-sm text-secondary">Cancelar boleto</a>
                           </div>
                        </div>
                     </div>

                     <div class="col-sm-4">
                        <div class="card border-secondary">
                           <div class="card-body">
                              <h5 class="card-title">
                                 Reenviar boleto
                              </h5>
                              <p class="card-text">
                                 Possibilita o reenvio do boleto bancário se a OS estiver com o status [Aguardando] ou [Não paga].
                              </p>
                              <a href="<?php echo site_url("transacoes/reenviar/$ordem->codigo"); ?>" class="btn-get-gerencianet btn btn-dark btn-sm text-secondary">Reenviar boleto</a>
                           </div>
                        </div>
                     </div>

                     <div class="col-sm-4">
                        <div class="card border-secondary">
                           <div class="card-body">
                              <h5 class="card-title">
                                 Histórico da transação
                              </h5>
                              <p class="card-text">
                                 Possibilita consultar todos os eventos gerados. 
                              </p>
                              <a href="<?php echo site_url("transacoes/consultar/$ordem->codigo"); ?>" class="btn-get-gerencianet btn btn-dark btn-sm text-secondary">Consultar transação</a>
                           </div>
                        </div>
                     </div>

                     <div class="col-sm-4">
                        <div class="card border-secondary">
                           <div class="card-body">
                              <h5 class="card-title">
                                 Marcar boleto como pago
                              </h5>
                              <p class="card-text">
                                 Permite marcar como pago (baixa manual) uma determinada transação / boleto.
                              </p>
                              <a href="<?php echo site_url("transacoes/pagar/$ordem->codigo"); ?>" class="btn-get-gerencianet btn btn-dark btn-sm text-secondary">Marcar como pago</a>
                           </div>
                        </div>
                     </div>

                  </div>

               </div>
            <?php endif; ?>
         </div>

         <!-- Example single danger button -->
         <div class="btn-group">
            <button type="button" class="btn btn-danger btn-sm dropdown-toggle" data-toggle="dropdown" aria-haspopup="true"
               aria-expanded="false">
               Ações
            </button>
            <div class="dropdown-menu">

               <?php if ($ordem->situacao === 'aberta'): ?>
                  
                  <a class="dropdown-item" href="<?php echo site_url("ordens/editar/$ordem->codigo"); ?>">Editar OS</a>

                  <a class="dropdown-item" href="<?php echo site_url("ordens/encerrar/$ordem->codigo"); ?>">Encerrar OS</a>

                  <a class="dropdown-item" href="<?php echo site_url("ordensitens/itens/$ordem->codigo"); ?>">Gerenciar itens da OS</a>

                  <a class="dropdown-item" href="<?php echo site_url("ordens/responsavel/$ordem->codigo"); ?>">Definir técnico responsável</a>
               <?php endif; ?>

               <a class="dropdown-item" href="<?php echo site_url("ordensevidencias/evidencias/$ordem->codigo"); ?>">Evidências da OS</a>

               <a class="dropdown-item" id="btn-enviar-email" href="<?php echo site_url("ordens/email/$ordem->codigo"); ?>">Enviar por e-mail</a>

               <a target="_blank" class="dropdown-item" href="<?php echo site_url("ordens/gerarpdf/$ordem->codigo"); ?>">Gerar PDF</a>

               <div class="dropdown-divider"></div>

               <?php if ($ordem->deletado_em == null): ?>
                  
                  <a class="dropdown-item" href="<?php echo site_url("ordens/excluir/$ordem->codigo"); ?>">Excluir OS</a>
               <?php else: ?>
                  
                  <a class="dropdown-item" href="<?php echo site_url("ordens/recuperar/$ordem->codigo"); ?>">Recuperar OS</a>
               <?php endif; ?>
            </div>
         </div>

         <a href="<?php echo site_url("ordens"); ?>" class="btn btn-secondary btn-sm ml-2">Voltar</a>
      </div>
   </div>
</div>

<?php echo $this->endSection(); ?>

<?php echo $this->section('scripts'); ?>

<script src="<?=site_url('recursos/vendor/loadingoverlay/loadingoverlay.min.js'); ?>"></script>

<script>
   $(document).ready(function() {
      $("#btn-enviar-email").on('click', function() {
         $.LoadingOverlay("show", {
            image: "",
            text: "Enviando e-mail...",
         });
      });

      $(".btn-get-gerencianet").on('click', function() {
         $(".block").LoadingOverlay("show", {
            image: "",
            text: "Processando...",
         });
      });

   });   
</script>

<?php echo $this->endSection(); ?>
