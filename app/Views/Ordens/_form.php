<div class="user-block">

   <div class="form-row mb-4">

      <div class="col-md-12">

         <?php if ($ordem->id === null): ?>

            <div class="contributions">OS aberta por: <?php echo usuario_logado()->nome; ?></div>
         <?php else: ?>

            <div class="contributions">OS aberta por: <?php echo esc($ordem->usuario_abertura); ?></div>

            <?php if ($ordem->usuario_responsavel !== null): ?>

               <div class="contributions">Técnico responsável: <?php echo esc($ordem->usuario_responsavel); ?></div>
            <?php endif; ?>
         <?php endif; ?>
      </div>
   </div>

   <?php if ($ordem->id === null): ?>

      <div class="form-group">
         <label class="form-control-label">Escolha o cliente</label>

         <select name="cliente_id" class="selectize">

            <option value="">Digite o nome do cliente ou o CPF...</option>
         </select>
      </div>
   <?php else: ?>

      <div class="form-group">
         <label class="form-control-label">Cliente</label>
         <a tabindex="0" style="text-decoration: none ;" role="button" data-toggle="popover" data-trigger="focus"
            title="Importante" data-content="Não é possível editar o cliente da ordem de serviço.">
            &nbsp;<i class="fa fa-question-circle text-info fa-lg"></i>
         </a>
         <input type="text" class="form-control" disabled readonly value="<?php echo esc($ordem->nome); ?>">
      </div>
   <?php endif; ?>


   <div class="form-group">
      <label class="form-control-label">Equipamento</label>
      <input type="text" name="equipamento" placeholder="Descreva o equipamento" class="form-control"
         value="<?php echo esc($ordem->equipamento); ?>">
   </div>

   <div class="form-group">
      <label class="form-control-label">Defeitos do equipamento</label>
      <textarea name="defeito" placeholder="Descreva os defeitos do equipamento"
         class="form-control"><?php echo esc($ordem->defeito); ?></textarea>
   </div>

   <div class="form-group">
      <label class="form-control-label">Observações da OS</label>
      <textarea name="observacoes" placeholder="Informe as observações"
         class="form-control"><?php echo esc($ordem->observacoes); ?></textarea>
   </div>

   <?php if ($ordem->id): ?>
      <div class="form-group">
         <label class="form-control-label">Parecer técnico</label>
         <textarea name="parecer_tecnico" placeholder="Informe o parecer técnico"
            class="form-control"><?php echo esc($ordem->parecer_tecnico); ?></textarea>
      </div>
   <?php endif; ?>

</div>
