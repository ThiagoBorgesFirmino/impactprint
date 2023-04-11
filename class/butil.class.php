<?php

// Apanhado de utilidades
// Felipe Gregorio
// bean@bsolucoes.com.br

class butil {

	// Posta informacoes em um host e retorna a respota
	static function getPostResponse($host, $port, $path, $request, $vars=array()){

		try {

			// $path = PATH_SITE."boletophp/boleto_bradesco.php";
			// $fullhost = "http://comercio.locaweb.com.br:80";

			$request_length = strlen($request);

			if($port=='443'){

			}
			else {

			}

			$header  = "POST {$path} HTTP/1.0\r\n";
			$header .= "Host: {$host}\r\n";
			$header .= "User-Agent: DoCoMo/1.0/P503i\r\n";
			$header .= "Content-type: application/x-www-form-urlencoded\r\n";
			$header .= "Content-length: {$request_length}\r\n";
			$header .= "\r\n";

			$header  = "POST {$path} HTTP/1.1\r\n";
			$header .= "Host: {$host}\r\n";
			$header .= "User-Agent: Mozilla/5.0 (Windows; U; Windows NT 6.1; en-US; rv:1.9.2.16) Gecko/20110319 Firefox/3.6.16\r\n";
			$header .= "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8\r\n";
			$header .= "Accept-Language: en-us,en;q=0.5\r\n";
			$header .= "Accept-Encoding: gzip,deflate\r\n";
			$header .= "Accept-Charset: ISO-8859-1,utf-8;q=0.7,*;q=0.7\r\n";
			$header .= "Connection: Close\r\n";
			$header .= "Content-type: application/x-www-form-urlencoded\r\n";
			$header .= "Content-length: {$request_length}\r\n";

			$response = '';

			// printr($header);
			// printr($request);
			// printr($host);
			// printr($port);
			// // die();

			//$fp = fsockopen($host,$port,&$err_num,&$err_msg,30);

			if($port=='443'){
				$fp = fsockopen("ssl://".$host, $port, $err_num, $err_msg, 30);
			}
			else {
				$fp = fsockopen($host, $port, $err_num, $err_msg, 30);
			}

			if(!$fp){
				throw new Exception('Falha ao abrir socket');
			}

			// printr($err_num);
			// printr($err_msg);

			fputs($fp, $header.$request);

			while ($line = fgets($fp, 4096)){


				$response .= $line;
			}

			fclose($fp);

			return $response;

		}
		catch (Exception $ex){
			throw new Exception($ex->getMessage());
		}

	}

	// Começa - Funções de validação

	static function is_email($email){

		return preg_match('/.@./',$email);
	}

	static function is_set($field){

		return !(!isset($field)||$field=="");
	}

	static function is_data($value){

		$temp = explode( '/' , $value ) ;
		if( sizeof($temp) == 3 ){
			list( $dia, $mes, $ano ) = $temp ;
			return (( $dia&&$mes&&$ano ) && ( strlen($dia)<=2 && strlen($mes)<=2 && strlen($ano) == 4 ) && ( checkdate($mes,$dia,$ano) )) ;
		}
		return false ;
	}

	// alias to is_data
	static function is_date($value){

		return self::is_data($value);
	}

	static function is_hora($hr){

		@list($h,$m,$s)=explode(':',$hr);

		$h=intval($h);
		$m=intval($m);
		$s=intval($s);

		if($h<0||$h>24){
			return false;
		}

		if($m<0||$m>59){
			return false;
		}

		if($s<0||$s>59){
			return false;
		}

		return true;
	}

	// static function is_cpf($cpf){

	// 	$cpf = preg_replace('/[a-z.-]/','',$cpf);

	// 	$cpf = str_pad(@ereg_replace('[^0-9]', '', $cpf), 11, '0', STR_PAD_LEFT);

	// 	// Verifica se nenhuma das sequências abaixo foi digitada, caso seja, retorna falso
	// 	if (strlen($cpf) != 11
	// 	|| $cpf == '00000000000'
	// 	|| $cpf == '11111111111' || $cpf == '22222222222' || $cpf == '33333333333'
	// 	|| $cpf == '44444444444' || $cpf == '55555555555' || $cpf == '66666666666'
	// 	|| $cpf == '77777777777' || $cpf == '88888888888' || $cpf == '99999999999')
	// 	{
	// 		return false;
	// 	}
	// 	else
	// 	{
	// 		// Calcula os números para verificar se o CPF é verdadeiro
	// 		for ($t = 9; $t < 11; $t++) {
	// 			for ($d = 0, $c = 0; $c < $t; $c++) {
	// 				$d += $cpf{$c} * (($t + 1) - $c);
	// 			}

