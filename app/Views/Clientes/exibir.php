<?php echo $this->extend('Layout/principal'); ?>

<?php echo $this->section('titulo'); ?> <?php echo $titulo; ?> <?php echo $this->endSection(); ?>

<?php echo $this->section('estilos'); ?>

<?php echo $this->endSection(); ?>

<?php echo $this->section('conteudo'); ?>

<div class="row">

   <div class='col-lg-4'>
      <div class="user-block block">

         <h5 class="card-title mt=2"><?php echo esc($cliente->nome); ?></h5>
         
         <p class="card-text"><?php echo ($cliente->pessoa === 'F' ? "CPF: " : "CNPJ: "); ?><?php echo esc($cliente->cpf_cnpj); ?></p>
         <p class="card-text"><?php echo ($cliente->pessoa === 'F' ? "RG: " : "IE: "); ?><?php echo esc($cliente->rg_ie); ?></p>
                  
         <p class="card-text">Telefone: <?php echo esc($cliente->telefone); ?></p>
         <p class="contributions"><?php echo $cliente->exibeSituacao(); ?></p>
         <p class="card-text">Criado <?php echo esc($cliente->criado_em->humanize()); ?></p>
         <p class="card-text">Atualizado <?php echo esc($cliente->atualizado_em->humanize()); ?></p>         

         <!-- Example single danger button -->
         <div class="btn-group">
            <button type="button" class="btn btn-danger dropdown-toggle" data-toggle="dropdown" aria-haspopup="true"
               aria-expanded="false">
               Ações
            </button>
            <div class="dropdown-menu">
               <a class="dropdown-item" href="<?php echo site_url("clientes/editar/$cliente->id"); ?>">Editar cliente</a>
               
               <a class="dropdown-item" href="<?php echo site_url("clientes/historico/$cliente->id"); ?>">Histórico de atendimento</a>
               
               <div class="dropdown-divider"></div>
               
               <?php if ($cliente->deletado_em == null): ?>
                  <a class="dropdown-item" href="<?php echo site_url("clientes/excluir/$cliente->id"); ?>">Excluir cliente</a>
               
               <?php else: ?>
               
                  <a class="dropdown-item" href="<?php echo site_url("clientes/recuperar/$cliente->id"); ?>">Recuperar cliente</a>
               <?php endif; ?>   
            </div>
         </div>

         <a href="<?php echo site_url("clientes"); ?>" class="btn btn-secondary ml-2">Voltar</a>

      </div>
   </div>
</div>

<?php echo $this->endSection(); ?>

<?php echo $this->section('scripts'); ?>

<?php echo $this->endSection(); ?>