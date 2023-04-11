<?php
class mosaico extends base {
	var $id;
	var $item_id;
	var $posicao;
	var $tamanho;
	var $data_cadastro;
	
	public function __get($pos){
		$mosaico = new mosaico(array('posicao'=>$pos));
		if($mosaico->id){
			$item = new item($mosaico->item_id);
			if($item->id){
				return "<img src='".PATH_SITE."img/produtos/1/{$item->imagem}' class='set_mosaico' data-id='{$item->id}' />";
			}
		}
		return "<img src='".PATH_SITE."img/mosaico_sem_imagem.png' />";
	}
	
	public function get($pos){
		$mosaico = new mosaico(array('posicao'=>intval($pos)));		
		if(!$this->getShowmosaico()){
			if($mosaico->id){
				$item = new item($mosaico->item_id);
				if($item->id){
					return "<img src='".PATH_SITE."img/produtos/1/{$item->imagem}' class='set_mosaico' data-id='{$item->id}' />";
				}
			}
			return "<img src='".PATH_SITE."img/mosaico_sem_imagem.png' />";
		}else{
			if($mosaico->id){
				$item = new item($mosaico->item_id);
				if($item->id && $item->st_ativo=='S'){
					$query = query("SELECT itemcategoria.* FROM itemcategoria INNER JOIN categoria ON(categoria.id = itemcategoria.categoria_id AND categoria.st_ativo='S') WHERE itemcategoria.item_id = {$item->id}");
					if(rows($query)>0){
						return "<a href='".INDEX."detalhe/{$item->tag_nome}'><img src='".PATH_SITE."timthumb/timthumb.php?src=".PATH_SITE."img/produtos/1/{$item->imagem}&w=339&h=339' /></a>";
					}
				}
			}
			return "<img src='".PATH_SITE."/timthumb/timthumb.php?src=".PATH_SITE."img/marcador_mosaico_home.jpg&w339&h339' />";
		}
	}

	public function setShowmosaico($show=false){
		$_SESSION['showmosaico'] = $show;
	}
	public function getShowmosaico(){
		if(array_key_exists("showmosaico",$_SESSION)){
			return $_SESSION['showmosaico'];
		}
	}
	
	public function itemPos($pos=0){
		$out = new stdClass();
		$out->imagem = '';
		$out->posicao = 0;
		$out->mosaico_id = 0;
		if($pos>0){
			$query = query("SELECT mosaico.* FROM mosaico INNER JOIN item ON(item.id = mosaico.item_id AND item.st_amamos='S') WHERE mosaico.posicao = {$pos} LIMIT 1");
			$fetch = fetch($query);
			$item = new item($fetch->item_id);
			$out->imagem = $item->imagem;
			$out->posicao = $fetch->posicao;
			$out->mosaico_id = $fetch->id;
		}
		return $out;
	}
	
