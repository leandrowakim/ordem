<?php echo $this->extend('Layout/principal'); ?>

<?php echo $this->section('titulo'); ?> <?php echo $titulo; ?> <?php echo $this->endSection(); ?>

<?php echo $this->section('estilos'); ?>

<?php echo $this->endSection(); ?>

<?php echo $this->section('conteudo'); ?>

<div class="row">

   <?php if($forma->id == 1) : ?>
      <div class='col-md-12'>
         <div class="alert alert-info" role="alert">
            <h4 class="alert-heading">Importante!</h4>
            <p>A forma de pagamento <b><?php echo $forma->nome; ?></b> não pode ser editada ou excluída, 
               pois a mesma poderá ser associada às Ordens de Serviços.</p>
            <hr>
            <p class="mb-0">As demais poderão ser editada ou excluída, conforme a necessidade.</p>
         </div>
      </div>
   <?php endif; ?>

   <?php if($forma->id == 2) : ?>
      <div class='col-md-12'>
         <div class="alert alert-info" role="alert">
            <h4 class="alert-heading">Importante!</h4>
            <p>A forma de pagamento <b><?php echo $forma->nome; ?></b> não pode ser editada ou excluída, 
               pois a mesma será associada às Ordens de Serviços que não gerarem valores.</p>
            <hr>
            <p class="mb-0">As demais poderão ser editada ou excluída, conforme a necessidade.</p>
         </div>
      </div>
   <?php endif; ?>

   <div class='col-lg-4'>
      <div class="user-block block">

         <h5 class="card-title mt=2"><?php echo esc($forma->nome); ?></h5>
         <p class="card-text"><?php echo esc($forma->descricao); ?></p>
         <p class="contributions"><?php echo $forma->exibeSituacao(); ?></p>
         <p class="card-text">Criado <?php echo esc($forma->criado_em->humanize()); ?></p>
         <p class="card-text">Atualizado <?php echo esc($forma->atualizado_em->humanize()); ?></p>

         <!-- Example single danger button -->
         <?php if($forma->id > 2 ) : ?>
         <div class="btn-group mr-2">
            <button type="button" class="btn btn-danger dropdown-toggle" data-toggle="dropdown" aria-haspopup="true"
               aria-expanded="false">
               Ações
            </button>
            <div class="dropdown-menu">
               <a class="dropdown-item" href="<?php echo site_url("formas/editar/$forma->id"); ?>">
                  Editar forma de pagamento
               </a>

               <div class="dropdown-divider"></div>
               
               <a class="dropdown-item" href="<?php echo site_url("formas/excluir/$forma->id"); ?>">
                  Excluir forma de pagamento
               </a>
            </div>
         </div>
         <?php endif; ?>

         <a href="<?php echo site_url("formas"); ?>" class="btn btn-secondary">Voltar</a>

      </div>
   </div>
</div>

<?php echo $this->endSection(); ?>

<?php echo $this->section('scripts'); ?>

<?php echo $this->endSection(); ?>
