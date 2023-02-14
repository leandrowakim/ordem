<?php

namespace App\Traits;

trait ValidacoesTrait
{
   public function consultaViaCep(string $cep) : array 
   {

      //Limpamos o cep
      $cep = str_replace('-', '', $cep);

      $url = "https://viacep.com.br/ws/{$cep}/json/";

      //Abrir a conexão
      $ch = curl_init();

      //Defina a url
      curl_setopt($ch, CURLOPT_URL, $url);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

      //Executamos a consulta
      $resposta = curl_exec($ch);

      //Capituramos um possível erro na consulta
      $erro = curl_error($ch);

      $retorno = [];

      if ($erro) {         
         
         $retorno['erro'] = $erro;
         return $retorno;
      }

      $consulta = json_decode($resposta);

      if (isset($consulta->erro) && !isset($consulta->cep)) {
         
         //Usaremos no controller
         session()->set('blockCep', true);         
         $retorno['erro'] = '<span class="text-danger">Informe um CEP válido</span>';
         return $retorno;
      }

      session()->set('blockCep', false);

      $retorno['endereco'] = esc($consulta->logradouro);
      $retorno['bairro'] = esc($consulta->bairro);
      $retorno['cidade'] = esc($consulta->localidade);
      $retorno['estado'] = esc($consulta->uf);

      return $retorno;
   }

   public function checkEmail(string $email, bool $bypass = false) : array
   {
      $retorno = [];

      if ($bypass) 
      {         
         return $retorno;
      }
      
      $curl = curl_init();

      curl_setopt_array($curl, array
         (
            CURLOPT_URL => "https://mailcheck.p.rapidapi.com/?domain={$email}",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array
            (
               "x-rapidapi-host: mailcheck.p.rapidapi.com",
               "x-rapidapi-key: ".getenv('CHAVE_CHECK_MAIL_ORG_API')
            ),
         )
      );
      
      $resposta = curl_exec($curl);
      $erro= curl_error($curl);

      curl_close($curl);

      if ($erro === null) 
      {         
         $retorno['erro'] = "cURL Error #: " . $erro;
         return $retorno;
      }

      $consulta = json_decode($resposta);

      //Usaremos no controller
      session()->set('blockEmail', esc($consulta->block));

      if ($consulta->block) {

         $retorno['erro'] = '<span class="text-danger">O domínio [ ' . $consulta->domain . ' ] não é válido. Tente novamente.</span>';
         return $retorno;
      }

      return $retorno;
   }

   public function tiraVirgulaValor(string $valor) : string
   {
      $novoValor = str_replace(",", "", $valor);
      return $novoValor;
   }

}