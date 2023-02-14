<?php echo $this->extend('Layout/Autenticacao/principal_autenticacao'); ?>

<?php echo $this->section('titulo'); ?> <?php echo $titulo; ?> <?php echo $this->endSection(); ?>

<?php echo $this->section('estilos'); ?> 

<?php echo $this->endSection(); ?>

<?php echo $this->section('conteudo'); ?>
  <!-- Aqui coloco o conteúdo da view -->
  <div class="row">
    <!-- Logo & Information Panel-->
    <div class="col-lg-6">
      <div class="info d-flex align-items-center">
        <div class="content">
          <div class="logo">
            <h1><?php echo $titulo; ?></h1>
          </div>
          <p>Informe seu e-mail de acesso para iniciarmos a recuperação de sua senha.</p>
        </div>
      </div>
    </div>
    <!-- Form Panel    -->
    <div class="col-lg-6 bg-white">
      <div class="form d-flex align-items-center">
        <div class="content">

        <?php echo form_open('', ['id' => 'form', 'class' => 'form-validate']); ?>

            <div id="response">

            </div>

            <div class="form-group">
              <input id="login-username" type="text" name="email" required data-msg="Por favor informe seu e-mail de acesso" class="input-material">
              <label for="login-username" class="label-material">Informe seu e-mail de acesso</label>
            </div>
            
            <input id="btn-esqueci" type="submit" class="btn btn-primary" value="Enviar">

          <?php echo form_close(); ?>
            
          <a href="<?php echo site_url('login') ;?>" class="forgot-pass mt-3">Lembrou sua senha de acesso?</a>
        </div>
      </div>
    </div>
  </div> 

<?php echo $this->endSection(); ?>

<?php echo $this->section('scripts'); ?>

<script>
  $(document).ready(function(){
    $("#form").on('submit',function(e){
         
      e.preventDefault();

      $.ajax({
        type: 'POST',
        url: '<?=site_url('password/processaesqueci'); ?>',
        data: new FormData(this),
        dataType: 'json',
        contentType: false,
        cache: false,
        processData: false,
        beforeSend: function() {
            
            $('#response').html('');
            $('#btn-esqueci').val('Aguarde...');

        },
        success: function(response){

            $('#btn-esqueci').val('Enviar');
            $('#btn-esqueci').removeAttr("disabled");

            $('[name=csrf_ordem]').val(response.token);

            if(!response.erro) {
              
              //Tudo certo com a atualização do usuário
              window.location.href = "<?php echo site_url("password/resetenviado"); ?>";

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
            
            $('#btn-esqueci').val('Salvar');
            $('#btn-esqueci').removeAttr("disabled");

        },
      });

    });

    $("#form").submit(function() {

      $(this).find(":submit").attr('disabled', 'disabled');

    });

  });
</script>

<?php echo $this->endSection(); ?>
