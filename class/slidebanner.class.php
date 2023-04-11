<?php

class slidebanner extends base {

	var
		$id
		,$titulo
		,$tipo
		,$texto
		,$imagem
		,$link;
		
		
	public function __get($value){
		$slidebanner = new slidebanner( array('tipo'=>$value) );
		if($slidebanner->id){
			return PATH_SITE."img/banner/{$slidebanner->imagem}";
		}
		return "";
	}
	
	static function showBanners(& $t){
		$query = query('SELECT * FROM slidebanner WHERE tipo="banner" AND imagem<>""');
		$cont=0;
		while($fetch=fetch($query)){
			$t->display = '';
			$t->bc_class = 'b_container';
			$t->imgB_opacity = '';
			if($cont>0){
				$t->display = 'display:none;';
				$t->bc_class = 'b_container_efect';
				$t->imgB_opacity = 'img_b_opacity';
			}
			$t->slidebanner = $fetch;
			$t->parseBlock('BLOCK_SLIDEBANNER',true);
			$t->parseBlock('BLOCK_SLIDEBANNER_THUMB',true);
			$cont++;
		}
		if(rows($query)>0){
			$t->parseBlock('BLOCK_BANNER');			
		}
	}

}

?>