	// 			$d = ((10 * $d) % 11) % 10;

	// 			if ($cpf{$c} != $d) {
	// 				return false;
	// 			}
	// 		}

	// 		return true;
	// 	}
	// }

	

	// Função que valida CNPJ
	static function is_cnpj($CampoNumero){


		$RecebeCNPJ=${"CampoNumero"};
		$RecebeCNPJ = preg_replace('/[a-z]/','',$RecebeCNPJ);

		$s="";

		for ($x=1; $x<=strlen($RecebeCNPJ); $x=$x+1) {
			$ch=substr($RecebeCNPJ,$x-1,1);
			if (ord($ch)>=48 && ord($ch)<=57) {
				$s=$s.$ch;
			}
		}

		$RecebeCNPJ=$s;

		if (strlen($RecebeCNPJ)!=14) {
			return false;
		}

		if ($RecebeCNPJ=="00000000000000") {
			return false;
		}

		$Numero[1]=intval(substr($RecebeCNPJ,1-1,1));
		$Numero[2]=intval(substr($RecebeCNPJ,2-1,1));
		$Numero[3]=intval(substr($RecebeCNPJ,3-1,1));
		$Numero[4]=intval(substr($RecebeCNPJ,4-1,1));
		$Numero[5]=intval(substr($RecebeCNPJ,5-1,1));
		$Numero[6]=intval(substr($RecebeCNPJ,6-1,1));
		$Numero[7]=intval(substr($RecebeCNPJ,7-1,1));
		$Numero[8]=intval(substr($RecebeCNPJ,8-1,1));
		$Numero[9]=intval(substr($RecebeCNPJ,9-1,1));
		$Numero[10]=intval(substr($RecebeCNPJ,10-1,1));
		$Numero[11]=intval(substr($RecebeCNPJ,11-1,1));
		$Numero[12]=intval(substr($RecebeCNPJ,12-1,1));
		$Numero[13]=intval(substr($RecebeCNPJ,13-1,1));
		$Numero[14]=intval(substr($RecebeCNPJ,14-1,1));

		$soma=$Numero[1]*5+$Numero[2]*4+$Numero[3]*3+$Numero[4]*2+$Numero[5]*9+$Numero[6]*8+$Numero[7]*7+$Numero[8]*6+$Numero[9]*5+$Numero[10]*4+$Numero[11]*3+$Numero[12]*2;

		$soma=$soma-(11*(intval($soma/11)));

		if ($soma==0 || $soma==1) {
			$resultado1=0;
		}
		else {
			$resultado1=11-$soma;
		}

		if ($resultado1==$Numero[13]) {

			$soma=$Numero[1]*6+$Numero[2]*5+$Numero[3]*4+$Numero[4]*3+$Numero[5]*2+$Numero[6]*9+$Numero[7]*8+$Numero[8]*7+$Numero[9]*6+$Numero[10]*5+$Numero[11]*4+$Numero[12]*3+$Numero[13]*2;
			$soma=$soma-(11*(intval($soma/11)));

			if ($soma==0 || $soma==1) {
				$resultado2=0;
			}
			else {
				$resultado2=11-$soma;
			}
		}

		if ($resultado2 != $Numero[14]) {
			return false;
		}

		return true;

	}

	// Termina - Funções de validação

	// Começa - Funções de formatação

	static function money($valor){
		// print $valor;
		return number_format( $valor, 2, ",", '') ;
	}

	static function percent($valor){
		return number_format( $valor, 1, ".", '') . '%'  ;
	}

	static function formata_cep($cep){
		if ( strlen($cep) == 8 ){
			return substr($cep,0,5) . '-' . substr($cep,5,3) ;
		}
		return $cep ;
	}

	// static function formata_data_br($datetime){
	// 	$return = '';
	// 	if($datetime){
	// 		if(@$datetime{4}=='-'){
	// 			@list($date, $time) = explode(' ', $datetime);
	// 			@list($yyyy, $mm, $dd ) = explode('-', $date);
	// 			$return = $dd.'/'.$mm.'/'.$yyyy;
	// 			if($return=='00/00/0000'){
	// 				$return='';
	// 			}
	// 		}
	// 		if(@$datetime{2}=='/'){
	// 			$return = substr($datetime,0,10);
	// 		}
	// 	}
	// 	return $return;
	// }

	
	static function formata_data_br($datetime){

		$return = $datetime;

		if($datetime){
			@list($date, $time) = explode(' ', $datetime);
			if(preg_match('/[0-9]{4}-[0-9]{2}-[0-9]{2}/',$date)){
				list($yyyy, $mm, $dd ) = explode('-', $date);
				$return = $dd.'/'.$mm.'/'.$yyyy;
			}
		}

		return $return;
	}

