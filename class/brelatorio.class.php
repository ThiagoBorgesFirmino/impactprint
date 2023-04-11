<?php

class brelatorio {

	// private
		// $sUser = DB_USER  ,
		// $sPass = DB_PASS  ,
		// $sData = DB_USER  ,
		// $sInte = "oracle" ,
		// $sConn = DB_TNS   ; // database connection

	private
		$sUser,
		$sPass,
		$sData,
		$sInte,
		$sConn; // database connection

	private
		$_xml_nome;

	var
		$titulo,
		$filtros,
		$sql ;

	var
		$condicoes = array()
		,$explicacao = array()
		,$html_filtros = array();

	var
		$visualizar = false;

	var
		$habilita_resultado_excel = false
		,$habilita_resultado_html = true
		,$habilita_resultado_txt = true
		,$habilita_carta_cobranca_empresa = false
		,$habilita_etiqueta_socio = false
		,$habilita_etiqueta_empresa = false;

	// especifico para ligacao com a carta de cobranca, impressao de etiquetas
	var
		$nome_campo_cnpj
		,$nome_campo_matricula;

	function filtro_add($filtro){
		$this->filtros[] = $filtro ;
	}

	function __construct(){

		// ini_set("include_path", ini_get("include_path").":".PHPREPORTS_PATH);
		
		// ini_set("include_path", ini_get("include_path").":".dirname(__FILE__).'/../admin/phpreports-0.5.0');
		// ini_set("include_path", PHPREPORTS_PATH);
		// ini_set("include_path", PHPREPORTS_PATH);		
		$path = PHPREPORTS_PATH;
		set_include_path(get_include_path() . PATH_SEPARATOR . $path);
		
		// print ini_get("include_path").":".dirname(__FILE__)."/../admin/phpreports-0.5.0";

		// print dirname(__FILE__);

		require_once(PHPREPORTS_PATH."/PHPReportMaker.php");
		// require_once("admin/phpreports-0.5.0/PHPReportMaker.php");		
		// require_once("admin/phpreports-0.5.0/PHPReportMaker.php");

		// configure your parameters here
		$this->sUser = BD_USER; // database user
		$this->sPass = BD_PASS; // database password
		$this->sData = BD_DATABASE; // database name
		$this->sConn = BD_HOST; // database host
		// $sInte = "oracle"; // database inteface
		$this->sInte = "mysql"; // database inteface
		// $sConn = DB_TNS ; // database connection

		// // check then
		// if(strlen($sUser)<1
		// || strlen($sPass)<1
		// || strlen($sInte)<1

		// // || strlen($sConn)<1	)
		// ){
			// print "ERROR: please configure this script before run!";
			// return;
		// }

		// check paths
		$sIncPath = getPHPReportsIncludePath();
		$sFilPath = getPHPReportsFilePath();
		$sTmpPath = getPHPReportsTmpPath();

		// print "Checking paths ...\n";
		if(is_null($sIncPath) || strlen(trim($sIncPath))<=0){
			print "ERROR: No INCLUDE path defined.";
			return;
		}
		if(is_null($sFilPath) || strlen(trim($sFilPath))<=0){
			print "ERROR: No FILE path defined.";
			return;
		}
		if(is_null($sTmpPath) || strlen(trim($sTmpPath))<=0){
			print "ERROR: No TEMP path defined.";
			return;
		}
	}

	public function setConnection($dbType, $usuario){

	}

	function quebra_linha(){
		$this->html_filtros[] = "<br clear='all'/>";
	}

	function filtro_add_select($campo, $label, $sql){
		if( request($campo) ){
			$this->condicoes[] = " and {$campo} = '".request($campo)."'";
			$this->explicacao[] = "{$label}: " . request($campo);
		}
		$this->html_filtros[] = select($campo, $select_value, $sql, $label, $extra, $extra_options, $data_value, $data_text) ;
	}

	function filtro_add_texto($campo, $label){
		if( request($campo) ){
			$this->condicoes[] = " and {$campo} = '".request($campo)."'";
			$this->explicacao[] = "{$label}: " . request($campo);
		}
		ob_start();
		echo '<div style="float:left;width:48%;">';
		$this->html_filtros[] = input_label( $campo, request($campo), $label, 30) ; ;
		echo '</div>';
		$this->html_filtros[] = ob_get_contents();
		ob_end_clean();
	}


	function filtro_add_select_sql($campo, $label, $sql){
		if( request($campo) && request($campo) != 'null' ){
			$this->condicoes[] = " and {$campo} = '".request($campo)."'";
			$this->explicacao[] = "{$label} " . request($campo);
		}
		printr('');
		ob_start();
		echo '<div style="float:left;width:48%;">';
		select($campo, request($campo), $sql, $label, null, 'opcao_null', $campo, $campo) ;
		echo '</div>';
		$this->html_filtros[] = ob_get_contents();
		ob_end_clean();
	}

