<?php echo $this->extend('Layout/principal'); ?>

<?php echo $this->section('titulo'); ?> <?php echo $titulo; ?> <?php echo $this->endSection(); ?>

<?php echo $this->section('estilos'); ?>

<?php echo $this->endSection(); ?>

<?php echo $this->section('conteudo'); ?>

<div class="row">

   <div class='col-lg-4'>
      <div class="user-block block">

         <h5 class="card-title mt=2"><?php echo esc($conta->razao); ?></h5>
         <p class="card-text">CNPJ: <?php echo esc($conta->cnpj); ?></p>
         <p class="card-text">Valor da conta: <?php echo esc(number_format($conta->valor_conta,2)); ?></p>
         <p class="card-text">Vencimento: <?php echo date("d/m/Y", strtotime($conta->data_vencimento)) ; ?></p>
         <p class="card-text">Descrição: <?php echo esc($conta->descricao_conta); ?></p>
         <p class="contributions"><?php echo $conta->exibeSituacao(); ?></p>
         <p class="card-text">Criado <?php echo esc($conta->criado_em->humanize()); ?></p>
         <p class="card-text">Atualizado <?php echo esc($conta->atualizado_em->humanize()); ?></p>         

         <!-- Example single danger button -->
         <div class="btn-group">
            <button type="button" class="btn btn-danger dropdown-toggle" data-toggle="dropdown" aria-haspopup="true"
               aria-expanded="false">
               Ações
            </button>
            <div class="dropdown-menu">
               <a class="dropdown-item" href="<?php echo site_url("contas/editar/$conta->id"); ?>">Editar conta</a>
               <div class="dropdown-divider"></div>
               <a class="dropdown-item" href="<?php echo site_url("contas/excluir/$conta->id"); ?>">Excluir conta</a>
            </div>
         </div>

         <a href="<?php echo site_url("contas"); ?>" class="btn btn-secondary ml-2">Voltar</a>

      </div>
   </div>
</div>

<?php echo $this->endSection(); ?>

<?php echo $this->section('scripts'); ?>

<?php echo $this->endSection(); ?>
