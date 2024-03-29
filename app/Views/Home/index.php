<?php echo $this->extend('Layout/principal'); ?>

<?php echo $this->section('titulo'); ?> <?php echo $titulo; ?> <?php echo $this->endSection(); ?>

<?php echo $this->section('estilos'); ?>

<?php echo $this->endSection(); ?>

<?php echo $this->section('conteudo'); ?>

<div class="row">

   <div class="col-md-3 col-sm-6">
      <div class="statistic-block block">
         <div class="progress-details d-flex align-items-end justify-content-between">
            <div class="title">
               <div class="icon"><i class="icon-user-1"></i></div><strong>Total Clientes</strong>
            </div>
            <div class="number dashtext-1"><?php echo $totalClientes; ?></div>
         </div>
         <div class="progress progress-template">
            <div role="progressbar" style="width: 30%" aria-valuenow="30" aria-valuemin="0" aria-valuemax="100"
               class="progress-bar progress-bar-template dashbg-1"></div>
         </div>
      </div>
   </div>
   <div class="col-md-3 col-sm-6">
      <div class="statistic-block block">
         <div class="progress-details d-flex align-items-end justify-content-between">
            <div class="title">
               <div class="icon"><i class="icon-contract"></i></div><strong>Total Fornecedores</strong>
            </div>
            <div class="number dashtext-2"><?php echo $totalFornecedores; ?></div>
         </div>
         <div class="progress progress-template">
            <div role="progressbar" style="width: 70%" aria-valuenow="70" aria-valuemin="0" aria-valuemax="100"
               class="progress-bar progress-bar-template dashbg-2"></div>
         </div>
      </div>
   </div>
   <div class="col-md-3 col-sm-6">
      <div class="statistic-block block">
         <div class="progress-details d-flex align-items-end justify-content-between">
            <div class="title">
               <div class="icon"><i class="icon-paper-and-pencil"></i></div><strong>Total Itens</strong>
            </div>
            <div class="number dashtext-3"><?php echo $totalItens; ?></div>
         </div>
         <div class="progress progress-template">
            <div role="progressbar" style="width: 55%" aria-valuenow="55" aria-valuemin="0" aria-valuemax="100"
               class="progress-bar progress-bar-template dashbg-3"></div>
         </div>
      </div>
   </div>
   <div class="col-md-3 col-sm-6">
      <div class="statistic-block block">
         <div class="progress-details d-flex align-items-end justify-content-between">
            <div class="title">
               <div class="icon"><i class="icon-writing-whiteboard"></i></div><strong>Total Ordens Encerradas</strong>
            </div>
            <div class="number dashtext-4"><?php echo $totalOrdensEncerradas; ?></div>
         </div>
         <div class="progress progress-template">
            <div role="progressbar" style="width: 35%" aria-valuenow="35" aria-valuemin="0" aria-valuemax="100"
               class="progress-bar progress-bar-template dashbg-4"></div>
         </div>
      </div>
   </div>
</div>

<div class="row">

   <div class="col-lg-6">
      <div class="line-chart block chart">
         <div class="title"><strong>Valores gerados por clientes em <?php echo date('Y'); ?></strong></div>

         <?php if(empty($dadosClientes)): ?>
         <p>Aqui será exibido os dados do gráfico</p>
         <?php else: ?>
         <canvas id="lineChartCustom1"></canvas>
         <?php endif; ?>

      </div>
   </div>

   <div class="col-lg-6">
      <div class="pie-chart chart block">
         <div class="title"><strong>Ordens abertas por atendentes em <?php echo date('Y'); ?></strong></div>
         <?php if(empty($dadosDesempenho)): ?>
         <p>Aqui será exibido os dados do gráfico</p>
         <?php else: ?>
         <div class="pie-chart chart margin-bottom-sm">
            <canvas id="pieChartCustom1"></canvas>
         </div>
         <?php endif; ?>
      </div>
   </div>

</div>