	private function sql2array($sql, $data_value, $data_text){
		$ret = array();
		$query = query($sql) ;
		while ( $object = fetch($query) ){
			$ret[$object->$data_value] = $object->$data_text;
		}
		return $ret;
	}

	function filtro_add_select_distinct($campo, $label, $sort='ASC'){

		$ret = '';

		if( request($campo) && request($campo) != 'null' ){
			$this->condicoes[] = " and {$campo} = '".request($campo)."'";
			$this->explicacao[] = "{$label} " . request($campo);
		}
		$sql = "SELECT DISTINCT {$campo} FROM ({$this->sql}) as x1 ORDER BY {$campo} {$sort}";
		// printr('');

		$arr = $this->sql2array($sql, $campo, $campo);

		// butil::printr($arr);
		$ret .= '<div style="float:left;width:48%;">';
		$ret .= butil::select($campo, request('campo'), $label, $arr, true) ;
		$ret .= '</div>';
		$this->html_filtros[] = $ret;
	}

	function filtro_add_select_array($campo, $label, $array){
		// implementar um dia
	}

	function filtro_add_intervalo_numerico($campo, $label){

		$campo1 = $campo."_de";
		$campo2 = $campo."_ate";

		if( request($campo1) && request($campo2) && request($campo1) != 'null' && request($campo2) != 'null' ){
			$valor1 = request($campo1);
			$valor2 = request($campo2);
			$this->condicoes[] = " and {$campo} between '{$valor1}' and '{$valor2}' ";
			$this->explicacao[] = " {$label} entre: {$valor1} e {$valor2}" ;
		}
		ob_start();
		echo '<br clear="all"/><p><label>'.$label.'</label></p><div style="float:left">';
		input_label( $campo1, request($campo1), 'De:', 15 ) ;
		echo '</div>';
		echo '<div style="float:left;margin-left:1px">';
		input_label( $campo2, request($campo2), 'At&eacute;', 15 ) ;
		echo '</div><br clear="all"/>';
		$this->html_filtros[] = ob_get_contents();
		ob_end_clean();
	}

		function filtro_add_intervalo_string($campo, $label){

		$campo1 = $campo."_de";
		$campo2 = $campo."_ate";

		if( request($campo1) && request($campo2) && request($campo1) != 'null' && request($campo2) != 'null' ){
			$valor1 = request($campo1);
			$valor2 = request($campo2);
			$this->condicoes[] = " and {$campo} between '{$valor1}' and '{$valor2}' ";
			$this->explicacao[] = " {$label} entre: {$valor1} e {$valor2}" ;
		}
		ob_start();
		echo '<br clear="all"/><p><label>'.$label.'</label></p><div style="float:left">';
		input_label( $campo1, request($campo1), 'De:', 15 ) ;
		echo '</div>';
		echo '<div style="float:left;margin-left:1px">';
		input_label( $campo2, request($campo2), 'At&eacute;', 15 ) ;
		echo '</div><br clear="all"/>';
		$this->html_filtros[] = ob_get_contents();
		ob_end_clean();
	}

		function filtro_add_intervalo_moeda($campo, $label){

		$campo1 = $campo."_de";
		$campo2 = $campo."_ate";

		if( request($campo1) && request($campo2) && request($campo1) != 'null' && request($campo2) != 'null' ){
			$valor1 = request($campo1);
			$valor2 = request($campo2);
			$this->condicoes[] = " and {$campo} between TO_NUMBER('{$valor1}','999999999999D99', 'nls_numeric_characters='',.''') and TO_NUMBER('{$valor2}','999999999999D99', 'nls_numeric_characters='',.''') ";
			$this->explicacao[] = " {$label} entre: {$valor1} e {$valor2}" ;
		}
		ob_start();
		echo '<br clear="all"/><p><label>'.$label.'</label></p><div style="float:left">';
		input_label( $campo1, request($campo1), 'De:', 15 ) ;
		echo '</div>';
		echo '<div style="float:left;margin-left:1px">';
		input_label( $campo2, request($campo2), 'At&eacute;', 15 ) ;
		echo '</div><br clear="all"/>';
		$this->html_filtros[] = ob_get_contents();
		ob_end_clean();
	}

