<?php

class gateway extends base {

	private $debug = false;
	private $verb = 'GET';

	var
		$id
		,$formapagamento_id
		,$pedido_id
		,$parcelas
		,$tipo_financiamento
		,$nome_titular
		,$numero_cartao
		,$cod_seguranca
		,$mes_validade
		,$ano_validade
		,$numero_autorizacao
		,$retorno_codigo
		,$retorno_msg
		,$retorno_full
		,$valor
		,$data_cadastro
		,$data_retorno;

	public function __get($key){
		// Checa se é msg do sitef
		if(strpos($key,'MKT_')>-1){
			// printr($this->retorno_msg);
			$out = new SimpleXMLElement($this->retorno_msg);
			return @$out->$key;
		}
	}

	public function getVencimento(){
		// $ano =
		if(strlen($this->ano_validade)==4){
			return "{$this->mes_validade}".substr($this->ano_validade,-2);
		}
		else {
			return "{$this->mes_validade}";
		}
	}

	// Processa requisicao na maquina escrava
	public function processa(&$out){

		$formapagamento = new formapagamento($this->formapagamento_id);

		// Só processa o gateway caso seja cartao
		if($formapagamento->isBoleto()){
			return;
		}

		if(!$formapagamento->isCartaoCredito()){
			return;
		}

		// $cartao = getNumbers($this->numero_cartao);
		// printr($cartao);
		// $valor = str_replace(array(',','.'), '', $this->valor);
		// $parcelas = @$this->parcelas ? $this->parcelas : "0" ;
		// $vencimento = $this->getVencimento();
		// $codigo = $this->cod_seguranca;
		// $tipo_financiamento = @$this->tipo_financiamento ? $this->tipo_financiamento : "0" ;

		// Caso não salve
		if(!$this->salva()){
			throw new Exception('Falha ao processar requisição');
		}

		// Checa se é cartão de de crédito e se os dados foram digitados no site
		if($formapagamento->isCartaoCredito()
		&& $formapagamento->integrador=='LOCAWEB'
		&& $formapagamento->st_digita_dados_site=='S' ){
			$this->procAutorizacaoDiretaTransacao();
		}

		// // Visa
		// if($formapagamento->id==formapagamento::get('VISA')->id){
			// if($formapagamento->integrador=='LOCAWEB'){
				// // Caso digite os dados no proprio site, tenta fazer a autorizacao direta
				// if($formapagamento->st_digita_dados_site=='S'){
					// $this->procAutorizacaoDiretaTransacao();
				// }
				// // printr($this);
				// // die();
			// }
			// if($formapagamento->integrador=='SOFTWAREEXPRESS'){
			// }
		// }

		// // Mastercard
		// if($formapagamento->id==formapagamento::get('MASTERCARD')->id){
			// if($formapagamento->integrador=='LOCAWEB'){
				// // Caso digite os dados no proprio site, tenta fazer a autorizacao direta
				// if($formapagamento->st_digita_dados_site=='S'){
					// $this->procAutorizacaoDiretaTransacao();
				// }
				// // printr($this);
				// // die();
			// }
			// if($formapagamento->integrador=='SOFTWAREEXPRESS'){
			// }
		// }

		// // Amex
		// if($formapagamento->id==formapagamento::get('AMEX')->id){
			// if($formapagamento->integrador=='LOCAWEB'){
				// // Caso digite os dados no proprio site, tenta fazer a autorizacao direta
				// if($formapagamento->st_digita_dados_site=='S'){
					// $this->procAutorizacaoDiretaTransacao();
				// }
				// // printr($this);
				// // die();
			// }
			// if($formapagamento->integrador=='SOFTWAREEXPRESS'){
			// }
		// }

		// Mastercard -

		// Itau shopline
		// if($formapagamento->id==formapagamento::get('MASTERCARD')->id){
			// if($formapagamento->integrador=='LOCAWEB'){
				// $this->valor = 100;
				// $this->procAutorizacaoDiretaTransacao();
				// // printr($this);
				// // die();
			// }
			// if($formapagamento->integrador=='SOFTWAREEXPRESS'){
			// }
		// }

		// die();

		// /*
		// $gateway->cartao = $cartao;
		// $valor = str_replace(array(',','.'), '', $this->valor);
		// $parcelas = @$this->parcelas ? $this->parcelas : "0" ;
		// $vencimento = $this->getVencimento();
		// $codigo = $this->cod_seguranca;
		// $tipo_financiamento = @$this->tipo_financiamento ? $this->tipo_financiamento : "0" ;
		// */

		// $this->refresh();

		// // Limpa dados sensiveis
		// // query("UPDATE gateway SET numero_cartao = 'xxx', cod_seguranca = 'xxx', mes_validade = 'xxx', ano_validade = 'xxx' WHERE id = {$this->id}");

		// $host = configuracao::get('TEF_SRV_HOST');
		// $port = configuracao::get('TEF_SRV_PORT');
		// $path = configuracao::get('TEF_SRV_PATH');

		// $par = array();

		// // printr($this);
		// // die();

		// $gateway = new gateway($this->id);
		// $valor = str_replace(array(',','.'), '', $gateway->valor);

		// $request = "?cartao={$cartao}&valor={$valor}&parcelas={$parcelas}&vencimento={$vencimento}&codigo={$codigo}&tipo_financiamento={$tipo_financiamento}";

		// // print $request;
		// // die();

		// ini_set("allow_url_fopen", 1); // Ativa a diretiva 'allow_url_fopen' para uso do 'fsockopen'

		// $request_length = strlen($request);

		// $response = '';

		// if($this->verb=='GET'){

			// // print $path."?".$request;

			// $ok = false;

			// $header  = "GET {$path}{$request} HTTP/1.1\r\n";
			// $header .= "Host: {$host}\r\n";

			// $header .= "User-Agent: Mozilla/5.0 (Windows; U; Windows NT 6.1; en-US; rv:1.9.2.16) Gecko/20110319 Firefox/3.6.16\r\n";
			// $header .= "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8\r\n";
			// $header .= "Accept-Language: en-us,en;q=0.5\r\n";
			// $header .= "Accept-Encoding: gzip,deflate\r\n";
			// $header .= "Accept-Charset: ISO-8859-1,utf-8;q=0.7,*;q=0.7\r\n";

			// $header .= "Connection: Close\r\n";

			// $header .= "\r\n";

			// if($this->debug){
				// printr($request);
				// printr($host.$path.$request);
				// printr($header);
			// }
			// $fp = fsockopen($host,$port,$err_num,$err_msg,30);

			// //print $host.$path.$request;
			// // var_dump($fp);

			// fputs($fp, $header.$request);
			// while ($line = fgets($fp, 4096)){
				// $response .= $line;
			// }

			// $responseFull = $response;

			// // Verifica se existe o [OK] no response
			// if(!(strpos($response, '<OUT>')===FALSE)){

				// $pos = (strpos($response, '<OUT>'));
				// $response = trim(substr($response, $pos, strlen($response)));

				// if($this->debug){
					// printr($response);
				// }

				// $response = iconv('iso-8859-1','utf-8',$response);

				// $out = new SimpleXMLElement($response);

				// if($out->OK == 'T'){
					// // return true;
					// query("UPDATE gateway SET numero_autorizacao = '{$out->MKT_NUMERO_AUTORIZACAO}', retorno_msg = '".limpa($response)."' WHERE id = {$this->id}");
				// }
				// else {
					// query("UPDATE gateway SET retorno_msg = '".limpa($response)."' WHERE id = {$this->id}");
					// throw new Exception($out->MSG);
				// }

			// }
			// else {
				// throw new Exception('Não foi possível processar o pagamento '.$response);
			// }

			// fclose($fp);
			// if($this->debug){
				// printr($responseFull);
			// }
		// }

		// if($this->verb=='POST'){
		// }

		// return $response;
	}

