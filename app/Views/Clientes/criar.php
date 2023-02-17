<?php echo $this->extend('Layout/principal'); ?>

<?php echo $this->section('titulo'); ?> <?php echo $titulo; ?> <?php echo $this->endSection(); ?>

<?php echo $this->section('estilos'); ?>

<?php echo $this->endSection(); ?>

<?php echo $this->section('conteudo'); ?>

<div class="row">
   <div class='col-lg-8'>
      <div class="block">
         <div class="block-body">

            <!-- Exibirá os retornos do backend -->
            <div id="response">

            </div>

            <?php echo form_open('', ['id' => 'form'], ['id' => "$cliente->id"]); ?>

            <?php echo $this->include('Clientes/_form'); ?>

            <div class="form-group mt-5 mb-2">
               <input id="btn-salvar" type="submit" value="Salvar" class="btn btn-danger btn-sm mr-2">
               <a href="<?php echo site_url("clientes"); ?>"
                  class="btn btn-secondary btn-sm ml-2">Voltar</a>
            </div>

            <?php echo form_close(); ?>
         </div>
      </div>
   </div>
</div>

<?php echo $this->endSection(); ?>

<?php echo $this->section('scripts'); ?>

<script src="<?=site_url('recursos/vendor/loadingoverlay/loadingoverlay.min.js'); ?>"></script>
<script src="<?=site_url('recursos/vendor/mask/jquery.mask.min.js'); ?>"></script>
<script src="<?=site_url('recursos/vendor/mask/app.js'); ?>"></script>

<script>
$(document).ready(function() {

   $("[name=pessoa]").on("click", function() {
      if ($(this).val() === "F") {
         $('#labelCpf').text("CPF");
         $('#inputCpf').attr({placeholder: "Insira o CPF"});
         $('#inputCpf').removeClass('cnpj');
         $('#inputCpf').addClass('cpf');
         $('#inputCpf').val('');
         $('#inputCpf').mask('00.000.000/0000-00', {reverse: true});

         $('#labelRg').text("RG");
         $('#inputRg').attr({placeholder: "Insira o RG"});
      } else {
         $('#labelCpf').text("CNPJ");
         $('#inputCpf').attr({placeholder: "Insira o CNPJ"});
         $('#inputCpf').removeClass('cpf');
         $('#inputCpf').addClass('cnpj');
         $('#inputCpf').val('');
         $('#inputCpf').mask('00.000.000/0000-00', {reverse: true});

         $('#labelRg').text("IE");
         $('#inputRg').attr({placeholder: "Insira a IE"});
      }
   });

   <?php echo $this->include('Clientes/_checkmail'); ?>
   
   <?php echo $this->include('Clientes/_viacep'); ?>

   $("#form").on('submit', function(e) {

      e.preventDefault();

      $.ajax({
         type: 'POST',
         url: '<?=site_url('clientes/cadastrar'); ?>',
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

               if (response.info) {

                  $('#response').html('<div class="alert alert-info">' + response.info + '</div>');
               } else {
                  //Tudo certo com a atualização do cliente
                  window.location.href =
                     "<?php echo site_url("clientes/exibir/"); ?>" + response.id;
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
