<?php

class carrinho {

	function __construct(){
		if (!@$_SESSION['S_CARRINHO']
		||  !is_array($_SESSION['S_CARRINHO'])){
			$_SESSION['S_CARRINHO'] = array();
			$_SESSION['S_CARRINHO']['itens'] = array();
			$_SESSION['S_CARRINHO']['entrega'] = array();
		}
		
		$this->recalculaDesconto();
	}

	public function getObservacoes(){
		if(array_key_exists('observacoes',$_SESSION['S_CARRINHO'])){
			//printr($_SESSION['S_CARRINHO']);
			return $_SESSION['S_CARRINHO']['observacoes'];
		}
		return '';
	}

	public function setObservacoes($obs){
		//printr($_SESSION['S_CARRINHO']);
		return $_SESSION['S_CARRINHO']['observacoes'] = $obs;
	}

	public function setImagemAnexo($img_anexo){
		$_SESSION['S_CARRINHO']['img_anexo'] = $img_anexo;
		//print $img_anexo;
	}

	public function getImagemAnexo(){
		if(array_key_exists('img_anexo',$_SESSION['S_CARRINHO'])){
			return $_SESSION['S_CARRINHO']['img_anexo'];
		}
		return '';
	}

	public function setImagemAnexoEspecial($img_anexo){
		$_SESSION['S_CARRINHO']['img_anexo_especial'] = $img_anexo;
	}

	public function getImagemAnexoEspecial(){
		if(array_key_exists('img_anexo_especial',$_SESSION['S_CARRINHO'])){
			return $_SESSION['S_CARRINHO']['img_anexo_especial'];
		}
		return '';
	}

	//corrigir erro de voltar tela, para nao mostar forma de pagamento, na tela do carrinho.
	public function zerarFormaPagamento(){
		if(isSet($_SESSION['S_CARRINHO']['formapagamento_id'])){
			$_SESSION['S_CARRINHO']['formapagamento_id'] = 0;
		}
	}

	public function getValorEmbalagens(){
		$return = 0;
		foreach($this->get_itens() as $item){
			if($item->st_embalagem == 'S'){
				$return += $item->item_preco_embalagem;
			}
		}
		return $return;
	}

	public function getValorTotal(){
		$valor_produtos = $this->getValorProdutos();
		$valor_frete = $this->getValorFrete();
		$valor_desconto = $this->getValorDesconto();
		$valor_desconto_formapagamento = $this->getValorDescontoFormapagamento();
		$valor_embalagens = $this->EmbalagensSubTotal();

		if( ($valor_desconto)>=($valor_produtos+$valor_embalagens)){
			$valor_produtos = 0;
			$valor_prods_desc = 0;
		}else{
			$valor_produtos = $valor_produtos+$valor_embalagens;
			$valor_prods_desc = $valor_produtos-$valor_desconto-$valor_desconto_formapagamento;
		}

		return $valor_prods_desc+$valor_frete;

	}

	public function getValorTotalSemDesconto(){
		$valor_produtos = $this->getValorProdutos();
		$valor_frete = $this->getValorFrete();
		$valor_desconto = $this->getValorDesconto();
		$valor_embalagens = $this->EmbalagensSubTotal();

		if( ($valor_desconto)>=($valor_produtos+$valor_embalagens)){
			$valor_produtos = 0;
			$valor_prods_desc = 0;
		}else{
			$valor_produtos = $valor_produtos+$valor_embalagens;
			$valor_prods_desc = $valor_produtos-$valor_desconto;
		}

		return $valor_prods_desc+$valor_frete;

	}

	public function getValorTotalSoProd($porcentagem_desconto){
		$valor_produtos = $this->getValorProdutos();
		$valor_frete = $this->getValorFrete();
		$valor_desconto = $this->getValorDesconto();
		// $valor_desconto_formapagamento = $this->getValorProdutos() * ($porcentagem_desconto/100);
		$valor_desconto_formapagamento = round($this->getValorProdutos() * ($porcentagem_desconto/100),2);
		$valor_embalagens = $this->EmbalagensSubTotal();

		if( ($valor_desconto)>=($valor_produtos+$valor_embalagens)){
			$valor_produtos = 0;
			$valor_prods_desc = 0;
		}else{
			$valor_produtos = $valor_produtos+$valor_embalagens;
			$valor_prods_desc = $valor_produtos-$valor_desconto-$valor_desconto_formapagamento;
		}

		return $valor_prods_desc+$valor_frete;

	}


