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
               <a class="nav-link active" id="pills-home-tab" data-toggle="pill" href="#pills-home" role="tab"
                  aria-controls="pills-home" aria-selected="true">Detalhes da OS</a>
            </li>
            <?php if(isset($ordem->evidencias)): ?>
               
               <li class="nav-item">
                  <a class="nav-link" id="pills-profile-tab" data-toggle="pill" href="#pills-profile" role="tab"
                     aria-controls="pills-profile" aria-selected="false">Evidências da OS</a>
               </li>
            <?php endif; ?>
         </ul>
         <div class="tab-content" id="pills-tabContent">
            <div class="tab-pane fade show active" id="pills-home" role="tabpanel" aria-labelledby="pills-home-tab">

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
            <?php if(isset($ordem->evidencias)): ?>
               
               <div class="tab-pane fade" id="pills-profile" role="tabpanel" aria-labelledby="pills-profile-tab">
               
                  <div class="col-md-12">

                     <?php if(empty($ordem->evidencias)): ?>

                     <div class="user-block">
                        <div class="contributions text-warning">
                           Essa ordem não possui evidências
                        </div>
                     </div>

                     <?php else: ?>

                     <div class="col-lg-12">
                        <ul class="list-inline">
                           <?php foreach($ordem->evidencias as $evidencia): ?>
                           <li class="list-inline-item">
                              <div class="card" style="width: 8rem">

                                 <?php if($ordem->ehUmaImagem($evidencia->evidencia)): ?>

                                 <a data-toogle="tooltip" data-placemen="top" target="_blank" title="Exibir Imagem"
                                    href="<?php echo site_url("ordensevidencias/arquivo/$evidencia->evidencia") ?>"
                                    class="btn btn-outline-danger mt-0"><img width="42"
                                       src="<?php echo site_url("ordensevidencias/arquivo/$evidencia->evidencia"); ?>"
                                       alt="<?php $ordem->codigo; ?>">
                                 </a>

                                 <?php else: ?>

                                 <a data-toogle="tooltip" data-placemen="top" target="_blank" title="Exibir PDF"
                                    href="<?php echo site_url("ordensevidencias/arquivo/$evidencia->evidencia") ?>"
                                    class="btn btn-outline-danger py-3">PDF
                                 </a>

                                 <?php endif; ?>

                              </div>
                           </li>
                           <?php endforeach; ?>
                        </ul>
                     </div>

                     <?php endif; ?>                  

                  </div>
               
               </div>
            <?php endif; ?>
         </div>

         <a href="<?php echo site_url("ordens/minhas"); ?>" class="btn btn-secondary btn-sm mt-3 ml-2">Voltar</a>
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
   });
</script>

<?php echo $this->endSection(); ?>
