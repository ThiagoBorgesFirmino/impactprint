<?php

class filtroutil {

	

	static function getDistinctValoresByTipo($sql, $tipo){

		$ret = array();

		$sql =
		"
		SELECT
		SQL_CACHE
			DISTINCT
			item.skuvariacao_valor1
		FROM
			item
		INNER JOIN ({$sql}) AS tmp_item ON (
			item.itemsku_id = tmp_item.id
		AND item.st_ativo = 'S' -- Apenas itens ativos
		AND item.preco > 0 -- Apenas itens com preco
		AND item.qtd_estoque > 0
		AND item.skuvariacao_tipo1 = '{$tipo}'
		)
		ORDER BY
			skuvariacao_valor1
		";
		
		// printr($sql);

		$query = query($sql);
		while($fetch=fetch($query)){
			$ret[] = $fetch->skuvariacao_valor1;
		}

		return $ret;

	}
	
	

	static function getDistinctVariacoesKey($sql){
	
		return array_unique(self::getDistinctVariacoesKey1($sql)+self::getDistinctVariacoesKey2($sql));
	
		// $ret = array();

		// $sql =
		// "
		// SELECT
		// SQL_CACHE
			// DISTINCT
			// skuvariacao_tipo
		// FROM
			// item
		// INNER JOIN ({$sql}) AS tmp_item ON (
			// item.itemsku_id = tmp_item.id
		// AND item.st_ativo = 'S' -- Apenas itens ativos
		// AND item.preco > 0 -- Apenas itens com preco
		// AND item.qtd_estoque > 0
		// )
		// ORDER BY
			// skuvariacao_tipo
		// ";
		
		// printr($sql);

		// $query = query($sql);
		// while($fetch=fetch($query)){
			// $ret[] = $fetch->skuvariacao_tipo;
		// }

		// return $ret;
	
	}
	
	static function getDistinctVariacoesKey1($sql){
	
		$ret = array();

		$sql =
		"
		SELECT
		SQL_CACHE
			DISTINCT
			skuvariacao_tipo1
		
		FROM
			item
		INNER JOIN ({$sql}) AS tmp_item ON (
			item.itemsku_id = tmp_item.id
		AND item.st_ativo = 'S' -- Apenas itens ativos
		AND item.preco > 0 -- Apenas itens com preco
		AND item.qtd_estoque > 0
		)
	
		WHERE
			skuvariacao_tipo1 <> ''
	
		ORDER BY 
			skuvariacao_tipo1				
		";
		
		// printr($sql);

		$query = query($sql);
		while($fetch=fetch($query)){
			$ret[] = $fetch->skuvariacao_tipo1;
		}

		return $ret;
	
	}
	
	static function getDistinctVariacoesKey2($sql){
	
		$ret = array();

		$sql =
		"
		SELECT
		SQL_CACHE
			DISTINCT
			skuvariacao_tipo2

		FROM
			item
		INNER JOIN ({$sql}) AS tmp_item ON (
			item.itemsku_id = tmp_item.id
		AND item.st_ativo = 'S' -- Apenas itens ativos
		AND item.preco > 0 -- Apenas itens com preco
		AND item.qtd_estoque > 0
		)
		
		WHERE
			skuvariacao_tipo2 <> ''

		ORDER BY 
				skuvariacao_tipo2			
		
		";
		
		// printr($sql);

		$query = query($sql);
		while($fetch=fetch($query)){
			$ret[] = $fetch->skuvariacao_tipo2;
		}

		return $ret;
	
	}
	
	static function getDistinctValoresByVariacaoKey($sql, $key){
		// printr($sql);
		// die();
		$ret = array();
		if($key == 'Sabor'){
			$sql =
			"
			SELECT
			SQL_CACHE
				DISTINCT
				CASE WHEN item.skuvariacao_tipo1 = 'Sabor' THEN 
					item.skuvariacao_valor1 
				ELSE 
					item.skuvariacao_valor2 
				END valor
				,count(pedidoitem.id) count_ped_item 
			FROM
				item
			INNER JOIN ({$sql}) AS tmp_item ON (
				item.itemsku_id = tmp_item.id
				AND item.st_ativo = 'S' -- Apenas itens ativos
				AND item.preco > 0 -- Apenas itens com preco
				AND ( item.skuvariacao_tipo1 = '{$key}'
					OR item.skuvariacao_tipo2 = '{$key}'
				)
			)
			LEFT OUTER JOIN pedidoitem ON(
				tmp_item.id = pedidoitem.item_id
			) 
			WHERE
				(  item.skuvariacao_tipo1 = '{$key}'
				OR item.skuvariacao_tipo2 = '{$key}'
			)
			GROUP BY
				valor
				
			ORDER BY 
				count_ped_item DESC
			";
		}else{
			$sql =
			"
			SELECT
			SQL_CACHE
				DISTINCT
				item.skuvariacao_valor valor
			FROM
				item
			INNER JOIN ({$sql}) AS tmp_item ON (
				item.itemsku_id = tmp_item.id
			AND item.st_ativo = 'S' -- Apenas itens ativos
			AND item.preco > 0 -- Apenas itens com preco
			AND item.qtd_estoque > 0
			AND item.skuvariacao_tipo = '{$key}'
			)
			ORDER BY 
				skuvariacao_valor
			";
		}
		
		
		// printr($sql);

		$query = query($sql);
		while($fetch=fetch($query)){
			// $ret[] = $fetch->skuvariacao_valor;
			$ret[] = $fetch->valor;
		}

		return $ret;
	
	}
	
}

?>