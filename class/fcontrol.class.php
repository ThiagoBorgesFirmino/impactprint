<?php

/**

Classe de integracao com a FControl

**/

/**

Exemplo de iframe

<iframe height="110" frameborder="0" width="300" src="https://secure.fcontrol.com.br/validatorframe/validatorframe.aspx
?login=meuLogin
&senha=minhaSenha
&nomeComprador=Nome+Do+Comprador
&ruaComprador=Nome+Da+Rua+Do+Comprador
&numeroComprador=339
&cidadeComprador=Nome+Da+Cidade+Comprador
&ufComprador=PR
&paisComprador=BRA
&cepComprador=83300000
&cpfComprador=11111111111
&emailComprador=email@comprador.com.br
&nomeEntrega=Nome+Para+Entrega
&ruaEntrega=Nome+Da+Rua+Para+Entrega
&cidadeEntrega=Nome+Cidade+Para+Entrega
&ufEntrega=PR
&paisEntrega=BRA
&cepEntrega=83222111
&dddEntrega=41
&telefoneEntrega=33334444
&codigoPedido=COD0001
&quantidadeItensDistintos=1
&quantidadeTotalItens=1
&valorTotalCompra=64326
&dataCompra=07/07/2010+15:00:00
&metodoPagamentos=5
&numeroParcelasPagamentos=3
&valorPagamentos=21442"> </iframe>
**/

/**

Configurações necessarias para funcionar:

HABILITA_FCONTROL -> S
FCONTROL_LOGIN -
FCONTROL_SENHA -

INSERT INTO config (grupo, chave, valor, st_podealterar, st_podeexcluir, st_admin, st_tipocampo) VALUES ('FControl', 'HABILITA_FCONTROL', 'S', 'S', 'N', 'N', 'TBOOLEAN');
INSERT INTO config (grupo, chave, valor, st_podealterar, st_podeexcluir, st_admin, st_tipocampo) VALUES ('FControl', 'FCONTROL_LOGIN', 'S', 'S', 'N', 'N', 'TLINE');
INSERT INTO config (grupo, chave, valor, st_podealterar, st_podeexcluir, st_admin, st_tipocampo) VALUES ('FControl', 'FCONTROL_SENHA', 'S', 'S', 'N', 'N', 'TLINE');

<iframe height="110" frameborder="0" width="300" src="https://secure.fcontrol.com.br/validatorframe/validatorframe.aspx?login=meuLogin&senha=minhaSenha&nomeComprador=Nome+Do+Comprador&ruaComprador=Nome+Da+Rua+Do+Comprador&numeroComprador=339&cidadeComprador=Nome+Da+Cidade+Comprador&ufComprador=PR&paisComprador=BRA&cepComprador=83300000&cpfComprador=11111111111&emailComprador=email@comprador.com.br&nomeEntrega=Nome+Para+Entrega&ruaEntrega=Nome+Da+Rua+Para+Entrega&cidadeEntrega=Nome+Cidade+Para+Entrega&ufEntrega=PR&paisEntrega=BRA&cepEntrega=83222111&dddEntrega=41&telefoneEntrega=33334444&codigoPedido=COD0001&quantidadeItensDistintos=1&quantidadeTotalItens=1&valorTotalCompra=64326&dataCompra=07/07/2010+15:00:00&metodoPagamentos=5&numeroParcelasPagamentos=3&valorPagamentos=21442"> </iframe>

**/



class fcontrol {

