<?php echo $this->extend('Layout/Autenticacao/principal_autenticacao'); ?>

<?php echo $this->section('titulo'); ?> <?php echo $titulo; ?> <?php echo $this->endSection(); ?>

<?php echo $this->section('estilos'); ?> 

<?php echo $this->endSection(); ?>

<?php echo $this->section('conteudo'); ?>
  <!-- Aqui coloco o conteúdo da view -->
  <div class="row text-center">
    <!-- Logo & Information Panel-->
    <div class="col-lg-6 mx-auto">
      <div class="form d-flex align-items-center bg-info">
        <div class="content">
          <div class="mt-5 text-center text-white">
            
            <p><?php echo $item->nome; ?></p>
            <p><?php echo $item->codigo_barras; ?></p>
            <p><?php echo $item->codigo_interno; ?></p>
            <p>
              <button class="btn btn-primary bg-dark mr-2" onclick="window.print();">Imprimir</button>
              <button class="btn btn-primary bg-dark" onclick="window.close();">Fechar</button>
            </p>

          </div>
        </div>
      </div>
    </div>
  </div> 

<?php echo $this->endSection(); ?>

<?php echo $this->section('scripts'); ?>

<?php echo $this->endSection(); ?>
