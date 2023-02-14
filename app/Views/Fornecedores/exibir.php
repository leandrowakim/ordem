<?php echo $this->extend('Layout/principal'); ?>

<?php echo $this->section('titulo'); ?> <?php echo $titulo; ?> <?php echo $this->endSection(); ?>

<?php echo $this->section('estilos'); ?>

<?php echo $this->endSection(); ?>

<?php echo $this->section('conteudo'); ?>

<div class="row">

   <div class='col-lg-4'>
      <div class="user-block block">

         <h5 class="card-title mt=2"><?php echo esc($fornecedor->razao); ?></h5>         
         <p class="card-text">CNPJ: <?php echo esc($fornecedor->cnpj); ?></p>
         <p class="card-text">Telefone: <?php echo esc($fornecedor->telefone); ?></p>
         <p class="contributions"><?php echo $fornecedor->exibeSituacao(); ?></p>
         <p class="card-text">Criado <?php echo esc($fornecedor->criado_em->humanize()); ?></p>
         <p class="card-text">Atualizado <?php echo esc($fornecedor->atualizado_em->humanize()); ?></p>         

         <!-- Example single danger button -->
         <div class="btn-group">
            <button type="button" class="btn btn-danger dropdown-toggle" data-toggle="dropdown" aria-haspopup="true"
               aria-expanded="false">
               Ações
            </button>
            <div class="dropdown-menu">
               <a class="dropdown-item" href="<?php echo site_url("fornecedores/editar/$fornecedor->id"); ?>">Editar fornecedor</a>
               <a class="dropdown-item" href="<?php echo site_url("fornecedores/notas/$fornecedor->id"); ?>">Gerenciar as notas fiscais</a>
               <div class="dropdown-divider"></div>
               <?php if ($fornecedor->deletado_em == null): ?>
                  <a class="dropdown-item" href="<?php echo site_url("fornecedores/excluir/$fornecedor->id"); ?>">Excluir fornecedor</a>
               <?php else: ?>
                  <a class="dropdown-item" href="<?php echo site_url("fornecedores/recuperar/$fornecedor->id"); ?>">Recuperar fornecedor</a>
               <?php endif; ?>   
            </div>
         </div>

         <a href="<?php echo site_url("fornecedores"); ?>" class="btn btn-secondary ml-2">Voltar</a>

      </div>
   </div>
</div>

<?php echo $this->endSection(); ?>

<?php echo $this->section('scripts'); ?>

<?php echo $this->endSection(); ?>