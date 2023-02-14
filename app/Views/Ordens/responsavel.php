<?php echo $this->extend('Layout/principal'); ?>

<?php echo $this->section('titulo'); ?> <?php echo $titulo; ?> <?php echo $this->endSection(); ?>

<?php echo $this->section('estilos'); ?>

<link rel="stylesheet" type="text/css" href="<?php echo site_url('recursos/vendor/selectize/selectize.bootstrap4.css') ?>" />   
   <style>
      /* Estilizando o select para acompanhar a formatação do template */
      .selectize-input,
      .selectize-control.single .selectize-input.input-active 
      {
         background: #2d3035 !important;
      }
      .selectize-dropdown,
      .selectize-input,
      .selectize-input input 
      {
         color: #777;
      }
      .selectize-input 
      {
         /*        height: calc(2.4rem + 2px);*/
         border: 1px solid #444951;
         border-radius: 0;
      }
   </style>

<?php echo $this->endSection(); ?>

<?php echo $this->section('conteudo'); ?>

<div class="row">

   <div class='col-lg-6'>
      <div class="block">

         <div class="user-block text-center">

            <div class="user-title mb-2">

               <h5 class="card-title mt=2"><?php echo esc($ordem->nome); ?></h5>
               <span>OS: <?php echo esc($ordem->codigo) ;?></span>
            </div>

            <p class="contributions"><?php echo $ordem->exibeSituacao(); ?></p>
            <p class="contributions">Aberta por: <?php echo esc($ordem->usuario_abertura); ?></p>
            <p class="contributions">Técnico responsável:
               <?php echo esc($ordem->usuario_responsavel !== null ? $ordem->usuario_responsavel : 'Não definido'); ?>
            </p>

            <?php if($ordem->situacao === 'encerrada') :?>
            <p class="contributions">Encerrada por: <?php echo esc($ordem->usuario_encerramento); ?></p>
            <?php endif; ?>

            <p class="card-text">Criado <?php echo esc($ordem->criado_em->humanize()); ?></p>
            <p class="card-text">Atualizado <?php echo esc($ordem->atualizado_em->humanize()); ?></p>

            <hr class="border-secondary">
            <!-- Exibirá os retornos do backend -->
            <div id="response">

            </div>
            <?php echo form_open('', ['id' => 'form', 'class' => 'text-left'], ["codigo" => "$ordem->codigo"]); ?>

            <div class="form-group">
               <label class="form-control-label">Escolha o técnico</label>

               <select name="usuario_responsavel_id" class="selectize">

                  <option value="">Digite o nome do técnico</option>
               </select>
            </div>

            <div class="form-group mt-2">
               <input id="btn-salvar" type="submit" value="Salvar" class="btn btn-danger btn-sm mr-2">
               <a href="<?php echo site_url("ordens/detalhes/$ordem->codigo"); ?>" class="btn btn-secondary btn-sm ml-2">Voltar</a>
            </div>

            <?php echo form_close(); ?>

         </div>
      </div>
   </div>
</div>

<?php echo $this->endSection(); ?>

<?php echo $this->section('scripts'); ?>

<script type="text/javascript" src="<?php echo site_url('recursos/vendor/selectize/selectize.min.js') ?>"></script>

<script>

   $(document).ready(function(){
      
      var $select = $(".selectize").selectize({
         create: false,
         //sortField: "text",

         maxItem: 1,
         valueField: 'id',
         labelField: 'nome',
         searchField: ['nome'],

         load: function(query, callback){
            if (query.length < 3) {
               return callback();
            }

            $.ajax({

               url: '<?php echo site_url("ordens/buscaresponsaveis/") ?>',
               data:{
                  termo : encodeURIComponent(query)
               },
               success: function(response){

                  $select.options = response;
                  callback(response);
               },
               error: function(){

                  alert('Não foi possível processar solicitação. Por favor entre em contato com o suporte!');
               }
            });
         }
      });

      $("#form").on('submit',function(e){
         
         e.preventDefault();
         $.ajax({
            type: 'POST',
            url: '<?=site_url('ordens/definirresponsavel'); ?>',
            data: new FormData(this),
            dataType: 'json',
            contentType: false,
            cache: false,
            processData: false,
            beforeSend: function() {
               
               $('#response').html('');
               $('#btn-salvar').val('Aguarde...');
            },
            success: function(response){

               $('#btn-salvar').val('Salvar');
               $('#btn-salvar').removeAttr("disabled");

               $('[name=csrf_ordem]').val(response.token);

               if(!response.erro) {
                  //Tudo certo com a atualização
                  window.location.href = "<?php echo site_url(); ?>" + response.redirect;
               } else {
                  //Erros de validação
                  $('#response').html('<div class="alert alert-danger">' + response.erro +'</div>');

                  if(response.erros_model) {

                     $.each(response.erros_model, function(key, value) {

                        $('#response').append('<ul class="list-unstyled"><li class="text-danger">' + value + '</li></ul>');
                     });
                  }
               }
            },
            error: function() {

               alert('Não foi possível processar solicitação. Por favor entre em contato com o suporte!');
               
               $('#btn-salvar').val('Salvar');
               $('#btn-salvar').removeAttr("disabled");
            },
         });
      });

      $("#form").submit(function() {

         $(this).find(":submit").attr('disabled', 'disabled');
      });

   });
</script>

<?php echo $this->endSection(); ?>