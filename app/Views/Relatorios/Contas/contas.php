<?php echo $this->extend('Layout/principal'); ?>

<?php echo $this->section('titulo'); ?> <?php echo $titulo; ?> <?php echo $this->endSection(); ?>

<?php echo $this->section('estilos'); ?>

<?php echo $this->endSection(); ?>

<?php echo $this->section('conteudo'); ?>

<div class="row">
   <div class="col-lg-6">
      <div class="block">

         <?php echo form_open('/', ['id' => 'form']); ?>
            <div id="response">

            </div>
            <div class="row">
               
               <div class="form-group col-lg-12 mt-3">                        
                  <label class="form-control-label">Situação da conta</label>
                  <select class="custom-select" name="situacao">
                     <option value="">Escolha...</option>
                     <option value="aberta">Contas em aberto</option>
                     <option value="pagas">Contas pagas</option>
                     <option value="vencidas">Contas vencidas</option>
                  </select>
               </div>
               
               <div class="form-group col-lg-6 vencidas">                        
                  <label class="form-control-label">Data inicial</label>
                  <input type="datetime-local" name="data_inicial" class="form-control">
               </div>
               
               <div class="form-group col-lg-6 vencidas">
                  <label class="form-control-label">Data final</label>
                  <input type="datetime-local" name="data_final" class="form-control">
               </div>

               <div class="form-group col-lg-6 mt-2">
                  <input id="btn-gerar-relatorio" type="submit" value="Gerar relatório" class="btn btn-dark btn-sm text-secondary">
               </div>
               
            </div>
         <?php echo form_close(); ?>
      </div>
   </div>
</div>

<?php echo $this->endSection(); ?>

<?php echo $this->section('scripts'); ?>

<script>
   $(document).ready(function(){

      $("[name=situacao").on("change", function() {
         if ($(this).val() === "vencidas") {

            $(".vencidas").hide("slow");
            $("[name=data_inicial").prop('disabled', true);
            $("[name=data_final").prop('disabled', true);
         } else {

            $(".vencidas").show("slow");
            $("[name=data_inicial").prop('disabled', false);
            $("[name=data_final").prop('disabled', false);
         }

      });

      $("#form").on('submit',function(e){
         
         e.preventDefault();

         $.ajax({
            type: 'POST',
            url: '<?=site_url('relatorios/gerarrelatoriocontas'); ?>',
            data: new FormData(this),
            dataType: 'json',
            contentType: false,
            cache: false,
            processData: false,
            beforeSend: function() {
               
               $('#response').html('');
               $('#btn-gerar-relatorio').val('Aguarde...');

            },
            success: function(response){

               $('#btn-gerar-relatorio').val('Gerar relatório');
               $('#btn-gerar-relatorio').removeAttr("disabled");

               $('[name=csrf_ordem]').val(response.token);

               if(!response.erro) {

                  if (response.info) {

                     $('#response').html('<div class="alert alert-info">' + response.info +'</div>');
                  }else {
                     
                     var url = "<?php echo site_url(); ?>" + response.redirect;
                     var win = window.open(url, '_blank');
                     win.focus();
                  }
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
               
               $('#btn-gerar-relatorio').val('Gerar relatório');
               $('#btn-gerar-relatorio').removeAttr("disabled");
            },
         });
      });

      $("#form").submit(function() {

         $(this).find(":submit").attr('disabled', 'disabled');
      });

   });
</script>

<?php echo $this->endSection(); ?>