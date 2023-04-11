<?php

// Metodos uteis para controle da entrega
class entregautil {

	//
	static function setContextoCompra($cepEntrega, $estadoEntrega, $cidadeEntrega, $valorProdutos, $pesoProdutos, $qtdProdutos){
	}

	//
	static function setCepCache($cep){
	}

	// getOpcoesEntrega
	static function getOpcoesEntrega($cep='', $uf='', $cidade='', $peso=0, $valorProdutos=0, $itens_id=array()){

		$ret = array();

		// Variavel temporaria dos itens que vamos buscar no correio
		$tmp_correio = array();

		$sql = "SELECT id, st_ativo, st_fixo, st_correio, chave, nome, descricao, correio_cod_servico, frete_gratis_acima, prazo_entrega_padrao, imagem	FROM formaentrega WHERE st_ativo = 'S' ORDER BY nome";

		$query = query($sql);

		while($fetch=fetch($query)){

			$formaentrega = new formaentrega();
			$formaentrega->load_by_fetch($fetch);

			// Checa se existe um valor configurado para este CEP, pesquisando por regiao
			$formaentregaregiaovalor = self::getFormaentregaRegiaoValor($formaentrega->id, $cep);

			// Checa se existe um valor configurado para este estado e cidade, caso nao haja, nao prossegue
			$formaentregaufvalor = self::getFormaentregaUfValor($formaentrega->id, $uf);

			// Checa se existe um valor de frete gratis configurado para este estado e cidade
			$formaentregaufgratis = self::getFormaentregaUfGratis($formaentrega->id, $uf);

			// Checa se essa forma de entrega esta associada a todos os itens de compra para ser gratis


			// Caso a opcao seja associada aos correios, guarda informacao para buscar no correio depois, de uma vez só
			if($formaentrega->st_correio == 'S'){
				$tmp_correio[] =  array(
									'formaentrega'=>$formaentrega
									,'formaentregaregiaovalor'=>$formaentregaregiaovalor
									,'formaentregaufvalor'=>$formaentregaufvalor
									,'formaentregaufgratis'=>$formaentregaufgratis
								);
				continue;
			}


			if(!$formaentregaregiaovalor->id
			&& !$formaentregaufvalor->id ){
				continue;
			}

			$item_entrega = new item_entrega();

			$item_entrega->setFormaEntrega($formaentrega);
			$item_entrega->setFormaEntregaUFValor($formaentregaufvalor);
			$item_entrega->setFormaEntregaRegiaoValor($formaentregaregiaovalor);
			$item_entrega->setFormaEntregaUFGratis($formaentregaufgratis);

			$item_entrega->setItensId($itens_id);

			//
			$item_entrega->setCep($cep);
			$item_entrega->setUf($uf);
			$item_entrega->setCidade($cidade);
			$item_entrega->setValorProdutos($valorProdutos);

			$ret[] = $item_entrega;
		}


		// Busca apenas os itens do correio
		$codigo_servico_correios = '';
		foreach($tmp_correio as $tmp){
			if($codigo_servico_correios!=''){
				$codigo_servico_correios .= ',';
			}
			$codigo_servico_correios .= $tmp['formaentrega']->correio_cod_servico;
		}

		foreach(self::frete_correios($cep, $peso, $codigo_servico_correios) as $opcao_correio){

			foreach($tmp_correio as $tmp){

				$formaentrega = $tmp['formaentrega'];

				if(@$formaentrega->correio_cod_servico == @$opcao_correio->codigo){

					$formaentregaregiaovalor = $tmp['formaentregaregiaovalor'];
					$formaentregaufvalor = $tmp['formaentregaufvalor'];
					$formaentregaufgratis = $tmp['formaentregaufgratis'];

					$item_entrega = new item_entrega();
					$item_entrega->setFormaEntrega($formaentrega);

					$item_entrega->setValor($opcao_correio->valor);
					$item_entrega->setPrazo($opcao_correio->prazo);

					$item_entrega->setFormaEntregaUFValor($formaentregaufvalor);
					$item_entrega->setFormaEntregaRegiaoValor($formaentregaregiaovalor);
					$item_entrega->setFormaEntregaUFGratis($formaentregaufgratis);

					$item_entrega->setItensId($itens_id);

					//
					$item_entrega->setCep($cep);
					$item_entrega->setUf($uf);
					$item_entrega->setCidade($cidade);
					$item_entrega->setValorProdutos($valorProdutos);

					$ret[] = $item_entrega;
				}
			}
		}

		return $ret;

	}

