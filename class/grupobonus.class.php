<?php

class grupobonus extends base {

	var
		$id
		,$st_ativo
		,$grupo
		,$valor_inicial
		,$valor_final
		,$qtd_bonus
		,$data_cadastro
		,$data_alteracao;

	//
	public function salva(){
		if(parent::salva()){
			$this->atualizaItensRelacionados();
		}
	}

	// Atualiza a quantidade de bonus nos itens que atendem esse grupo, levando em conta o preco do produto, e a faixa de preco que o grupo de bonus esta configurada
	private function atualizaItensRelacionados(){

		$sql =
		"
		UPDATE item
		SET qtd_bonus = (SELECT qtd_bonus FROM grupobonus WHERE id = {$this->id})
		WHERE preco BETWEEN {$this->valor_inicial} AND {$this->valor_final}
		";

		query($sql);

	}

}

?>