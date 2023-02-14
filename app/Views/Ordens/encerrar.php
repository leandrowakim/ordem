<?php echo $this->extend('Layout/principal'); ?>

<?php echo $this->section('titulo'); ?> <?php echo $titulo; ?> <?php echo $this->endSection(); ?>

<?php echo $this->section('estilos'); ?>

<?php echo $this->endSection(); ?>

<?php echo $this->section('conteudo'); ?>

<div class="row">

   <div class='col-lg-12'>
      <div class="block">

         <div class="user-block text-center">

            <div id="accordion">
               <div class="card">
                  <div class="card-header" id="headingOne">
                     <h5 class="mb-0 text-left">
                        <button class="btn btn-link" data-toggle="collapse" data-target="#collapseOne"
                           aria-expanded="true" aria-controls="collapseOne">
                           Detalhes da OS
                        </button>
                     </h5>
                  </div>

                  <div id="collapseOne" class="collapse" aria-labelledby="headingOne" data-parent="#accordion">
                     <div class="card-body">

                        <div class="user-title mb-2">
                           <h5 class="card-title mt=2">Cliente: <?php echo esc($ordem->nome); ?></h5>
                        </div>

                        <p class="contributions"><?php echo $ordem->exibeSituacao(); ?></p>
                        <p class="contributions">Aberta por: <?php echo esc($ordem->usuario_abertura); ?></p>
                        <p class="contributions">Técnico responsável:
                           <?php echo esc($ordem->usuario_responsavel !== null ? $ordem->usuario_responsavel : 'Não definido'); ?>
                        </p>

                        <?php if($ordem->situacao === 'encerrada') :?>
                           <p class="contributions">Encerrada por: <?php echo esc($ordem->usuario_encerramento); ?></p>
                        <?php endif; ?>

                        <p class="card-text">Criado à <?php echo esc($ordem->criado_em->humanize()); ?> e Atualizado à <?php echo esc($ordem->atualizado_em->humanize()); ?></p>

                        <hr class="border-secondary">

                        <?php if($ordem->itens === null) :?>
                           <div class="contributions py-3">

                              <p>Nenhum item foi adicionado</p>

                              <?php if($ordem->situacao === 'aberta') :?>
                              <a class="btn btn-outline-info btn-sm"
                                 href="<?php echo site_url("ordensitens/itens/$ordem->codigo") ?>">Adicionar item</a>
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
                                       <th scope="col" class="text-center">Remover</th>
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

                                          $hiddenAcoes = [
                                             'id_principal' => $item->id_principal,
                                             'item_id' => $item->id,
                                          ];
                                       ?>

                                       <tr>                     
                                          <th scope="row"><?php echo ellipsize($item->nome, 32, .5); ?></th>
                                          <td class="text-center"><?php echo esc(ucfirst($item->tipo)); ?></td>
                                          <td class="text-right">R$ <?php echo esc(number_format($item->preco_venda, 2)); ?></td>
                                          <td>
                                             <?php echo form_open("ordensitens/atualizarquantidade/$ordem->codigo", ['class' => 'form-inline'], $hiddenAcoes); ?>
                                             <input style="max-width: 80px !important" type="number" name="item_quantidade" class="form-control form-control-sm mx-auto" value="<?php echo $item->item_quantidade; ?>" require>
                                             
                                             <button type="submit" class="btn btn-outline-success btn-sm ml-2">
                                                <i class="fa fa-refresh"></i>
                                             </button>

                                             <?php echo form_close(); ?>
                                          </td>
                                          <td class="text-right">R$ <?php echo esc(number_format($item->item_quantidade * $item->preco_venda, 2)); ?></td>
                                          <td>
                                             <?php
                                                $atributos = [
                                                   'class'   => 'form-inline',
                                                   'onClick' => 'return confirm("Tem certeza da exclusão?")',
                                                ];                              
                                             ?>
                                             <?php echo form_open("ordensitens/removeritem/$ordem->codigo", $atributos, $hiddenAcoes) ?>                           
                                                <button type="submit" class="btn btn-outline-danger btn-sm ml-2 mx-auto">
                                                   <i class="fa fa-times"></i>
                                                </button>
                                             <?php echo form_close(); ?>
                                          </td>
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
                                          <!-- Button trigger modal -->
                                          <button type="button" class="btn btn-outline-info btn-sm" 
                                             data-toggle="modal"
                                             data-target="#exampleModalCenter"
                                             data-placement="top"
                                             title="Gerenciar desconto">
                                             Valor do desconto
                                          </button>

                                          <!-- <label>%nbsp;Valor do desconto:</label> -->
                                       </td>
                                    <td class="text-right font-weight-bold">R$ <?php echo esc(number_format($ordem->valor_desconto, 2)); ?></td>
                                    </tr>                                     
                                    <tr>
                                       <td class="text-right font-weight-bold" colspan="4">
                                          <label>Valor total:</label>
                                       </td>
                                       <td class="text-right font-weight-bold">R$ <?php echo esc(number_format(($valorProdutos + $valorServicos) - $ordem->valor_desconto, 2)); ?></td>
                                    </tr>
                                 </tfoot>

                              </table>

                              <!-- <div class="float-right mt-2">
                                 <div class="card">
                                    <div class="card-body">
                                       <button type="button" class="btn btn-outline-info btn-sm" data-toggle="modal"
                                          data-target="#exampleModalCenter">
                                          Gerenciar desconto
                                       </button>
                                    </div>
                                 </div>
                              </div> -->

                           </div>                           

                        <?php endif; ?>
                     </div>
                  </div>
               </div>

               <div class="card text-left">
                  <div class="card-header" id="headingTwo">
                     <h5 class="mb-0">
                        <button class="btn btn-link collapsed" data-toggle="collapse" data-target="#collapseTwo"
                           aria-expanded="false" aria-controls="collapseTwo">
                           Escolha a forma <br class="d-sm-none"> de pagamento para <br class="d-sm-none"> encerrar a Ordem
                        </button>
                     </h5>
                  </div>
                  <div id="collapseTwo" class="collapse show" aria-labelledby="headingTwo" data-parent="#accordion">
                     <div class="card-body">
                        <!-- Exibirá os retornos do backend -->
                        <div id="response">

                        </div>
                        <div class="block-body">

                           <?php echo form_open('/', ['id' => 'formEncerramento'], ['codigo' => $ordem->codigo]); ?>

                           <div class="form-row">
                              <div class="form-group col-md-6">
                                 <label class="form-control-label">Forma de pagamento</label>
                                 <select name="forma_pagamento_id" class="custom-select">
                                    <option value="">Escolha a forma...</option>

                                    <?php foreach($formasPagamentos as $forma): ?>
                                       <?php 
                                          $textoDesconto = (isset($descontoBoleto) && $forma->id == 1 ? "-&nbsp;(Desconto de $descontoBoleto)" : "");
                                       ?>
                                       <option value="<?php echo $forma->id; ?>"><?php echo $forma->nome; ?>&nbsp;<?php echo $textoDesconto; ?></option>
                                    <?php endforeach; ?>

                                 </select>
                              </div>
                           </div>

                           <div class="form-row">
                              <div id="boleto" class="form-group col-md-2 d-none">
                                 <label class="form-control-label">Data de vencimento do boleto</label>
                                 <input type="date" name="data_vencimento" class="form-control">
                              </div>
                           </div>

                           
                           <div class="form-group mt-5">
                              <input id="btn-encerramento" type="submit" value="Processar encerramento" class="btn btn-outline-success">
                           </div>
                           

                           <?php echo form_close(); ?>

                        </div>

                     </div>
                  </div>
               </div>
            </div>

         </div>

         <!-- Example single danger button -->
         <div class="btn-group">
            <button type="button" class="btn btn-danger btn-sm dropdown-toggle" data-toggle="dropdown"
               aria-haspopup="true" aria-expanded="false">
               Ações
            </button>
            <div class="dropdown-menu">

               <?php if ($ordem->situacao === 'aberta'): ?>

               <a class="dropdown-item" href="<?php echo site_url("ordens/editar/$ordem->codigo"); ?>">Editar OS</a>

               <?php endif; ?>

               <a class="dropdown-item"
                  href="<?php echo site_url("ordensevidencias/evidencias/$ordem->codigo"); ?>">Evidências da OS</a>

               <a class="dropdown-item" id="btn-enviar-email"
                  href="<?php echo site_url("ordens/email/$ordem->codigo"); ?>">Enviar por e-mail</a>

               <a target="_blank" class="dropdown-item" href="<?php echo site_url("ordens/gerarpdf/$ordem->codigo"); ?>">Gerar PDF</a>

               <div class="dropdown-divider"></div>

               <?php if ($ordem->deletado_em == null): ?>

               <a class="dropdown-item" href="<?php echo site_url("ordens/excluir/$ordem->codigo"); ?>">Excluir OS</a>
               <?php else: ?>

               <a class="dropdown-item" href="<?php echo site_url("ordens/recuperar/$ordem->codigo"); ?>">Recuperar
                  OS</a>
               <?php endif; ?>
            </div>
         </div>

         <a href="<?php echo site_url("ordens/detalhes/$ordem->codigo"); ?>" class="btn btn-secondary btn-sm ml-2">Voltar</a>
      </div>
   </div>