	function filtro_add_intervalo_data($campo, $label){
		$campo1 = $campo."_de";
		$campo2 = $campo."_ate";

		if( request($campo1) && request($campo2) && request($campo1) != 'null' && request($campo2) != 'null' ){
			$valor1 = request($campo1);
			$valor2 = request($campo2);

			//			$this->condicoes[] = " and to_date({$campo},'dd/mm/yyyy') between to_date('{$valor1}','dd/mm/yyyy') and to_date('{$valor2}','dd/mm/yyyy')";
			$this->condicoes[] = " and to_date({$campo},'dd/mm/yyyy') between to_date('{$valor1}','dd/mm/yyyy') and to_date('{$valor2}','dd/mm/yyyy') ";
			// $this->condicoes[] = " and {$campo} between '{$valor1}' and '{$valor2}' ";
			$this->explicacao[] = " {$label} entre: {$valor1} e {$valor2}" ;
		}
		ob_start();
		echo '<br clear="all"/><p><label>'.$label.'</label></p><div style="float:left">';
		input_data( $campo1, request($campo1), 'De:' ) ;
		echo '</div>';
		echo '<div style="float:left;margin-left:1px">';
		input_data( $campo2, request($campo2), 'At&eacute;' ) ;
		echo '</div><br clear="all"/>';
		$this->html_filtros[] = ob_get_contents();
		ob_end_clean();
	}

	function filtro_add_faixa_salarial($campo, $label){



	}

	//

	public function render(){

		$ret = '';

		if(request('relatorio_html')
		|| request('relatorio_excel')){

			if(request('relatorio_excel')){
				//CARREGA UM CABECALHO PRA TRANSFORMAR O HTML EM EXCEL
				// require_once('header-excel.php');
				header("Content-Type:application/vnd.ms-excel");
				header("Expires:0");
				header("Cache-Control:must-revalidate, post-check=0, pre-check=0");
				header("Filename:excel".date('d-m-Y').".xls");
			}

			$this->render_resultados();
		}
		else {
			return $this->render_filtros();
		}
	}

	public function render_filtros(){

		$ret = '';

		?>
		<!--form name="formrelatorio" accept-charset="ISO-8859-1" id="formrelatorio" action="index-simples.php" target="_blank" method="post"-->
		<?php
		$ret .= butil::tag('h1', $this->titulo);
		$ret .= $this->render_botoes();
		foreach($this->html_filtros as $html_filtro){
			$ret .= $html_filtro;
		}
		?>
		<!--/form-->
		<?php
		return $ret;
	}

	public function render_resultados(){

		$sCode  = _tempnam(null,"code");
		$sXMLOut = _tempnam(null,"xml");
		$sHTMLOut = _tempnam(null,"html");

		$aParms = array();
		
		$aParms["titulo"] = $this->titulo;
		$aParms["explicacao"] = $this->get_explicacoes();
		$aParms["url"] = config::get('URL');

		$sql = "SELECT * FROM (".$this->get_sql().") as x1 WHERE 1=1 " . $this->get_condicoes();

		$oRpt = new PHPReportMaker();
		$oRpt->setXML($this->get_xml_nome());
		$oRpt->setSQL($sql) ;
		//printr($oRpt->sSQL) ;
		$oRpt->setUser($this->sUser);
		$oRpt->setPassword($this->sPass);
		$oRpt->setDatabase($this->sData);
		$oRpt->setDatabaseInterface($this->sInte);
		$oRpt->setConnection($this->sConn);
		$oRpt->setCodeOutput($sCode);
		$oRpt->setXMLOutputFile($sXMLOut);
		$oRpt->setOutput($sHTMLOut);
		$oRpt->setParameters($aParms);
		
		// echo $this->get_sql() ;
		// pri aqui
		// printr($sql);
		// print "Creating the default output plugin ...\n";
		
		$oOut = $oRpt->createOutputPlugin("default");
		if(is_null($oOut)){
			print "ERROR: could not create an output plugin.";
			return;
		}
		
		$oOut->setClean(false);
		$oRpt->setOutputPlugin($oOut);

		$oRpt->run();

		// check if everything was ok
		if(!file_exists($sCode))
			print "ERROR: code file $sCode does not exists, no code to process.";

		if(filesize($sCode)<=0)
			print "ERROR: code file $sCode does not have a valid size, no code to process.";

		if(!file_exists($sXMLOut))
			print "ERROR: XML data file $sXMLOut does not exists, no data to process.";

		if(filesize($sXMLOut)<=0)
			print "ERROR: XML data file $sXMLOut does not have a valid size, no data to process.";

		if(!file_exists($sHTMLOut))
			print "ERROR: HTML result file $sHTMLOut does not exists, no result to show.";

		if(filesize($sHTMLOut)<=0)
			print "ERROR: HTML result file $sHTMLOut does not have a valid size, no result to show.";

		if(request('relatorio_excel')){
			print iconv('utf-8','iso-8859-1//TRANSLIT', file_get_contents($sHTMLOut)) ;
		}
		else {
			print file_get_contents($sHTMLOut) ;
		}
		
	}

