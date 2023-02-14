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

   <?php if(empty($usuarios)): ?>

   <h3 class="color" style="text-align: center;">Não há dados para serem exibidos no momento.</h3>

   <?php else: ?>
      
   <table id="pdf">
      <thead>
         <tr>
            <th scope="col" style="text-align: center;">ID usuário</th>
            <th scope="col" style="text-align: center;">Nome</th>
            <th scope="col" style="text-align: center;">Qtde de Ordens</th>
         </tr>
      </thead>
      <tbody>
         
         <?php foreach($usuarios as $usuario): ?>
         <tr>
            <td><?php echo esc($usuario->id); ?></td>
            <td><?php echo esc($usuario->nome); ?></td>
            <td style="text-align: right;"><?php echo esc($usuario->qtde_ordens); ?></td>
         </tr>
         <?php endforeach; ?>

      </tbody>

   </table>
   <?php endif; ?>

</div>