	static function Iframe(
		$login
		,$senha
		,$nomeComprador
		,$ruaComprador
		,$numeroComprador
		,$cidadeComprador
		,$ufComprador
		,$paisComprador
		,$cepComprador
		,$cpfComprador
		,$emailComprador
		,$dddComprador
		,$telefoneComprador
		,$dddCelularComprador
		,$celularComprador
		,$nomeEntrega
		,$ruaEntrega
		,$cidadeEntrega
		,$ufEntrega
		,$paisEntrega
		,$cepEntrega
		,$dddEntrega
		,$telefoneEntrega
		,$codigoPedido
		,$quantidadeItensDistintos
		,$quantidadeTotalItens
		,$valorTotalCompra
		,$dataCompra
		,$metodoPagamentos
		,$numeroParcelasPagamentos
		,$valorPagamentos){

		$ret = '';

		// Encode params
		$login = urlencode($login);
		$senha = urlencode($senha);
		$nomeComprador = urlencode($nomeComprador);
		$ruaComprador = urlencode($ruaComprador);
		$numeroComprador = urlencode($numeroComprador);
		$cidadeComprador = urlencode($cidadeComprador);
		$ufComprador = urlencode($ufComprador);
		$paisComprador = urlencode($paisComprador);
		$cepComprador = urlencode($cepComprador);
		$cpfComprador = urlencode($cpfComprador);
		$emailComprador = urlencode($emailComprador);
		
		//Correçao para os telefone devido a mudança no cadastro
		//FONE RES
		if($dddComprador==''){
			$fone_res_ddd = substr(fcontrol::cleanFone($telefoneComprador),0,2);
			$fone_res     = substr(fcontrol::cleanFone($telefoneComprador),2);
		
			$dddComprador      = fcontrol::cleanFone($fone_res_ddd);
			$telefoneComprador = fcontrol::cleanFone($fone_res);			
		}else{
			$dddComprador      = fcontrol::cleanFone(urlencode($dddComprador));
			$telefoneComprador = fcontrol::cleanFone(urlencode($telefoneComprador));			
		}
		
		//FONE CEL
		if($dddCelularComprador==''){			
			$fone_cel_ddd = substr(fcontrol::cleanFone($celularComprador),0,2);
			$fone_cel     = substr(fcontrol::cleanFone($celularComprador),2);
			
			$dddCelularComprador = fcontrol::cleanFone($fone_cel_ddd);
			$celularComprador    = fcontrol::cleanFone($fone_cel);		
		}else{
			$dddCelularComprador = fcontrol::cleanFone(urlencode($dddCelularComprador));
			$celularComprador    = fcontrol::cleanFone(urlencode($celularComprador));
		}
		//***//
		
		$nomeEntrega = urlencode($nomeEntrega);
		$ruaEntrega = urlencode($ruaEntrega);
		$cidadeEntrega = urlencode($cidadeEntrega);
		$ufEntrega = urlencode($ufEntrega);
		$paisEntrega = urlencode($paisEntrega);
		$cepEntrega = urlencode($cepEntrega);
		
		
		//Correçao para os telefone devido a mudança no cadastro
		if($dddEntrega==''){
			$entrega_ddd     = substr(fcontrol::cleanFone($telefoneEntrega),0,2);
			$entrega_fone    = substr(fcontrol::cleanFone($telefoneEntrega),2);
			
			$dddEntrega      = fcontrol::cleanFone($entrega_ddd);
			$telefoneEntrega = fcontrol::cleanFone($entrega_fone);
		}else{
			$dddEntrega      = fcontrol::cleanFone(urlencode($dddEntrega));
			$telefoneEntrega = fcontrol::cleanFone(urlencode($telefoneEntrega));
		}
		
		$codigoPedido = urlencode($codigoPedido);
		$quantidadeItensDistintos = urlencode($quantidadeItensDistintos);
		$quantidadeTotalItens = urlencode($quantidadeTotalItens);
		$valorTotalCompra = urlencode($valorTotalCompra);
		$dataCompra = urlencode($dataCompra);
		$metodoPagamentos = urlencode($metodoPagamentos);
		$numeroParcelasPagamentos = urlencode($numeroParcelasPagamentos);
		$valorPagamentos = urlencode($valorPagamentos);

		$ret .= "<iframe height='110' frameborder='0' width='300' src='https://secure.fcontrol.com.br/validatorframe/validatorframe.aspx";
		$ret .= "?login={$login}";
		$ret .= "&senha={$senha}";
		$ret .= "&nomeComprador={$nomeComprador}";
		$ret .= "&ruaComprador={$ruaComprador}";
		$ret .= "&numeroComprador={$numeroComprador}";
		$ret .= "&cidadeComprador={$cidadeComprador}";
		$ret .= "&ufComprador={$ufComprador}";
		$ret .= "&paisComprador={$paisComprador}";
		$ret .= "&cepComprador={$cepComprador}";
		$ret .= "&cpfComprador={$cpfComprador}";
		$ret .= "&emailComprador={$emailComprador}";
		$ret .= "&dddComprador={$dddComprador}";
		$ret .= "&telefoneComprador={$telefoneComprador}";
		$ret .= "&dddCelularComprador={$dddCelularComprador}";
		$ret .= "&celularComprador={$celularComprador}";
		$ret .= "&nomeEntrega={$nomeEntrega}";
		$ret .= "&ruaEntrega={$ruaEntrega}";
		$ret .= "&cidadeEntrega={$cidadeEntrega}";
		$ret .= "&ufEntrega={$ufEntrega}";
		$ret .= "&paisEntrega={$paisEntrega}";
		$ret .= "&cepEntrega={$cepEntrega}";
		$ret .= "&dddEntrega={$dddEntrega}";
		$ret .= "&telefoneEntrega={$telefoneEntrega}";
		$ret .= "&codigoPedido={$codigoPedido}";
		$ret .= "&quantidadeItensDistintos={$quantidadeItensDistintos}";
		$ret .= "&quantidadeTotalItens={$quantidadeItensDistintos}";
		$ret .= "&valorTotalCompra={$valorTotalCompra}";
		$ret .= "&dataCompra={$dataCompra}";
		$ret .= "&metodoPagamentos={$metodoPagamentos}";
		$ret .= "&numeroParcelasPagamentos={$numeroParcelasPagamentos}";
		$ret .= "&valorPagamentos={$valorPagamentos}'></iframe>";

		return $ret;

	}

	static function cleanFone($str){
		return str_replace(array('-',' ','_','(',')'), '', $str);
	}

}

// class fcontrolData {

	// private $

	// ?login=meuLogin
// &senha=minhaSenha
// &nomeComprador=Nome+Do+Comprador
// &ruaComprador=Nome+Da+Rua+Do+Comprador
// &numeroComprador=339
// &cidadeComprador=Nome+Da+Cidade+Comprador
// &ufComprador=PR
// &paisComprador=BRA
// &cepComprador=83300000
// &cpfComprador=11111111111
// &emailComprador=email@comprador.com.br
// &nomeEntrega=Nome+Para+Entrega
// &ruaEntrega=Nome+Da+Rua+Para+Entrega
// &cidadeEntrega=Nome+Cidade+Para+Entrega
// &ufEntrega=PR
// &paisEntrega=BRA
// &cepEntrega=83222111
// &dddEntrega=41
// &telefoneEntrega=33334444
// &codigoPedido=COD0001
// &quantidadeItensDistintos=1
// &quantidadeTotalItens=1
// &valorTotalCompra=64326
// &dataCompra=07/07/2010+15:00:00
// &metodoPagamentos=5
// &numeroParcelasPagamentos=3
// &valorPagamentos=21442"> </iframe>

// }


?>