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

   <?php if(empty($itens)): ?>

   <h3 class="color" style="text-align: center;">Não há informações de itens mais vendidos na base de dados</h3>

   <?php else: ?>
      
   <table id="pdf">
      <thead>
         <tr>
            <th scope="col">Item</th>
            <th scope="col" style="text-align: center;">Código interno</th>
            <th scope="col" style="text-align: center;">Tipo</th>
            <th scope="col" style="text-align: center;">Vendidos</th>
         </tr>
      </thead>
      <tbody>
         
         <?php foreach($itens as $item): ?>
         <tr>
            <td><?php echo word_limiter($item->nome, 10); ?></td>
            <td style="text-align: center;"><?php echo esc($item->codigo_interno); ?></td>
            <td style="text-align: center;"><?php echo esc(ucfirst($item->tipo)); ?></td>
            <td style="text-align: right;"><?php echo $item->quantidade; ?></td>
         </tr>
         <?php endforeach; ?>

      </tbody>

   </table>
   <?php endif; ?>

</div>