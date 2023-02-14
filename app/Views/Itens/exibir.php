<?php echo $this->extend('Layout/principal'); ?>

<?php echo $this->section('titulo'); ?> <?php echo $titulo; ?> <?php echo $this->endSection(); ?>

<?php echo $this->section('estilos'); ?>

<?php echo $this->endSection(); ?>

<?php echo $this->section('conteudo'); ?>

<div class="row">

   <div class='col-lg-4'>
      <div class="user-block block">

         <?php if($item->tipo === "produto"): ?>
            <div class="text-center">

               <?php if($item->imagem == null): ?>

               <img src="<?php echo site_url('recursos/img/item_sem_imagem.png'); ?>" class="card-img-top"
                  style="width: 80%;" alt="Item sem imagem">

               <?php else: ?>

               <img src="<?php echo site_url("itens/imagem/$item->imagem"); ?>" class="card-img-top"
                  style="width: 80%;" alt="<?php echo esc($item->nome); ?>">

               <?php endif; ?>

               <a href="<?php echo site_url("itens/editarimagem/$item->id"); ?>" class="btn btn-outline-primary btn-sm mt-3">Alterar imagem</a>

            </div>
            <hr class="border-secundary">
         <?php endif; ?>

         <h5 class="card-title mt=2"><?php echo esc($item->nome); ?></h5>
         <p class="contributions"><?php echo $item->exibeTipo(); ?></p>
         <p class="contributions">Estoque: <?php echo $item->exibeEstoque(); ?></p>
         <p class="contributions"><?php echo $item->exibeSituacao(); ?></p>
         <p class="contributions mt-0">
            <a class="btn btn-sm" target="_blank" href="<?php echo site_url("itens/codigobarras/$item->id"); ?>">Ver código de barras do item</a>
         </p>
         <p class="card-text">Preço de Venda R$ <?php echo number_format($item->preco_venda,2); ?></p>
         <p class="card-text">Criado <?php echo esc($item->criado_em->humanize()); ?></p>
         <p class="card-text">Atualizado <?php echo esc($item->atualizado_em->humanize()); ?></p>         

         <!-- Example single danger button -->
         <div class="btn-group">
            <button type="button" class="btn btn-danger dropdown-toggle" data-toggle="dropdown" aria-haspopup="true"
               aria-expanded="false">
               Ações
            </button>
            <div class="dropdown-menu">
               <a class="dropdown-item" href="<?php echo site_url("itens/editar/$item->id"); ?>">Editar item</a>

               <div class="dropdown-divider"></div>
               <?php if ($item->deletado_em == null): ?>
                  <a class="dropdown-item" href="<?php echo site_url("itens/excluir/$item->id"); ?>">Excluir item</a>
               <?php else: ?>
                  <a class="dropdown-item" href="<?php echo site_url("itens/recuperar/$item->id"); ?>">Recuperar item</a>
               <?php endif; ?>   
            </div>
         </div>

         <a href="<?php echo site_url("itens"); ?>" class="btn btn-secondary ml-2">Voltar</a>

      </div>
   </div>

   <div class='col-lg-8'>
      <div class="user-block block">
         
         <p class="contributions text-danger">Histórico de alterações do item</p>
         
         <?php if(isset($item->historico) === false): ?>

            <p>
               <span class="text-white">Item não possui histórico de alterações</span>
            </p>
         <?php else: ?>
            <div id="accordion">
               <?php foreach($item->historico as $key => $historico): ?>

                  <div class="card">
                     <div class="card-header" id="heading-<?php echo $key; ?>">
                        <h5 class="mb-0">
                        <button class="btn btn-link" data-toggle="collapse" 
                           data-target="#collapse-<?php echo $key; ?>" aria-expanded="true" 
                           aria-controls="collapse-<?php echo $key; ?>">
                           <?php echo $historico['acao'] ?> em 
                           <?php echo date("d/m/Y H:i", strtotime($historico['criado_em'])) ?> pelo usuário 
                           <?php echo $historico['usuario'] ?>
                        </button>
                        </h5>
                     </div>

                     <div id="collapse-<?php echo $key; ?>" class="collapse <?php echo ($key === 0 ? 'show' : '') ?>" 
                        aria-labelledby="heading-<?php echo $key; ?>" data-parent="#accordion">
                        <div class="card-body">
                        
                           <?php foreach($historico['atributos_alterados'] as $evento): ?>
                              
                              <p><?php echo $evento; ?></p>

                           <?php endforeach; ?>                           
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