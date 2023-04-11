<?php

class carrinhoabandonado extends base{
	var
		$id
		,$session_id
		,$cadastro_id
		,$cupomdesconto_id
		,$st_abandonado
		,$carrinho
		,$valor_desconto
		,$link_carrinho
		,$data_cadastro
		,$codigo;

	public function salva(){
		$this->codigo = MD5('carrinhoabandonado'.$this->id.uniqid());
		parent::salva();
	}

	public function GeraCupom(){
		$config = new config();
		if(!$this->cupomdesconto_id){

			$cadastro = new cadastro($this->cadastro_id);

			$cupomdesconto = new cupomdesconto();
			$cupomdesconto->cadastro_id = $cadastro->id;
			$cupomdesconto->st_ativo = 'S';
			$cupomdesconto->st_codigo_automatico = 'S';
			$cupomdesconto->nome = "Cupom de desconto - {$cadastro->nome} - Bônus";
			$cupomdesconto->tipo_validade = 'Q';
			$cupomdesconto->tipo_desconto = 'DP';
			$cupomdesconto->porcentagem = $this->valor_desconto;
			$cupomdesconto->qtd_max_utilizacao = 1;
			$cupomdesconto->qtd_utilizacoes = 0;

			$cupomdesconto->salva();

			$this->cupomdesconto_id = $cupomdesconto->id;
			$this->link_carrinho = $config->URL."index.php/RecuperarCarrinhoAbandonado/{$cupomdesconto->codigo}/{$this->codigo}";

			$this->atualiza();

		}
	}

	public function EnviarEmail(){
		$config = new config();

		$this->valor_desconto = request('carrinho_desconto');

		$this->GeraCupom();

		//registra data de envio de e-mail e valor%
		$carrinhoabandonadoenviado = new carrinhoabandonadoenviado();
		$carrinhoabandonadoenviado->porcentagem = $this->valor_desconto;
		$carrinhoabandonadoenviado->data_envio = bd_now();
		$carrinhoabandonadoenviado->carrinhoabandonado_id = $this->id;
		$carrinhoabandonadoenviado->salva();


		$email = new email();
		$Temail = new Template('tpl.email-carrinhoabandonado.html');
		$cadastro = new cadastro($this->cadastro_id);

		$Temail->link_carrinho = $this->link_carrinho;
		$Temail->path = $config->URL;
		$Temail->desconto = $this->valor_desconto;

		// print $Temail->getContent();
		// die();

		$email->addHtml($Temail->getContent());
		$email->addTo($cadastro->email, $cadastro->nome);
		// $email->addCc('nazario@ajung.com.br', $cadastro->nome);
		// $email->addto('rafael21boy@hotmail.com', $cadastro->nome);

		$email->send("Recupere seu carrinho - ".$config->EMPRESA);

		$_SESSION['sucesso'] = tag('p','E-mail enviado ao cliente para recupera&ccedil;&atilde;o de carrinho.');

	}
	
	public function exclui(){
	
		if($this->id){
		
			query("DELETE FROM carrinhoabandonadoenviado WHERE carrinhoabandonado_id = {$this->id}");
		
			return parent::exclui();
		
		}
		
		return false;
	
	
	}
	
}

?>