</div>

<!-- Modal -->
<div class="modal fade" id="exampleModalCenter" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle"
   aria-hidden="true">
   <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content">
         <div class="modal-header">
            <h5 class="modal-title" id="exampleModalLongTitle">Gerenciar desconto da OS</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
               <span aria-hidden="true">&times;</span>
            </button>
         </div>
         <div class="modal-body">

            <div id="response"></div>

            <div class="block-body">

               <?php echo form_open('/', ['id' => 'formDesconto'], ['codigo' => $ordem->codigo]); ?>

               <div class="form-row">

                  <div class="form-group col-md-12">
                     <label class="form-control-label">Valor do desconto (opcional)</label>
                     <?php $desconto = ($ordem->valor_desconto !== null ? number_format($ordem->valor_desconto,2) : ''); ?>
                     <input type="text" name="valor_desconto" class="form-control text-right money" value="<?php echo $desconto ?>" placeholder="0.00">
                  </div>

               </div>

               <div class="form-group">
                  <input id="btn-desconto" type="submit" value="Salvar desconto" class="btn btn-outline-success btn-block">
               </div>

               <?php echo form_close(); ?>

            </div>
         </div>
      </div>
   </div>
</div>

<?php echo $this->endSection(); ?>

<?php echo $this->section('scripts'); ?>

