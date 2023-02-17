<div class="row">
   <div class="form-group col-md-12">
      <label class="form-control-label">Nome completo</label>
      <input type="text" name="nome" placeholder="Insira a nome completo" class="form-control" value="<?php echo esc($cliente->nome); ?>">
   </div>

   <div class="col-md-12">
      <div class="custom-control custom-radio mb-2">
         <input type="radio" class="custom-control-input" id="pf" name="pessoa" value="F" 
            <?php if($cliente->pessoa == 'F'): ; ?> checked <?php endif; ?>>
         <label class="custom-control-label" for="pf">Pessoa Física</label>
      </div>

      <div class="custom-control custom-radio mb-2">
         <input type="radio" class="custom-control-input" id="pj" name="pessoa" value="J" 
            <?php if($cliente->pessoa == 'J'): ; ?> checked <?php endif; ?>>
         <label class="custom-control-label" for="pj">Pessoa Jurídica</label>
      </div>
   </div>

   <div class="form-group col-md-4">
      <label class="form-control-label" id="labelCpf"><?php echo ($cliente->pessoa == 'F' ? 'CPF' : 'CNPJ'); ?></label>
      <input type="text" name="cpf_cnpj" id="inputCpf" placeholder="<?php echo ($cliente->pessoa == 'F' ? 'Insira o CPF' : 'Insira o CNPJ'); ?>" class="form-control" value="<?php echo esc($cliente->cpf_cnpj); ?>">
   </div>

   <div class="form-group col-md-4">
      <label class="form-control-label" id="labelRg"><?php echo ($cliente->pessoa == 'F' ? 'RG' : 'IE'); ?></label>
      <input type="text" name="rg_ie" id="inputRg" placeholder="<?php echo ($cliente->pessoa == 'F' ? 'Indira o RG' : 'Insira a IE'); ?>" class="form-control" value="<?php echo esc($cliente->rg_ie); ?>">
   </div>

   <div class="form-group col-md-6">
      <label class="form-control-label">E-mail (para acesso ao sistema)</label>
      <input type="text" name="email" placeholder="Insira o E-mail" class="form-control" value="<?php echo esc($cliente->email); ?>">
      <div id="email"></div>
   </div>

   <div class="form-group col-md-6">
      <label class="form-control-label">Telefone</label>
      <input type="text" name="telefone" placeholder="Insira o telefone" class="form-control sp_celphones" value="<?php echo esc($cliente->telefone); ?>">
   </div>

   <div class="form-group col-md-4">
      <label class="form-control-label">Cep</label>
      <input type="text" name="cep" placeholder="Insira o CEP" class="form-control cep" value="<?php echo esc($cliente->cep); ?>">
      <div id="cep"></div>
   </div>

   <div class="form-group col-md-8"></div>

   <div class="form-group col-md-6">
      <label class="form-control-label">Endereço</label>
      <input type="text" name="endereco" placeholder="Insira o endereço" class="form-control" value="<?php echo esc($cliente->endereco); ?>" readonly>
   </div>
   <div class="form-group col-md-2">
      <label class="form-control-label">Nº</label>
      <input type="text" name="numero" placeholder="Insira o Nº" class="form-control" value="<?php echo esc($cliente->numero); ?>">
   </div>

   <div class="form-group col-md-4">
      <label class="form-control-label">Complemento</label>
      <input type="text" name="complemento" placeholder="Insira o complemento" class="form-control" value="<?php echo esc($cliente->complemento); ?>">
   </div>

   <div class="form-group col-md-4">
      <label class="form-control-label">Bairro</label>
      <input type="text" name="bairro" placeholder="Insira o bairro" class="form-control" value="<?php echo esc($cliente->bairro); ?>" readonly>
   </div>

   <div class="form-group col-md-6">
      <label class="form-control-label">Cidade</label>
      <input type="text" name="cidade" placeholder="Insira a cidade" class="form-control" value="<?php echo esc($cliente->cidade); ?>" readonly>
   </div>

   <div class="form-group col-md-2">
      <label class="form-control-label">Estado</label>
      <input type="text" name="estado" placeholder="UF" class="form-control" value="<?php echo esc($cliente->estado); ?>" readonly>
   </div>      

</div>