	/*
	 *
	 * Essa função utiliza o cep do remetente fixo dentro da função
	 * Você especifica o cep destino e peso
	 * o terceiro parametro é como você quer o retorno:
	 * 'objeto', 'arrray', 'json'
	 *
	 */
	private function frete_correios($cep_destino, $peso='0.300', $codigo_servico_correios=''){

		try {

			// return array();

			if(floatval($peso)==0){
				$peso = '0.300';
			}

			// TRATA OS CEP'S
			$cep_destino = @eregi_replace("([^0-9])",'',$cep_destino);
			$cep_origem = str_replace( "-", "", config::get('CEP_SAIDA'));// CEP DE QUEM ESTÁ ENVIANDO

			// print $cep_origem;
			// die();

			/*
			* TIPOS DE FRETE
			41106 = PAC sem contrato
			40010 = SEDEX sem contrato
			40045 = SEDEX a Cobrar, sem contrato
			40215 = SEDEX 10, sem contrato
			40290 = SEDEX Hoje, sem contrato
			40096 = SEDEX com contrato
			40436 = SEDEX com contrato
			40444 = SEDEX com contrato
			81019 = e-SEDEX, com contrato
			41068 = PAC com contrato
			*/

			// ESTE ARRAYS PARA O RETORNO (NO MEU CASO SÓ QUERO MOSTRAR ESTES)
			$rotulo = array(
				'41106' => 'PAC'
				,'40010' => 'SEDEX'
				,'40045' => 'SEDEX'
				,'40215' => 'SEDEX 10'
				,'40290' => 'SEDEX'
				,'40096' => 'SEDEX'
				,'40436' => 'SEDEX'
				,'40444' => 'SEDEX'
				,'81019' => 'e-SEDEX'
				,'41068' => 'PAC'
			);

			//$webservice = 'http://shopping.correios.com.br/wbm/shopping/script/CalcPrecoPrazo.asmx?WSDL';// URL ANTIGA
			$webservice = 'http://ws.correios.com.br/calculador/CalcPrecoPrazo.asmx?WSDL';

			// TORNA EM OBJETO AS VARIAVEIS
			$parms = new stdClass;

			// $parms->nCdServico = '41068,40436,81019';// PAC, SEDEX E ESEDEX (TODOS COM CONTRATO) - se vc precisar de mais tipos adicione aqui
			// $parms->nCdServico = '40215,41106,40010';// PAC, SEDEX E ESEDEX (TODOS COM CONTRATO) - se vc precisar de mais tipos adicione aqui

			$parms->nCdServico = $codigo_servico_correios;
			// $parms->nCdServico = '40010,40045,40215,40290,41106';

			$login_empresa = config::get('CORREIOS_LOGIN_EMPRESA');
			$senha_empresa = config::get('CORREIOS_SENHA_EMPRESA');

			if($login_empresa!='' && $senha_empresa!='' ){
				//$parms->nCdEmpresa = $login_empresa;// <- LOGIN DO CADASTRO NO CORREIOS (OPCIONAL)
				$parms->nCdEmpresa = $login_empresa;// <- LOGIN DO CADASTRO NO CORREIOS (OPCIONAL)
				$parms->sDsSenha = $senha_empresa;// <- SENHA DO CADASTRO NO CORREIOS (OPCIONAL)
			}

			$parms->StrRetorno = 'xml';

			// DADOS DINAMICOS
			$parms->sCepDestino = $cep_destino;// CEP CLIENTE
			$parms->sCepOrigem = $cep_origem;// CEP DA LOJA (BD)
			$parms->nVlPeso = $peso;

			// VALORES MINIMOS DO PAC (SE VC PRECISAR ESPECIFICAR OUTROS FAÇA ISSO AQUI)
			$parms->nVlComprimento = '18';
			$parms->nVlDiametro = 5;
			$parms->nVlAltura = 2;
			$parms->nVlLargura = 11;

			// OUTROS OBRIGATORIOS (MESMO VAZIO)
			$parms->nCdFormato = 1;
			$parms->sCdMaoPropria = 'N';
			$parms->nVlValorDeclarado = 0;
			$parms->sCdAvisoRecebimento = 'N';

			// Inicializa o cliente SOAP
			$soap = new SoapClient($webservice, array(
				'trace' => true
				,'exceptions' => true
				,'compression' => SOAP_COMPRESSION_ACCEPT | SOAP_COMPRESSION_GZIP
				,'connection_timeout' => 1000
			));

			
			
			// Resgata o valor calculado
			$resposta = $soap->CalcPrecoPrazo($parms);			
			
			$objeto = $resposta->CalcPrecoPrazoResult->Servicos->cServico;
			

			$array = array();
			foreach($objeto as $obj){
				$tipo = isset($rotulo[$obj->Codigo]) ? strtolower($rotulo[$obj->Codigo]) : '';
				
				if($tipo!=''){

					if(trim($obj->MsgErro)=='' || $obj->MsgErro==0){
						$valor = floatval(str_replace(',','.',$obj->Valor));
						
						if($valor>0){
							$array[] = (object)array(
								'tipo' => strtoupper($tipo)
								,'codigo' => $obj->Codigo
								,'valor' => $valor
								,'prazo' => $obj->PrazoEntrega
								,'erro'=>$obj->Erro
								,'msg'=>$obj->MsgErro
							);
						}
					}
				}
			}

			
			
			// retorno
			return $array;

		}
		catch( Exception $ex ){
			throw new Exception($ex->getMessage());
		}
	}

