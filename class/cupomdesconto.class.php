<?php

// Modelo de dados para a tabela cupomdesconto
class cupomdesconto extends base {

	var
		$id
		,$cadastro_id
		,$categoria_id
		,$marca_id
		,$st_ativo
		,$st_codigo_automatico
		,$nome
		,$codigo
		,$tipo_validade
		,$tipo_cupom
		,$tipo_desconto
		,$porcentagem
		,$valor
		,$comboitem_id
		,$comboitem_preco
		,$qtd_max_utilizacao
		,$dt_validade
		,$qtd_utilizacoes
		,$dt_cadastro
		,$dt_alteracao;


	// const TIPO_CUPOM_DESCONTO_MARCA = 'MAR';
	// const TIPO_CUPOM_DESCONTO_CATEGORIA = 'MAR';

	// 'DP' => 'Desconto por porcentagem'

			// ,'DV' => 'Desconto por valor'
			// ,'C' => 'Combo de produto'

			// ,'NC' => 'Novo cadastro'
			// ,'AN' => 'Assinar newsletter'


			// ,'MAR' => 'Desconto por marca'
			// ,'CAT' => 'Desconto por categoria'

	//
	public function salva(){
		$novo = !$this->id;
		if($novo){
			$this->dt_cadastro = bd_now();
		}
		if(parent::salva()){
			//
			if($novo){
				if($this->st_codigo_automatico == 'S' ){
					$codigo = md5('cupomdesconto'.$this->id.uniqid());
					$this->codigo = $codigo;
					query("UPDATE cupomdesconto SET codigo = '{$codigo}' WHERE id = {$this->id}");
				}
			}
			return true;
		}
		return false;
	}
	

	// Utilizou
	public function utilizou(){
		if($this->id){
			query("UPDATE cupomdesconto SET qtd_utilizacoes = qtd_utilizacoes+1 WHERE id = {$this->id}");
		}
	}

	// Checa se o cupom esta vencido
	public function isCupomVencido(){
		// ret
		$id = intval($this->id);
		$sql = "SELECT 1, dt_validade FROM cupomdesconto WHERE id = {$id} AND (tipo_validade = 'D' OR tipo_validade = 'QD')  AND dt_validade<CURDATE()";
		return rows(query($sql))==1;
	}

	// Checa se pode utilizar
	public function podeUtilizar(){

	}

	// Valida dados antes de salvar
	public function validaDados(&$erro=array()){




		return sizeof($erro)==0;
	}

	//
	public function getLink(){
		return config::get('URL').'index.php/cupom/'.$this->codigo;
	}

	// Opcoes do tipo de validade
	// 'D=Data/Hora, Q=Quantidade'
	static function opcoes_tipo_validade(){

		return array(
			'D' => 'Data/Hora'
			,'Q' => 'Quantidade de utilizações'
		);

	}

	// Opcoes do tipo de cupom
	// 'DP=Desconto porcentagem, DV=Desconto valor, C=Combo produto'
	static function opcoes_tipo_cupom(){

		return array(

			'DP' => 'Desconto por porcentagem'

			,'DV' => 'Desconto por valor'
			,'C' => 'Combo de produto'

			,'NC' => 'Novo cadastro'
			,'AN' => 'Assinar newsletter'


			,'MAR' => 'Desconto por marca'
			,'CAT' => 'Desconto por categoria'

		);

	}

	// Opcoes do tipo de desconto
	static function opcoes_tipo_desconto(){

		return array(

			'DP' => 'Desconto por porcentagem'
			,'DV' => 'Desconto por valor'
		);

	}


	// Valida a utilizacao do cupom de desconto
	public function validaUtilizacao(&$erro=''){

		$erro = '';

		// Checa se esta ativo
		if(!($this->st_ativo=='S')){
			$erro = 'Cupom de desconto não está ativo';
		}

		// Checa validade do cupom de desconto
		else if($this->tipo_validade == 'D'){
			if($this->isCupomVencido()){
				$erro = 'Este cupom de desconto já está vencido';
			}
		}

		// Sendo a validacao em funcao da quantidade de utilizacoes, checa se ja chegou ao numero maximo
		else if($this->tipo_validade == 'Q'){
			if((($this->qtd_utilizacoes+1) > $this->qtd_max_utilizacao)){
				$erro = 'Este cupom já chegou a quantidade máxima de utilizações';
			}
		}
		
		// Verifica velidade do cupom e quantidade de utilizações
		else if($this->tipo_validade == 'QD'){
			if($this->isCupomVencido()){
				$erro = 'Este cupom de desconto já está vencido';
			}elseif((($this->qtd_utilizacoes+1) > $this->qtd_max_utilizacao)){
				$erro = 'Este cupom já chegou a quantidade máxima de utilizações';
			}
		}

		// Retorna true em caso de nenhum erro
		return $erro == '';

	}

	// Retorna valor de desconto
	public function getValorDesconto($valor_pedido){
		if($this->tipo_desconto=='DP'){		// Desconto por porcentagem
			return $valor_pedido * ($this->porcentagem/100);
		}
		elseif($this->tipo_desconto=='DV'){ // Desconto por valor
			return $this->valor;
		}
		
		//return 0;
	}

	//
	public function getDataCadastroFormatado(){
		// printr($this);
		return formata_datahora_br($this->dt_cadastro);
	}
	
	public function getDataValidadeFormatado(){
		if($this->isCupomVencido()){
			return  "<span style='color:red; font-size:10px;'>CUPOM EXPIROU<br />".formata_datahora_br($this->dt_validade)."</span>";
		}else{
			return formata_datahora_br($this->dt_validade);
		}
	}

	public function getValorFormatado(){
		return money($this->valor);
	}

	public function getJaUtilizadoFormatado(){
		return $this->qtd_utilizacoes>0?'Sim':'Não';
	}


	// set('DP','DV','C','MAR','CAT')
	public function isTipoMarca(){
		return $this->tipo_cupom == 'MAR';
	}

	public function isTipoCategoria(){
		return $this->tipo_cupom == 'CAT';
	}
	
	// Executa exclusao
	public function exclui(){
		// asdf
		if($this->id>0){

			// Checa se existem pedidos relacionados
			if(rows(query("select * from pedido where cupomdesconto_id = {$this->id}"))>0){
				throw new Exception("Pedidos relacionados, não é possível excluir");
			}

			// Executa exclusao
			return parent::exclui();

		}
	}

}

?>