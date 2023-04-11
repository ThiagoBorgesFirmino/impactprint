<?php

class cep {

	var 
		$logradouro,
       	$bairro,
       	$cidade,
       	$estado,
       	$cep,
		$frete,
		$ok;
		
	/*	
	valor formato: 100,00	
	*/
		
	function cep($cep_numero=null,$valor=0,$peso=1){
		
		$CepOrig		= "04548005";
		$CepDest		= trim($cep_numero);
		$PesoTotal		= $peso;
		$ValorDeclarado	= $valor;
		$metodo			= "leitura";

		$this->ok =false ;

		if ( strlen( $CepDest ) != 8 ){
			$this->ok = false ;
			return ;
		}

		$url = 'http://cartao.locaweb.com.br/correios/calcula_sedex.asp?cepOrig='.$CepOrig.'&cepDest='.$CepDest.'&pesoDeclarado='.$PesoTotal.'&vlrDeclarado='.$ValorDeclarado.'&metodo='.$metodo ;

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

		if( ! $file ){
			$this->frete = "0";
			return ;
		}

		//$retorna = $file[0];
		$arrLinhas = explode("<br>", $file);
		
		$i = 0;
		foreach ($arrLinhas as $line) {
			
			if ($i == 0 && ereg("^OK$", $line)) {
				//ok
			} 
			elseif ($i == 0) {			
				//echo 'ERRO: '. $msgErro;
				$this->ok = false ;
				return ;			
			} 			
			
			list($variavel, $valor) = explode(':', ($line));
			$variavel = strtolower(trim($variavel));
			
			$ $variavel = trim($valor) ;
			
			$i ++;
		}
		
		$this->logradouro = $endereco ;
		$this->bairro 	  = $bairro ;
		$this->cidade			= $cidade ;
		$this->estado			= $uf ;
		$this->cep				= $cep;
		$this->frete			= str_replace(",",".",$frete);		
		$this->ok 				= true ;
		
	}

}
?>