	// static function formata_hora_br($datetime){
	// 	$return = '';
	// 	if($datetime&&$datetime!=''){
	// 		//print $datetime;
	// 		if(strlen($datetime)>=5&&@$datetime{4}=='-'){
	// 			@list($date, $time) = explode(' ', $datetime);
	// 			@list($yyyy, $mm, $dd ) = explode('-', $date);
	// 			$return = $dd.'/'.$mm.'/'.$yyyy;
	// 			if($return=='00/00/0000'){
	// 				$return='';
	// 			}
	// 		}
	// 		if(strlen($datetime)>=3&&@$datetime{2}=='/'){
	// 			@list($date, $time) = explode(' ', $datetime);
	// 			@list($yyyy, $mm, $dd ) = explode('-', $date);
	// 			@list($h, $m, $s ) = explode(':', $time);
	// 			$return = $h.':'.$m;
	// 		}
	// 	}
	// 	return $return;
	// }

	static function formata_datahora_br($datetime){
		$return = '';
		if($datetime){
			if( preg_match('/([0-9]{4})-([0-9]{2})-([0-9]{2}) ([0-9]{2}):([0-9]{2}):([0-9]{2})/', $datetime, $matches) ){
				//printr($matches);
				list(,$ano,$mes,$dia,$hora,$minuto,$segundo)=$matches;
				$return = $dia.'/'.$mes.'/'.$ano .' ' .$hora.':'.$minuto.':'.$segundo	;
			}
		}
		return $return;
	}

	// Recebe uma string e devolve outra com formatacao de CPF
	static function formata_cpf($cpf){
		$cpf = self::getNumbers($cpf);
		if(strlen($cpf)==11){
		// if(is_cpf($cpf)){
			// 320 342 418 51
			return substr($cpf,0,3)
				.'.'.substr($cpf,3,3)
				.'.'.substr($cpf,6,3)
				.'-'.substr($cpf,9,2) ;
		}
		return $cpf;
	}

	// Recebe uma string e devolve outra com formatacao de CNPJ
	static function formata_cnpj($cnpj){
		$cnpj = self::getNumbers($cnpj);
		if(strlen($cnpj)==14){
		// if(is_cpf($cpf)){
			// 320 342 418 51
			// 08 711 573 0001 35
			return substr($cnpj,0,2)
				.'.'.substr($cnpj,2,3)
				.'.'.substr($cnpj,5,3)
				.'/'.substr($cnpj,8,4)
				.'-'.substr($cnpj,12,2);
		}
		return $cnpj;
	}

	// Termina - Funções de formatação


	// Comeca - Funcoes que printam html

	static function buttonAction($value, $action) {
		$return = '<input class="button" type="button" value="'.$value.'" onclick="enviar(\''.$action.'\')"/>' ;
		return $return ;
	}

	static function editor( $name, $value, $label) {
		require_once 'admin/fckeditor/fckeditor.php' ;
		//print '<p><label for="'.$name.'">'.$label.'</label></p>' ;
		$oFCKeditor = new FCKeditor($name) ;
		$oFCKeditor->BasePath	= PATH_SITE.'admin/fckeditor/' ;
		$oFCKeditor->Value		= $value ;
		$x = tag('p',tag('label',$label)).$oFCKeditor->CreateHtml() ;
		unset($oFCKeditor);
		return $x;
	}

	static function pLabel($name='', $label='') {
		return ( $label != '' ? '<p><label for="'.$name.'">'.$label.'</label></p>' : '' ) ;
	}

	static function textArea( $textarea_name, $textarea_value, $label, $cols=null, $rows=null, $max_length=null, $extra=null){
		$cols = ! $cols ? '30' : $cols ;
		$rows = ! $rows ? '4' : $rows ;
		$max_length = ! $max_length  ? '20000' : $max_length ;
		$extra = '' . $extra ;
		return self::pLabel($textarea_name,$label) . '<textarea name="'.$textarea_name.'" id="'.$textarea_name.'"  cols="'.$cols.'" rows="'.$rows.'" '.$extra.'>'.$textarea_value.'</textarea>' ;
	}

	static function inputSimples( $name, $value, $label, $size, $max_length = null, $extra = '' ) {
		return self::pLabel($name,$label) . '<input class="text" type="text" name="'.$name.'" id="'.$name.'" value="'.$value.'" size="'.$size.'" maxlength="'.$max_length.'" '.$extra.'/>' ;
	}

