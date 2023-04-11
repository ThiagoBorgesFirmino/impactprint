<?php

/**********************

Criado em:
Ultima alteração: 23/07/2010

Change log:
23-07-2010 - melhoria na funcao to_bd_date

**********************/


function request($parametro){
	return str_replace("'","",@$_REQUEST[$parametro]) ;
}

function sanit($str){
	return str_replace("'","",$str) ;
}

function limpa($string){
	return modulo_database::db_quote($string);
}

function clean_input($str){
	// return mysql_real_escape_string($str);
	return modulo_database::db_quote($str);
}

function aj_autoload($class){

    // print_r($class);

    $paths = array(
        'admin/modulos/'.$class.'/'.$class.'.class.php'
        ,'class/'.$class.'.class.php'
        // ,'../class/'.$class.'.class.php'
        ,'admin/modulos/'.str_replace('modulo_','',$class).'/'.str_replace('modulo_','',$class).'.php'
    );

    $has = 0;
    foreach($paths as $file){
        if(file_exists($file)){
            require_once($file) ;
            $has ++;
            break;
        }
    }

    if($has == 0){
        // throw new Exception('Não encontrado: '.join(',', $paths));
    }
}
spl_autoload_register('aj_autoload');

/**
	pega as propriedades de um item do array e transforma em um objeto
**/
function array2obj($a){
	if(is_array($a)){
		list($value, $text) = each($a);
		$std = new stdClass();
		$std->value = $value;
		$std->text = $texto;
		return $str;
	}
}

function stringAsTag($string){

	//$string = strtolower($string);
	$string = str_replace(array(' ','/','\\','°'),'-',$string);

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
	
	preg_match_all( '/([a-z0-9]{1,})/i', $string, $matches);
	return join('-',$matches[0]);
}