	public function EmbalagensSubTotal(){
		$itens = $this->get_itens();
		$valor_embalagens_total = 0;
		foreach($itens as $item){
			if(isSet($_SESSION['embalagem'.$item->item_id]) && $_SESSION['embalagem'.$item->item_id]=='S'){
				$valor_embalagens_total +=  $item->item_preco_embalagem*$item->item_qtd;
			}
		}
		return $valor_embalagens_total;
	}
	public function getEmbalangemSubTotalFormatado(){
		return money($this->EmbalagensSubTotal());
	}

	//
	public function getValorTotalFormatado(){
		return money($this->getValorTotal());
	}

	public function getValorProdutos(){
		$return = 0;
		foreach($this->get_itens() as $item){
			$return += $item->getItemSubTotal();
		}
		return $return;
	}

	public function getPesoProdutos(){
		$ret = 0;
		foreach($this->get_itens() as $item){
			if($item->item_qtd>0){
				$ret += $item->getItemPesoSubTotal()/1000;
				
				$brinde = $item->getBrinde();
				if($brinde){
					$ret += (int)$brinde->peso>0 ? ((int)$brinde->peso/1000)*$item->item_qtd:0;
				
				}
				//
				$ret += ($this->getItensKitPeso($item->item_id)*$item->item_qtd);
				
			}
		}
		return $ret;
	}

	public function getValorProdutosFormatado(){
		return money($this->getValorProdutos());
	}

	//Carrega carrinho abandonado
	public function setCarrinhoAbandonado($cupomdesconto, $carrinhoabandonado){
		if($cupomdesconto->id && $carrinhoabandonado->id){
			$_SESSION['S_CARRINHO'] = unserialize($carrinhoabandonado->carrinho);
			$this->setCupomDesconto($cupomdesconto);

			return true;
			die();
		}
		return false;
	}


	public function getValorDesconto(){
		if(array_key_exists('valor_desconto',$_SESSION['S_CARRINHO'])){
			// $valor_desconto = $_SESSION['S_CARRINHO']['valor_desconto'];
			return $_SESSION['S_CARRINHO']['valor_desconto'];
		}
		return 0;
	}

	public function getValorDescontoFormatado(){
		return money($this->getValorDesconto());
	}

	public function setValorFrete($valor_frete){
		$_SESSION['S_CARRINHO']['valor_frete'] = $valor_frete;
	}

	public function getValorFrete(){
		if(array_key_exists('valor_frete',$_SESSION['S_CARRINHO'])){
			return $_SESSION['S_CARRINHO']['valor_frete'];
		}
		$return = 0;
	}

	public function getValorFreteFormatado(){
		return money($this->getValorFrete());
	}

	/************
	Vale de presente
	************/

	public function isValePresente(){
		$valepresente = $this->getValePresente();
		return (@$valepresente->id>0);
	}

	public function getValePresente(){
		$codigo = $this->getValePresenteCodigo();
		if($codigo!=''){
			return new valepresente(array('codigo'=>$codigo));
		}
		return null;
	}

	public function setValePresente($valepresente){
		if($valepresente->id){
			$_SESSION['S_CARRINHO']['valepresente_codigo'] = $valepresente->codigo;
			$_SESSION['S_CARRINHO']['valor_desconto'] = $valepresente->valor;
		}
	}

	public function clearValePresente(){
		unset($_SESSION['S_CARRINHO']['valepresente_codigo']);
		unset($_SESSION['S_CARRINHO']['valor_desconto']);
	}

	public function getValePresenteCodigo(){
		if(array_key_exists('valepresente_codigo',$_SESSION['S_CARRINHO'])){
			return $_SESSION['S_CARRINHO']['valepresente_codigo'];
		}
		return '';
	}

	/************
	Cupom de desconto
	************/

	public function getCupomDesconto(){
		$codigo = $this->getCupomDescontoCodigo();
		if($codigo!=''){
			return new cupomdesconto(array('codigo'=>$codigo));
		}
		return null;
	}

	public function setCupomDesconto($cupomdesconto){
		if($cupomdesconto&&$cupomdesconto->id){

			$_SESSION['S_CARRINHO']['cupomdesconto_codigo'] = $cupomdesconto->codigo;
			$_SESSION['S_CARRINHO']['valor_desconto'] = $cupomdesconto->getValorDesconto($this->getValorProdutos());
		}
	}

	public function recalculaDesconto(){
		$this->setCupomDesconto($this->getCupomDesconto());
	}

	public function clearCupomDesconto(){
		unset($_SESSION['S_CARRINHO']['cupomdesconto_codigo']);
		unset($_SESSION['S_CARRINHO']['valor_desconto']);
	}