	static function checkbox( $name, $value, $label, $checked='' ) {
		return self::pLabel($name,$label) . '<input class="checkbox" type="checkbox" name="'.$name.'" id="'.$name.'" value="'.$value.'" '.$checked.' />' ;
	}

	static function inputImgNews( $name, $value, $label, $size, $max_length = null ) {
		return self::pLabel($name,$label) . '<input class="text" type="text" name="'.$name.'" id="'.$name.'" value="'.$value.'" size="'.$size.'" maxlength="'.$max_length.'" />' ;
	}

	static function inputReadOnly( $name, $value, $label, $size, $max_length = null ) {
		return self::pLabel($name,$label) . '<input readonly class="text readonly" type="text" name="'.$name.'" id="'.$name.'" value="'.$value.'" size="'.$size.'" maxlength="'.$max_length.'"  />' ;
	}

	static function inputData( $name, $value, $label ) {
		$return = self::pLabel($name,$label) . '<input class="text date" type="text" name="'.$name.'" id="'.$name.'" value="'.$value.'" size="15" maxlength="10"  />' ;
		$return .= '<script language="javascript">MaskInput(document.getElementById(\''.$name.'\'), "99/99/9999");</script>' ;
		return $return;
	}

	static function inputPass($name, $value, $label, $size, $max_length = null){
		return self::pLabel($name,$label) . '<input class="text" type="password" name="'.$name.'" id="'.$name.'" value="'.$value.'" size="'.$size.'" maxlength="'.$max_length.'"  />' ;
	}

	static function inputCep( $name, $value, $label, $efetua_busca_cep = false ) {
		$return = self::pLabel($name,$label) . '<input class="text" type="text" name="'.$name.'" id="'.$name.'" value="'.$value.'" size="10" maxlength="9"  />' ;
		$return .= '<script language="javascript">MaskInput(g(\''.$name.'\'), "99999-999");</script>' ;
		return $return ;
	}

	static function inputNumero( $name, $value, $label, $size, $max_length = null ) {
		//$extra = 'onBlur="javascript:Formata_Numero(this);" ' ;
		return self::pLabel($name,$label) .	'<input class="text" type="text" name="'.$name.'" id="'.$name.'" value="'.$value.'" size="'.$size.'" maxlength="'.$max_length.'"  />' ;
	}

	static function inputDecimal( $name, $value, $label, $casas_decimais=2 ) {
		return self::pLabel($name,$label) . '<input class="text" type="text" name="'.$name.'" id="'.$name.'" value="'.$value.'" size="5" maxlength="10" onKeyPress="return(formataMoeda(this,event))"/>';
	}

	static function inputHidden( $name, $value ){
		return '<input type="hidden" name="'.$name.'" id="'.$name.'" value="'.$value.'"  />' ;
	}

	static function inputFile( $name, $value, $label ){
		return self::pLabel($name,$label) . '<input type="file" name="'.$name.'" id="'.$name.'" value="'.$value.'"  size="40"/>' ;
	}

	static function inputAutoComplete($name, $value, $value_text, $label, $data_json, $size=60, $opcao_branco=false){

		// Need: <script language="javascript" src="js/jquery.autocomplete.min.js"></script>

		$id = str_replace('[', '_', $name);
		$id = str_replace(']', '', $id);

		$id_auto = "{$id}_autocomplete";

		$id_input = '';

		return self::pLabel($name, $label)
		.'<input type="hidden" name="'.$name.'" id="'.$id.'" value="'.$value.'"/>'
		.'<input class="text" type="text" name="'.$id_auto.'" id="'.$id_auto.'" value="'.$value_text.'" size="'.$size.'"/>'
		.
		"
		<script>

		// Instancia rotina de autocomplete
	
		$('#{$id_auto}').autocomplete({$data_json}, {
			minChars: 0
			,width: 440
			,matchContains: 'letter'
			,autoFill: false
			,formatItem: function(row, i, max) {
				return row.value;
			}
			,formatMatch: function(row, i, max) {
				return row.value + ' ' + row.value;
			}
			,formatResult: function(row) {
				return row.ds_input;
			}
		});
		
		$('#{$id_auto}').result(function(event, data, formatted) {
			if (data){
				$('#{$id}').val(data.id);
			}
			else {
				$('#{$id}').val('');
			}
		});

		</script>";

	}

	static function select($name, $value, $label, $array, $opcao_branco=false){
		//printr($array);
		$return = self::pLabel($name,$label) . '<select name="'.$name.'" id="'.$name.'">' . ($opcao_branco?'<option></option>':'') ;
		foreach ( $array as $arrayKey => $arrayValue ){
			$return .= '<option value="'.$arrayKey.'" '. ( $value == $arrayKey ? 'selected' : '' ) .'>'.$arrayValue.'</option>' ;
		}
		return $return . '</select>' ;
	}

