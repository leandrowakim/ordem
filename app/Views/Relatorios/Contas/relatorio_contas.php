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
      <?php if(isset($periodo)): ?>
         <h5 class="color" style="text-align: center;"><?php echo $periodo; ?></h4>
      <?php endif; ?>   
   </div>

   <?php if(empty($contas)): ?>

   <h3 class="color" style="text-align: center;">Não há dados para serem exibidos no momento.</h3>

   <?php else: ?>
      
   <table id="pdf">
      <thead>
         <tr>
            <th scope="col" style="text-align: center;">Fornecedor</th>
            <th scope="col" style="text-align: center;">Situação</th>
            <th scope="col" style="text-align: center;">Valor da Conta</th>            
         </tr>
      </thead>
      <tbody>
         
         <?php foreach($contas as $conta): ?>
         <tr>
            <td><?php echo esc($conta->razao) . ' - CNPJ: ' . esc($conta->cnpj); ?></td>
            <td style="text-align: center;"><?php echo $conta->exibeSituacao(); ?></td>
            <td style="text-align: right;"><?php echo number_format($conta->valor_conta,2); ?></td>
         </tr>
         <?php endforeach; ?>

      </tbody>

   </table>
   <?php endif; ?>

</div>