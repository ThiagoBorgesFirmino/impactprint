<?php

class upload {
	
	public
		$diretorio ;
		
	public
		$objFile ;
		
	private
		$type ;
	
	private
		$erros = array();
		
	private
		$validacaoOk = false;
		
		/**
		 * Implementar outras constantes para validar outros tipos de arquivo
		 *
		 */
	const TYPE_IMAGE = 'image\/(pjpeg|jpeg|png|gif|bmp)' ;
		
	/**
	 * Seta o type do arquivo que estiver vindo, usado na validacao, setar com uma constant TYPE_* 
	 *
	 * @param string $type
	 * @return void
	 */
	public function set_type($type){
		$this->type = $type	;
	}
			
	public function setFile($file){
		$this->objFile = $file;
	}

	public function setDiretorio($diretorio){
		$this->diretorio = $diretorio;
	}

	/**
	 * Valida o $file e $type do arquivo
	 *
	 * @return true
	 */
	public function valida(){
	
		$return = false ;
			
		if ( ! $this->objFile ){
			$this->erros[] = 'Erro interno' ;
		}
		elseif( ! eregi("^{$this->type}$", $this->objFile['type'])){
		 	$this->erros[] = "Arquivo em formato inv&aacute;ido! A imagem deve ser jpg, jpeg" ;
		}
		elseif ( ! is_dir($this->diretorio) ) {
			$this->erros[] = 'Erro interno' ;
		}
		else {
			$return = true ;
			$this->validacaoOk = true ;	
		}
			
		return $return ;
	}
	
	/**
	 * Move o arquivo para a pasta oficial
	 * 
	 * @return void
	 */
	public function grava($name=''){
		if($name==''){
			$name = $this->objFile['name'];
		}

		if(!is_dir($this->diretorio)) mkdir($this->diretorio,0777);
		copy($this->objFile["tmp_name"], $this->diretorio.$name);
	}
	
	/**
	 * Retorna um array de erros
	 *
	 * @return array
	 */
	public function getErros(){
		return ( $this->erros );
	}
	
}

?>