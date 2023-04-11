<?php

class filtro {

	private
		$html = '' ;

	private
		$fields = array(),
		$operacoes = array(),
		$fields_categoria = array();

	private
		$cat,
		$item_cat;

	public
		$botao_novo = true,
		$botao_buscar = true;

	private function request($field){
		return @$_REQUEST['filtro'][$field];
	}

	public function begin_block() {
		$this->html .= '<div style="float:left;margin-right:5px;margin-bottom:5px">';
	}

	public function end_block() {
		$this->html .= '</div>';
	}

	public function add_clear() {
		$this->html .= tag('br clear="all"');
	}

	public function add_input( $field, $label ) {
		if($this->request($field)!=''){
			$this->operacoes[] = " and $field like '".get_like($this->request($field))."' ";
		}
		$this->html .= '<div class="pull-left" style="padding-right:5px;padding-bottom:5px;">'.inputSimples("filtro[{$field}]", $this->request($field), $label, 40, 60).'</div>';
	}

	public function add_data( $field, $label ) {
		$this->operacoes[] = " and $field = '".$this->request($field)."' " ;
		$this->html .= inputSimples("filtro[{$field}]", $this->request($field), $label, 40, 60);
	}

	public function add_periodo( $field, $label ) {
		if( $this->request($field.'_de') && $this->request($field.'_ate') ){
			$this->operacoes[] = " and {$field} between '".to_bd_date($this->request($field.'_de'))." 00:00:00' and '".to_bd_date($this->request($field.'_ate'))." 23:59:59'" ;
		}
        $this->html .= '<div class="pull-left" style="padding-right:5px;padding-bottom:5px;">'.'<label class="filtro_label">'.$label.'</label>' . 'De: ' . inputData("filtro[{$field}_de]", $this->request($field.'_de'), '') . ' At&eacute;: ' . inputData("filtro[{$field}_ate]", $this->request($field.'_ate'), '').'</div>';
	}

	public function add_select( $field, $label, $array ) {
		if( $this->request($field) ){
			$this->operacoes[] = " and $field = '".$this->request($field)."' " ;
		}
        $this->html .= '<div class="pull-left" style="padding-right:5px;padding-bottom:5px;">'.select("filtro[{$field}]", $this->request($field), $label, $array, true).'</div>';
	}

	public function add_checkbox( $field, $label ) {
		$this->operacoes[] = " and $field in ( '".join(',',$this->request($field))."' ) " ;
		$this->html .= inputSimples("filtro[{$field}]", $this->request($field), $label, 40, 60); ;
	}

	public function add_categoria( $field ) {
		if($this->request($field)){
			if($this->request($field)=='nenhuma'){
				$this->operacoes[] = " AND NOT EXISTS (SELECT item_id FROM itemcategoria WHERE itemcategoria.item_id = x.id)";
			}
			else{
				$this->operacoes[] = " AND EXISTS (SELECT item_id FROM itemcategoria WHERE categoria_id = ".$this->request($field)." and itemcategoria.item_id = x.id)";
			}
		}
        $this->html .= '<div class="pull-left" style="padding-right:5px;padding-bottom:5px;">'.selectCategoria("filtro[{$field}]", $this->request($field), 'Categoria/Sub-categoria de:', true).'</div>';
	}

    public function add_itemcor($field,$label='Cor') {
        if($this->request($field)){
            if($this->request($field)=='nenhuma'){
                // $this->operacoes[] = " AND NOT EXISTS (SELECT item_id FROM item WHERE itemcategoria.item_id = x.id)";
            }
            else{
                $this->operacoes[] = " AND EXISTS (SELECT 1 FROM item a WHERE a.cor_id = ".$this->request($field)." AND (a.id = x.id OR a.itemsku_id = x.id)) ";
            }
        }
        $this->html .= '<div class="pull-left" style="padding-right:5px;padding-bottom:5px;">'.selectCor("filtro[{$field}]", $this->request($field), $label.':', true).'</div>';
    }

	public function add_status( $field='st_ativo', $label = 'Status:' ) {
		if( $this->request($field) ){
			$this->operacoes[] = " and {$field} = '".$this->request($field)."' " ;
		}
        $this->html .= '<div class="pull-left" style="padding-right:5px;padding-bottom:5px;">'.select("filtro[{$field}]", $this->request($field), $label, array(''=>'','S'=>'Sim','N'=>'N&atilde;o')).'</div>';
	}

	public function get_where() {
		//print join('',$this->operacoes);
		if($this->request('buscar')){
			$_REQUEST['pag']=0;
			//document.forms[0].elements.pag.value = page ;
		}
		//print join('',$this->operacoes);
		return join('',$this->operacoes);
	}
	
	public function render() {
		if($this->botao_buscar){
			if($this->html!=''){
				print '<div class="well">' ;
				print '<a href="#" onclick="$(\'.js-filtros\').slideToggle();"><span class="glyphicon glyphicon-search"></span> Pesquisar</a><br>';
                print '<div class="js-filtros" style="'.(!isset($_REQUEST['filtro'])?'display:none':'').'">' ;
				print $this->html ;
                print '<br clear="all">';
                print '<button type="submit" name="filtro[buscar]" value="buscar" id="buttonBuscar" class="btn btn-default"><span class="glyphicon glyphicon-search"></span> Buscar</button>' ;
                if(@$this->excel){
                    print $this->excel;
                }
                print '</div>' ;
				print '</div>' ;
			}
		}
		if($this->botao_novo){
			print '<div class="xtext-center well"><button type="button" class="btn btn-lg btn-primary" onclick="javascript:gridEditPop(0)"><span class="glyphicon glyphicon-plus"></span> Incluir Novo</button></div>' ;
		}
	}
}