	public function addBlock($qtdblock){
		$block = intval($qtdblock)+1;
		$pagina = floor($block/27);
		$resto  = $block%27;
		
		if($resto>=1&&$resto<=12){
				$bloco = 1;
			}
			if($resto>=13&&$resto<=18){
				$bloco = 2;
			}
			if($resto>=19&&$resto<=27){
				$bloco=3;
			}
		
		$pos = $pagina*27;
		
		$pos++;
		switch($bloco){
			case 1 : 
				return "<li>
						<table cellpadding='0' cellspacing='0' class='t_block'>
							<tr>
								<td class='t_block_m droppable edit_productBox' data-pos='".$pos."'>".($this->getShowmosaico()?"":"<span class='bt_excluir'>X</span>")."".$this->get($pos)."</td>
								<td class='t_block_m droppable edit_productBox' data-pos='".($pos=$pos+1)."'>".($this->getShowmosaico()?"":"<span class='bt_excluir'>X</span>")."".$this->get($pos)."</td>
							</tr>
							<tr>
								<td class='t_block_p'>
									<table cellpadding='0' cellspacing='0'>
										<tr>
											<td class='droppable edit_productBox' data-pos='".($pos=$pos+1)."'>".($this->getShowmosaico()?"":"<span class='bt_excluir'>X</span>")."".$this->get($pos)."</td>
											<td class='droppable edit_productBox' data-pos='".($pos=$pos+1)."'>".($this->getShowmosaico()?"":"<span class='bt_excluir'>X</span>")."".$this->get($pos)."</td>
										</tr>
										<tr>
											<td class='droppable edit_productBox' data-pos='".($pos=$pos+1)."'>".($this->getShowmosaico()?"":"<span class='bt_excluir'>X</span>")."".$this->get($pos)."</td>
											<td class='droppable edit_productBox' data-pos='".($pos=$pos+1)."'>".($this->getShowmosaico()?"":"<span class='bt_excluir'>X</span>")."".$this->get($pos)."</td>
										</tr>
									</table>
								</td>
								<td class='t_block_m droppable edit_productBox' data-pos='".($pos=$pos+1)."'>".($this->getShowmosaico()?"":"<span class='bt_excluir'>X</span>")."".$this->get($pos)."</td>
							</tr>
							<tr>
								<td class='t_block_m droppable edit_productBox' data-pos='".($pos=$pos+1)."'>".($this->getShowmosaico()?"":"<span class='bt_excluir'>X</span>")."".$this->get($pos)."</td>
								<td class='t_block_p'>
									<table cellpadding='0' cellspacing='0'>
										<tr>
											<td class='droppable edit_productBox' data-pos='".($pos=$pos+1)."'>".($this->getShowmosaico()?"":"<span class='bt_excluir'>X</span>")."".$this->get($pos)."</td>
											<td class='droppable edit_productBox' data-pos='".($pos=$pos+1)."'>".($this->getShowmosaico()?"":"<span class='bt_excluir'>X</span>")."".$this->get($pos)."</td>
										</tr>
										<tr>
											<td class='droppable edit_productBox' data-pos='".($pos=$pos+1)."'>".($this->getShowmosaico()?"":"<span class='bt_excluir'>X</span>")."".$this->get($pos)."</td>
											<td class='droppable edit_productBox' data-pos='".($pos=$pos+1)."'>".($this->getShowmosaico()?"":"<span class='bt_excluir'>X</span>")."".$this->get($pos)."</td>
										</tr>
									</table>
								</td>
							</tr>
						</table>
					</li>";break;
			case 2 :
				$pos = $pos+12;
				return "<li>
						<table cellpadding='0' cellspacing='0' class='t_block'>
							<tr>
								<td colspan='2' rowspan='2' class='t_block_g droppable edit_productBox' data-pos='".$pos."'>".($this->getShowmosaico()?"":"<span class='bt_excluir'>X</span>")."".$this->get($pos)."</td>
							</tr>
							<tr></tr>
							<tr>
								<td class='t_block_m droppable edit_productBox' data-pos='".($pos=$pos+1)."'>".($this->getShowmosaico()?"":"<span class='bt_excluir'>X</span>")."".$this->get($pos)."</td>
								<td class='t_block_p'>
									<table cellpadding='0' cellspacing='0'>
										<tr>
											<td class='droppable edit_productBox' data-pos='".($pos=$pos+1)."'>".($this->getShowmosaico()?"":"<span class='bt_excluir'>X</span>")."".$this->get($pos)."</td>
											<td class='droppable edit_productBox' data-pos='".($pos=$pos+1)."'>".($this->getShowmosaico()?"":"<span class='bt_excluir'>X</span>")."".$this->get($pos)."</td>
										</tr>
										<tr>
											<td class='droppable edit_productBox' data-pos='".($pos=$pos+1)."'>".($this->getShowmosaico()?"":"<span class='bt_excluir'>X</span>")."".$this->get($pos)."</td>
											<td class='droppable edit_productBox' data-pos='".($pos=$pos+1)."'>".($this->getShowmosaico()?"":"<span class='bt_excluir'>X</span>")."".$this->get($pos)."</td>
										</tr>
									</table>
								</td>
							</tr>
						</table>
					</li>";break;
			case 3 :
				$pos = $pos+18;
				return "<li>
							<table cellpadding='0' cellspacing='0' class='t_block'>
								<tr>
									<td class='t_block_m droppable edit_productBox' data-pos='".$pos."'>".($this->getShowmosaico()?"":"<span class='bt_excluir'>X</span>")."".$this->get($pos)."</td>
									<td class='t_block_m droppable edit_productBox' data-pos='".($pos=$pos+1)."'>".($this->getShowmosaico()?"":"<span class='bt_excluir'>X</span>")."".$this->get($pos)."</td>
								</tr>
								<tr>
									<td class='t_block_m droppable edit_productBox' data-pos='".($pos=$pos+1)."'>".($this->getShowmosaico()?"":"<span class='bt_excluir'>X</span>")."".$this->get($pos)."</td>
									<td class='t_block_p'>
										<table cellpadding='0' cellspacing='0'>
											<tr>
												<td class='droppable edit_productBox' data-pos='".($pos=$pos+1)."'>".($this->getShowmosaico()?"":"<span class='bt_excluir'>X</span>")."".$this->get($pos)."</td>
												<td class='droppable edit_productBox' data-pos='".($pos=$pos+1)."'>".($this->getShowmosaico()?"":"<span class='bt_excluir'>X</span>")."".$this->get($pos)."</td>
											</tr>
											<tr>
												<td class='droppable edit_productBox' data-pos='".($pos=$pos+1)."'>".($this->getShowmosaico()?"":"<span class='bt_excluir'>X</span>")."".$this->get($pos)."</td>
												<td class='droppable edit_productBox' data-pos='".($pos=$pos+1)."'>".($this->getShowmosaico()?"":"<span class='bt_excluir'>X</span>")."".$this->get($pos)."</td>
											</tr>
										</table>
									</td>
								</tr>
								<tr>
									<td class='t_block_m droppable edit_productBox' data-pos='".($pos=$pos+1)."'>".($this->getShowmosaico()?"":"<span class='bt_excluir'>X</span>")."".$this->get($pos)."</td>
									<td class='t_block_m droppable edit_productBox' data-pos='".($pos=$pos+1)."'>".($this->getShowmosaico()?"":"<span class='bt_excluir'>X</span>")."".$this->get($pos)."</td>
								</tr>
							</table>
						</li>";break;	
		}
	}

