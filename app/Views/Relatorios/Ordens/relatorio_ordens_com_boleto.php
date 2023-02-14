<style>
   #body-pdf {
      font-family: Arial, Helvetica, sans-serif;
   }

   #pdf {
      font-family: Arial, Helvetica, sans-serif;
      border-collapse: collapse;
      width: 100%;
   }

   #pdf td,
   #pdf th {
      border: 1px solid #ddd;
      padding: 8px;
   }

   #pdf tr:nth-child(even) {
      background-color: #f2f2f2;
   }

   #pdf tr:hover {
      background-color: #ddd;
   }

   #pdf th {
      padding-top: 12px;
      padding-bottom: 12px;
      text-align: left;
      background-color: #04AA6D;
      color: white;
   }

   .color {
      color: #04AA6D;
   }
</style>

<div id="body-pdf">

   <div>
      <h4 class="color" style="text-align: center;"><?php echo $titulo; ?></h4>
      <h5 class="color" style="text-align: center;"><?php echo $periodo; ?></h4>
   </div>

   <?php if(empty($ordens)): ?>

   <h3 class="color" style="text-align: center;">Não há Ordens de Serviços Processadas com Boleto nesse período.</h3>

   <?php else: ?>
      
   <table id="pdf">
      <thead>
         <tr>
            <th scope="col" style="text-align: center;">Ordem</th>
            <th scope="col" style="text-align: center;">ID Boleto</th>
            <th scope="col" style="text-align: center;">Data de Vencimento</th>
            <th scope="col" style="text-align: center;">Situação</th>
            <th scope="col" style="text-align: center;">Valor da Ordem</th>
            
         </tr>
      </thead>
      <tbody>
         
         <?php foreach($ordens as $ordem): ?>
         <tr>
            <td><?php echo esc($ordem->codigo); ?></td>
            <td><?php echo esc($ordem->charge_id); ?></td>
            <td style="text-align: center;"><?php echo date('d/m/Y', strtotime($ordem->expire_at)); ?></td>
            <td style="text-align: center;"><?php echo $ordem->exibeSituacao(); ?></td>
            <td style="text-align: right;"><?php echo number_format($ordem->valor_ordem,2); ?></td>
         </tr>
         <?php endforeach; ?>

      </tbody>

   </table>
   <?php endif; ?>

</div>