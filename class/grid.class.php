<?php

class grid {

	public
		$sql ;

	public
		$filtro ;

	public
		$html ;
		
	public
		$geraxml = false;

	public
		$botao_excluir = true;

	public
		$botao_copia = false;

	public
		$campo_rastreamento = false;

	//abilita checkbox na listagem
	public
		$check_box = false;

	//exibe botoes de cancelar e imprimir pedido
	public
		$listagem_pedido = false;

		//ja come�a a listagem carregada, caso haja checkbox
	public
		$listagem = false;

		
	public
		$link_pop = true;	
	
	public
		$metodo = '';	
	
	public
		$abilita_link = false;

	public $sql_final = '';
	public $totalizador = '';
	public $qtd_por_pagina = 50;
	
	public $orderby_custom = "";
	
	public $functions = array();

	public $botao_editar = true;

	public function render(){

		ob_start();

        if(!$this->filtro){
            $this->filtro = new filtro();
        }

		$this->filtro->render();
		
		$sql = "select * from ({$this->sql}) as x where 1=1 {$this->filtro->get_where()}";
		
		//printr($sql);

		//Condicao para nao iniciar listagem de produtos na tela de Up precos em massa
		if($this->check_box && !$this->listagem){
			if($this->filtro->get_where() =='' && $this->filtro->get_where() != 'nenhuma'){
				$sql = "select * from ({$this->sql}) as x where 1=1 AND id=0" ;
			}
		}
		
		print $this->totalizador;

		$sort = request('sort');
		$sort_ordem = request('sort_ordem');

		if($sort){
			$sql .= 'order by '.$sort . ' ' . $sort_ordem;
		}elseif($this->orderby_custom!=""){
			$sql .= " order by ".$this->orderby_custom;
		}

		//printr($sql) ;

		$query = query($sql);
		$zebra = true ;

		$npag    = @$_REQUEST['pag'] + 0;
		$ntot    = rows($query);
		$nrecpag = $this->qtd_por_pagina ;

		if ( $npag  == 0 ){
			$npag = 1 ;
		}

		$totpg = intval( $ntot / $nrecpag ) ;
		if( ( $ntot % $nrecpag ) != 0 ) $totpg ++ ;

		$nini = ($npag - 1) * $nrecpag  ;
		$nfim = ( $nini + $nrecpag  ) ;
		$pager = '' ;

		$style = 'float:left;padding-right:5px';

		if(!$this->geraxml){
			//INICIO PAGINADOR
			$pager .= "<br />";
			if(($npag-1) >= 1){
				$pg_ant = $npag - 1;
				$pager .= '<a class="seta_d_esq" href="javascript:gridPage(1)" style="'.$style.'">&nbsp;<img src="'.PATH_SITE.'img/seta-dupla-esquerda.jpg" width="10px" height="10" /> &nbsp;&nbsp;</a>' ;
				$pager .= '<a class="seta_i_esq" href="javascript:gridPage('.$pg_ant.')" style="'.$style.'"><img src="'.PATH_SITE.'img/seta-individual-esquerda.jpg" width="10px" height="10" />&nbsp;</a>' ;
			}
			for ( $pg = 1 ; $pg <= $totpg ; $pg ++ ){
				$npag_menos = $npag - 3;
				$npag_mais = $npag + 3;

				if($npag_menos<1){
					$npag_menos =0;
					$npag_mais = 6;
				}
				if($npag_mais>$totpg){
					$npag_mais=$totpg+1;
					$npag_menos =$totpg-5;
				}
				if(($pg > $npag_menos)&&(($pg < $npag_mais))){
					if ( $pg == $npag )	{
						// $pager .= "<font color=red><b>$pg</b></font>&nbsp;&nbsp;" ;
						$pager .= '<a href="javascript:gridPage('.$pg.')" style="'.$style.';color:red">'.$pg.'</a>' ;
					}
					else{
						$pager .= '<a href="javascript:gridPage('.$pg.')" style="'.$style.'; color:#2B64A0" >'.$pg.'</a>' ;
					}
				}
			}
			if(($npag+1) <= $totpg){
				$pg_prox = $npag + 1;
				$pager .= '<a class="seta_i_dir" href="javascript:gridPage('.$pg_prox.')" style="'.$style.'">&nbsp;<img src="'.PATH_SITE.'img/seta-individual-direita.jpg" width="10px" height="10" /></a>' ;
				$pager .= '<a class="seta_d_dir" href="javascript:gridPage('.$totpg.')" style="'.$style.'">&nbsp;&nbsp;<img src="'.PATH_SITE.'img/seta-dupla-direita.jpg"  width="10px" height="10" /></a>' ;
			}else{
				$pager .="&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
			}

			if($pager!='') {
				$pager = '<div style="float:right;"><div style="text-align:right">'.$pager.'</div></div><br clear="all" />' ;
			}
		}
		else{
			$tProds = rows(query($sql));
			$nrecpag = $tProds;
		}
		//FIM PAGINADOR

		$this->sql_final = $sql;
		$query = query( $sql = "{$sql} limit {$nini}, {$nrecpag} ");
		//printr($sql) ;

		?>
		<style>
		table.grid tr td {
			cursor:normal;
		}

		table.grid tr.troca td {
			/*background-color:#ffffdd;*/
		}

		table.grid tr.over td {
			/*background-color:#4babee;*/
		}

		</style>
		<script language="javascript">

			function gridOver(objTr) {
				objTr.className += ' over' ;
			}
			function gridOut(objTr) {
				objTr.className = objTr.className.replace(/ ?over/ig,'') ;
				//alert(objTr.className);
			}
			function gridClick(objTr){
				er = / ?click/ig ; 
				if(er.test(objTr.className)){
					objTr.className = objTr.className.replace(/ ?click/ig,'') ;
				}else{
					objTr.className += ' click' ;
				}
			}
			function gridEdit(id) {
				// Abrindo na mesma janela
				// document.forms[0].elements.id.value = id;
				// document.forms[0].elements.action.value = 'editar';
				// document.forms[0].submit();
				// window.open();
			}
			function gridEditPop(id){
				window.open('{index}<?php echo $this->metodo; ?>/?action=editar&id='+id+'&pop=1', 'pop_'+id, 'width=1280,resizable=no,scrollbars=yes,status=no,titlebar=no,toolbar=no,height='+$(window).height())
			}
			function gridRemove(id) {
				if(confirm('Tem certeza?')) {
					document.forms[0].elements.id.value = id;
					enviar('excluir');
				}
			}

			function gridCopia(id) {
				document.forms[0].elements.id.value = id;
				enviar('copiar');
			}
			function gridPage(page) {
				//alert(document.forms[0].elements.pag);
				document.forms[0].elements.pag.value = page ;
				document.forms[0].submit();
			}

			function gridSort(oSort,aOrdem) {
				document.forms[0].elements.sort.value = oSort ;
				document.forms[0].elements.sort_ordem.value = aOrdem ;
				document.forms[0].submit();
			}

			function pedidosImprimir() {
				document.forms[0].elements.action.value = 'pedidosImprimir';
				document.forms[0].target = "_blank";
				document.forms[0].submit();
				document.forms[0].elements.action.value = '';
				document.forms[0].target = "_self";

			}
			function pedidosCancelar() {
				document.forms[0].elements.action.value = 'pedidosCancelar';
				document.forms[0].submit();
			}
		</script>
		<input type="hidden" name="id" id="id" />
		<input type="hidden" name="pag" id="pag" value="<?php echo $npag ?>" />
		<input type="hidden" name="sort" id="sort" value="<?php echo $sort ?>" />
		<input type="hidden" name="sort_ordem" id="sort_ordem" value="<?php echo $sort_ordem ?>" />
		<?php print $pager ; ?>
		<p style="text-align:right"><small>(<?php echo $ntot;?>)</small></p>
		<table class="table table-bordered table-striped table-hover">
			<!-- TITULOS -->
			<?php
			$nao_aparece = explode(',',@$this->nao_aparece);
			echo '<tr>' ;

			if($this->check_box){
				echo '<th width="20" align="center"><input type="checkbox" onclick="check_massa(this)" name="checkMassa" /></th>';
			}

			for( $i = 0, $m = modulo_database::num_fields($query) ; $i < $m ; $i ++ ) {
				$c = modulo_database::field_name($query,$i) ;
				if(strtoupper($c)=='FIXO'||strtoupper($c)=='_ID') {
					continue;
				}
				if(in_array($c,$nao_aparece)){
					continue;
				}
				//echo "<th align=\"center\" width=".(strtoupper($c)=='ID'?'20px':'')."><a href=\"javascript:gridSort('{$c}','".($c!=$sort?'ASC':($sort_ordem=='ASC'?'DESC':'ASC'))."')\"><span ".($c==$sort?'style="color:black"':'').">".str_replace('_',' ',$c=="id"?"ID":$c)."</span></a></th>";

				echo "<th scope='col' align='center' width=".(strtoupper($c)=='ID'?'60px':(strtoupper($c)=="STATUS"?'120px':'')).">";
				echo "<a style='display:block;".(strtoupper($c)=="STATUS"?"text-align:center;":"")."' href=\"javascript:gridSort('{$c}','".($c!=$sort?'ASC':($sort_ordem=='ASC'?'DESC':'ASC'))."')\">";
				echo "<i class='fa fa-sort' aria-hidden='true'></i>&nbsp;<span ".($c==$sort?'style="color:black"':'').">". ucfirst( str_replace('_',' ',$c=="id"?"ID":$c) )."</span>";
				echo "</a></th>";
			}

			if($this->campo_rastreamento){
				echo '<th width="10"><span>Rastreamento</span></th>';
			}

			if($this->botao_editar){
				echo '<th width="10"><span>Editar</span></th>';
			}
			
			
			if($this->botao_excluir){
				echo '<th width="10"><span>Excluir</span></th>';
				
			}
			
			if($this->botao_copia){
				echo '<th width="10"><span>Copiar</span></th>';
			}

			echo '</tr>' ;
			while( $fetch = fetch($query) ) {
				echo '<tr class="'.($zebra?'troca':'').'" '.($this->check_box?"":($this->abilita_link?'style="cursor:pointer"':"")).' onclick="javascript:gridClick(this);" onMouseOver="javascript:gridOver(this);" onMouseOut="javascript:gridOut(this);">' ;
				if($this->check_box){
					echo '<td style="vertical-align:middle;" ><input type="checkbox" name="atualiza['.$fetch->id.']" class="check_massa" /></td>';
				}
				for( $i = 0 ; $i < $m ; $i ++ ) {
					$c = modulo_database::field_name($query,$i) ;
					if(strtoupper($c)=='FIXO'||strtoupper($c)=='_ID') {
						continue;
					}
					if(in_array($c,$nao_aparece)){
						continue;
					}
					/** Verifica formato data:hora e trata para modelo br  */
					if( preg_match('/([0-9]{4})-([0-9]{2})-([0-9]{2}) ([0-9]{2}):([0-9]{2}):([0-9]{2})/', $fetch->$c, $matches) ){
						list(,$ano,$mes,$dia,$hora,$minuto,$segundo)=$matches;
						$fetch->$c = $dia.'/'.$mes.'/'.$ano .' ' .$hora.':'.$minuto.':'.$segundo	;
					}
					elseif( preg_match('/([0-9]{4})-([0-9]{2})-([0-9]{2})/', $fetch->$c, $matches) ){
						list(,$ano,$mes,$dia)=$matches;
						$fetch->$c = $dia.'/'.$mes.'/'.$ano;
					}
					/**  */

					switch($c){
						case 'Status':
							// $fetch->$c = ($fetch->$c == 'S' ? '<img src="'.PATH_SITE.'admin/assets/bola_verde.png"/>' : '<img src="'.PATH_SITE.'admin/assets/bola_vermelha.png"/>');
							$fetch->$c = ($fetch->$c == 'S' ? '<i style="font-size:28px;color:#1abb22;" class="fa fa-check" aria-hidden="true"></i>' 
								: '<i style="font-size:28px;color:#ca5d1c;" class="fa fa-ban" aria-hidden="true"></i>');
							break;
						case 'Entrou_no_Estoque':
							$fetch->$c = ($fetch->$c == 'S' ? '<img src="'.PATH_SITE.'admin/assets/bola_verde.png"/>' : '');
							break;
					}


					// Abrindo na mesma janela
					$td = '<td style="vertical-align:middle;" '.($c == 'Status'?'align=center width=10':'').($this->abilita_link?'onClick="javascript:gridEditPop('.$fetch->id.')"':'').'>' . $fetch->$c . '</td>';

					if(array_key_exists($c,$this->functions)){
						$td = '<td style="position:relative;vertical-align:middle;" '.($c == 'Status'?'align=center width=10':'').'>' . $this->functions[$c]($fetch) . '</td>';
					}

					if($this->check_box && !$this->listagem){
						$td = '<td style="vertical-align:middle;" '.($c == 'Status'?'align=center width=10':'').'>' . $fetch->$c . '</td>';
					}

					if(!$this->link_pop){
						$td = '<td  style="cursor:auto;vertical-align:middle;"'.($c == 'Status'?'align=center width=10':'').'>' . $fetch->$c . '</td>';
					}

					echo $td;

					// echo '<td  '.($c == 'Status'?'align=center width=10':'').' ><a class="fancybox_frame" href="?action=editar&id='.$fetch->id.'&pop=1">'.$fetch->$c.'</a></td>';
				}

				if($this->campo_rastreamento){
					echo '<td style="text-align:center;vertical-align:middle;"><input type="text" name="rastreamento" id="rastreamento_'.$fetch->id.'" value="'.$fetch->Codigo_Envio.'" /> <input type="button" value="Salvar" onclick="AlteraRastreamento('.$fetch->id.')" /></td>';
				}
				
				if($this->botao_editar){
					echo '<td style="text-align:center;vertical-align:middle;"><a href="javascript:gridEditPop('.$fetch->id.')"><i style="font-size:28px;" class="fa fa-edit"></i></td>';
				}

				if($this->botao_excluir){
					echo '<td style="text-align:center;vertical-align:middle;">'. ( ! @$fetch->fixo || $fetch->fixo == 'N' ? '<a href="javascript:gridRemove('.$fetch->id.')"><i style="font-size:28px;color:#df1d2b;" class="fa fa-trash" aria-hidden="true"></i>' : '' )  . '</td>';
				}

				if($this->botao_copia){
					echo '<td style="text-align:center;vertical-align:middle;">'. ( ! @$fetch->fixo || $fetch->fixo == 'N' ? '<a href="javascript:gridCopia('.$fetch->id.')"><img src="'.PATH_SITE.'admin/assets/copia.png" width="35px" height="39px" /></a>' : '' ) . '</td>';
				}

				echo '</tr>' ;
				$zebra = ! $zebra;
			}
			?>
		</table>

		<script>
			function check_massa(obj){
				if(obj.checked){
					for(i=0;i<document.getElementsByClassName("check_massa").length; i++){
						document.getElementsByClassName("check_massa").item(i).checked = true;
					}
				}else{
					for(i=0;i<document.getElementsByClassName("check_massa").length; i++){
						document.getElementsByClassName("check_massa").item(i).checked = false;
					}
				}
			}

			function AlteraRastreamento(id){
				rastreamento = document.getElementById('rastreamento_'+id).value;
				$.ajax({
				url: '<?php echo PATH_SITE; ?>admin.php/rastreamento_alt/'+id+'/'+rastreamento+'',
				success: 	function(data) {
							if(data == '1'){
								alert('Nao foi possivel adcionar o codigo de rastreamento pois este nao foi enviado ainda.');
								document.getElementById('rastreamento_'+id).value = '';
							}else{
								alert(data);
								document.forms[0].elements.action.value = '';
								document.forms[0].submit();
							}
						}
				});
			}
		</script>

		<?php
		print $pager ;
		print '<br clear="all" />' ;
		$this->html = ob_get_contents();

		if($this->listagem_pedido){
			$this->html .= '<br clear="all" />
			<input type="hidden" id="check" value="" name="check" />
			<input type="button" class="button" onclick="pedidosImprimir();" Value="Imprimir Selecionados" />
			<input type="button" class="button" onclick="pedidosCancelar();" Value="Cancelar Pedidos" />';
		}

		ob_end_clean();
		return $this->html ;
	}

}
?>