<div class="row">
   <div class="col-lg-4">
      <div class="stats-with-chart-2 block">
         <div class="title"><strong class="d-block">TOP <?php echo count($produtosMaisVendidos); ?> produtos mais vendidos em <?php echo date('Y'); ?></strong>
            <span class="d-block">Produtos</span>
         </div>
         <div class="piechart chart">
            <canvas id="pieChartHome1"></canvas>
            <div class="text"><strong class="d-block"><?php echo array_sum(array_column($produtosMaisVendidos, 'qtde')); ?></strong><span class="d-block">Vendidos</span></div>
         </div>
      </div>
   </div>
   <div class="col-lg-4">
      <div class="stats-with-chart-2 block">
         <div class="title"><strong class="d-block">TOP <?php echo count($servicosMaisVendidos); ?> serviços mais vendidos em <?php echo date('Y'); ?></strong>
            <span class="d-block">Serviços</span>
         </div>
         <div class="piechart chart">
            <canvas id="pieChartHome2"></canvas>
            <div class="text"><strong class="d-block"><?php echo array_sum(array_column($servicosMaisVendidos, 'qtde')); ?></strong><span class="d-block">Vendidos</span></div>
         </div>
      </div>
   </div>
   <div class="col-lg-4">
      <div class="stats-with-chart-2 block">
         <div class="title"><strong class="d-block">Atendimentos por mês em <?php echo date('Y'); ?></strong>
            <span class="d-block">Atendimentos</span>
         </div>
         <div class="piechart chart">
            <canvas id="pieChartHome3"></canvas>
            <div class="text"><strong class="d-block"><?php echo array_sum(array_column($atendimentosPorMes, 'total_ordens')); ?></strong><span class="d-block">Atendimentos</span></div>
         </div>
      </div>
   </div>
</div>


<?php echo $this->endSection(); ?>

<?php echo $this->section('scripts'); ?>

<script src="<?php echo site_url('recursos/vendor/chart.js/Chart.min.js'); ?>"></script>
<script src="<?php //echo site_url('recursos/js/charts-custom.js'); ?>"></script>

