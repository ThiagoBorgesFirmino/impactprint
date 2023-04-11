<?php

class carrinhodesejo {

	// Construtor
	function __construct(){
		if ( ! @$_SESSION['S_CARRINHODESEJO']
			|| ! is_array($_SESSION['S_CARRINHODESEJO']) ){
			$_SESSION['S_CARRINHODESEJO'] = array();
		}
	}

	// Associa o desejo ao pedido
	public function setDesejo($desejo){
		$_SESSION['S_CARRINHODESEJO']['desejo'] = $desejo;
	}
	
	// Retorna objeto do vale presente
	public function getDesejo(){
		// printr($_SESSION);
		
		if( array_key_exists('S_CARRINHODESEJO',$_SESSION) && is_array($_SESSION['S_CARRINHODESEJO']) && array_key_exists('desejo',@$_SESSION['S_CARRINHODESEJO'])){
			return @$_SESSION['S_CARRINHODESEJO']['desejo'];
		}
	}
	
	// Limpa desejo
	public function clearDesejo(){
		unset($_SESSION['S_CARRINHODESEJO']);
	}
}

?>
