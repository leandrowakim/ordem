<?php echo $this->extend('Layout/principal'); ?>

<?php echo $this->section('titulo'); ?> <?php echo $titulo; ?> <?php echo $this->endSection(); ?>

<?php echo $this->section('estilos'); ?>

<?php echo $this->endSection(); ?>

<?php echo $this->section('conteudo'); ?>

<div class="row">

   <?php if($ordem->situacao === 'aberta'): ?>

   <div class='col-lg-6'>
      <div class="block">
         <div class="block-body">

            <!-- Exibirá os retornos do backend -->
            <div id="response">

            </div>

            <?php echo form_open_multipart('', ['id' => 'form'], ['codigo' => "$ordem->codigo"]); ?>

            <div class="form-group">
               <label class="form-control-label">Escolha uma ou mais evidências(Imagem ou PDF)</label>
               <input type="file" name="evidencias[]" class="form-control" multiple>
            </div>

            <div class="form-group mt-5 mb-2">
               <input id="btn-salvar" type="submit" value="Salvar" class="btn btn-danger btn-sm mr-2">
               <a href="<?php echo site_url("ordens/detalhes/$ordem->codigo"); ?>"
                  class="btn btn-secondary btn-sm ml-2">Voltar</a>
            </div>

            <?php echo form_close(); ?>
         </div>
      </div>
   </div>

   <?php endif; ?>

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

               <?php if($ordem->situacao === 'aberta'): ?>
                  <div class="card-body text-center">
                     <?php echo form_open("ordensevidencias/removerevidencia/$evidencia->evidencia", ['onSubmit' => 'return confirm("Tem certeza da exclusão?");'], ['codigo' => $ordem->codigo]); ?>

                     <button data-toogle="tooltip" data-placemen="top" title="Excluir arquivo" type="submit" class="btn btn-danger"><i class="fa fa-trash fa fa-lg"></i></button>

                     <?php echo form_close(); ?>
                  </div>
               <?php endif; ?>
            </div>
         </li>
         <?php endforeach; ?>
      </ul>
   </div>

   <?php endif; ?>

</div>

<?php echo $this->endSection(); ?>

<?php echo $this->section('scripts'); ?>

<script>
$(document).ready(function() {

   $("#form").on('submit', function(e) {

      e.preventDefault();

      $.ajax({
         type: 'POST',
         url: '<?=site_url('ordensevidencias/upload'); ?>',
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
               //Tudo certo com a atualização
               window.location.href =
                  "<?php echo site_url("ordensevidencias/evidencias/$ordem->codigo"); ?>";
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