	public function getCupomDescontoCodigo(){
		if(array_key_exists('cupomdesconto_codigo',$_SESSION['S_CARRINHO'])){
			return $_SESSION['S_CARRINHO']['cupomdesconto_codigo'];
		}
		return '';
	}

	public function getTextoItens(){
		$x = $this->getQtdItens();
		if($x>0){
			// return "{$x} item(s)";
            return "<span class='badge badge-carrinho'>{$x}</span>";
		}
		return "";
	}

	// Limpa os dados de entrega
	public function clearEntrega(){
		unset($_SESSION['S_CARRINHO']['entrega']);
	}

	// Verifica se o valor total de desconto é maior que o valor de frete + produtos
	public function isDescontoMaiorQueValorTotal(){
		//return 1;
		// if($this->getValorProdutos()+$this->getValorFrete()<=$this->getValorDesconto())
		$valor_produtos = $this->getValorProdutos();
		$valor_frete = $this->getValorFrete();
		$valor_desconto = $this->getValorDesconto();

		// printr($valor_desconto);

		if($valor_desconto > ($valor_produtos+$valor_frete)){
			return true;
		}

		return false;

	}

	// Verifica se o o cep de entrega esta definido
	public function isEntregaDefinida(){
		if(array_key_exists('entrega',$_SESSION['S_CARRINHO'])){
			if(intval(@$this->getEntrega()->cep)>0){
				return true;
			}
		}
		return false;
	}

	// Seta as propriedades do objeto entrega
	public function setEntrega($endereco){
		$_SESSION['S_CARRINHO']['entrega'] = $endereco;
	}

	// Retorna a propriedade destinatario da entrega
	public function setEntregaDestinatario($destinatario){
		$_SESSION['S_CARRINHO']['entrega']->destinatario = $destinatario;
	}

	// Retorna a propriedade cep da entrega
	public function getEntregaCep(){
		if($this->isEntregaDefinida()){
			return $this->getEntrega()->cep;
		}
	}

	// Retorna objeto entrega
	public function getEntrega(){
		// printr($_SESSION);
		if(array_key_exists('entrega',$_SESSION['S_CARRINHO'])){
			return @$_SESSION['S_CARRINHO']['entrega'];
		}
	}

	// Define
	public function isTipoFreteDefinido(){
		return $this->getTipoFrete() != '';
	}

	public function setTipoFrete($tipo_frete, $prazo_entrega){
		$_SESSION['S_CARRINHO']['tipo_frete'] = $tipo_frete;
		$_SESSION['S_CARRINHO']['prazo_entrega'] = $prazo_entrega;
	}

	public function getTipoFrete(){
		if(array_key_exists('tipo_frete',$_SESSION['S_CARRINHO'])){
			return $_SESSION['S_CARRINHO']['tipo_frete'];
		}
		return '';
	}

	public function getPrazoEntrega(){
		if(array_key_exists('prazo_entrega',$_SESSION['S_CARRINHO'])){
			return $_SESSION['S_CARRINHO']['prazo_entrega'];
		}
		return '';
	}

	// Forma de pagamento
	public function setFormaPagamentoId($formapagamento_id){
		$_SESSION['S_CARRINHO']['formapagamento_id'] = $formapagamento_id;
	}

	public function getFormaPagamentoId(){
		return @$_SESSION['S_CARRINHO']['formapagamento_id'];
	}

	public function clearFormaPagamentoId(){
		unset($_SESSION['S_CARRINHO']['formapagamento_id']);
	}

	// Desconto para a forma de pagamento
	public function getValorDescontoFormaPagamento(){
		$ret = 0;
		$formapagamento = new formapagamento($this->getFormaPagamentoId());
		if($formapagamento->porcentagem_desconto>0){
			return round($this->getValorProdutos() * ($formapagamento->porcentagem_desconto/100),2);
			// return $this->getValorProdutos() * ($formapagamento->porcentagem_desconto/100);
		}
		return $ret;
	}

	public function getValorDescontoFormaPagamentoFormatado(){
		return money($this->getValorDescontoFormaPagamento());
	}

	public function getEnderecoEntregaSemNumero(){
		if($this->isEntregaDefinida()){
			return $this->getEntrega()->logradouro
					.'<br/> '.$this->getEntrega()->bairro
					.' '.$this->getEntrega()->cidade
					.' - '.$this->getEntrega()->uf;
		}
		return '';
	}