	private function getFormaentregaUfValor($formaentrega_id, $uf){

		$formaentregaufvalor = new formaentregaufvalor();

		$sql =
		"
		SELECT
			formaentregaufvalor.*
		FROM
			formaentregaufvalor
		INNER JOIN uf ON (
			uf.id = formaentregaufvalor.uf_id
		AND	formaentregaufvalor.formaentrega_id = {$formaentrega_id}
		)
		WHERE
			formaentregaufvalor.formaentrega_id = {$formaentrega_id}
		AND	uf.sigla = '{$uf}'
		AND formaentregaufvalor.st_ativo = 'S'
		ORDER BY
			formaentregaufvalor.id
		";

		$fetch = fetch(query($sql));
		if($fetch&&$fetch->id){
			$formaentregaufvalor->load_by_fetch($fetch);
		}

		return $formaentregaufvalor;

	}

	private function getFormaentregaUfGratis($formaentrega_id, $uf){

		$formaentregaufgratis = new formaentregaufgratis();

		$sql =
		"
		SELECT
			formaentregaufgratis.*
		FROM
			formaentregaufgratis
		INNER JOIN uf ON (
			uf.id = formaentregaufgratis.uf_id
		AND	formaentregaufgratis.formaentrega_id = {$formaentrega_id}
		)
		WHERE
			formaentregaufgratis.formaentrega_id = {$formaentrega_id}
		AND	uf.sigla = '{$uf}'
		AND formaentregaufgratis.st_ativo = 'S'
		ORDER BY
			formaentregaufgratis.id
		";

		$fetch = fetch(query($sql));
		if($fetch&&$fetch->id){
			$formaentregaufgratis->load_by_fetch($fetch);
		}

		return $formaentregaufgratis;

	}

	private function getFormaentregaRegiaoValor($formaentrega_id, $cep){

		$cep = intval($cep);

		$formaentregaregiaovalor = new formaentregaregiaovalor();

		$sql =
		"
		SELECT
			formaentregaregiaovalor.*
		FROM
			formaentregaregiaovalor
		INNER JOIN regiaoentrega ON (
			regiaoentrega.id = formaentregaregiaovalor.regiaoentrega_id
		AND	formaentregaregiaovalor.formaentrega_id = {$formaentrega_id}
		)
		WHERE
			{$cep} BETWEEN regiaoentrega.cep_inicial AND regiaoentrega.cep_final
		AND formaentregaregiaovalor.st_ativo = 'S'
		ORDER BY
			formaentregaregiaovalor.id
		";

		$fetch = fetch(query($sql));
		if($fetch&&$fetch->id){
			$formaentregaregiaovalor->load_by_fetch($fetch);
		}

		return $formaentregaregiaovalor;

	}

}

class item_entrega {

	private $_cep;
	private $_uf;
	private $_cidade;

	private $_id;
	private $_descricao;

	private $_prazo;
	private $_valor;

	private $_prazoCorreio;
	private $_valorCorreio;

	private $_formaentrega; // Instancia para o registro "formaentrega" referenciado
	private $_formaentregaufvalor; // Instancia para o registro "formaentregaufvalor" referenciado
	private $_formaentregaregiaovalor; // Instancia para o registro "formaentregaufvalor" referenciado
	private $_formaentregaufgratis; // Instancia para o registro "formaentregaufvalor" referenciado

	private $_valorCompra;
	private $_valorProdutos;

	private $_algumProdutoNaoAceitaFreteGratis;

	private $_itens_id = array();

	/***
	Getters
	***/

	private function isEntregaPorUf(){
		return $this->_formaentregaufvalor && $this->_formaentregaufvalor->id;
	}