	static function selectCategoria($name, $value, $label, $opcao_null=false) {
		return self::pLabel($name,$label) . "<select name='{$name}' id='{$name}'>" . ( $opcao_null ? '<option value=""></option>' : '' ) . self::optionsCategoria(0,$value) . "</select>" ;
	}

	static function selectEstado($name, $value, $label, $opcao_null=false) {
		return select($name, $value, $label, optionsEstado(), $opcao_null) ;
	}

	static function selectGravacao($name, $value, $label, $opcao_null=false) {
		return select($name, $value, $label, optionsGravacao(), $opcao_null) ;
	}

	static function selectCor($name, $value, $label, $opcao_null=false) {
		return select($name, $value, $label, optionsCor(), $opcao_null) ;
	}

	static function optionsCategoria($cat_id,$value,$espacos=0) {
		$return = '';
		$query = query($sql='SELECT * FROM categoria WHERE categoria_id = '.$cat_id .' ORDER BY nome');
		while($fetch=fetch($query)){
			$return .= "<option value='{$fetch->id}' ".($fetch->id==$value?'selected':'') ." >".str_repeat("---",$espacos)."{$fetch->nome}</option>";
			if(rows(query($sql='SELECT * FROM categoria WHERE categoria_id = '.$fetch->id .''))>0) {
				//printr($sql);
				$return .= optionsCategoria($fetch->id, $value, ++ $espacos);
				$espacos -- ;
			}
		}
		return $return ;
	}

	static function optionsEstado() {
		return array(
			'AC' => 'Acre', 'AL' => 'Alagoas', 'AP' => 'Amapá', 'AM' => 'Amazonas', 'BA' => 'Bahia', 'CE' => 'Ceará',
			'ES' => 'Espírito Santo', 'GO' => 'Goiás', 'MA' => 'Maranhão', 'MT' => 'Mato Grosso', 'MS' => 'Mato Grosso do Sul', 'MG' => 'Minas Gerais',
			'PA' => 'Pará', 'PB' => 'Paraíba', 'PR' => 'Paraná', 'PE' => 'Pernambuco', 'PI' => 'Piauí', 'RJ' => 'Rio de Janeiro',
			'RN' => 'Rio Grande do Norte', 'RS' => 'Rio Grande do Sul', 'RO' => 'Rondônia', 'RR' => 'Roraima', 'SC' => 'Santa Catarina', 'SP' => 'São Paulo',
			'SE' => 'Sergipe', 'TO' => 'Tocantins', 'DF' => 'Distrito Federal') ;
	}

	static function selectTipoLogradouro($name, $value, $label){
		$return = '<p><label for="'.$name.'">'.$label.'</label></p>' ;
		$return .= '<select name="'.$name.'" id="'.$name.'" >' ;
		$tipos = array( "RUA","AVENIDA","AEROPORTO","ALAMEDA","AREA","CAMPO","CHACARA","COLONIA", "CONDOMINIO","CONJUNTO","DISTRITO","ESPLANADA","ESTACAO","ESTRADA","FAVELA","FAZENDA", "FEIRA","JARDIM","LADEIRA","LAGO","LAGOA","LARGO","LOTEAMENTO","MORRO", "NUCLEO","PARQUE","PASSARELA","PATIO","PRACA","QUADRA","RECANTO","RESIDENCIAL", "RODOVIA","SETOR","SITIO","TRAVESSA","TRECHO","TREVO","VALE","VEREDA", "VIA","VIADUTO","VIELA","VILA") ;
		foreach ( $tipos as $tipo ){
			$return .= '<option value="'.$tipo.'" '.($value==$tipo?'selected':'').'>'.$tipo.'</option>' ;
		}
		$return .= '</select>' ;
		return $return ;
	}

	static function inputFone( $name, $value, $label ){
		$return = self::pLabel($name,$label) ;
		$return .= '<input class="text" type="text" name="'.$name.'" id="'.$name.'" value="'.$value.'" size="20" maxlength="18"/>' ;
		$return .= '<script language="javascript">MaskInput(g(\''.$name.'\'), "(99)9999-9999");</script>' ;
		return $return ;
	}

	static function inputCnpj( $name, $value, $label ){
		$return = self::pLabel($name,$label) ;
		$return .= '<input class="text" type="text" name="'.$name.'" id="'.$name.'" value="'.$value.'" size="20" maxlength="18"/>' ;
		$return .= '<script language="javascript">MaskInput(g(\''.$name.'\'), "99.999.999/9999-99");</script>' ;
		return $return ;
	}

