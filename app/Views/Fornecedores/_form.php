<div class="row">
   <div class="form-group col-md-12">
      <label class="form-control-label">Razão Social</label>
      <input type="text" name="razao" placeholder="Insira a razão social" class="form-control" value="<?php echo esc($fornecedor->razao); ?>">
   </div>

   <div class="form-group col-md-4">
      <label class="form-control-label">CNPJ</label>
      <input type="text" name="cnpj" placeholder="Insira o CNPJ" class="form-control cnpj" value="<?php echo esc($fornecedor->cnpj); ?>">
   </div>

   <div class="form-group col-md-4">
      <label class="form-control-label">Inscrição estadual</label>
      <input type="text" name="ie" placeholder="Insira a IE" class="form-control" value="<?php echo esc($fornecedor->ie); ?>">
   </div>

   <div class="form-group col-md-4">
      <label class="form-control-label">Telefone</label>
      <input type="text" name="telefone" placeholder="Insira o telefone" class="form-control sp_celphones" value="<?php echo esc($fornecedor->telefone); ?>">
   </div>

   <div class="form-group col-md-4">
      <label class="form-control-label">Cep</label>
      <input type="text" name="cep" placeholder="Insira o CEP" class="form-control cep" value="<?php echo esc($fornecedor->cep); ?>">
      <div id="cep"></div>
   </div>

   <div class="form-group col-md-8"></div>

   <div class="form-group col-md-10">
      <label class="form-control-label">Endereço</label>
      <input type="text" name="endereco" placeholder="Insira o endereço" class="form-control" value="<?php echo esc($fornecedor->endereco); ?>" readonly>
   </div>
   <div class="form-group col-md-2">
      <label class="form-control-label">Nº</label>
      <input type="text" name="numero" placeholder="Insira o Nº" class="form-control" value="<?php echo esc($fornecedor->numero); ?>">
   </div>

   <div class="form-group col-md-4">
      <label class="form-control-label">Bairro</label>
      <input type="text" name="bairro" placeholder="Insira o bairro" class="form-control" value="<?php echo esc($fornecedor->bairro); ?>" readonly>
   </div>

   <div class="form-group col-md-6">
      <label class="form-control-label">Cidade</label>
      <input type="text" name="cidade" placeholder="Insira a cidade" class="form-control" value="<?php echo esc($fornecedor->cidade); ?>" readonly>
   </div>

   <div class="form-group col-md-2">
      <label class="form-control-label">Estado</label>
      <input type="text" name="estado" placeholder="UF" class="form-control" value="<?php echo esc($fornecedor->estado); ?>" readonly>
   </div>      

</div>

<div class="custom-control custom-checkbox">

   <input type="hidden" name="ativo" value="0">
   
   <input type="checkbox" name="ativo" value="1" class="custom-control-input" id="ativo" <?php if($fornecedor->ativo == true): ; ?> checked <?php endif; ?>>

   <label class="custom-control-label" for="ativo">Usuário Ativo</label>
</div>