<script src="<?php echo site_url('recursos/vendor/mask/jquery.mask.min.js'); ?>"></script>
<script src="<?php echo site_url('recursos/vendor/mask/app.js'); ?>"></script>

<script src="<?=site_url('recursos/vendor/loadingoverlay/loadingoverlay.min.js'); ?>"></script>

<script>
$(document).ready(function() {
   $("#btn-enviar-email").on('click', function() {
      $.LoadingOverlay("show", {
         image: "",
         text: "Enviando e-mail...",
      });
   });

   $(function () {
      $('[data-toggle="tooltip"]').tooltip()
   })

   $("#formDesconto").on('submit', function(e) {
      e.preventDefault();
      $.ajax({
         type: 'POST',
         url: '<?=site_url('ordens/atualizardesconto'); ?>',
         data: new FormData(this),
         dataType: 'json',
         contentType: false,
         cache: false,
         processData: false,
         beforeSend: function() {
            $('#response').html('');
            $('#btn-desconto').val('Aguarde...');
         },
         success: function(response) {
            $('#btn-desconto').val('Salvar');
            $('#btn-desconto').removeAttr("disabled");
            $('[name=csrf_ordem]').val(response.token);
            if (!response.erro) {
               if (response.info) {
                  $('#response').html('<div class="alert alert-info">' + response.info + '</div>');
               } else {
                  //Tudo certo com a atualização
                  window.location.href =
                     "<?php echo site_url("ordens/encerrar/$ordem->codigo"); ?>";
               }
            } else {
               //Erros de validação
               $('#response').html('<div class="alert alert-danger">' + response.erro + '</div>');
               if (response.erros_model) {
                  $.each(response.erros_model, function(key, value) {
                     $('#response').append(
                        '<ul class="list-unstyled"><li class="text-danger">' + value +
                        '</li></ul>');
                  });
               }
            }
         },
         error: function() {
            alert(
               'Não foi possível processar solicitação. Por favor entre em contato com o suporte!'
            );
            $('#btn-desconto').val('Salvar');
            $('#btn-desconto').removeAttr("disabled");
         },
      });
   });

   $("[name=forma_pagamento_id]").on('change', function(){
      var forma_pagamento_id = parseInt($(this).val());
      if (forma_pagamento_id === 1) {

         $("#boleto").removeClass('d-none');
         $("[name=data_vencimento]").prop('disabled', false);
      } else {

         $("#boleto").addClass('d-none');
         $("[name=data_vencimento]").prop('disabled', true);
      }
   });

   $("#formEncerramento").on('submit', function(e) {
      e.preventDefault();
      $.ajax({
         type: 'POST',
         url: '<?=site_url('ordens/processaencerramento'); ?>',
         data: new FormData(this),
         dataType: 'json',
         contentType: false,
         cache: false,
         processData: false,
         beforeSend: function() {
            $(".block").LoadingOverlay("show", {
               image: "",
               text: "Processando o encerramento...",
            });

            $('#response').html('');
            $('#btn-encerramento').val('Aguarde...');
         },
         success: function(response) {
            $(".block").LoadingOverlay("hide", true);

            $('#btn-encerramento').val('Processar encerramento');
            $('#btn-encerramento').removeAttr("disabled");
            $('[name=csrf_ordem]').val(response.token);
            if (!response.erro) {
               if (response.info) {
                  $('#response').html('<div class="alert alert-info">' + response.info + '</div>');
               } else {
                  //Tudo certo com a atualização
                  window.location.href =
                     "<?php echo site_url("ordens/detalhes/$ordem->codigo"); ?>";
               }
            } else {
               //Erros de validação
               $('#response').html('<div class="alert alert-danger">' + response.erro + '</div>');
               if (response.erros_model) {
                  $.each(response.erros_model, function(key, value) {
                     $('#response').append(
                        '<ul class="list-unstyled"><li class="text-danger">' + value +
                        '</li></ul>');
                  });
               }
            }
         },
         error: function() {
            $(".block").LoadingOverlay("hide", true);
            
            alert(
               'Não foi possível processar solicitação. Por favor entre em contato com o suporte!'
            );
            $('#btn-encerramento').val('Processar encerramento');
            $('#btn-encerramento').removeAttr("disabled");
         },
      });
   });

   $("#formDesconto").submit(function() {
      $(this).find(":submit").attr('disabled', 'disabled');
   });

   $("#formEncerramento").submit(function() {
      $(this).find(":submit").attr('disabled', 'disabled');
   });

});

</script>

<?php echo $this->endSection(); ?>