<?php

class briefing extends base {

	var
		$id
		,$nome
		,$email
		,$empresa
		,$telefone
		,$tipo_acao_evento

		,$publico_masculino
		,$publico_feminino
		,$publico_unisex

		,$faixa_etaria_0_10
		,$faixa_etaria_11_16
		,$faixa_etaria_17_23
		,$faixa_etaria_24_32
		,$faixa_etaria_33_50
		,$faixa_etaria_51

		,$qtd_brindes
		,$verba_aproximada
		,$prazo_entrega

		,$obs;

	public function getPublico(){

		$tmp = array();

		if($this->publico_masculino=='S'){
			$tmp[] = 'Masculino';
		}
		if($this->publico_feminino=='S'){
			$tmp[] = 'Feminino';
		}
		if($this->publico_unisex=='S'){
			$tmp[] = 'Unisex';
		}

		return join(', ', $tmp);
	}

	public function getPublicoMasculinoChecked(){
		return $this->publico_masculino=='S'?'checked':'';
	}

	public function getPublicoFemininoChecked(){
		return $this->publico_feminino=='S'?'checked':'';
	}

	public function getPublicoUnisexChecked(){
		return $this->publico_unisex=='S'?'checked':'';
	}

	public function getFaixaEtaria(){

		$tmp = array() ;

		if($this->faixa_etaria_0_10=='S'){
			$tmp[] = '0 - 10 Anos';
		}
		if($this->faixa_etaria_11_16=='S'){
			$tmp[] = '11 - 16 Anos';
		}
		if($this->faixa_etaria_17_23=='S'){
			$tmp[] = '17 - 23 Anos';
		}
		if($this->faixa_etaria_24_32=='S'){
			$tmp[] = '24 - 32 Anos';
		}
		if($this->faixa_etaria_33_50=='S'){
			$tmp[] = '33 - 50 Anos';
		}
		if($this->faixa_etaria_51=='S'){
			$tmp[] = '51 ou mais';
		}

		return join(', ', $tmp);
	}

	public function getFaixaEtaria010Checked(){
		return $this->faixa_etaria_0_10=='S'?'checked':'';
	}

	public function getFaixaEtaria1116Checked(){
		return $this->faixa_etaria_11_16=='S'?'checked':'';
	}

	public function getFaixaEtaria1723Checked(){
		return $this->faixa_etaria_17_23=='S'?'checked':'';
	}

	public function getFaixaEtaria2432Checked(){
		return $this->faixa_etaria_24_32=='S'?'checked':'';
	}

	public function getFaixaEtaria3350Checked(){
		return $this->faixa_etaria_33_50=='S'?'checked':'';
	}

	public function getFaixaEtaria51Checked(){
		return $this->faixa_etaria_51=='S'?'checked':'';
	}

	public function validaDados(& $erros=array()){

		$algumErro = false;

		if(!is_set($this->nome)){
			$erros['nome'] = 'Digite seu nome corretamente';
		}
		if(!is_set($this->empresa)){
			$erros['empresa'] = 'Digite o nome da sua empresa';
		}
		if(!is_email($this->email)){
			$erros['email'] = 'Digite seu e-mail corretamente';
		}
		if(!is_set($this->telefone)){
			$erros['telefone'] = 'Digite seu telefone corretamente';
		}
		if(!is_set($this->tipo_acao_evento)){
			$erros['tipo_acao_evento'] = 'Digite o tipo de ação ou evento';
		}
		if((!$this->publico_masculino)
			&&(!$this->publico_feminino)
			&&(!$this->publico_unisex)){

			//$erros['publico'] = 'Selecione o publico desejado';

		}
		if((!$this->faixa_etaria_0_10)
			&&(!$this->faixa_etaria_11_16)
			&&(!$this->faixa_etaria_17_23)
			&&(!$this->faixa_etaria_24_32)
			&&(!$this->faixa_etaria_33_50)
			&&(!$this->faixa_etaria_51)){

			//$erros['faixa_etaria'] = 'Selecione a faixa estaria';

		}
		if(!is_set($this->qtd_brindes)){
			$erros['qtd_brindes'] = 'Digite a quantidade brindes';
		}
		if(!is_set($this->verba_aproximada)){
			$erros['verba_aproximada'] = 'Digite a verba aproximada';
		}
		if(!is_set($this->prazo_entrega)){
			$erros['prazo_entrega'] = 'Digite o prazo de entrega';
		}
		if(!is_set($this->obs)){
			$erros['obs'] = 'Digite suas observações';
		}

		return sizeof($erros)==0;
	}

}

?>