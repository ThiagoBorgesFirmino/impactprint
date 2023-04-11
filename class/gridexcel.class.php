<?php

// Classe para exibicao de um "grid" na tela
class gridexcel {

	public
		$sql
		,$filtro 
		,$html ;
		
	public
		$botao_excluir = true;
		
	public function render() {

		ob_start();
		
		if(!@$this->filtro)
		{
			$this->filtro = new filtro();
		}

		$sql = "select * from ({$this->sql}) as x where 1=1 {$this->filtro->get_where()}" ;
		
		// printr($sql);
		// die();
		
		// Chega se o usuario selecionou algum filtro de coluna/titulo e se esta preenchido com um array
		if(array_key_exists('EXP_EXCEL_FILTRO_TITULO',$_SESSION) && is_array($_SESSION['EXP_EXCEL_FILTRO_TITULO']) ){
			foreach($_SESSION['EXP_EXCEL_FILTRO_TITULO'] as $key => $value ){
			
				// printr($key);
				// printr($value);
				
				if(trim($value)!='-----'){
					$sql .= "AND {$key} = '{$value}'";
				}
			}
			
		}

		$sort = request('sort');
		$sort_ordem = request('sort_ordem');

		if($sort){
			$sql .= 'order by '.$sort . ' ' . $sort_ordem;
		}

		//print $sql ;

		$query = query($sql);
		$ntot    = rows($query);

		$query = query($sql);
		//print $sql ;

		?>
		<?php
		$nao_aparece = explode(',',@$this->nao_aparece);
		for( $i = 0, $m = mysql_num_fields($query) ; $i < $m ; $i ++ ) {
			$c = mysql_field_name($query,$i) ;
			if(strtoupper($c)=='FIXO'||strtoupper($c)=='_ID') {
				continue;
			}
			if(in_array($c,$nao_aparece)){
				continue;
			}
			if($c=='Status'){
				continue;
			}
			$this->output(str_replace('_',' ',$c));
		}
		
		print "\n";
		
		while( $fetch = fetch($query) ) {
			for( $i = 0 ; $i < $m ; $i ++ ) {
				$c = mysql_field_name($query,$i) ;
				if(strtoupper($c)=='FIXO'||strtoupper($c)=='_ID') {
					continue;
				}
				if(in_array($c,$nao_aparece)){
					continue;
				}
				if( preg_match('/([0-9]{4})-([0-9]{2})-([0-9]{2}) ([0-9]{2}):([0-9]{2}):([0-9]{2})/', $fetch->$c, $matches) ){
					//printr($matches);
					list(,$ano,$mes,$dia,$hora,$minuto,$segundo)=$matches;
					$fetch->$c = $dia.'/'.$mes.'/'.$ano .' ' .$hora.':'.$minuto.':'.$segundo	;
				}
				if($c=='Status'){
					continue;
				}
				$this->output($fetch->$c);
			}
			print "\n";
		}
		?>
		<?php
		$this->html = ob_get_contents();
		ob_end_clean();
		return $this->html ;
	}
	
	private function output($str)
	{
		print iconv('utf-8','iso-8859-1//TRANSLIT',str_replace(";",'',$str)).";";
	}
	
}
?>