	static function bandeira_br() {
		return '<img src="'. PATH_SITE .'admin/assets/bandeiras/br.png" />' ;
	}

	static function bandeira_es() {
		return '<img src="'. PATH_SITE .'admin/assets/bandeiras/es.png" />' ;
	}

	static function bandeira_in() {
		return '<img src="'. PATH_SITE .'admin/assets/bandeiras/us.png" />' ;
	}

	static function tag( $tag, $content='' ){
		$return = '' ;
		list($tagName) = explode(' ', $tag);
		$attributes = trim(substr($tag, strlen($tagName),strlen($tag)));
		if($content!=''){
			$return = "<{$tagName} {$attributes}>{$content}</$tagName> ";
		}
		else {
			$return = "<{$tagName} {$attributes} />";
		}
		return $return;
	}

	static function js($code){
		ob_start();
		?>
		<script language="javascript" type="text/javascript">
			<?php
			echo $code;
			?>
		</script>
		<?php
		$return = ob_get_contents();
		ob_end_clean();
		return $return;
	}

	// Termina - Funcoes que printam html


	// Youtube
	static function youtube_embed($codigo, $width='560', $height='315'){
		return '<iframe width="'.$width.'" height="'.$height.'" src="http://www.youtube.com/embed/'.$codigo.'" frameborder="0" allowfullscreen></iframe>';
	}


	// Retorna diferenca de dias entre duas datas
	static function getDaysBetween($dt1, $dt2)
	{
		if(!self::is_date($dt1))
		{
			throw new Exception('Data 1 inválida');
		}

		if(!self::is_date($dt2))
		{
			throw new Exception('Data 2 inválida');
		}

		list($d1,$m1,$y1) = explode('/',$dt1);
		list($d2,$m2,$y2) = explode('/',$dt2);

		return mktime(0,0,0,$m1,$d1,$y1)-mktime(0,0,0,$m2,$d2,$y2);

	}

	// Retorna apenas os numeros de uma string
	static function getNumbers($str)
	{
		preg_match_all( '/([0-9]{1,})/i', $str, $matches);
		return join('',$matches[0]);
	}

	// "Encoda" uma string
	static function encode($s){
		return base64_encode($s);
	}

	// Desencoda uma string
	static function decode($s){
		return base64_decode($s) ;
	}

	// Limpa user input
	static function cleanInput($str){

		return str_replace(array("'",">","<"),"",$str) ;
	}


	// get random string
	static function getRandomString($length){

		$a = array('a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z'
		 ,'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z'
		 ,0, 1, 2, 3, 4, 5, 6, 7, 8, 9);

		$p = '';
		$s = count($a)-1;
		for($i=0; $i < $length; $i++){
			$p .= $a[rand(0, $s)];
		}

		return $p;
	}

	// Trata data no formato pt-br para insercao/atualizacao no mysql
	static function to_bd_date($data){

		$return = '';

		// vindo como dd/mm/yyyy
		// formato do mysql = yyyy-mm-dd

		if($data!=''
		// &&strlen($data)==10)
		&&preg_match('/[0-9]{1,2}\/[0-9]{1,2}\/[0-9]{4}/', $data))
		{
			// @list( sprintf('%s', $dd), sprintf('%s', $mm), $yyyy ) = explode('/',$data);
			@list( $dd, $mm, $yyyy) = explode('/', $data);

			$dd = (strlen($dd)==1?'0'.$dd:$dd);
			$mm = (strlen($mm)==1?'0'.$mm:$mm);

			$return = $yyyy . '-' . $mm . '-' . $dd ;
		}
		return $return;

	}

	// Trata data/hora no formato pt-br para insercao/atualizacao no mysql
	static function to_bd_datetime($data_hora){

		$return = '';

		// vindo como dd/mm/yyyy
		// formato do mysql = yyyy-mm-dd

		if($data_hora!=''
		// &&strlen($data)==10)
		&&preg_match('/[0-9]{1,2}\/[0-9]{1,2}\/[0-9]{4} [0-9]{1,2}:[0-9]{1,2}:[0-9]{1,2}/', $data_hora))
		{
			// @list( sprintf('%s', $dd), sprintf('%s', $mm), $yyyy ) = explode('/',$data);
			@list( $data, $hora) = explode(' ', $data_hora);
			@list( $dd, $mm, $yyyy) = explode('/', $data);
			@list( $h, $m, $s) = explode(':', $hora);

			$dd = (strlen($dd)==1?'0'.$dd:$dd);
			$mm = (strlen($mm)==1?'0'.$mm:$mm);

			$h = (strlen($h)==1?'0'.$h:$h);
			$m = (strlen($m)==1?'0'.$m:$m);
			$s = (strlen($s)==1?'0'.$s:$s);

			$return = "{$yyyy}-{$mm}-{$dd} {$h}:{$m}:{$s}" ;
		}

		return $return;

	}
	
