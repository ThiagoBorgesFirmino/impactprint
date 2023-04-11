<?php

// Modelo de dados
class endereco extends base {

	var
		$id
		,$cadastro_id
		,$nome
		,$sobrenome
		,$pto_referencia
		,$identificacao
		,$logradouro
		,$numero
		,$complemento
		,$bairro
		,$cidade
		,$cep
		,$uf
		,$email
		,$fone_com
		,$fone_com_ddd
		,$fone_res
		,$fone_res_ddd
		,$fone_cel
		,$fone_cel_ddd
		,$st_cobranca_padrao
		,$st_entrega_padrao
		,$data_cadastro
		,$data_alteracao
	;

	public function getEtiquetaHtml(){

		$complemento = $this->complemento!=""?"{$this->complemento},":"";

		$titulo = '';
		$titulo2 = '';

		if($this->st_cobranca_padrao == 'S'){
			$titulo = 'Endereço de Cobrança Padrão';
		}
		if($this->st_entrega_padrao == 'S'){
			$titulo2 = 'Endereço de Entrega Padrão';
		}


		return
		"
		<table border=\"0\" class=\"t-enderecos\">
		<tr>
			<td align=left>
				<p>{$titulo}</p>
				<p>{$titulo2}</p>
			</td>
		</tr>

		<tr>
			<td align=left>
				{$this->logradouro}, {$this->numero}, {$complemento} {$this->bairro}
			</td>
		</tr>
		<tr>
			<td align=left>
				{$this->cidade} - {$this->uf}
			</td>
		</tr>
		<tr>
			<td align=left>
				{$this->cep}
			</td>
		</tr>

		<tr>
			<td>
				<a href={index}us_end_edit/{$this->id}>Editar endereço</a>
			</td>
		</tr>
		</table>
		";

		return '';

		// Felipe Gregorio
		// Rua Bernardino Estazione, 1, Vila das Belezas
		// Sao Paulo - SP
		// 05840-030
		// (11)2509-1263

	}

	public function getEtiquetaHtmlAdmin(){

		$complemento = $this->complemento!=""?"{$this->complemento},":"";

		$titulo = '';
		$titulo2 = '';

		if($this->st_cobranca_padrao == 'S'){
			$titulo = 'Endereço de Cobrança Padrão';
		}
		if($this->st_entrega_padrao == 'S'){
			$titulo2 = 'Endereço de Entrega Padrão';
		}

		return
		"
		<table border=\"0\" class=\"t-enderecos\">
		<tr>
			<td align=left>
				<p>{$titulo}</p>
				<p>{$titulo2}</p>
			</td>
		</tr>
		<tr>
			<td align=left>
				{$this->logradouro}, {$this->numero}, {$complemento} {$this->bairro}
			</td>
		</tr>
		<tr>
			<td align=left>
				{$this->cidade} - {$this->uf}
			</td>
		</tr>
		<tr>
			<td align=left>
				{$this->cep}
			</td>
		</tr>
		<tr>
			<td>
				<a href='".PATH_SITE."admin.php/enderecoEditar/?id={$this->id}&cadastro_id={$this->cadastro_id}&action=editar&pop=1'>Editar endereço</a>
			</td>
		</tr>
		</table>
		";
	}


	public function ValidaDados(){
		$erro = '';
		// if($this->nome == ''){
			// $erro .= tag('p', 'Digite seu nome');
		// }
		// if($this->sobrenome == ''){
			// $erro .= tag('p', 'Digite seu sobrenome');
		// }

		// if($this->fone_res_ddd == ''){
			// $erro .= tag('p', 'Digite o DDD do seu telefone');
		// }
		// if($this->fone_res == ''){
			// $erro .= tag('p', 'Digite seu telefone');
		// }

		// if($this->fone_cel_ddd == ''){
			// $erro .= tag('p', 'Digite o DDD do seu celular');
		// }
		// if($this->fone_cel == ''){
			// $erro .= tag('p', 'Digite seu celular');
		// }

		if($this->cep == ''){
			$erro .= tag('p', 'Digite seu cep');
		}
		if($this->logradouro == ''){
			$erro .= tag('p', 'Digite seu logradouro');
		}
		if($this->numero == ''){
			$erro .= tag('p', 'Digite seu numero');
		}
		if($this->bairro == ''){
			$erro .= tag('p', 'Digite seu bairro');
		}
		if($this->cidade == ''){
			$erro .= tag('p', 'Digite seu cidade');
		}
		if($this->uf == ''){
			$erro .= tag('p', 'Digite seu estado');
		}

		return $erro;

	}

	public function getStCobrancaPadraoChecked(){
		return $this->st_cobranca_padrao == 'S' ? 'checked' : '';
	}

	public function getStEntregaPadraoChecked(){
		return $this->st_entrega_padrao == 'S' ? 'checked' : '';
	}



	public function setEntregaPadrao(){
		query("UPDATE endereco SET st_entrega_padrao = 'N' WHERE cadastro_id = {$this->cadastro_id}");
		query("UPDATE endereco SET st_entrega_padrao = 'S' WHERE id = {$this->id}");
	}


}

?>