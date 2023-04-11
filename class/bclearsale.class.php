<?php

class bclearsale
{

	/*
	Tipo de Pagamento
	Código Descrição
	1 Cartão de Crédito
	2 Bloqueto Bancário
	3 Débito Bancário
	4 Débito Bancário – Dinheiro
	5 Débito Bancário – Cheque
	6 Transferência Bancária
	7 Sedex a Cobrar
	8 Cheque
	9 Dinheiro
	10 Financiamento
	11 Fatura
	12 Cupom
	13 Multicheque
	14 Outros
	*/
	
	const TIPOPAGAMENTO_CARTAODECREDITO = 1;
	const TIPOPAGAMENTO_BLOQUETOBANCARIO = 2;
	const TIPOPAGAMENTO_DEBITOBANCARIO = 3;
	const TIPOPAGAMENTO_DEBITOBANCARIODINHEIRO = 4;
	const TIPOPAGAMENTO_DEBITOBANCARIOCHEQUE = 5;
	const TIPOPAGAMENTO_TRANSFERENCIABANCARIA = 6;
	const TIPOPAGAMENTO_SEDEXACOBRAR = 7;
	const TIPOPAGAMENTO_CHEQUE = 8;
	const TIPOPAGAMENTO_DINHEIRO = 9;
	const TIPOPAGAMENTO_FINANCIAMENTO = 10;
	const TIPOPAGAMENTO_FATURA = 11;
	const TIPOPAGAMENTO_CUPOM = 12;
	const TIPOPAGAMENTO_MULTICHEQUE = 13;
	const TIPOPAGAMENTO_OUTROS = 14;

	/*
	Bandeira Cartão
	Código Descrição
	1 Diners
	2 MasterCard
	3 Visa
	4 Outros
	5 American Express
	6 HiperCard
	7 Aura
	*/
	
	const  BANDEIRACARTAO_DINERS = 1;
	const  BANDEIRACARTAO_MASTERCARD = 2;
	const  BANDEIRACARTAO_VISA = 3;
	const  BANDEIRACARTAO_OUTROS = 4;
	const  BANDEIRACARTAO_AMERICAN_EXPRESS = 5;
	const  BANDEIRACARTAO_HIPERCARD = 6;
	const  BANDEIRACARTAO_AURA = 7;
	