<script>
$(document).ready(function() {
   'use strict';

   Chart.defaults.global.defaultFontColor = '#75787c';

   <?php if(! empty($dadosClientes)): ?>
   // ------------------------------------------------------- //
   // Line Chart Custom 1
   // ------------------------------------------------------ //
   var nomesClientes = [];
   var ordens = [];
   var valorGerado = [];

   <?php foreach ($dadosClientes as $cliente): ?>
   nomesClientes.push('<?php echo $cliente->nome; ?>');
   ordens.push('<?php echo $cliente->ordens; ?>');
   valorGerado.push('<?php echo $cliente->valor_gerado; ?>');
   <?php endforeach; ?>

   var LINECHARTEXMPLE = $('#lineChartCustom1');
   var lineChartExample = new Chart(LINECHARTEXMPLE, {
      type: 'line',
      options: {
         hover: {
            mode: null
         },
         responsive: true,
         legend: {
            labels: {
               fontColor: "#777",
               fontSize: 12
            }
         },
         scales: {
            xAxes: [{
               display: false,
               gridLines: {
                  color: 'transparent'
               }
            }],
            yAxes: [{
               ticks: {
                  //max: 60,
                  min: 0
               },
               display: true,
               gridLines: {
                  color: 'transparent'
               }
            }]
         },
      },
      data: {
         labels: nomesClientes,
         datasets: [{
               label: "Ordens encerradas",
               fill: true,
               lineTension: 0,
               backgroundColor: "rgba(134, 77, 217, 0.88)",
               borderColor: "rgba(134, 77, 217, 088)",
               borderCapStyle: 'butt',
               borderDash: [],
               borderDashOffset: 0.0,
               borderJoinStyle: 'miter',
               borderWidth: 1,
               pointBorderColor: "rgba(134, 77, 217, 0.88)",
               pointBackgroundColor: "#fff",
               pointBorderWidth: 1,
               pointHoverRadius: 5,
               pointHoverBackgroundColor: "rgba(134, 77, 217, 0.88)",
               pointHoverBorderColor: "rgba(134, 77, 217, 0.88)",
               pointHoverBorderWidth: 2,
               pointRadius: 4,
               pointHitRadius: 10,
               data: ordens,
               spanGaps: false
            },
            {
               label: "Valor gerado",
               fill: true,
               lineTension: 0,
               backgroundColor: "rgba(98, 98, 98, 0.5)",
               borderColor: "rgba(98, 98, 98, 0.5)",
               borderCapStyle: 'butt',
               borderDash: [],
               borderDashOffset: 0.0,
               borderJoinStyle: 'miter',
               borderWidth: 1,
               pointBorderColor: "rgba(98, 98, 98, 0.5)",
               pointBackgroundColor: "#fff",
               pointBorderWidth: 1,
               pointHoverRadius: 5,
               pointHoverBackgroundColor: "rgba(98, 98, 98, 0.5)",
               pointHoverBorderColor: "rgba(98, 98, 98, 0.5)",
               pointHoverBorderWidth: 2,
               pointRadius: 4,
               pointHitRadius: 10,
               data: valorGerado,
               spanGaps: false
            }
         ]
      }
   });
   <?php endif; ?>

   <?php if(! empty($dadosDesempenho)): ?>
   // ------------------------------------------------------- //
   // Pie Chart Custom 1
   // ------------------------------------------------------ //
   var nomesAtendentes = [];
   var qtdeOrdens = [];
   var backgroundColor = [];

   <?php foreach ($dadosDesempenho as $desempenho): ?>
   nomesAtendentes.push('<?php echo $desempenho->nome; ?>');
   qtdeOrdens.push('<?php echo $desempenho->qtde_os; ?>');
   backgroundColor.push('#' + Math.floor(Math.random() * 16777215).toString(16));
   <?php endforeach; ?>

   var PIECHARTEXMPLE = $('#pieChartCustom1');
   var pieChartExample = new Chart(PIECHARTEXMPLE, {
      type: 'pie',
      options: {
         responsive: true,
         legend: {
            display: true,
            position: "left"
         }
      },
      data: {
         labels: nomesAtendentes,
         datasets: [{
            data: qtdeOrdens,
            borderWidth: 0,
            backgroundColor: backgroundColor,
            // hoverBackgroundColor: [
            //    '#723ac3',
            //    "#864DD9",
            //    "#9762e6",
            //    "#a678eb"
            // ]
         }]
      }
   });
   <?php endif; ?>

   <?php if(! empty($produtosMaisVendidos)): ?>
   // ------------------------------------------------------- //
   // Pie Chart 1
   // ------------------------------------------------------ //
   var nomesProdutos = [];
   var qtdeProdutos = [];
   var backgroundColor = [];

   <?php foreach ($produtosMaisVendidos as $produto): ?>
      nomesProdutos.push('<?php echo $produto->nome; ?>');
      qtdeProdutos.push('<?php echo $produto->qtde; ?>');
      backgroundColor.push('#' + Math.floor(Math.random() * 16777215).toString(16));
   <?php endforeach; ?>

   var PIECHART = $('#pieChartHome1');
   var myPieChart = new Chart(PIECHART, {
      type: 'doughnut',
      options: {
         responsive: true,
         cutoutPercentage: 90,
         legend: {
            display: false
         }
      },
      data: {
         labels: nomesProdutos,
         datasets: [{
            data: qtdeProdutos,
            borderWidth: [0, 0, 0, 0],
            backgroundColor: backgroundColor,
            // hoverBackgroundColor: [
            //    '#6933b9',
            //    "#8553d1",
            //    "#a372ec",
            //    "#be9df1"
            // ]
         }]
      }
   });
   <?php endif; ?>

   <?php if(! empty($servicosMaisVendidos)): ?>
   // ------------------------------------------------------- //
   // Pie Chart 2
   // ------------------------------------------------------ //
   var nomesServicos = [];
   var qtdeServicos = [];
   var backgroundColor = [];

   <?php foreach ($servicosMaisVendidos as $servico): ?>
      nomesServicos.push('<?php echo $servico->nome; ?>');
      qtdeServicos.push('<?php echo $servico->qtde; ?>');
      backgroundColor.push('#' + Math.floor(Math.random() * 16777215).toString(16));
   <?php endforeach; ?>

   var PIECHART = $('#pieChartHome2');
   var myPieChart = new Chart(PIECHART, {
      type: 'doughnut',
      options: {
         responsive: true,
         cutoutPercentage: 90,
         legend: {
            display: false
         }
      },
      data: {
         labels: nomesServicos,
         datasets: [{
            data: qtdeServicos,
            borderWidth: [0, 0, 0, 0],
            backgroundColor: backgroundColor,
            // hoverBackgroundColor: [
            //    '#9528b9',
            //    "#b046d4",
            //    "#c767e7",
            //    "#e394fe"
            // ]
         }]
      }
   });
   <?php endif; ?>

   <?php if(! empty($atendimentosPorMes)): ?>
   // ------------------------------------------------------- //
   // Pie Chart 3
   // ------------------------------------------------------ //
   var nomesMes = [];
   var totalOrdens = [];
   var backgroundColor = [];

   <?php foreach ($atendimentosPorMes as $atendimento): ?>
      nomesMes.push('<?php echo $atendimento->mes_nome; ?>');
      totalOrdens.push('<?php echo $atendimento->total_ordens; ?>');
      backgroundColor.push('#' + Math.floor(Math.random() * 16777215).toString(16));
   <?php endforeach; ?>

   var PIECHART = $('#pieChartHome3');
   var myPieChart = new Chart(PIECHART, {
      type: 'doughnut',
      options: {
         responsive: true,
         cutoutPercentage: 90,
         legend: {
            display: false
         }
      },
      data: {
         labels: nomesMes,
         datasets: [{
            data: totalOrdens,
            borderWidth: [0, 0, 0, 0],
            backgroundColor: backgroundColor,
            // hoverBackgroundColor: [
            //    '#da4d60',
            //    "#e96577",
            //    "#f28695",
            //    "#ffb6c1"
            // ]
         }]
      }
   });
   <?php endif; ?>
});
</script>

<?php echo $this->endSection(); ?>