	private function isEntregaPorRegiao(){
		return $this->_formaentregaregiaovalor && $this->_formaentregaregiaovalor->id;
	}

	private function isEntregaPorUfGratis(){
		return $this->_formaentregaufgratis && $this->_formaentregaufgratis->id;
	}

	private function isEntregaPorCorreio(){
		return $this->_formaentrega->st_correio=='S';
	}

	public function getValor() {

		// Caso esteja configurada a opcao de "Frete Gratis Acima" e a compra ultrapasse tal valor, retorna 0

		if($this->_formaentrega->frete_gratis_acima>0
		&& $this->getValorProdutos() > $this->_formaentrega->frete_gratis_acima){
			return 0;
		}

		// Checa se tem um valor de frete gratis para aquele estado
		if($this->isEntregaPorUfGratis() 
		&& ($this->getValorProdutos() > $this->_formaentregaufgratis->frete_gratis_acima)){
		
			return 0;
		}

		// Tem algum item setado para calculo do frete
		if(sizeof($this->getItensId())>0){

			$todosOk = true;
			foreach($this->getItensId() as $item_id){
				// Pesquisa config de fretes gratis
				$pesquisa = new itemformaentregaconfig(array('item_id'=>$item_id, 'formaentrega_id'=>$this->_formaentrega->id, 'st_gratis'=>'S'));
				if(!$pesquisa->id){
					$todosOk = false;
					// break;
				}
			}

			// Se chegou aqui, todos os itens do carrinho estão setados para frete gratis, e zera o valor
			if($todosOk){
				return 0;
			}
		}



		// Checa se a entrega é por regiao
		if($this->isEntregaPorRegiao()){
			return $this->_formaentregaregiaovalor->valor;
		}

		// Checa se a entrega é por uf
		elseif($this->isEntregaPorUf()){

			$uf = new uf($this->_formaentregaufvalor->uf_id);

			if(self::getStrUpperAndClean($uf->capital) == self::getStrUpperAndClean($this->_cidade)){
				return $this->_formaentregaufvalor->valor_capital;
			}
			else {
				return $this->_formaentregaufvalor->valor_interior;
			}
		}

		return $this->_valor;
	}

	public function getValorFormatado(){
		return money($this->getValor());
	}

	public function getDescricao(){
		$str = $this->_formaentrega->nome;
		if($this->getValor()==0){
			$str .= ' - (Grátis)';
		}
		return $str;
	}

	public function getValorCompra(){
		return $this->_valorCompra;
	}

	public function getPrazo(){

		// Caso haja um prazo de entrega padrao definido, retorna esse
		if($this->_formaentrega->prazo_entrega_padrao > 0){
			return $this->_formaentrega->prazo_entrega_padrao;
		}

		if($this->isEntregaPorRegiao()){
			if($this->_formaentregaregiaovalor->prazo==0 && $this->_prazo >0){
				return $this->_prazo;
			}
			return $this->_formaentregaregiaovalor->prazo;
		}
		elseif($this->isEntregaPorUf()){

			$uf = new uf($this->_formaentregaufvalor->uf_id);

			if(self::getStrUpperAndClean($uf->capital) == self::getStrUpperAndClean($this->_cidade)){
				return $this->_formaentregaufvalor->prazo_capital;
			}
			else {
				return $this->_formaentregaufvalor->prazo_interior;
			}
		}

		return $this->_prazo;
	}

	public function getValorProdutos(){
		return $this->_valorProdutos;
	}

	private function getStrUpperAndClean($str){
		return trim(strtoupper(butil::stringAsTag($str)));
	}

	public function getItensId(){
		return $this->_itens_id;
	}

	/***
	Setters
	***/

	public function setCep($val){
		$this->_cep = $val;
	}

	public function setUf($val){
		$this->_uf = $val;
	}

	public function setCidade($val){
		$this->_cidade = $val;
	}

	public function setValor($val){
		$this->_valor = $val;
	}

	public function setFormaEntrega($val){
		$this->_formaentrega = $val;
	}

	public function setFormaEntregaUFValor($val){
		$this->_formaentregaufvalor = $val;
	}

	public function setFormaEntregaRegiaoValor($val){
		$this->_formaentregaregiaovalor = $val;
	}

	public function setFormaEntregaUFGratis($val){
		$this->_formaentregaufgratis = $val;
	}

	public function setValorCompra($val){
		$this->_valorCompra = $val;
	}

	public function setPrazo($val){
		$this->_prazo = $val;
	}

	public function setValorProdutos($val){
		$this->_valorProdutos = $val;
	}

	public function setItensId($val){
		$this->_itens_id = $val;
	}

}

?>