	public function showMosaico(){
		$blocos = '';
		$especial = '';

		$query = query($sql="SELECT DISTINCT item.* FROM item 
			INNER JOIN mosaico ON(mosaico.item_id = item.id) 
			-- INNER JOIN itemcategoria ON(itemcategoria.item_id = item.id) 
			-- INNER JOIN categoria ON(categoria.id = itemcategoria.categoria_id AND categoria.st_ativo)
			
			WHERE 1=1 
            AND item.st_ativo = 'S'
            AND item.imagem <> ''
				{$especial}
			ORDER BY mosaico.posicao, item.id
		");
		
		//printr($sql);
		
		if(!(rows($query)>0)){return $blocos;}
		
		$query = query("SELECT MAX(posicao) POS FROM mosaico LIMIT 1");
		$pos = fetch($query);
		$mosaico = new mosaico();
		$mosaico->setShowmosaico(true);
		if($pos->POS>27){
			$pagina = floor($pos->POS/27);
			$resto = $pos->POS%27;
			
			for($i=0;$i<$pagina;$i++){
				$posicao = $i*27;
				$blocos .= $mosaico->addBlock($posicao); // bloco 1
				$blocos .= $mosaico->addBlock($posicao+12); // bloco 2
				$blocos .= $mosaico->addBlock($posicao+18); // bloco 3
			}
			
			$posicaoFinal = $pagina*27;
			if($resto>=1&&$resto<=12){
				$blocos .= $mosaico->addBlock($posicaoFinal);
			}
			if($resto>=13&&$resto<=18){
				$blocos .= $mosaico->addBlock($posicaoFinal);
				$blocos .= $mosaico->addBlock($posicaoFinal+12);
			}
			if($resto>=19&&$resto<=27){
				$blocos .= $mosaico->addBlock($posicaoFinal);
				$blocos .= $mosaico->addBlock($posicaoFinal+12);
				$blocos .= $mosaico->addBlock($posicaoFinal+18);
			}			
		}else{
			if($pos->POS>=1&&$pos->POS<=12){
				$blocos .= $mosaico->addBlock(1);
			}
			if($pos->POS>=13&&$pos->POS<=18){
				$blocos .= $mosaico->addBlock(1);
				$blocos .= $mosaico->addBlock(12);
			}
			if($pos->POS>=19&&$pos->POS<=27){
				$blocos .= $mosaico->addBlock(1);
				$blocos .= $mosaico->addBlock(12);
				$blocos .= $mosaico->addBlock(18);
			}		
		}
		
		if(array_key_exists("showmosaico",$_SESSION)){
			unset($_SESSION["showmosaico"]);
		}
		
		return $blocos;
	}
}
?>