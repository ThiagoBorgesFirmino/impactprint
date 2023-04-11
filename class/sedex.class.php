<?php

class sedex {

	var
		$cepSaida
		,$cepDestino
		,$enderecoSaida
		,$enderecoDestino
		,$valor /* valor formato: 100,00 */
		,$peso
		,$valorFrete
		,$ok;

	public function calcula(){

		$CepOrig		= str_replace(array(' ','-'),'',$this->cepSaida) ;
		$CepDest		= str_replace(array(' ','-'),'',$this->cepDestino) ;
		$PesoTotal		= $this->peso;
		$ValorDeclarado	= $this->valor;
		$metodo			= "leitura";

		$this->ok = true ;

		if (strlen($CepDest) != 8 ){
			$this->ok = false ;
			return ;
		}

	//	$url = "https://cartao.locaweb.com.br/correios/calcula_sedex.asp?cepOrig={$CepOrig}&cepDest={$CepDest}&pesoDeclarado={$PesoTotal}&vlrDeclarado={$ValorDeclarado}&metodo={$metodo}" ;
		$url = "cartao.locaweb.com.br/correios/calcula_sedex.asp?cepOrig={$CepOrig}&cepDest={$CepDest}&pesoDeclarado={$PesoTotal}&vlrDeclarado={$ValorDeclarado}&metodo={$metodo}" ;
			

		if(function_exists('curl_init')) {
			$sh = curl_init($url);
			curl_setopt($sh, CURLOPT_RETURNTRANSFER, 1);
			$file = curl_exec($sh);
			curl_exec($sh) ;
			curl_close($sh) ;
		}
		else {
			$file = file_get_contents($url);
		}

		// print $file;
		
		if( ! $file ){
			$this->frete = "0";
			return ;
		}

		$file = iconv('iso-8859-1','utf-8',$file);

		//$retorna = $file[0];
		$arrLinhas = explode("<br>", $file);

		$i = 0;
		foreach ($arrLinhas as $line) {

			if ($i == 0 
			&& @ereg("^OK$", $line)) {
				//ok
			}
			elseif ($i == 0) {
				$this->ok = false ;
				return ;
			}

			@list($variavel, $valor) = explode(':', ($line));
			$variavel = strtolower(trim($variavel));

			@$$variavel = trim($valor) ;

			$i ++;
		}

		$this->enderecoDestino = new stdClass();

		$this->enderecoDestino->logradouro = $endereco ;
		$this->enderecoDestino->bairro 	  = $bairro ;
		$this->enderecoDestino->cidade	= $cidade ;
		$this->enderecoDestino->uf	= $uf ;
		$this->enderecoDestino->cep		= $cep;
		$this->valorFrete = toFloat($frete);

	}


}

?>