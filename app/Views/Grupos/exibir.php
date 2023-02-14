<?php echo $this->extend('Layout/principal'); ?>

<?php echo $this->section('titulo'); ?> <?php echo $titulo; ?> <?php echo $this->endSection(); ?>

<?php echo $this->section('estilos'); ?>

<?php echo $this->endSection(); ?>

<?php echo $this->section('conteudo'); ?>

<div class="row">

   <?php if($grupo->id < 3 ) : ?>
      <div class='col-md-12'>
         <div class="alert alert-info" role="alert">
            <h4 class="alert-heading">Importante!</h4>
            <p>O grupo <b><?php echo $grupo->nome; ?></b> não pode ser editado ou excluído, pois o mesmo não pode ter suas permissões
               revogadas.</p>
            <hr>
            <p class="mb-0">Os demais grupos poderão ser editado ou excluído, conforme a necessidade.</p>
         </div>
      </div>

   <?php endif; ?>

   <div class='col-lg-4'>
      <div class="user-block block">

         <h5 class="card-title mt=2"><?php echo esc($grupo->nome); ?></h5>
         <p class="card-text"><?php echo esc($grupo->descricao); ?></p>
         <p class="contributions"><?php echo $grupo->exibeSituacao(); ?>

            <?php if($grupo->deletado_em == null): ?>

            <a tabindex="0" style="text-decoration: none ;" role="button" data-toggle="popover" data-trigger="focus"
               title="Importante"
               data-content="Este grupo <?php echo ($grupo->exibir == true ? 'será' : 'não será') ?> exibido como opção na hora de definir um <b>Responsável Técnico</b> pela ordem de serviço.">&nbsp;<i
                  class="fa fa-question-circle text-danger fa-lg"></i></a>

            <?php endif; ?>

         </p>
         <p class="card-text">Criado <?php echo esc($grupo->criado_em->humanize()); ?></p>
         <p class="card-text">Atualizado <?php echo esc($grupo->atualizado_em->humanize()); ?></p>

         <!-- Example single danger button -->
         <?php if($grupo->id > 2 ) : ?>
         <div class="btn-group mr-2">
            <button type="button" class="btn btn-danger dropdown-toggle" data-toggle="dropdown" aria-haspopup="true"
               aria-expanded="false">
               Ações
            </button>
            <div class="dropdown-menu">
               <a class="dropdown-item" href="<?php echo site_url("grupos/editar/$grupo->id"); ?>">Editar grupo de
                  acesso</a>
               <?php if ($grupo->id > 2): ?>
               
                  <a class="dropdown-item" href="<?php echo site_url("grupos/permissoes/$grupo->id"); ?>">Gerenciar permissões 
                  do grupo de acesso</a>
               
               <?php endif; ?>   
               <div class="dropdown-divider"></div>
               <?php if ($grupo->deletado_em == null): ?>
               <a class="dropdown-item" href="<?php echo site_url("grupos/excluir/$grupo->id"); ?>">Excluir grupo de
                  acesso</a>
               <?php else: ?>
               <a class="dropdown-item" href="<?php echo site_url("grupos/recuperar/$grupo->id"); ?>">Recuperar grupo de
                  acesso</a>
               <?php endif; ?>
            </div>
         </div>
         <?php endif; ?>

         <a href="<?php echo site_url("grupos"); ?>" class="btn btn-secondary">Voltar</a>

      </div>
   </div>
</div>

<?php echo $this->endSection(); ?>

<?php echo $this->section('scripts'); ?>

<?php echo $this->endSection(); ?>