	public function getEnderecoEntregaComNumero(){
		if($this->isEntregaDefinida()){
			return $this->getEntrega()->logradouro
					.' '.$this->getEntrega()->bairro
					.' '.$this->getEntrega()->cidade
					.' - '.$this->getEntrega()->uf;
		}
		return '';
	}

	public function getMaximoParcelas(){
		return getMaximoParcelas($this->getValorTotal());
	}

	public function getValorMinimoParcela(){
		$parcelas = $this->getMaximoParcelas();
		return ($this->getValorTotal()/$parcelas);
	}

	public function getValorMinimoParcelaFormatado(){
		return money($this->getValorMinimoParcela());
	}

	public function verificaItem($item_id,$cor_id){
		foreach($this->get_itens() as $key=>$value){
			if($value->item_id == $item_id && $value->cor_id == $cor_id){
				return false;
			}
		}
		return true;
	}
	
	// Adiciona um item na sessao do carrinho
	public function add_item($objs){
		
		// Instancia objeto
		$o = new item_carrinho();

		foreach ($objs as $obj){
			// printr($obj);
			$class_name = get_class($obj);
			foreach(get_object_vars($obj) as $key=>$value){
				$p = $class_name.'_'.$key;
				$o->$p = $obj->$key;
			}
		}

		$o->unique_id = uniqid();
		$_SESSION['S_CARRINHO']['itens'][] = $o;

		return $o;
	}

	// Retorna um array dos itens que estao no carrinho
	public function get_itens(){
		//printr($_SESSION['S_CARRINHO']) ;
		if (array_key_exists('itens', $_SESSION['S_CARRINHO'])
		&& is_array($_SESSION['S_CARRINHO']['itens'])){
			return $_SESSION['S_CARRINHO']['itens'] ;
		}
		else {
			return array();
		}
	}

	// Retorna a quantidade de itens no carrinho
	public function getQtdItens(){
		$return = 0 ;
		foreach ( $this->get_itens() as $item_carrinho ){
			//$return += $item_carrinho->item_qtd;
			if($item_carrinho->item_qtd>0){
				//Aqui retorna a quantidade de intens
				$return += 1;
			}
		}
		return $return ;
	}

	public function getQtdTotalItens(){
		$return = 0 ;
		foreach ( $this->get_itens() as $item_carrinho ){
			$return += $item_carrinho->item_qtd + $item_carrinho->getQtdBrindes() ;
		}
		return $return ;
	}

	// Retorna a quantidade de brindes no carrinho
	public function getQtdBrindes(){
		$ret = 0;
		// Loop nos itens
		foreach($this->get_itens() as $item){
			if($item->item_qtd>0){
				$ret += $item->getQtdBrindes();
			}
		}
		return $ret;
	}

	// Retorna a quantidade de bonus
	public function getQtdBonus(){
		$return = 0 ;
		foreach ( $this->get_itens() as $item_carrinho ){
			if($item_carrinho->item_qtd>0){
				$return += $item_carrinho->item_qtd_bonus * $item_carrinho->item_qtd ;
			}
		}
		return $return ;
	}

	// Retorna o valor da embalagem
	public function getValorEmbalagem(){
		$ret = 0 ;
		foreach ( $this->get_itens() as $item_carrinho ){
			if($item_carrinho->item_qtd>0){
				if($item_carrinho->getStEmbalagem()=='S'){
					$ret += $item_carrinho->getItemPrecoEmbalagemSubTotal();
				}
			}
		}
		return $ret ;
	}

	public function getValorEmbalagemFormatado(){
		return money($this->getValorEmbalagem());
	}



	// Retorna um array com os brindes agrupados, util para validar o estoque de maneira unificada
	public function getBrindes(){
		$ret = array();
		// Loop nos itens
		foreach($this->get_itens() as $item){
			$brinde = $item->getBrinde();
			if($brinde){
				if(!@$ret[$brinde->id]){
					$ret[$brinde->id] = (object) array('id' => $brinde->id, 'referencia' => $brinde->referencia, 'nome' => $brinde->nome, 'qtd' => $item->getItemQtd());
				}
				else {
					$ret[$brinde->id]->qtd += $item->getItemQtd();
				}
			}
		}
		return $ret;
	}


	//
	public function validaQtdEstoque($qtdDesejada, $itemId){



	}


	// Itens pedido do kit
	public function addItensKit($kit_id, $kit_item_id){
		if(array_key_exists("kit_".$kit_id, $_SESSION['S_CARRINHO'])){
			$_SESSION['S_CARRINHO']["kit_".$kit_id][sizeof($_SESSION['S_CARRINHO']["kit_".$kit_id])] = $kit_item_id;
		}else{
			$_SESSION['S_CARRINHO']["kit_".$kit_id][0] = $kit_item_id;
		}
	}
	
