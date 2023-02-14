<?php echo $this->extend('Layout/principal'); ?>

<?php echo $this->section('titulo'); ?> <?php echo $titulo; ?> <?php echo $this->endSection(); ?>

<?php echo $this->section('estilos'); ?>

<?php echo $this->endSection(); ?>

<?php echo $this->section('conteudo'); ?>

<div class="row">
   <div class='col-lg-6'>
      <div class="block">
         <div class="block-body">

            <!-- Exibirá os retornos do backend -->
            <div id="response">

            </div>

            <?php echo form_open('', ['id' => 'form']); ?>

            <div class="form-group">
               <label class="form-control-label">Digite sua senha atual</label>
               <input type="password" name="password_atual" placeholder="Senha atual" class="form-control">
            </div>

            <div class="form-group">
               <label class="form-control-label">Digite sua nova senha</label>
               <input type="password" name="password" placeholder="Senha" class="form-control">
            </div>

            <div class="form-group">
               <label class="form-control-label">Confirme sua nova senha</label>
               <input type="password" name="password_confirmation" placeholder="Confirme a Senha" class="form-control">
            </div>

            <div class="form-group mt-5 mb-2">
               <input id="btn-salvar" type="submit" value="Salvar" class="btn btn-danger btn-sm mr-2">
            </div>

            <?php echo form_close(); ?>
         </div>
      </div>
   </div>
</div>

<?php echo $this->endSection(); ?>

<?php echo $this->section('scripts'); ?>
<script>
$(document).ready(function() {
   $("#form").on('submit', function(e) {

      e.preventDefault();

      $.ajax({
         type: 'POST',
         url: '<?=site_url('usuarios/atualizarsenha'); ?>',
         data: new FormData(this),
         dataType: 'json',
         contentType: false,
         cache: false,
         processData: false,
         beforeSend: function() {

            $('#response').html('');
            $('#btn-salvar').val('Aguarde...');

         },
         success: function(response) {

            $('#btn-salvar').val('Salvar');
            $('#btn-salvar').removeAttr("disabled");

            $('[name=csrf_ordem]').val(response.token);

            if (!response.erro) {
               //Tudo certo
               //Limpa o formulário
               $("#form")[0].reset();

               if (response.info) {

                  $('#response').html('<div class="alert alert-info">' + response.info + '</div>');
               } else {
                 
                  $('#response').html('<div class="alert alert-success">' + response.sucesso + '</div>');
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
               'Não foi possível processar solicitação. Por favor entre em contato com o suporte!');

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