	public function getMensagemRetorno(){
		return "({$this->retorno_codigo}) - {$this->retorno_msg}";
	}

	public function getFormaPagamento(){
		return new formapagamento($this->formapagamento_id);
	}

	public function getFormapagamentoVisaChecked(){
		return ($this->formapagamento_id==formapagamento::get('VISA')->id)?'checked':'';
	}

	public function getFormapagamentoMasterChecked(){
		return ($this->formapagamento_id==formapagamento::get('MASTERCARD')->id)?'checked':'';
	}

	public function getFormapagamentoBoletoChecked(){
		return ($this->formapagamento_id==formapagamento::get('BOLETOBRADESCO')->id)?'checked':'';
	}

	private function procAutorizacaoDiretaTransacao(){

		$msg_recusa = "Pedido não concluído

		Problemas com o cartão, favor verificar e conferir os dados de seu cartão.
		Caso o erro persista, siga as instruções abaixo:
		1) Utilize outro cartão de crédito.
		2) Utilize outra forma de pagamento.
		3) Entre em contato com a central de atendimento do seu cartão.
		";

		$XMLtransacao = $this->procAutorizacaoDiretaTransacaoGetURL();
		butil::__log($XMLtransacao);

		// Carrega o XML
		$objDom = new DomDocument();
		$loadDom = @$objDom->loadXML($XMLtransacao);

		if(!$loadDom){
			throw new Exception('1 - Problemas no processamento '.(DEBUG=='1'?$XMLtransacao:''));
		}

		$retorno_codigo_erro = '';

		$nodeErro = $objDom->getElementsByTagName('erro')->item(0);
		if ($nodeErro != '') {
			$nodeCodigoErro = $nodeErro->getElementsByTagName('codigo')->item(0);
			$retorno_codigo_erro = $nodeCodigoErro->nodeValue;

			$nodeMensagemErro = $nodeErro->getElementsByTagName('mensagem')->item(0);
			$retorno_mensagem_erro = $nodeMensagemErro->nodeValue;
		}

		if(!$retorno_codigo_erro==''){
			throw new Exception("{$retorno_codigo_erro} - {$retorno_mensagem_erro}");
		}

		$nodeTransacao = $objDom->getElementsByTagName('transacao')->item(0);

		if($nodeTransacao == ''){
			throw new Exception('2 - Não foi possível identificar o retorno da transação');
		}

		$nodeTID = $nodeTransacao->getElementsByTagName('tid')->item(0);
		$retorno_tid = $nodeTID->nodeValue;

		$nodePAN = $nodeTransacao->getElementsByTagName('pan')->item(0);
		$retorno_pan = $nodePAN->nodeValue;

		$nodeDadosPedido = $nodeTransacao->getElementsByTagName('dados-pedido')->item(0);
		if ($nodeTransacao != '') {
			$nodeNumero = $nodeDadosPedido->getElementsByTagName('numero')->item(0);
			$retorno_pedido = $nodeNumero->nodeValue;

			$nodeValor = $nodeDadosPedido->getElementsByTagName('valor')->item(0);
			$retorno_valor = $nodeValor->nodeValue;

			$nodeMoeda = $nodeDadosPedido->getElementsByTagName('moeda')->item(0);
			$retorno_moeda = $nodeMoeda->nodeValue;

			$nodeDataHora = $nodeDadosPedido->getElementsByTagName('data-hora')->item(0);
			$retorno_data_hora = $nodeDataHora->nodeValue;

			$nodeDescricao = $nodeDadosPedido->getElementsByTagName('descricao')->item(0);
			$retorno_descricao = $nodeDescricao->nodeValue;

			$nodeIdioma = $nodeDadosPedido->getElementsByTagName('idioma')->item(0);
			$retorno_idioma = $nodeIdioma->nodeValue;
		}

		$nodeFormaPagamento = $nodeTransacao->getElementsByTagName('forma-pagamento')->item(0);
		if ($nodeFormaPagamento != '') {
			$nodeBandeira = $nodeFormaPagamento->getElementsByTagName('bandeira')->item(0);
			$retorno_bandeira = $nodeBandeira->nodeValue;

			$nodeProduto = $nodeFormaPagamento->getElementsByTagName('produto')->item(0);
			$retorno_produto = $nodeProduto->nodeValue;

			$nodeParcelas = $nodeFormaPagamento->getElementsByTagName('parcelas')->item(0);
			$retorno_parcelas = $nodeParcelas->nodeValue;
		}

		$nodeStatus = $nodeTransacao->getElementsByTagName('status')->item(0);
		$retorno_status = $nodeStatus->nodeValue;

		// $nodeAutenticacao = $nodeTransacao->getElementsByTagName('autenticacao')->item(0);
		// if ($nodeAutenticacao != '') {
			// $nodeCodigoAutenticacao = $nodeAutenticacao->getElementsByTagName('codigo')->item(0);
			// $retorno_codigo_autenticacao = $nodeCodigoAutenticacao->nodeValue;

			// $nodeMensagemAutenticacao = $nodeAutenticacao->getElementsByTagName('mensagem')->item(0);
			// $retorno_mensagem_autenticacao = $nodeMensagemAutenticacao->nodeValue;

			// $nodeDataHoraAutenticacao = $nodeAutenticacao->getElementsByTagName('data-hora')->item(0);
			// $retorno_data_hora_autenticacao = $nodeDataHoraAutenticacao->nodeValue;

			// $nodeValorAutenticacao = $nodeAutenticacao->getElementsByTagName('valor')->item(0);
			// $retorno_valor_autenticacao = $nodeValorAutenticacao->nodeValue;

			// $nodeECIAutenticacao = $nodeAutenticacao->getElementsByTagName('eci')->item(0);
			// $retorno_eci_autenticacao = $nodeECIAutenticacao->nodeValue;
		// }

		// $nodeAutorizacao = $nodeTransacao->getElementsByTagName('autorizacao')->item(0);
		// if ($nodeAutorizacao != '') {
			// $nodeCodigoAutorizacao = $nodeAutorizacao->getElementsByTagName('codigo')->item(0);
			// $retorno_codigo_autorizacao = $nodeCodigoAutorizacao->nodeValue;

			// $nodeMensagemAutorizacao = $nodeAutorizacao->getElementsByTagName('mensagem')->item(0);
			// $retorno_mensagem_autorizacao = $nodeMensagemAutorizacao->nodeValue;

			// $nodeDataHoraAutorizacao = $nodeAutorizacao->getElementsByTagName('data-hora')->item(0);
			// $retorno_data_hora_autorizacao = $nodeDataHoraAutorizacao->nodeValue;

			// $nodeValorAutorizacao = $nodeAutorizacao->getElementsByTagName('valor')->item(0);
			// $retorno_valor_autorizacao = $nodeValorAutorizacao->nodeValue;

			// $nodeLRAutorizacao = $nodeAutorizacao->getElementsByTagName('lr')->item(0);
			// $retorno_lr_autorizacao = $nodeLRAutorizacao->nodeValue;

			// $nodeARPAutorizacao = $nodeAutorizacao->getElementsByTagName('arp')->item(0);
			// $retorno_arp_autorizacao = $nodeARPAutorizacao->nodeValue;
		// }

		// $nodeURLAutenticacao = $nodeTransacao->getElementsByTagName('url-autenticacao')->item(0);
		// $retorno_url_autenticacao = $nodeURLAutenticacao->nodeValue;

		// PossÃ­veis status de transaÃ§Ã£o

		/*
		0 Criada
		1 Em andamento
		2 Autenticada
		3 Nao autenticada
		4 Autorizada ou pendente de captura
		5 Nao autorizada
		6 Capturada
		8 Nao capturada
		9 Cancelada
		10 Em autenticacao
		*/

		switch($retorno_status){

			case "0": // Criada
				// throw new Exception("Status não esperado - Criada - 0");
				throw new Exception($msg_recusa);
			break;

			case "1": // Em andamento
				// throw new Exception("Status não esperado - Em andamento - 1");
				throw new Exception($msg_recusa);
			break;

			case "2": // Autenticada
			case "4": // Autorizada ou pendente de captura

				// $pedido->visa_tid = $_REQUEST['tid'];
				// // $pedido->visa_lr = $_REQUEST['lr'];
				// // $pedido->visa_ars = $_REQUEST['ars'];

				// $pedido->atualiza();

				// $URLConfirmacao = PATH_SITE_ABSOLUTO.'index.php/pedido_confirmacao/'.$pedido->id;
				// <SCRIPT LANGUAGE=javascript>
					// window.location = 'echo $URLConfirmacao' ;
				// </SCRIPT>
				// die();

			break;

			case "3": // NÃ£o autenticada
				// throw new Exception("Não autenticada - 3");
				throw new Exception($msg_recusa);
			break;

			case "5": // NÃ£o autorizada
				// throw new Exception("Não autorizada - 5");
				throw new Exception($msg_recusa);
			break;

			case "6": // Capturada
				// $pedido->visa_tid = $_REQUEST['tid'];
				// // $pedido->visa_lr = $_REQUEST['lr'];
				// // $pedido->visa_ars = $_REQUEST['ars'];

				// $pedido->atualiza();

				// $URLConfirmacao = PATH_SITE_ABSOLUTO.'index.php/pedido_confirmacao/'.$pedido->id;
				// <SCRIPT LANGUAGE=javascript>
				// window.location = '<?php echo $URLConfirmacao' ;
				// </SCRIPT>
				// <?php
				// die();
			break;

			case "8": // NÃ£o capturada
				// throw new Exception("N&atilde;o capturada - 8");
				throw new Exception($msg_recusa);
			break;

			case "9": // Cancelada
				// throw new Exception("Cancelada - 9");
				throw new Exception($msg_recusa);
			break;

			case "10": // Em autenticaÃ§Ã£o
				// throw new Exception("Em autentica&ccedil;&atilde;o - 10");
				throw new Exception($msg_recusa);
			break;
		}

		// printr($_REQUEST);

		// echo '<b> TRANSAÇÃO </b><br />';
		// echo '<b>Código de identificação do pedido (TID): </b>' . $retorno_tid . '<br />';
		// echo '<b>PAN do pedido (pan): </b>' . $retorno_pan . '<br />';

		// echo '<b>Número do pedido (numero): </b>' . $retorno_pedido . '<br />';
		// echo '<b>Valor do pedido (valor): </b>' . $retorno_valor . '<br />';
		// echo '<b>Moeda do pedido (moeda): </b>' . $retorno_moeda . '<br />';
		// echo '<b>Data e hora do pedido (data-hora): </b>' . $retorno_data_hora . '<br />';
		// echo '<b>Descrição do pedido (descricao): </b>' . $retorno_descricao . '<br />';
		// echo '<b>Idioma do pedido (idioma): </b>' . $retorno_idioma . '<br />';

		// echo '<b>Bandeira (bandeira): </b>' . $retorno_bandeira . '<br />';
		// echo '<b>Forma de pagamento (produto): </b>' . $retorno_produto . '<br />';
		// echo '<b>Número de parcelas (parcelas): </b>' . $retorno_parcelas . '<br />';

		// echo '<b>Status do pedido (status): </b>' . $retorno_status . '<br />';

		$this->numero_autorizacao = $retorno_tid;
		$this->retorno_codigo = $retorno_status;
		$this->retorno_full = addslashes($XMLtransacao);

		$this->atualiza();

		// printr($this);
		// die();

		return;

		// Se não ocorreu erro exibe parâmetros
		if ($retorno_codigo_erro == '') {
			echo '<b> TRANSAÇÃO </b><br />';
			echo '<b>Código de identificação do pedido (TID): </b>' . $retorno_tid . '<br />';
			echo '<b>PAN do pedido (pan): </b>' . $retorno_pan . '<br />';

			echo '<b>Número do pedido (numero): </b>' . $retorno_pedido . '<br />';
			echo '<b>Valor do pedido (valor): </b>' . $retorno_valor . '<br />';
			echo '<b>Moeda do pedido (moeda): </b>' . $retorno_moeda . '<br />';
			echo '<b>Data e hora do pedido (data-hora): </b>' . $retorno_data_hora . '<br />';
			echo '<b>Descrição do pedido (descricao): </b>' . $retorno_descricao . '<br />';
			echo '<b>Idioma do pedido (idioma): </b>' . $retorno_idioma . '<br />';

			echo '<b>Bandeira (bandeira): </b>' . $retorno_bandeira . '<br />';
			echo '<b>Forma de pagamento (produto): </b>' . $retorno_produto . '<br />';
			echo '<b>Número de parcelas (parcelas): </b>' . $retorno_parcelas . '<br />';

			echo '<b>Status do pedido (status): </b>' . $retorno_status . '<br />';

			echo '<b>URL para autenticação (url-autenticacao): </b>' . $retorno_url_autenticacao . '<br /><br />';

			echo '<b> AUTENTICAÇÃO </b><br />';
			echo '<b>Código da autenticação (codigo): </b>' . $retorno_codigo_autenticacao . '<br />';
			echo '<b>Mensagem da autenticação (mensagem): </b>' . $retorno_mensagem_autenticacao . '<br />';
			echo '<b>Data e hora da autenticação (data-hora): </b>' . $retorno_data_hora_autenticacao . '<br />';
			echo '<b>Valor da autenticação (valor): </b>' . $retorno_valor_autenticacao . '<br />';
			echo '<b>ECI da autenticação (eci): </b>' . $retorno_eci_autenticacao . '<br /><br />';

			echo '<b> AUTORIZAÇÃO </b><br />';
			echo '<b>Código da autorização (codigo): </b>' . $retorno_codigo_autorizacao . '<br />';
			echo '<b>Mensagem da autorização (mensagem): </b>' . $retorno_mensagem_autorizacao . '<br />';
			echo '<b>Data e hora da autorização (data-hora): </b>' . $retorno_data_hora_autorizacao . '<br />';
			echo '<b>Valor da autorização (valor): </b>' . $retorno_valor_autorizacao . '<br />';
			echo '<b>LR da autorização (LR): </b>' . $retorno_lr_autorizacao . '<br />';
			echo '<b>ARP da autorização (ARP): </b>' . $retorno_arp_autorizacao . '<br /><br />';

		}
		else {
			echo '<b>Erro: </b>' . $retorno_codigo_erro . '<br />';
			echo '<b>Mensagem: </b>' . $retorno_mensagem_erro . '<br />';
		}

	}

	private function procAutorizacaoDiretaTransacaoGetURL(){

	    // Dados obtidos da loja para a transação
		$formapagamento = new formapagamento($this->formapagamento_id);

		// - dados do processo
		$identificacao = config::get('LOCAWEB_GATEWAY_IDENTIFICACAO');
		$modulo = 'CIELO';
		$operacao = 'Autorizacao-Direta';
		$ambiente = config::get('LOCAWEB_GATEWAY_AMBIENTE');

		// - dados do cartão
		$nome_portador_cartao = $this->nome_titular;
		$numero_cartao = $this->numero_cartao;
		$validade_cartao = $this->ano_validade.$this->mes_validade;

		// Indicador do código de segurança do cartão. Utilizar: 0 (não informado), 1 (informado), 2 (ilegível) e 9 (inexistente). Para Mastercard, deve ser sempre 1.
		$indicador_cartao = '1';
		$codigo_seguranca_cartao = $this->cod_seguranca;

		// - dados do pedido
		$idioma = 'PT';
		$valor = str_replace(array('.',','),'',money($this->valor));
		$pedido = $this->id;
		$descricao = 'Ref. compra no site '.config::get('EMPRESA').' #'.$this->id;

		// - dados do pagamento
		if($formapagamento->id==formapagamento::get('VISA')->id){
			$bandeira = 'visa';
		}
		elseif($formapagamento->id==formapagamento::get('MASTERCARD')->id){
			$bandeira = 'mastercard';
		}
		elseif($formapagamento->id==formapagamento::get('AMEX')->id){
			$bandeira = 'amex';
		}
		elseif($formapagamento->id==formapagamento::get('DINERS')->id){
			$bandeira = 'diners';
		}

		// $bandeira = '';

		// Forma de pagamento. Utilizar: 1 (Crédito à Vista), 2 (Parcelado loja), 3 (Parcelado administradora), A (Débito)
		$forma_pagamento = $this->parcelas==1 ? '1' : '2';
		$parcelas = $this->parcelas;
		$autorizar = '';

		$capturar = 'false';

		if($formapagamento->id==formapagamento::get('VISA')->id
		&& config::get('VISA_CAPTURA_AUTOMATICA') == 'N' ){
			$capturar = 'false';
		}
		elseif($formapagamento->id==formapagamento::get('MASTERCARD')->id
		&& config::get('MASTERCARD_CAPTURA_AUTOMATICA') == 'N' ){
			$capturar = 'false';
		}

		// - dados adicionais
		$campo_livre = $this->id;

		// Monta a variável com os dados para postagem
		$request = 'identificacao=' . $identificacao;
		$request .= '&modulo=' . $modulo;
		$request .= '&operacao=' . $operacao;
		$request .= '&ambiente=' . $ambiente;

		$request .= '&nome_portador_cartao=' . $nome_portador_cartao;
		$request .= '&numero_cartao=' . $numero_cartao;
		$request .= '&validade_cartao=' . $validade_cartao;
		$request .= '&indicador_cartao=' . $indicador_cartao;
		$request .= '&codigo_seguranca_cartao=' . $codigo_seguranca_cartao;

		$request .= '&idioma=' . $idioma;
		$request .= '&valor=' . $valor;
		$request .= '&pedido=' . $pedido;
		$request .= '&descricao=' . $descricao;

		$request .= '&bandeira=' . $bandeira;
		$request .= '&forma_pagamento=' . $forma_pagamento;
		$request .= '&parcelas=' . $parcelas;
		$request .= '&autorizar=' . $autorizar;
		$request .= '&capturar=' . $capturar;

		$request .= '&campo_livre=' . $campo_livre;

		// Faz a postagem para a Cielo
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, 'https://comercio.locaweb.com.br/comercio.comp');
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $request);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$response = curl_exec($ch);
		curl_close($ch);

		return $response;
	}





	// static function

	static function opcoes_parcelas($valor_total=0){

		$ret = array();

		if($valor_total > 0){

			// Monta opcoes de parcelamento
			for( $i = 1, $n = getMaximoParcelas($valor_total); $i <= $n; $i ++ ){

				// Se a parcela 1, mostra no texto Á Vista
				if($i==1){
					$ret[$i] = '1x '.money($valor_total/$i);;
				}
				// Mostra label de parcelamento
				else{
					$ret[$i] = $i. 'x de '. money($valor_total/$i);
				}
			}

		}

		return $ret;

	}

	static function opcoes_mes_validade(){

		return array(
			'01' => 'Janeiro','02' => 'Fevereiro','03' => 'Março','04' => 'Abril','05' => 'Maio','06' => 'Junho','07' => 'Julho','08' => 'Agosto','09' => 'Setembro','10' => 'Outubro','11' => 'Novembro','12' => 'Dezembro'
		);

	}

	static function opcoes_ano_validade(){

		$ret = array();

		//set Ano do cartao de credito
		$anoAtual = date('Y');

		$ano ='';

		$i = 0;
		while($i <= 10){
			$ano .= $anoAtual + $i.' ';
			$i++;
		}

		$anos = array();
		$anos = explode(" ", $ano);

		foreach ($anos as $nanos => $ano){
			if($ano != ''){
				$ret[$ano] = $ano;
			}
		}

		return $ret;

	}

	// Valida dados
	public function validaDados(){
	
		if(isset($_REQUEST['gateway_cartao'])&&($_REQUEST['gateway_cartao'] =='on')){
			if(!$this->formapagamento_id){
				throw new Exception('Cart&atilde;o n&atilde;o identificado. Digite um cart&atilde;o v&aacute;lido.');
			}
		}

		$formapagamento = new formapagamento($this->formapagamento_id);
		if(!$formapagamento->id){
			throw new Exception('Selecione a forma de pagamento');
		}

		if($formapagamento->isBoleto()){
			$this->parcelas = 1;
		}

		// Valida pagamento com cartão
		if($formapagamento->isCartaoCredito()
		&& $formapagamento->st_digita_dados_site == 'S'){

			if(trim($this->numero_cartao)==''){
				throw new Exception('Cartão inválido');
			}
			$numero_cartao = getNumbers($this->numero_cartao);
			if(strlen($numero_cartao)<13){
				throw new Exception('Cartão inválido');
			}
			if(intval($this->parcelas)==0){
				throw new Exception('É preciso definir as parcelas');
			}
			if(trim($this->nome_titular)==''){
				throw new Exception('Digite o nome do titular');
			}
			$this->cod_seguranca = trim(getNumbers($this->cod_seguranca));
			if($this->cod_seguranca!=''){
				if(strlen($this->cod_seguranca)<3){
					throw new Exception('Código de segurança inválido');
				}
				if(strlen($this->cod_seguranca)>5){
					throw new Exception('Código de segurança inválido');
				}
			}
			$this->ano_validade = intval($this->ano_validade);
			if(strlen($this->ano_validade)!=4){
				throw new Exception('Digite o ano de validade no formato yyyy, por exemplo '.(date('Y')+2));
			}
			// Os anos válidos são: ano atual + 20 anos.
			$maior_ano_possivel = date('Y') + 20;
			if($this->ano_validade>$maior_ano_possivel){
				throw new Exception('Ano de validade do cartão inválido');
			}
			// Nao valida caso o ano de validade seja menor que no ano atual
			// Recomendacao Certified: 05/08/2011
			if($this->ano_validade < date('Y')){
				// throw new Exception('Ano de validade do cartão inválido');
			}

		}

		// Valida duplicidade
		$this->validaDuplicidade();

	}


	// Valida duplicidade da transação
	public function validaDuplicidade(){

		$pesquisa = new gateway(
			array(
				'formapagamento_id' => $this->formapagamento_id
				,'parcelas' => $this->parcelas
				,'nome_titular' => $this->nome_titular
				,'numero_cartao' => $this->numero_cartao
				,'cod_seguranca' => $this->cod_seguranca
				,'mes_validade' => $this->mes_validade
				,'ano_validade' => $this->ano_validade
				,'valor' => $this->valor
				,'date_format(data_cadastro,\'%d/%m/%Y %H\')' => date('d/m/Y H')
			)

		);

		if($pesquisa->id){
			throw new Exception('Ops! Houve um erro ao transmitir seu pagamento, mas provavelmente ele já foi processado. Favor entrar em contato conosco para finalizar sua compra: '.config::get('TELEFONE'));
			// throw new Exception('Aparentemente o seu pagamento com cartão já foi enviado, por favor entre em contato conosco para finalizar a sua compra: '.config::get('TELEFONE'));
		}

	}

}