function stringAsTag_v2($string){

	//$string = strtolower($string);
	$string = str_replace(array(' ','/','\\','°'),'-',$string);

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

function strClearCharSpc($string){
	$string = str_replace(array('@','#','$','%','¨','&','*','+','=','§','!','?','ª','º',',','.',';',':','|','{','}','[',']','`','´','^','~','<','>'),'-',$string);
	return stringAsTag_v2($string);
}

function destaca_html($subject, $word) {
   	$regex_chars = '\.+?(){}[]^$';
	for ($i=0; $i<strlen($regex_chars); $i++) {
		$char = substr($regex_chars, $i, 1);
		$word = str_replace($char, '\\'.$char, $word);
   	}
   	$word = '(.*)('.$word.')(.*)';
   	return eregi_replace($word, '\1<span class="destaca">\2</span>\3', $subject);
}

function redireciona($url,$tempo=1000){
	 echo '<script>setTimeout("window.location=\''.$url.'\'",'.$tempo.');</script>';
}

function js($code){
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

function printr( $object ){
	echo '<div style="background-color:#eee;text-align:left"><pre>' ; print_r( $object ) ; echo '</pre></div>' ;
}

function encode($s){
	return base64_encode($s);
}

function decode($s){
	return base64_decode($s) ;
}

function get_like($s){
	$procura_like=$s;
	return '%' . str_replace(' ','%',$s) . '%';
}

/**
 * Converte uma string com "enters" para uma string com <br />
 *
 * @param string $t
 * @return string
 */
function enter_2_br($t){
	return str_replace("\n",'<br />',$t) ;
}

/**
 * Converte uma string com <br> para uma string com "enters"
 *
 * @param string $t
 * @return string
 */
function br_2_enter($t){
	return str_replace('<br />',"\n",$t) ;
}

############# COMECA FUNCOES BANCO

function query($sql){
	return modulo_database::query($sql);
}

function pdo_lastID(){
	return modulo_database::last_inserted_id();
}


/*
RETORNA RESULTADO DE UMA COLUNA E UMA LINHA, USADO EM CASOS BEM ESPECIFICOS
*/

function query_col($sql){
	//print $sql;
	$query = query($sql) ;
	// $coluna_nome = mysql_field_name($query,0) ;
	$coluna_nome = modulo_database::field_name($query,0);
	$fetch = fetch($query) ;
	if(!$fetch){
		if(DEBUG=='1'){
			//print 'ERRO: '.$sql;
			return;	
		}

	}
	return $fetch->$coluna_nome ;
}

function fetch($query){
	// if($fetch = mysql_fetch_object( $query )){
	
	// 	return $fetch;		
	// }else{
	// 	//printr(mysql_error());
	// 	//die();
	// }
	return modulo_database::fetch($query);
}

function rows($query){
	return modulo_database::rows($query);
}

function results($sql){

	$query = query($sql);
	$return = array();

	while($fetch=fetch($query)){
		$return[] = $fetch;
	}

	return $return;
}

function bd_now(){
	return date('Y-m-d H:i:s');
}

function to_bd_date($data){

	$return = $data;

	if(preg_match('/[0-9]{2}\/[0-9]{2}\/[0-9]{4}/',$data)){
		list( $dd, $mm, $yyyy ) = explode('/',$data);
		$return = "{$yyyy}-{$mm}-{$dd}" ;
	}

	return $return;
}

### TERMINA FUNCOES BANCO

function recortaImagem($pathImagem, $larguraDesejada, $alturaDesejada){

	//print $pathImagem;

	list($larguraOriginal, $alturaOriginal) = getimagesize($pathImagem);
	$ratio_orig = $larguraOriginal/$alturaOriginal;

	if ($larguraDesejada/$alturaDesejada > $ratio_orig) {
		$larguraDesejada = $alturaDesejada*$ratio_orig;
	}
	else {
		$alturaDesejada = $larguraDesejada/$ratio_orig;
	}

	$imagemTemporaria = imagecreatetruecolor($larguraDesejada, $alturaDesejada);

	$imagem = @imagecreatefromjpeg($pathImagem);
	if(!$imagem){
		return false;
	}
	imagecopyresampled($imagemTemporaria, $imagem, 0, 0, 0, 0, $larguraDesejada, $alturaDesejada, $larguraOriginal, $alturaOriginal);
	imagejpeg($imagemTemporaria, $pathImagem, 100);
	return true;}

function recortaImagemPng($pathImagem, $larguraDesejada, $alturaDesejada){

	//print $pathImagem;

	list($larguraOriginal, $alturaOriginal) = getimagesize($pathImagem);
	$ratio_orig = $larguraOriginal/$alturaOriginal;

	if ($larguraDesejada/$alturaDesejada > $ratio_orig) {
		$larguraDesejada = $alturaDesejada*$ratio_orig;
	}
	else {
		$alturaDesejada = $larguraDesejada/$ratio_orig;
	}

	$imagemTemporaria = imagecreatetruecolor($larguraDesejada, $alturaDesejada);
	imagealphablending($imagemTemporaria, false);
	$imagem = imagecreatefrompng($pathImagem);
	imagecopyresampled($imagemTemporaria, $imagem, 0, 0, 0, 0, $larguraDesejada, $alturaDesejada, $larguraOriginal, $alturaOriginal);
	imagesavealpha($imagemTemporaria, true);
	//imagepng($imagemTemporaria, $pathImagem, 100);
	//$pngQuality = ($ - 100) / 11.111111;
	imagepng($imagemTemporaria, $pathImagem);
}


function isImagemJPG($file){
	if ( preg_match('/(jpeg|pjpeg|jpg)/', basename($file) )) {
		return true;
	}
	return false;
}
function isImagemPNG($file){
	if ( preg_match('/(png)/', basename($file) )) {
		return true;
	}
	return false;
}
function isImagemGIF($file){
	if ( preg_match('/(gif)/', basename($file) )) {
		return true;
	}
	return false;
}

function isImagemComTamanho($file, $largura_desejada, $altura_desejada) {

	list($largura_original, $altura_original) = getimagesize($file);

	if( $largura_desejada == $largura_original && $altura_desejada == $altura_original ){
		return true;
	}

	return false;
}

function isImagemComTamanhoMinimo($file, $largura_desejada, $altura_desejada) {

	/*
	$largura_desejada = 750;
	$altura_desejada = 750;

	$largura_original = 100;
	$altura_original = 800;
	*/

	list($largura_original, $altura_original) = getimagesize($file);

	//printr( getimagesize($file));

	if( $largura_desejada > $largura_original || $altura_desejada > $altura_original ){
		return false;
	}

	return true;
}

function imagecopymerge_alpha($dst_im, $src_im, $dst_x, $dst_y, $src_x, $src_y, $src_w, $src_h, $pct){
	$opacity=$pct;
	// getting the watermark width
	$w = imagesx($src_im);
	// getting the watermark height
	$h = imagesy($src_im);

	// creating a cut resource
	$cut = imagecreatetruecolor($src_w, $src_h);
	// copying that section of the background to the cut
	imagecopy($cut, $dst_im, 0, 0, $dst_x, $dst_y, $src_w, $src_h);
	// inverting the opacity
	$opacity = 100 - $opacity;

	// placing the watermark now
	imagecopy($cut, $src_im, 0, 0, $src_x, $src_y, $src_w, $src_h);
	imagecopymerge($dst_im, $cut, $dst_x, $dst_y, $src_x, $src_y, $src_w, $src_h, $opacity);
}

function isImagemQuadrada($file) {
	list($largura_original, $altura_original) = getimagesize($file);
	return ( $largura_original === $altura_original );
}

// funcao responsavel pela traducao
// se ela receber um objeto, procura por propriedades finalizadas com _in ou _es e atribue o valor delas caso exista pela propriedade de mesmo nome
// se ela receber uma string, procura no array de traducoes
function traduz($a) {
	if(is_object($a)){
		$idioma = @$_SESSION['IDIOMA'];
		if( $idioma != 'pt' && $idioma != '' ){
			if(is_a($a, 'base')){
				$vs = get_class_vars(get_class($a))	;
			}
			else {
				$vs = get_object_vars($a)	;
			}
			foreach ($vs as $k => $v){
				if ( preg_match("/([a-z]{1,})_".$idioma."/",$k,$matches) ){
					if( isset($a->$k) && $a->$k != '' ) {
						$a->$matches[1] = $a->$k ;
					}
				}
			}
		}
	}
}

function tofloat($s) {
	return to_float($s);
}

function to_float($s) {
	$s = preg_replace('/[A-Za-z$]/','',$s);
	$s = str_replace('.','',$s);
	$s = str_replace(',','.',$s);
	//return ($s);
	return floatval($s);
}

function cad_1_n_1( $tabela_a, $tabela_b, $tabela_c, $tabela_c_id, $orderby = "" ) {

	$return = '';

	$query = query($sql=
					"
					SELECT
						{$tabela_a}.*
						,CASE WHEN {$tabela_b}.id > 0 THEN 'checked' ELSE '' END checked
					FROM
						{$tabela_a}
					LEFT JOIN {$tabela_b} ON (
							{$tabela_a}.id = {$tabela_b}.{$tabela_a}_id
						AND {$tabela_b}.{$tabela_c}_id = {$tabela_c_id}
					)
					".($orderby!=""?"ORDER BY {$orderby}":"")
				);


	while($fetch=fetch($query)){
		$return .= "<input type=\"checkbox\" name=\"".$tabela_a."_id[]\" value=\"".$fetch->id."\" {$fetch->checked}/>".($fetch->nome!=""?$fetch->nome:$fetch->descricao)."<br/>"  ;
	}
	return $return;
}

// by naza 03/04/2014
function file_tratamento($nome_file, &$msg='', &$file=''){
	if(array_key_exists($nome_file,$_FILES)){
		$file = $_FILES[$nome_file];
		switch($_FILES[$nome_file]['error']){
			case 0 : return true; break;
			case 1 : $msg = tag("p","({$_FILES[$nome_file]['name']}) - O arquivo excedeu ".ini_get('upload_max_filesize').", tamanho limite definido pelo servidor.<br />"); break;
			case 2 : $msg = tag("p","({$_FILES[$nome_file]['name']}) - O arquivo &eacute; muito grande.<br />"); break;
			case 3 : $msg = tag("p","({$_FILES[$nome_file]['name']}) - O upload do arquivo foi feito parcialmente.<br />"); break;
			//case 4 : $msg = tag("p","({$_FILES[$nome_file]['name']}) - Não foi feito o upload do arquivo.<br />"); break;
		}
	}
	return false;
}
function file_tratamento_multiplo($nome_file, $key, &$msg=''){
	switch($_FILES[$nome_file]['error'][$key]){
		case 0 : return true; break;
		case 1 : $msg .= tag("p","({$_FILES[$nome_file]['name'][0]}) - O arquivo excedeu ".ini_get('upload_max_filesize').", tamanho limite definido pelo servidor.<br />"); break;
		case 2 : $msg .= tag("p","({$_FILES[$nome_file]['name'][0]}) - O arquivo &eacute; muito grande.<br />"); break;
		case 3 : $msg .= tag("p","({$_FILES[$nome_file]['name'][0]}) - O upload do arquivo foi feito parcialmente.<br />"); break;
		//case 4 : $msg .= tag("p","({$_FILES[$nome_file]['name'][0]}) - Não foi feito o upload do arquivo.<br />"); break;
	}
	return false;
}

function filtroProdutos(){
	$tfiltroproduto = new Template("admin/tpl.filtro-produto.html");
	$tfiltroproduto->path = PATH_SITE;
	foreach(categoria::opcoes() as $key => $value){
		$tfiltroproduto->categoria = new categoria($key);
		$tfiltroproduto->parseBlock("BLOCK_BUSCA_CATEGORIAS",true);
	}
	return $tfiltroproduto->getContent();
}

function setLogado($cadastro){
	$_SESSION['CADASTRO']=$cadastro;
	$_SESSION['SESSAO_ID']=session_id();
}
function setLogout(){
	if(isset($_SESSION['CADASTRO']))unset($_SESSION['CADASTRO']);
	if(isset($_SESSION['LOGADO']))unset($_SESSION['LOGADO']);
}

/* Add newsletter */
function addnews ($cadastro){
	if($cadastro->id){
		$newscadastro = new newscadastro(array("email"=>$cadastro->email));
		if(!$newscadastro->id){
			$newscadastro->email = $cadastro->email;
			$newscadastro->nome = $cadastro->nome;
			$newscadastro->st_ativo = "S";
			$newscadastro->salva();
		}
	}
};
function partDadosClienteCheckout(& $t){
	$part = new Template("tpl.part-dados-cliente-checkout.html");
	$part->token = session_id();
	$t->dadoscliente = $part->getContent();
}


// http://tutsheap.com/web/how-to-create-zip-file-using-php/
function createZip($files = array(),$zip_name = '') {

    //create the archive
    $zip = new ZipArchive();

    if($zip->open($zip_name, ZIPARCHIVE::CREATE)!==TRUE)
    {
        echo "<p style='color:red;'>Sorry ZIP creation failed at this time</p>";
        return false;
    }

    //add the files
    foreach($files as $file) {
        $zip->addFile($file['file'], $file['folder'].'/'.basename($file['file']));
    }

    //close the zip -- done!
    $zip->close();

    // var_dump($files);
    // var_dump($zip_name);
    // var_dump($zip);

    //check to make sure the file exists
    return file_exists($zip_name);

}
// VERIFICA SE POSSUI INFORMACOES NO ADMIN -> CONFIGURAÇÕES 
function redesSociais(&$t){
	$bloco ="";
	if(config::get("WHATSAPP") != "" && config::get("WHATSAPP") != "#"){
		$t->parseBlock('BLOCK_WHATSAPP');
		$t->parseBlock('BLOCK_WHATSAPP_NETWORK');
		$bloco= true;
	}

	if(config::get('INSTAGRAM') != '' && config::get('INSTAGRAM') != '#'){
		$t->parseBlock('BLOCK_INSTAGRAM');
		$t->parseBlock('BLOCK_INSTAGRAM_NETWORK');
		$bloco = true;
	}
	if($bloco){
		$t->parseblock('BLOCK_SOCIAIS'); 
	}
 }

function getStatus($status){
	return ($status == 'S' ? '<img src="'.PATH_SITE.'admin/assets/bola_verde.png"/>' : '<img src="'.PATH_SITE.'admin/assets/bola_vermelha.png"/>');
}

//  by naza 12/04/2017 Migration: for version control. 
function setNovaChave($chave=""){
 if($chave!=""){
	 // ADICIONAR NOVAS CHAVES 
	$chaves = array(
		"QTD_PRODUTOS_SELECIONADOS"=>array("grupo"=>"Categorias","chave"=>"QTD_PRODUTOS_SELECIONADOS","valor"=>"8","st_tipocampo"=>"TLINE","st_podealterar"=>"S","st_podeexcluir"=>"N","st_admin"=>"S")
		,"TESTE_NAZA"=>array("grupo"=>"Categorias","chave"=>"TESTE_NAZA","valor"=>"NAZA TESTE","st_tipocampo"=>"TLINE","st_podealterar"=>"S","st_podeexcluir"=>"N","st_admin"=>"S")
		,"IMAGEM_ESPECIAL_PRODUTO"=>array("grupo"=>"Produto","chave"=>"IMAGEM_ESPECIAL_PRODUTO","valor"=>"S","st_tipocampo"=>"TBOOLEAN","st_podealterar"=>"S","st_podeexcluir"=>"N","st_admin"=>"S")
		,"HORARIO_ATENDIMENTO_CONTINUACAO" => array("grupo"=>"Dados Gerais","chave"=>"HORARIO_ATENDIMENTO_CONTINUACAO","valor"=>"Sextas: das 8:00 as 12:00hs das 13:00 as 16:30hs","st_tipocampo"=>"TLINE","st_podealterar"=>"S","st_podeexcluir"=>"N","st_admin"=>"S")
		,"TIMTHUMB_HABILITADO"=>array("grupo"=>"Configuração de imagens","chave"=>"TIMTHUMB_HABILITADO","valor"=>"S","st_tipocampo"=>"TBOOLEAN","st_podealterar"=>"S","st_podeexcluir"=>"N","st_admin"=>"S")
		
		,"HABILITA_BANNER_LISTAGEM"=>array("grupo"=>"Produtos","chave"=>"HABILITA_BANNER_LISTAGEM","valor"=>"N","st_tipocampo"=>"TBOOLEAN","st_podealterar"=>"S","st_podeexcluir"=>"N","st_admin"=>"S")
		
		,"INSTRUCOES_COMPRA"=>array("grupo"=>"Orçamento","chave"=>"INSTRUCOES_COMPRA","valor"=>"","st_tipocampo"=>"TMULTIPLE","st_podealterar"=>"S","st_podeexcluir"=>"N","st_admin"=>"S","st_literal"=>"N")
		,"ESCOLHENDO_FRETE"=>array("grupo"=>"Orçamento","chave"=>"ESCOLHENDO_FRETE","valor"=>"","st_tipocampo"=>"TMULTIPLE","st_podealterar"=>"S","st_podeexcluir"=>"N","st_admin"=>"S","st_literal"=>"N")
		
		,"JS_GOOGLE_ANALYTICS"=>array("grupo"=>"Google","chave"=>"JS_GOOGLE_ANALYTICS","valor"=>"#","st_tipocampo"=>"TMULTIPLE","st_podealterar"=>"S","st_podeexcluir"=>"N","st_admin"=>"S","st_literal"=>"N")
	);
  
  if(isset($chaves[$chave])){
   $config = new config(array("chave"=>$chave));  
   if(!$config->id){
    $config->set_by_array($chaves[$chave]);
    if(!$config->salva()){
     printr("CONFIG ERRO #1");
     printr(mysql_error());
     die();
    }
   }
   return $chaves[$chave]['valor']; 
  }
 }
 
 return false;
}

require 'funcoes-html.php';
require 'funcoes-formatacao.php';
require 'funcoes-validacao.php';
require 'funcoes-site.php';
require 'funcoes-seo.php';