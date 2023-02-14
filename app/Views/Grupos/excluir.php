<?php echo $this->extend('Layout/principal'); ?>

<?php echo $this->section('titulo'); ?> <?php echo $titulo; ?> <?php echo $this->endSection(); ?>

<?php echo $this->section('estilos'); ?>

<?php echo $this->endSection(); ?>

<?php echo $this->section('conteudo'); ?>

<div class="row">
   <div class='col-lg-6'>
      <div class="block">
         <div class="block-body">

            <?php echo form_open("grupos/excluir/$grupo->id"); ?>

            <div class="alert alert-success" role="alert">
               <h4 class="alert-heading">Atenção!</h4>
               <hr>
               <p class="mb-0">Confirma essa exclusão ?</p>
            </div>

            <div class="form-group mt-5 mb-2">
               <input id="btn-salvar" type="submit" value="Sim" class="btn btn-danger btn-sm mr-2">
               <a href="<?php echo site_url("grupos/exibir/$grupo->id"); ?>"
                  class="btn btn-secondary btn-sm ml-2">Não</a>
            </div>

            <?php echo form_close(); ?>
         </div>
      </div>
   </div>
</div>

<?php echo $this->endSection(); ?>

<?php echo $this->section('scripts'); ?>

<?php echo $this->endSection(); ?>