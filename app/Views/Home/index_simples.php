<?php echo $this->extend('Layout/principal'); ?>

<?php echo $this->section('titulo'); ?> <?php echo $titulo; ?> <?php echo $this->endSection(); ?>

<?php echo $this->section('estilos'); ?>

<?php echo $this->endSection(); ?>

<?php echo $this->section('conteudo'); ?>

<div class="row">

   <div class="col-lg-8">
      <div class="bar-chart block chart">
         <div class="title"><strong>Minhas ordens de serviço de <?php echo date('Y'); ?></strong></div>
         <?php if(empty($atendimentosClientePorMes)): ?>
            <p>Aqui será exibido os dados do gráfico</p>
         <?php else: ?>
            <div class="bar-chart chart">
               <canvas id="barChartCustom3"></canvas>
            </div>
         <?php endif; ?>
      </div>
   </div>

</div>

<?php echo $this->endSection(); ?>

<?php echo $this->section('scripts'); ?>

<script src="<?php echo site_url('recursos/vendor/chart.js/Chart.min.js'); ?>"></script>

<script>
$(document).ready(function() {
   'use strict';

   Chart.defaults.global.defaultFontColor = '#75787c';

   <?php if(! empty($atendimentosClientePorMes)): ?>

   // ------------------------------------------------------- //
   // Bar Chart
   // ------------------------------------------------------ //
   var mes = [];
   var ordens = [];
   var valorGerado = [];

   <?php foreach ($atendimentosClientePorMes as $cliente): ?>
      mes.push('<?php echo $cliente->mes_nome; ?>');      
      ordens.push('<?php echo $cliente->total_ordens; ?>');
      valorGerado.push('<?php echo $cliente->valor_gerado; ?>');
   <?php endforeach; ?>

   var BARCHARTEXMPLE = $('#barChartCustom3');
   var barChartExample = new Chart(BARCHARTEXMPLE, {
      type: 'bar',
      options: {
         scales: {
            xAxes: [{
               display: true,
               gridLines: {
                  color: 'transparent'
               }
            }],
            yAxes: [{
               display: true,
               gridLines: {
                  color: 'transparent'
               }
            }]
         },
      },
      data: {
         labels: mes,
         datasets: [
            {
               label: "Qtde Mês",
               backgroundColor: [
                  "#864DD9",
                  "#864DD9",
                  "#864DD9",
                  "#864DD9",
                  "#864DD9",
                  "#864DD9",
                  "#864DD9"
               ],
               hoverBackgroundColor: [
                  "#864DD9",
                  "#864DD9",
                  "#864DD9",
                  "#864DD9",
                  "#864DD9",
                  "#864DD9",
                  "#864DD9"
               ],
               borderColor: [
                  "#864DD9",
                  "#864DD9",
                  "#864DD9",
                  "#864DD9",
                  "#864DD9",
                  "#864DD9",
                  "#864DD9"
               ],
               borderWidth: 0.5,
               data: ordens,
            },
            {
               label: "Valor Mês",
               backgroundColor: [
                  "rgba(98, 98, 98, 0.5)",
                  "rgba(98, 98, 98, 0.5)",
                  "rgba(98, 98, 98, 0.5)",
                  "rgba(98, 98, 98, 0.5)",
                  "rgba(98, 98, 98, 0.5)",
                  "rgba(98, 98, 98, 0.5)",
                  "rgba(98, 98, 98, 0.5)"
               ],
               hoverBackgroundColor: [
                  "rgba(98, 98, 98, 0.5)",
                  "rgba(98, 98, 98, 0.5)",
                  "rgba(98, 98, 98, 0.5)",
                  "rgba(98, 98, 98, 0.5)",
                  "rgba(98, 98, 98, 0.5)",
                  "rgba(98, 98, 98, 0.5)",
                  "rgba(98, 98, 98, 0.5)"
               ],
               borderColor: [
                  "rgba(98, 98, 98, 0.5)",
                  "rgba(98, 98, 98, 0.5)",
                  "rgba(98, 98, 98, 0.5)",
                  "rgba(98, 98, 98, 0.5)",
                  "rgba(98, 98, 98, 0.5)",
                  "rgba(98, 98, 98, 0.5)",
                  "rgba(98, 98, 98, 0.5)"
               ],
               borderWidth: 0.5,
               data: valorGerado,
            }
         ]
      }
   });

   <?php endif; ?>

});
</script>

<?php echo $this->endSection(); ?>