	// Retorna data no formata insercao
	static function bd_now(){
		return date('Y-m-d H:i:s');
	}

	// Debug
	static function printr($out){
		print '<pre>';
		print_r($out);
		print '</pre>';
	}

	//
	static function to_float($s){
		$s = str_replace('.','',$s) ;
		$s = str_replace(',','.',$s);
		return floatval($s);
	}

	static function stringAsTag($string){
		return butil::url_title_2($string);
	}	
	
	static function convert_accented_characters($str)
    {
        
		$foreign_characters = array(
			'/ä|æ|ǽ/' => 'ae',
			'/ö|œ/' => 'oe',
			'/ü/' => 'ue',
			'/Ä/' => 'Ae',
			'/Ü/' => 'Ue',
			'/Ö/' => 'Oe',
			'/À|Á|Â|Ã|Ä|Å|Ǻ|Ā|Ă|Ą|Ǎ/' => 'A',
			'/à|á|â|ã|å|ǻ|ā|ă|ą|ǎ|ª|ã/' => 'a',
			'/Ç|Ć|Ĉ|Ċ|Č/' => 'C',
			'/ç|ć|ĉ|ċ|č/' => 'c',
			'/Ð|Ď|Đ/' => 'D',
			'/ð|ď|đ/' => 'd',
			'/È|É|Ê|Ë|Ē|Ĕ|Ė|Ę|Ě/' => 'E',
			'/è|é|ê|ë|ē|ĕ|ė|ę|ě/' => 'e',
			'/Ĝ|Ğ|Ġ|Ģ/' => 'G',
			'/ĝ|ğ|ġ|ģ/' => 'g',
			'/Ĥ|Ħ/' => 'H',
			'/ĥ|ħ/' => 'h',
			'/Ì|Í|Î|Ï|Ĩ|Ī|Ĭ|Ǐ|Į|İ/' => 'I',
			'/ì|í|î|ï|ĩ|ī|ĭ|ǐ|į|ı/' => 'i',
			'/Ĵ/' => 'J',
			'/ĵ/' => 'j',
			'/Ķ/' => 'K',
			'/ķ/' => 'k',
			'/Ĺ|Ļ|Ľ|Ŀ|Ł/' => 'L',
			'/ĺ|ļ|ľ|ŀ|ł/' => 'l',
			'/Ñ|Ń|Ņ|Ň/' => 'N',
			'/ñ|ń|ņ|ň|ŉ/' => 'n',
			'/Ò|Ó|Ô|Õ|Ō|Ŏ|Ǒ|Ő|Ơ|Ø|Ǿ/' => 'O',
			'/ò|ó|ô|õ|ō|ŏ|ǒ|ő|ơ|ø|ǿ|º/' => 'o',
			'/Ŕ|Ŗ|Ř/' => 'R',
			'/ŕ|ŗ|ř/' => 'r',
			'/Ś|Ŝ|Ş|Š/' => 'S',
			'/ś|ŝ|ş|š|ſ/' => 's',
			'/Ţ|Ť|Ŧ/' => 'T',
			'/ţ|ť|ŧ/' => 't',
			'/Ù|Ú|Û|Ũ|Ū|Ŭ|Ů|Ű|Ų|Ư|Ǔ|Ǖ|Ǘ|Ǚ|Ǜ/' => 'U',
			'/ù|ú|û|ũ|ū|ŭ|ů|ű|ų|ư|ǔ|ǖ|ǘ|ǚ|ǜ/' => 'u',
			'/Ý|Ÿ|Ŷ/' => 'Y',
			'/ý|ÿ|ŷ/' => 'y',
			'/Ŵ/' => 'W',
			'/ŵ/' => 'w',
			'/Ź|Ż|Ž/' => 'Z',
			'/ź|ż|ž/' => 'z',
			'/Æ|Ǽ/' => 'AE',
			'/ß/'=> 'ss',
			'/Ĳ/' => 'IJ',
			'/ĳ/' => 'ij',
			'/Œ/' => 'OE',
			'/ƒ/' => 'f'
		);
		
        if ( ! isset($foreign_characters))
        {
            return $str;
        }

        return preg_replace(array_keys($foreign_characters), array_values($foreign_characters), $str);
    }