	public function getItensKit($kit_id){
		if(array_key_exists("kit_".$kit_id, $_SESSION['S_CARRINHO'])){
			return $_SESSION['S_CARRINHO']["kit_".$kit_id];
		}
		return array();
	}
	
	public function getItensKitPeso($kit_id){
		$peso = 0;
		foreach($this->getItensKit($kit_id) as $key=>$value){
			$item = new item($key);
			$peso += (int)$item->peso>0 ? (int)$item->peso/1000:0;
		}
		return $peso;
	}
	
	
	

	// Retorna os ids de todos os "item" no carrinho
	public function getItensId(){
		$ret = array();
		foreach($this->get_itens() as $item){
			$ret[] = $item->item_id;
		}
		return $ret;
	}

	public function clear(){
		$_SESSION['S_CARRINHO'] = array();
	}
	

}



class item_carrinho {

	public function __get($key){
		return @$this->$key;
	}

	public function setStEmbalagem($st_embalagem){
		// printr($st_embalagem);
		if($st_embalagem == 'S'){
			$this->st_embalagem = 'S';
		}
		elseif($st_embalagem == 'N'){
			$this->st_embalagem = 'N';
		}
	}

	public function getLinkDesejo(){
		return INDEX.'desejo/?item_id='.$this->item_id;
	}

	public function getItemQtd(){
		return $this->item_qtd;
	}

	public function getItemNome(){
		return $this->item_nome;
	}

	public function getCompreJuntoId(){
		return @$this->comprejunto_id ;
	}

	public function getSaldo($consulta_erp=false){
		// if($consulta_erp){
			// $projeto = new projeto();
			// $projeto->processaErpAtualizaItem($this->item_id);
		// }
		return query_col("SELECT qtd_estoque FROM item WHERE id = ".$this->item_id);
	}

	public function getBrindeSaldo($consulta_erp=false){
		$brinde = $this->getBrinde();
		if($brinde){
			return query_col("SELECT qtd_estoque FROM item WHERE id = ".$brinde->id);
		}
	}

	public function getStEmbalagem(){
		if($this->st_embalagem == 'S'){
			return 'S';
		}
		return 'N';
	}

	public function getStEmbalagemChecked(){
		if($this->getStEmbalagem() == 'S'){
			return 'checked';
		}
		return '';
	}

	public function setMsgEmbalagem($msg){
		if($this->st_embalagem == 'S'){
			$this->msg_embalagem = $msg;
		}
		return '';
	}

	public function getMsgEmbalagem(){
		if($this->st_embalagem == 'S'){
			return $this->msg_embalagem;
		}
		return '';
	}

	public function getItemPrecoFormatado(){
		return money($this->item_preco);
	}

	public function getItemSubTotal(){
		return $this->item_preco*$this->item_qtd;
	}

	public function getItemPesoSubTotal(){
		return $this->item_peso*$this->item_qtd;
	}

	public function getItemSubTotalFormatado(){
		return money($this->item_preco*$this->item_qtd);
	}

	public function getItemPrecoEmbalagem(){
		return $this->item_preco_embalagem;
	}

	public function getItemPrecoEmbalagemSubTotal(){
		return $this->getItemPrecoEmbalagem()*$this->item_qtd;
	}

	public function getItemPrecoEmbalagemFormatado(){
		return money($this->getItemPrecoEmbalagem());
	}

	public function getItemSubTotalQtdBonus(){
		return $this->item_qtd_bonus*$this->item_qtd;
	}

	public function getVariacaoDescricao(){
		$item = new item($this->item_id);
		return $item->getVariacaoDescricao();
	}

	public function getVariacaoDescricaoHtml(){
		return nl2br($this->getVariacaoDescricao());
	}

	public function getBrinde(){
		// Separa brinde do item
		if($this->item_brinde_id && $this->item_brinde_id>0){
			return new item($this->item_brinde_id);
		}

		return null;
	}

	public function getQtdBrindes(){
		$ret = 0;
		$brinde = $this->getBrinde();
		if($brinde){
			$ret = $this->item_qtd;
			if($ret>$brinde->qtd_estoque){
				$ret = $brinde->qtd_estoque;
			}
		}
		return $ret;
	}

	public function getItemQtdEstoqueFake(){
		$qtd_estoque = $this->item_qtd_estoque;
		if($qtd_estoque > 5){
			return 5;
		}
		return $this->item_qtd_estoque;
	}
	

}

?>