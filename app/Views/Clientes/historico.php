<?php echo $this->extend('Layout/principal'); ?>

<?php echo $this->section('titulo'); ?> <?php echo $titulo; ?> <?php echo $this->endSection(); ?>

<?php echo $this->section('estilos'); ?>

<?php echo $this->endSection(); ?>

<?php echo $this->section('conteudo'); ?>

<div class="row">

   <div class="col-lg-4">
      <div class="user-block block">

         <h5 class="card-title mt=2"><?php echo esc($cliente->nome); ?></h5>         
         <p class="card-text">CPF: <?php echo esc($cliente->cpf); ?></p>
         <p class="card-text">Telefone: <?php echo esc($cliente->telefone); ?></p>
         <p class="contributions"><?php echo $cliente->exibeSituacao(); ?></p>
         <p class="card-text">Criado <?php echo esc($cliente->criado_em->humanize()); ?></p>
         <p class="card-text">Atualizado <?php echo esc($cliente->atualizado_em->humanize()); ?></p>         

         <a href="<?php echo site_url("clientes/exibir/$cliente->id"); ?>" class="btn btn-secondary ml-2">Voltar</a>

      </div>
   </div>

   <div class="col-lg-8">
      <div class="user user-block block">
         <?php if(! isset($ordensCliente)): ?>
            <div class="contributions text-center text-warning">
               Esse cliente ainda não possui histórico de atendimento
            </div>         
         <?php else: ?>
            <div id="accordion">
               <?php foreach($ordensCliente as $key => $ordem): ?>

                  <div class="card">
                     <div class="card-header" id="heading-<?php echo $key; ?>">
                        <h5 class="mb-0">
                        <button class="btn btn-link" data-toggle="collapse" 
                           data-target="#collapse-<?php echo $key; ?>" aria-expanded="true" 
                           aria-controls="collapse-<?php echo $key; ?>">
                           Atendimento realizado em  
                           <?php echo date("d/m/Y H:i", strtotime($ordem->criado_em)) ?>
                        </button>
                        </h5>
                     </div>

                     <div id="collapse-<?php echo $key; ?>" class="collapse <?php echo ($key === 0 ? 'show' : '') ?>" 
                        aria-labelledby="heading-<?php echo $key; ?>" data-parent="#accordion">
                        <div class="card-body">
                           <p><strong>OS:&nbsp;</strong><?php echo $ordem->codigo; ?></p>
                           <p><strong>Situação:&nbsp;</strong><?php echo $ordem->exibeSituacao(); ?></p>
                           <p><strong>Equipamento:&nbsp;</strong><?php echo esc($ordem->equipamento); ?></p>
                           <p><strong>Defeito:&nbsp;</strong><?php echo ($ordem->defeito != null ? esc($ordem->defeito) : 'Não informado'); ?></p>
                           <p><strong>Obersações:&nbsp;</strong><?php echo ($ordem->observacoes != null ? esc($ordem->observacoes) : 'Não informado'); ?></p>

                           <a target="_blank" class="btn btn-outline-info text-white btn-sm" href="<?php echo site_url("ordens/detalhes/$ordem->codigo") ?>">Mais detalhes</a>
                        </div>
                     </div>
                  </div>
               <?php endforeach; ?>
            </div>         
         <?php endif; ?>
      </div>
   </div>
</div>

<?php echo $this->endSection(); ?>

<?php echo $this->section('scripts'); ?>

<?php echo $this->endSection(); ?>