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
      <!--- <img src="<?php echo site_url('recursos/img/logo.png'); ?>" style="width: 150px; height: 150px;"> --->
      <h3 style="text-align: center;"><strong class="color">OS: </strong><?php echo esc($ordem->codigo); ?></h3>
      <hr>
      <h3><strong class="color">Cliente: </strong><?php echo esc($ordem->nome); ?></h3>
      
      <p><strong class="color">Situação: </strong><?php echo $ordem->exibeSituacao(); ?></p>
      <p><strong class="color">Aberta por: </strong><?php echo esc($ordem->usuario_abertura); ?></p>
      <p><strong class="color">Responsável técnico: </strong><?php echo ($ordem->usuario_responsavel != null ? esc($ordem->usuario_responsavel) : "Não definido!"); ?></p>

      <?php if($ordem->situacao === 'encerrada'): ?>
         <p><strong class="color">Encerrada por: </strong><?php echo esc($ordem->usuario_encerramento); ?></p>
      <?php endif; ?>

      <p><strong class="color">Criada em: </strong><?php echo $ordem->criado_em->humanize(); ?></p>
      <p><strong class="color">Atualizada em: </strong><?php echo $ordem->atualizado_em->humanize(); ?></p>
   </div>
</div>

<?php if(empty($ordem->itens)): ?>
   <h3 style="text-align: center;"><strong class="color">Ainda não tem itens cadastrados</h3>
<?php else: ?>
   <table  id="pdf">
      <thead>
         <tr>
            <th scope="col">Item</th>
            <th scope="col" style="text-align: center;">Tipo</th>
            <th scope="col" style="text-align: center;">Preço unitário</th>
            <th scope="col" style="text-align: center;">Qtde item</th>
            <th scope="col" style="text-align: center;">Subtotal</th>
         </tr>
      </thead>
      <tbody>
         <?php 
            $valorProdutos = 0;
            $valorServicos = 0;
         ?>
         <?php foreach($ordem->itens as $item): ?>

            <?php
               if ($item->tipo === 'produto') {
                  $valorProdutos += $item->preco_venda * $item->item_quantidade;
               } else {
                  $valorServicos += $item->preco_venda * $item->item_quantidade;
               }
            ?>

            <tr>                     
               <td><?php echo ellipsize($item->nome, 32, .5); ?></td>
               <td style="text-align: center;"><?php echo esc(ucfirst($item->tipo)); ?></td>
               <td style="text-align: right;">R$ <?php echo esc(number_format($item->preco_venda, 2)); ?></td>
               <td style="text-align: right;"><?php echo $item->item_quantidade; ?></td>
               <td style="text-align: right;">R$ <?php echo esc(number_format($item->item_quantidade * $item->preco_venda, 2)); ?></td>
            </tr>
         <?php endforeach; ?>
      </tbody>

      <tfoot>
         <tr>
            <td colspan="4" style="text-align: right;">
               <label>Valor dos produtos:</label>
            </td>
            <td style="text-align: right;">R$ <?php echo esc(number_format($valorProdutos, 2)); ?></td>
         </tr>
         <tr>
            <td colspan="4" style="text-align: right;">
               <label>Valor dos serviços:</label>
            </td>
            <td style="text-align: right;">R$ <?php echo esc(number_format($valorServicos, 2)); ?></td>
         </tr>
         <tr>
            <td colspan="4" style="text-align: right;">
               <label>Valor total sem desconto:</label>
            </td>
            <td style="text-align: right;">R$ <?php echo esc(number_format($valorProdutos + $valorServicos, 2)); ?></td>
         </tr>
         <tr>
            <td colspan="4" style="text-align: right;">
               <label>Valor do desconto:</label>
            </td>
            <td style="text-align: right;">R$ <?php echo esc(number_format($ordem->valor_desconto, 2)); ?></td>
         </tr>                              
         <tr>
            <td colspan="4" style="text-align: right;">
               <label>Valor total da OS:</label>
            </td>
            <td style="text-align: right;">R$ <?php echo esc(number_format(($valorProdutos + $valorServicos) - $ordem->valor_desconto, 2)); ?></td>
         </tr>
      </tfoot>
   </table>
<?php endif; ?>