	static function Iframe (
		$ambiente='homologacao' // || producao
		,$CodigoIntegracao
		,$PedidoID
		,$Data
		,$IP
		,$Total
		,$TipoPagamento
		,$TipoCartao
		,$Parcelas
		,$Cobranca_Nome
		,$Cobranca_Email
		,$Cobranca_Documento
		,$Cobranca_Logradouro
		,$Cobranca_Logradouro_Numero
		,$Cobranca_Logradouro_Complemento
		,$Cobranca_Bairro
		,$Cobranca_Cidade
		,$Cobranca_Estado
		,$Cobranca_CEP
		,$Cobranca_Pais
		,$Cobranca_DDD_Telefone
		,$Cobranca_Telefone
		,$Cobranca_DDD_Celular
		,$Cobranca_Celular
		,$Entrega_Nome
		,$Entrega_Email
		,$Entrega_Documento
		,$Entrega_Logradouro
		,$Entrega_Logradouro_Numero
		,$Entrega_Logradouro_Complemento
		,$Entrega_Bairro
		,$Entrega_Cidade
		,$Entrega_Estado
		,$Entrega_CEP
		,$Entrega_Pais
		,$Entrega_DDD_Telefone
		,$Entrega_Telefone
		,$Entrega_DDD_Celular
		,$Entrega_Celular
		,$itens = array()){
	
		if($ambiente=='homologacao'){
			$host = "homologacao.clearsale.com.br";
		}
		elseif($ambiente=='producao'){
			$host = "www.clearsale.com.br";
		}
		else {
			throw new Exception('Não foi possível definir se o ambiente é de "homologacao" ou "producao"');
		}
		
		$path = "/integracaov2/FreeClearSale/frame.aspx";
		$port = "80";
		
		$param = array();
		
		// CodigoIntegracao:
		$param['CodigoIntegracao'] = $CodigoIntegracao;
		
		// Dados do Pedido
		$param['PedidoID'] = $PedidoID;
		// $param['Data'] = $Data;
		$param['Data'] = '06/09/2011 14:00:00';
		$param['IP'] = $IP;
		$param['Total'] = $Total;
		$param['TipoPagamento'] = $TipoPagamento;
		$param['TipoCartao'] = $TipoCartao;
		$param['Parcelas'] = $Parcelas;
		
		// Dados de Cobrança
		$param['Cobranca_Nome'] = $Cobranca_Nome;
		$param['Cobranca_Email'] = $Cobranca_Email;
		$param['Cobranca_Documento'] = $Cobranca_Documento;
		$param['Cobranca_Logradouro'] = $Cobranca_Logradouro;
		$param['Cobranca_Logradouro_Numero'] = $Cobranca_Logradouro_Numero;
		$param['Cobranca_Logradouro_Complemento'] = $Cobranca_Logradouro_Complemento;
		$param['Cobranca_Bairro'] = $Cobranca_Bairro;
		$param['Cobranca_Cidade'] = $Cobranca_Cidade;
		$param['Cobranca_Estado'] = $Cobranca_Estado;
		$param['Cobranca_CEP'] = $Cobranca_CEP;
		$param['Cobranca_Pais'] = $Cobranca_Pais;
		$param['Cobranca_DDD_Telefone'] = $Cobranca_DDD_Telefone;
		$param['Cobranca_Telefone'] = $Cobranca_Telefone;
		$param['Cobranca_DDD_Celular'] = $Cobranca_DDD_Celular;
		$param['Cobranca_Celular'] = $Cobranca_Celular;
		
		// Dados de Entrega
		$param['Entrega_Nome'] = $Entrega_Nome;
		$param['Entrega_Email'] = $Entrega_Email;
		$param['Entrega_Documento'] = $Entrega_Documento;
		$param['Entrega_Logradouro'] = $Entrega_Logradouro;
		$param['Entrega_Logradouro_Numero'] = $Entrega_Logradouro_Numero;
		$param['Entrega_Logradouro_Complemento'] = $Entrega_Logradouro_Complemento;
		$param['Entrega_Bairro'] = $Entrega_Bairro;
		$param['Entrega_Cidade'] = $Entrega_Cidade;
		$param['Entrega_Estado'] = $Entrega_Estado;
		$param['Entrega_CEP'] = $Entrega_CEP;
		$param['Entrega_Pais'] = $Entrega_Pais;
		$param['Entrega_DDD_Telefone'] = $Entrega_DDD_Telefone;
		$param['Entrega_Telefone'] = $Entrega_Telefone;
		$param['Entrega_DDD_Celular'] = $Entrega_DDD_Celular;
		$param['Entrega_Celular'] = $Entrega_Celular;
		
		$i = 1;
		foreach ( $itens as $item )	
		{
			$param["Item_ID_{$i}"] = $item->ID;
			$param["Item_Nome_{$i}"] = $item->Nome;
			$param["Item_Qtd_{$i}"] = $item->Qtd;
			$param["Item_Valor_{$i}"] = $item->Valor;
			$param["Item_Categoria_{$i}"] = $item->Categoria;
			
			$i ++;
		}
		
		$request = '';
		foreach($param as $key => $value){
			if($request!=''){
				$request .= '&';
			}
			else {
				$request .= '?';
			}
			$request .= $key.'='.urlencode(@iconv("UTF-8", "ISO-8859-1", $value));
		}

		return '<iframe src="http://'.$host.$path.$request.'" width="280" height="85" frameborder="0" scrolling="no"><P>Seu Browser não suporta iframes</P></iframe>';
	
	}	
}

?>