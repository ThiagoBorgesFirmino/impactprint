<?php

class gridrelatorio {

	public
		$sql ;

	public
		$filtro ;

	public
		$html ;

	public
		$botao_excluir = true;

	public
		$botao_copia = false;

	public
		$check_box = false;

	public function render(){

		ob_start();
		$this->filtro->render();

		$sql = "SELECT * FROM ({$this->sql}) as x WHERE 1=1 {$this->filtro->get_where()}" ;

		$sort = request('sort');
		$sort_ordem = request('sort_ordem');

		if($sort){
			$sql .= 'order by '.$sort . ' ' . $sort_ordem;
		}

		//printr($sql) ;

		$query = query($sql);
		$zebra = true ;

		$npag    = @$_REQUEST['pag'] + 0;
		$ntot    = rows($query);
		$nrecpag = 50 ;

		if ( $npag  == 0 ){
			$npag = 1 ;
		}

		$totpg = intval( $ntot / $nrecpag ) ;
		if( ( $ntot % $nrecpag ) != 0 ) $totpg ++ ;

		$nini = ($npag - 1) * $nrecpag  ;

		$nfim = ( $nini + $nrecpag  ) ;
		$pager = '' ;

		$style = 'float:left;padding-right:5px';


		//INICIO PAGINADOR
		$pager .= "<br />";
		if(($npag-2) > 1){
			$pg_ant = $npag - 1;
			$pager .= '<a href="javascript:gridPage(1)" style="'.$style.'">&nbsp;<img src="'.PATH_SITE.'img/seta-dupla-esquerda.jpg" width="10px" height="10" /> &nbsp;&nbsp;</a>' ;
			$pager .= '<a href="javascript:gridPage('.$pg_ant.')" style="'.$style.'"><img src="'.PATH_SITE.'img/seta-individual-esquerda.jpg" width="10px" height="10" />&nbsp;</a>' ;
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
		if(($npag+2) < $totpg){
			$pg_prox = $npag + 1;
			$pager .= '<a href="javascript:gridPage('.$pg_prox.')" style="'.$style.'">&nbsp;<img src="'.PATH_SITE.'img/seta-individual-direita.jpg"width="10px" height="10" /></a>' ;
			$pager .= '<a href="javascript:gridPage('.$totpg.')" style="'.$style.'">&nbsp;&nbsp;<img src="'.PATH_SITE.'img/seta-dupla-direita.jpg"  width="10px" height="10" /></a>' ;
		}else{
			$pager .="&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
		}

		if($pager!='') {
			$pager = '<div style="float:right;"><div style="text-align:right">'.$pager.'</div></div><br clear="all" />' ;
		}

		// FIM PAGINADOR
		$query = query( $sql = "{$sql} limit {$nini}, {$nrecpag} ");
		//print $sql ;

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
			function gridEdit(id) {
				// Abrindo na mesma janela
				// document.forms[0].elements.id.value = id;
				// document.forms[0].elements.action.value = 'editar';
				// document.forms[0].submit();
				// window.open();
			}
			function gridEditPop(id){
				window.open('?action=editar&id='+id+'&pop=1', 'pop_'+id, 'width=880,resizable=no,scrollbars=yes,status=no,titlebar=no,toolbar=no,height='+$(window).height())
			}
			function gridRemove(id) {
				if(confirm('Tem certeza?')) {
					document.forms[0].elements.id.value = id;
					enviar('excluir');
				}
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
		</script>
		<input type="hidden" name="id" id="id" />
		<input type="hidden" name="pag" id="pag" value="<?php echo $npag ?>" />
		<input type="hidden" name="sort" id="sort" value="<?php echo $sort ?>" />
		<input type="hidden" name="sort_ordem" id="sort_ordem" value="<?php echo $sort_ordem ?>" />
		<?php print $pager ; ?>
		<p style="text-align:right"><small>(<?php echo $ntot;?>)</small></p>
		<table class="grid">
			<!-- TITULOS -->
			<?php
			$nao_aparece = explode(',',@$this->nao_aparece);
			echo '<tr>' ;

			for( $i = 0, $m = mysql_num_fields($query) ; $i < $m ; $i ++ ) {
				$c = mysql_field_name($query,$i) ;
				if(strtoupper($c)=='FIXO'||strtoupper($c)=='_ID') {
					continue;
				}
				if(in_array($c,$nao_aparece)){
					continue;
				}
				echo "<th align=\"center\" width=".(strtoupper($c)=='ID'?'20px':'')."><a href=\"javascript:gridSort('{$c}','".($c!=$sort?'ASC':($sort_ordem=='ASC'?'DESC':'ASC'))."')\"><span ".($c==$sort?'style="color:black"':'').">".str_replace('_',' ',$c=="id"?"ID":$c)."</span></a></th>";

			}

			if($this->botao_excluir){
				echo '<th width="10"><span>Excluir</span></th>';
			}

			if($this->botao_copia){
				echo '<th width="10"><span>Copiar</span></th>';
			}

			echo '</tr>' ;
			while( $fetch = fetch($query) ) {
				echo '<tr class="'.($zebra?'troca':'').'" '.($this->check_box?"":'style="cursor:pointer"').' onMouseOver="javascript:gridOver(this);" onMouseOut="javascript:gridOut(this);">' ;
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
					elseif( preg_match('/([0-9]{4})-([0-9]{2})-([0-9]{2})/', $fetch->$c, $matches) ){
						//printr($matches);
						list(,$ano,$mes,$dia)=$matches;
						$fetch->$c = $dia.'/'.$mes.'/'.$ano;
					}
					if($c=='Status'){
						//$fetch->$c = ($fetch->$c == 'S' ? '<img src="'.PATH_SITE.'admin/assets/bola_verde.png"/>' : '<img src="'.PATH_SITE.'admin/assets/bola_off.png"/>');
						$fetch->$c = ($fetch->$c == 'S' ? '<img src="'.PATH_SITE.'admin/assets/bola_verde.png"/>' : '');
					}

					// Abrindo na mesma janela
					if($this->check_box){
						echo '<td  '.($c == 'Status'?'align=center width=10':'').'>' . $fetch->$c . '</td>';
					}else{
						echo '<td  '.($c == 'Status'?'align=center width=10':'').' onClick="javascript:gridEditPop('.$fetch->id.')">' . $fetch->$c . '</td>';
					}
					// echo '<td  '.($c == 'Status'?'align=center width=10':'').' ><a class="fancybox_frame" href="?action=editar&id='.$fetch->id.'&pop=1">'.$fetch->$c.'</a></td>';
				}
				if($this->botao_excluir){
					echo '<td style="text-align:center">'. ( ! @$fetch->fixo || $fetch->fixo == 'N' ? '<a href="javascript:gridRemove('.$fetch->id.')"><img src="'.PATH_SITE.'admin/assets/x.gif"/></a>' : '' ) . '</td>';
				}

				if($this->botao_copia){
					echo '<td style="text-align:center">'. ( ! @$fetch->fixo || $fetch->fixo == 'N' ? '<a href="javascript:gridCopia('.$fetch->id.')"><img src="'.PATH_SITE.'admin/assets/copia.png" width="35px" height="39px" /></a>' : '' ) . '</td>';
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
		</script>

		<?php
		print $pager ;
		print '<br clear="all" />' ;
		$this->html = ob_get_contents();
		ob_end_clean();
		return $this->html ;
	}

}
?>