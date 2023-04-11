<?php

class TemplatePedido extends Template {

	public function __construct($filename, $accurate = false){

		parent::__construct('tpl.base-pedido.html');

		$this->addFile('miolo',$filename);

		$this->path_site = PATH_SITE;
		$this->token = session_id();
		//$this->server_request_uri = $_SERVER['REQUEST_URI'];
		
		$this->configuracao = new configuracao();
		$this->carrinho = new carrinho();

		if(array_key_exists('erro', $_SESSION)){
			$this->erro = $_SESSION['erro'];
			$this->parseBlock('BLOCK_MSG_ERRO');
			unset($_SESSION['erro']);
		}

		if(array_key_exists('sucesso', $_SESSION)){
			$this->sucesso = $_SESSION['sucesso'];
			$this->parseBlock('BLOCK_MSG_SUCESSO');
			unset($_SESSION['sucesso']);
		}

	}
}

?>