	static function url_title_2($str, $separator = '-', $lowercase = FALSE)
	{
		// butil::printr($str);
		// butil::printr(butil::convert_accented_characters($str));
		// butil::printr(butil::convert_accented_characters(utf8_encode($str)));
		// $str = butil::convert_accented_characters(utf8_encode($str));

        $str = butil::convert_accented_characters($str);
	
		if ($separator == 'dash')
		{
			$separator = '-';
		}
		else if ($separator == 'underscore')
		{
			$separator = '_';
		}

		$q_separator = preg_quote($separator);

		$trans = array(
			'&.+?;'                 => '',
			'[^a-z0-9 _-]'          => '',
			'\s+'                   => $separator,
			'('.$q_separator.')+'   => $separator
		);

		$str = strip_tags($str);

		foreach ($trans as $key => $val)
		{
			$str = preg_replace("#".$key."#i", $val, $str);
		}

		if ($lowercase === TRUE)
		{
			$str = strtolower($str);
		}

		return trim($str, $separator);
	}

	static function replaceAcentos($string){

		// a
		$string = str_replace(array('á', 'à', 'ã', 'â'),'a',$string);
		$string = str_replace(array('Á', 'À', 'Ã', 'Â'),'A',$string);
		// e
		$string = str_replace(array('é', 'è', 'ê'),'e',$string);
		$string = str_replace(array('É', 'È', 'Ê'),'E',$string);
		// i
		$string = str_replace(array('í', 'ì'),'i',$string);
		$string = str_replace(array('Í', 'Ì'),'I',$string);
		// o
		$string = str_replace(array('ó', 'ò', 'õ', 'ô'),'o',$string);
		$string = str_replace(array('Ó', 'Ò', 'Õ', 'Ô'),'O',$string);
		// u
		$string = str_replace(array('ú', 'ù', 'û'),'u',$string);
		$string = str_replace(array('Ú', 'Ù', 'Û'),'U',$string);

		// ç
		$string = str_replace(array('ç'),'c',$string);
		$string = str_replace(array('Ç'),'C',$string);

		return $string;

	}

	// Vamos desistir dessa abordagem de converter tudo para UTF-8, aparentem
	static function iso2utf8($str){
		return iconv('iso-8859-1','utf-8//translit',$str);
	}

	// Rede social
	static function twitter_last($username){
		// $username='repense'; // set user name
		$format='json'; // set format
		$tweet=json_decode(file_get_contents("http://api.twitter.com/1/statuses/user_timeline/{$username}.{$format}")); // get tweets and decode them into a variable
		return $tweet[0]->text; // show latest tweet
	}

	static function facebook_last($page_id){

		$opts = array(
			'http'=>array(
				'method'=>"GET"
				,'header'=>"Accept-language: en\r\n"
				."User-Agent: DoCoMo/1.0/P503i\r\n"
				."Cookie: foo=bar\r\n"
			)
		);

		$context = stream_context_create($opts);

		$str="http://pt-br.facebook.com/feeds/page.php?id={$page_id}&format=rss20";

		// print $str;
		// die();

		$xml = file_get_contents($str, false, $context);
		// print $xml;

		$out = array();

		$obj = @new SimpleXMLElement($xml);

		// butil::printr(@$obj->channel->item);

		$out['link'] = (string)@$obj->channel->item->link;
		$out['description'] = (string)@$obj->channel->item->description;

		return $out;

	}

	// Retorna array de meses
	static function arrayMeses(){
		return array('Janeiro','Fevereiro','Março','Abril','Maio','Junho','Julho','Agosto','Setembro','Outubro','Novembro','Dezembro');
	}

	// Grava log
	static function __log($obj)
	{
		if(false){
			$trace = debug_backtrace();
			$level = 1;
			$file = $trace[$level]['file'];
			$line = $trace[$level]['line'];
			$object = @$trace[$level]['object'];
			if (is_object($object)) { $object = get_class($object); }

			$dbgMsg = "\n";
			$dbgMsg .= "Trace: line {$line} of {$object} (in {$file})";
			$dbgMsg .= "\n";

			$filelog = '_log/'.date('dmyh').'.txt';

			if(!file_exists($filelog))
			{
				$file = fopen($filelog, 'w');
				fwrite($file, '');
				fclose($file);

				$str = var_export($obj, true);
			}
			else
			{
				$content = file_get_contents($filelog);
				$str = $content . "\n\n" . __LINE__ . "\n\n" . var_export($obj, true);
			}

			if(is_writable($filelog))
			{
				$file = fopen($filelog,'r+');
				fwrite($file, $dbgMsg . $str);
				fclose($file);
			}
		}
	}

}

?>