	public function render_botoes(){

		$ret = '';

		$ret =
		'
		<input type="hidden" name="relatorio_html" value="0"/>
		<script>
		function gerarHTML(btn){
			btn.form.target = "_blank";
			btn.form.elements["relatorio_html"].value = "1";
			btn.form.submit();
			btn.form.target = \'_self\';
		}
		</script>
		';

		$ret .= '<div class="menubar">';
		
		if($this->habilita_carta_cobranca_empresa){
			$ret .= '<input type="submit" name="carta_cobranca_empresa" value="Carta de Cobranca" class="botao-padrao"/>' ;
		}
		
		if($this->habilita_etiqueta_socio){
			$ret .= '<input type="submit" name="etiqueta_socio" value="Etiqueta (SÓCIOS)" class="botao-padrao"/>' ;
		}
		
		if($this->habilita_etiqueta_empresa){
			$ret .= '<input type="submit" name="etiqueta_empresa" value="Etiqueta (EMPRESAS)" class="botao-padrao"/>' ;
		}
		
		if($this->habilita_resultado_html){
			$ret .= '<br /><button onclick="gerarHTML(this);" value="" class="botao-padrao"/>Gerar Relatório (HTML)</button>' ;
		}
		
		if($this->habilita_resultado_excel){
			$ret .= '<br /><input type="submit" name="relatorio_excel" value="Gerar (EXCEL)" class="botao-padrao"/>' ;
		}
		
		$ret .= '</div>';
		return $ret;
	}

	/**
	metodos abaixo para tratar a geracao de etiquetas
	*/

	function render_etiqueta_socio(){

		require_once('class/socios.php');
		require_once('class/endereco_socios.php');

		$matriculas = array();

		$sql = ("SELECT * FROM (".$this->get_sql().") WHERE 1=1 " . $this->get_condicoes());
		$query = query($sql) ;


		// so carrega os cnpjs em um array
		while( $object = fetch($query) ){
			$matriculas[] = $object[$this->nome_campo_matricula];
		}

		ob_start();

		// loop nas matriculas
		foreach ($matriculas as $matricula){

			$socios = new socios();
			$endereco_socios = new endereco_socios();

			$socios->get_by_id($matricula);
			$endereco_socios->get_by_fk_name('soci_nr_matricula', $socios->nr_matricula);

			$endereco_socios->nr_cep = formata_cep($endereco_socios->nr_cep);

			?>
			<table class='etiqueta'><tr><td>
				<?php echo $socios->nm ?> - <?php echo $socios->nr_matricula ?><br />
			  	<?php echo $endereco_socios->nm_logr ?> <?php echo $endereco_socios->nm_endereco ?>, <?php echo $endereco_socios->nr_endereco ?> <?php echo $endereco_socios->nm_compl ;?><br />
				<?php echo $endereco_socios->nr_cep ?> <?php echo $endereco_socios->nm_bairro ?> - <?php echo $endereco_socios->nm_cidade ?> - <?php echo $endereco_socios->nm_uf ?>
			</td></tr></table>

			<?php

			unset($empresas);
			unset($endereco_socios);


		}

		$etiquetas = ob_get_contents();
		ob_end_clean();

		unset($carta_cobranca);

		// printa na tela a saida
		?>
		<html>
			<head></head>
			<style type="text/css" media="print,screen">
			* {
				margin:0px;
				padding:0px;
				font-family:arial;
				font-size:12px;
			}
			body{
				margin:0px;
				padding:0px;

			}
			table.etiqueta {
				float:left;
				xborder:1px solid;
				width:420px;
				height:96px;
			}
			table.etiqueta td{
				xwidth:30%;
			}
			</style>
			<body>
				<?php
				echo $etiquetas ;
				?>
			</body>
		</html>
		<?php
	}

	#auxiliares
	public function set_xml_nome($str){
		$this->_xml_nome = $str;
	}
	
	function get_xml_nome(){
		return $this->_xml_nome;
		return PHPREPORTS_PATH . '/../' . str_replace( '.php', '.xml', MODULO ) ;
	}

	function get_xml_carta_cobranca_empresa(){
		print PHPREPORTS_PATH . '/../' . str_replace( '.php', '.xml', MODULO ) ;
		return PHPREPORTS_PATH . '/../' . str_replace( '.php', '.xml', MODULO ) ;
	}
	
	function get_sql(){
		return $this->sql ;
	}
	
	function get_condicoes(){
		return join(' ', $this->condicoes);
	}
	
	function get_explicacoes(){
		return join(', ', $this->explicacao);
	}



}
?>
