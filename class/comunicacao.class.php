<?php

class comunicacao {

	public function montaEmailPedidoLoja($pedido){

		$t = new Template('tpl.email-pedido-cliente.html');

		//printr($pedido);

		$cliente = $pedido->get_parent('cliente');
		$representante = $pedido->get_parent('representante');

		$cliente_cadastro = $cliente->get_parent('cadastro');

		$t->cliente_cadastro = $cliente_cadastro;
		$t->cliente_endereco = $cliente->get_parent('endereco');

		$representante_cadastro = $representante->get_parent('cadastro');

		$t->representante_cadastro = $representante_cadastro;
		//$t->representante_endereco = $representante->get_parent('endereco');

		$t->pedido = $pedido;
		$t->configuracao = new configuracao();
		$t->path_peq = PATH_PEQ;
		//$t->workflow = $workflow;

		$valor_total = 0;
		$qtd_itens = 0;

		foreach ( $pedido->get_childs('pedido_item') as $pedido_item ){
			$item = $pedido_item->get_parent('item');

			//$valor_total += $pedido_item->qtd*$pedido_item->item_preco;

			//$pedido_item->sub_total = money($pedido_item->qtd*$pedido_item->item_preco);
			//$pedido_item->item_preco = money($pedido_item->item_preco);

			$t->pedido_item = $pedido_item;
			$t->parseBlock('BLOCK_PEDIDO_ITEM', true);

			//$qtd_itens += $pedido_item->qtd;
		}

		//$t->valor_total = money($valor_total);
		//$t->qtd_itens = $qtd_itens;

		return $t->getContent();
	}

	public function montaEmailProjeto($pedido){

		$t = new Template('tpl.email-pedido-projeto.html');

		//printr($pedido);

		$cliente = $pedido->get_parent('cliente');
		$representante = $pedido->get_parent('representante');

		$cliente_cadastro = $cliente->get_parent('cadastro');

		$t->cliente_cadastro = $cliente_cadastro;
		//$t->cliente_endereco = $cliente->get_parent('endereco');

		$representante_cadastro = $representante->get_parent('cadastro');

		$t->representante_cadastro = $representante_cadastro;
		//$t->representante_endereco = $representante->get_parent('endereco');

		$t->pedido = $pedido;
		$t->configuracao = new configuracao();
		//$t->workflow = $workflow;

		$valor_total = 0;
		$qtd_itens = 0;

		$email = new email();
		$email->addHtml($t->getContent());

		//$t->valor_total = money($valor_total);
		//$t->qtd_itens = $qtd_itens;

		return $t->getContent();
	}

	public function montaEmailPedido($pedido){

		$t = new Template('tpl.email-pedido.html');

		//printr($pedido);

		$cad = $pedido->get_parent('cadastro');

		$t->pedido = $pedido;
		$t->cadastro = $cad;
		$t->configuracao = new configuracao();
		$t->path_peq = PATH_PEQ;

		//$t->workflow = $workflow;

		$valor_total = 0;

		foreach ( $pedido->get_childs('pedido_item') as $pedido_item ){
			$item = $pedido_item->get_parent('item');

			///$valor_total += $pedido_item->qtd*$pedido_item->item_preco;

			//$pedido_item->sub_total = money($pedido_item->qtd*$pedido_item->item_preco);
			//$pedido_item->item_preco = money($pedido_item->item_preco);

			$t->pedido_item = $pedido_item;
			$t->parseBlock('BLOCK_PEDIDO_ITEM', true);
		}

		$email = new email();
		$email->addHtml($t->getContent());

		return $t->getContent();
	}
}

?>