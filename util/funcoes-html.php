<?php

/**********************

Criado em:
Ultima alteração: 23/07/2010

Change log:
23-07-2010 - melhoria na funcao inputData

**********************/

function buttonAction( $value, $action) {
	$return = '<input class="button" type="button" value="'.$value.'" onclick="enviar(\''.$action.'\')"/>' ;
	return $return ;
}

function editor( $name, $value, $label) {
	require_once 'admin/fckeditor/fckeditor.php' ;
	//print '<p><label for="'.$name.'">'.$label.'</label></p>' ;
	$oFCKeditor = new FCKeditor($name) ;
	$oFCKeditor->BasePath	= PATH_SITE.'admin/fckeditor/' ;
	$oFCKeditor->Value		= $value ;
	$x = tag('p',tag('label',$label)).$oFCKeditor->CreateHtml() ;
	unset($oFCKeditor);
	return $x;
}

function pLabel($name='', $label='') {
	return ( $label != '' ? '<label class="filtro_label" for="'.$name.'">'.$label.'</label>' : '' ) ;
}

function textArea( $textarea_name, $textarea_value, $label, $cols=null, $rows=null, $max_length=null, $extra=null){
	$cols = ! $cols ? '30' : $cols ;
	$rows = ! $rows ? '4' : $rows ;
	$max_length = ! $max_length  ? '20000' : $max_length ;
	$extra = '' . $extra ;
	return pLabel($textarea_name,$label) . '<textarea class="form-control" name="'.$textarea_name.'" id="'.$textarea_name.'"  cols="'.$cols.'" rows="'.$rows.'" '.$extra.'>'.$textarea_value.'</textarea>' ;
}

function textAreaCount( $textarea_name, $textarea_value, $label, $cols=null, $rows=null, $max_length=null, $extra=null){
	$cols = ! $cols ? '30' : $cols ;
	$rows = ! $rows ? '4' : $rows ;
	$max_length = ! $max_length  ? '20000' : $max_length ;
	$extra = '' . $extra ;
	return pLabel($textarea_name,$label) . '<textarea class="form-control _count" data-count="61" name="'.$textarea_name.'" id="'.$textarea_name.'"  cols="'.$cols.'" rows="'.$rows.'" '.$extra.'>'.$textarea_value.'</textarea>' ;
}

function textAreaCkeditor( $textarea_name, $textarea_value, $label, $cols=null, $rows=null, $max_length=null, $extra=null){
	$cols = ! $cols ? '30' : $cols ;
	$rows = ! $rows ? '4' : $rows ;
	$max_length = ! $max_length  ? '20000' : $max_length ;
	$extra = '' . $extra ;
	return pLabel($textarea_name,$label) . '<textarea name="'.$textarea_name.'" id="'.$textarea_name.'"  cols="'.$cols.'" rows="'.$rows.'" class="ckeditor" '.$extra.'>'.$textarea_value.'</textarea>' ;
}


function textAreaTinyMCE( $textarea_name, $textarea_value, $label, $cols=null, $rows=null, $max_length=null, $extra=null){
	$cols = ! $cols ? '30' : $cols ;
	$rows = ! $rows ? '4' : $rows ;
	$max_length = ! $max_length  ? '20000' : $max_length ;
	$extra = '' . $extra ;
	return pLabel($textarea_name,$label) . '<textarea class="mceEditor" style="width:100%;height:400px""  name="'.$textarea_name.'" id="'.$textarea_name.'" '.$extra.'>'.$textarea_value.'</textarea>' ;
}

function inputSimples( $name, $value, $label, $size = null, $max_length = null, $extra = '' ) {
	$id = str_replace(array('[',']'),'_',$name);
	return pLabel($name,$label) . '<input class="form-control" type="text" name="'.$name.'" id="'.$id.'" value="'.$value.'" '.($size?'size="'.$size.'"':'').' maxlength="'.$max_length.'" '.$extra.' />' ;
}

function inputTelefone( $name, $value, $label, $size, $max_length = null, $extra = '' ){
	$id = str_replace(array('[',']'),'_',$name);
	$return = pLabel($name,$label) . '<input class="text" type="text" name="'.$name.'" id="'.$id.'" value="'.$value.'" size="'.$size.'" maxlength="'.$max_length.'" '.$extra.' />' ;
	return $return;
}

function checkbox( $name, $value, $label, $checked='' ) {
	// $id = $name.$value;
	$id = str_replace(array('[',']'),'_',$name.$value);
	//return pLabel($id,$label) . '<input class="checkbox" type="checkbox" name="'.$name.'" id="'.$id.'" value="'.$value.'" '.$checked.' />' ;

    return '<div class="checkbox">
      <label>
        <input type="checkbox" class="checkbox" type="checkbox" name="'.$name.'" id="'.$id.'" value="'.$value.'" '.$checked.'>
            '.$label.'
      </label>
    </div>';
}

function checkbox2( $name, $value, $label, $checked='', $extra ) {
	//$id =$name.'['.$value.']';
	$id =$name;
	return pLabel($name,$label) . '<input class="checkbox" type="checkbox" name="'.$name.'" id="'.$id.'" value="'.$value.'" '.$checked.' '.$extra.' />' ;
}

function radio( $name, $value, $label, $checked='' ) {
	$id = $name.$value;
	return pLabel($name,$label) . '<input class="checkbox" type="radio" name="'.$name.'" id="'.$id.'" value="'.$value.'" '.$checked.' />' ;
}

function inputImgNews( $name, $value, $label, $size, $max_length = null ) {
	return pLabel($name,$label) . '<input class="text" type="text" name="'.$name.'" id="'.$name.'" value="'.$value.'" size="'.$size.'" maxlength="'.$max_length.'" />' ;
}

function inputReadOnly( $name, $value, $label, $size, $max_length = null ) {
	return pLabel($name,$label) . '<input readonly class="form-control disabled" type="text" name="'.$name.'" id="'.$name.'" value="'.$value.'" maxlength="'.$max_length.'"  />' ;
}

function inputData( $name, $value, $label ) {
	$id = str_replace(array('[',']'),'_',$name);
	$return = pLabel($name,$label) . '<input class="text form-control" style="width:auto;max-width:100%;display:inline-block;" type="text" name="'.$name.'" id="'.$id.'" value="'.$value.'" size="12" maxlength="10"  />' ;
	//$return .= '<script language="javascript">MaskInput(document.getElementById(\''.$id.'\'), "99/99/9999");</script>' ;
	$return .= '<script language="javascript">$("#'.$id.'").mask("99/99/9999");</script>' ;
	return $return;
}


function inputHora( $name, $value, $label ) {
	$id = str_replace(array('[',']'),'_',$name);
	$return = pLabel($name,$label) . '<input class="text form-control" type="text" name="'.$name.'" id="'.$id.'" value="'.$value.'" size="15" maxlength="10"  />' ;
	//$return .= '<script language="javascript">MaskInput(document.getElementById(\''.$id.'\'), "99:99:99");</script>' ;
	$return .= '<script language="javascript">$("#'.$id.'").mask("99:99:99");</script>' ;
	return $return;
}


function inputPass($name, $value, $label, $size, $max_length = null){
	return pLabel($name,$label) . '<input class="text" type="password" name="'.$name.'" id="'.$name.'" value="'.$value.'" size="'.$size.'" maxlength="'.$max_length.'"  />' ;
}

function inputCep( $name, $value, $label, $efetua_busca_cep = false ) {
	$return = pLabel($name,$label) . '<input class="text" type="text" name="'.$name.'" id="'.$name.'" value="'.$value.'" size="10" maxlength="9"  />' ;
	//$return .= '<script language="javascript">MaskInput(g(\''.$name.'\'), "99999-999");</script>' ;
	$return .= '<script language="javascript">$("#'.$name.'").mask("99999-999");</script>' ;
	return $return ;
}

function inputNumero( $name, $value, $label, $size, $max_length = null ) {
	//$extra = 'onBlur="javascript:Formata_Numero(this);" ' ;
	return pLabel($name,$label).'<input class="text form-control" type="number" name="'.$name.'" id="'.$name.'" value="'.$value.'" size="'.$size.'" maxlength="'.$max_length.'" />';
}

function inputDecimal( $name, $value, $label, $casas_decimais=2 ) {
	$id = str_replace(array('[',']'),'_',$name);
	return pLabel($name,$label) . '<input class="text form-control" type="text" name="'.$name.'" id="'.$id.'" value="'.$value.'" size="10" maxlength="10" onKeyPress="return(formataMoeda(this,event))"/>';
}

function inputHidden( $name, $value ){
	return '<input type="hidden" name="'.$name.'" id="'.$name.'" value="'.$value.'"  />' ;
}

function inputFile( $name, $value, $label ){
	$html= pLabel($name,$label) . '<input class="xfilestyle" type="file" name="'.$name.'" id="'.$name.'" value="'.$value.'"   data-buttonName="btn-primary" />' ;
	//$html .="<script>$(document).ready(function(){ $('#{$name}').filestyle({buttonName: 'btn-primary','icon': false}); });</script>";
	return $html;
}

function inputAutoComplete($name, $value, $value_text, $label, $data_json, $size=60, $opcao_branco=false){

	// Need: <script language="javascript" src="js/jquery.autocomplete.min.js"></script>

	$id = str_replace('[', '_', $name);
	$id = str_replace(']', '', $id);

	$id_auto = "{$id}_autocomplete";

	$id_input = '';

	return pLabel($name, $label)
	.'<input type="hidden" name="'.$name.'" id="'.$id.'" value="'.$value.'"/>'
	.'<input class="text" type="text" name="'.$id_auto.'" id="'.$id_auto.'" value="'.$value_text.'" size="'.$size.'"/>'
	.
	"
	<script>

	// Instancia rotina de autocomplete

	$('#{$id_auto}').autocomplete({$data_json}, {
		minChars: 0
		,max: 999999
		,width: 440
		,matchContains: 'letter'
		,autoFill: false
		,formatItem: function(row, i, max) {
			return row.value;
		}
		,formatMatch: function(row, i, max) {
			return row.value;
		}
		,formatResult: function(row) {
			return row.ds_input;
		}
	});
	
	$('#{$id_auto}').result(function(event, data, formatted) {
		if (data){
			$('#{$id}').val(data.id);
			$('#{$id}').trigger('change');
		}
		else {
			$('#{$id}').val('');
			$('#{$id}').trigger('change');
		}
	});

	</script>";

}


function select($name, $value, $label, $array, $opcao_branco=false){
	//printr($array);
	$return = pLabel($name,$label) . '<select class="selectpicker form-control w_auto" name="'.$name.'" id="'.str_replace(array('[',']'),'_',$name).'">' . ($opcao_branco?'<option></option>':'') ;
	foreach ( $array as $arrayKey => $arrayValue ){
		$return .= '<option value="'.$arrayKey.'" '. ( $value == $arrayKey ? 'selected' : '' ) .'>'.$arrayValue.'</option>' ;
	}
	return $return . '</select>' ;
}


function select_script($name, $value, $label, $array, $opcao_branco=false,  $script=''){
	//printr($array);
	$return = pLabel($name,$label) . '<select class="selectpicker form-control" name="'.$name.'" id="'.str_replace(array('[',']'),'_',$name).'" '.$script.'>' . ($opcao_branco?'<option></option>':'') ;
	foreach ( $array as $arrayKey => $arrayValue ){
		$return .= '<option value="'.$arrayKey.'" '. ( $value == $arrayValue ? 'selected' : '' ) .'>'.$arrayValue.'</option>' ;
	}
	return $return . '</select>' ;
}

function selectCategoria($name, $value, $label, $opcao_null=false) {
	return pLabel($name,$label) . "<select class='selectpicker form-control' name='{$name}' id='{$name}'>" . ( $opcao_null ? '<option value=""></option>' : '' ) . optionsCategoria(0,$value) . "</select>" ;
}

function selectMarca($name, $value, $label, $opcao_null=false) {
	return pLabel($name,$label) . "<select class='selectpicker form-control' name='{$name}' id='{$name}'>" . ( $opcao_null ? '<option value=""></option>' : '' ) . optionsMarcas(0,$value) . "</select>" ;
}

function selectSplash($name, $value, $label, $opcao_null=false) {
	return pLabel($name,$label) . "<select class='selectpicker form-control' name='{$name}' id='{$name}'>" . ( $opcao_null ? '<option value="nenhuma" selected></option>' : '' ) . optionsSplash(0,$value) . "</select>" ;
}

function selectEstado($name, $value, $label, $opcao_null=false) {
	return select($name, $value, $label, optionsEstado(), $opcao_null) ;
}

function selectGravacao($name, $value, $label, $opcao_null=false) {
	return select($name, $value, $label, optionsGravacao(), $opcao_null) ;
}

function selectCor($name, $value, $label, $opcao_null=false) {
	return select($name, $value, $label, cor::opcoes(), $opcao_null) ;
}

function optionsCategoria($cat_id,$value,$espacos=0,&$categorias=array()) {

    if(sizeof($categorias)==0){
        $categorias = results("SELECT id,categoria_id,nome FROM categoria");
    }

	$return = '';
	// $query = query($sql='SELECT * FROM categoria WHERE categoria_id = '.$cat_id .' ORDER BY nome');

    $tmp = array();
    foreach($categorias as $categoria){
        if(intval($categoria->categoria_id)==$cat_id){
            $tmp[] = $categoria;
        }
    }

	// while($fetch=fetch($query)){
    foreach($tmp as $fetch){
		$return .= "<option value='{$fetch->id}' ".($fetch->id==$value?'selected':'') ." >".str_repeat("---",$espacos)."{$fetch->nome}</option>";
        $espacos ++;
        $return .= optionsCategoria($fetch->id, $value, $espacos, $categorias);
        $espacos -- ;
	}
	return $return ;
}

function optionsSplash($cat_id,$value,$espacos=0) {
	$return = '';
	$query = query($sql='SELECT * FROM splash ORDER BY nome');
	while($fetch=fetch($query)){
		$return .= "<option value='{$fetch->id}' ".($fetch->id==$value?'selected':'') ." >{$fetch->nome}</option>";
	}
	return $return ;
}


function optionsMarcas($marca_id,$value,$espacos=0) {
	$return = '';
	$query = query($sql='SELECT * FROM marca ORDER BY nome');
	while($fetch=fetch($query)){
		$return .= "<option value='{$fetch->id}' ".($fetch->id==$value?'selected':'') ." >{$fetch->nome}</option>";
	}
	return $return ;
}


function optionsEstado() {
	return array(
		'AC' => 'Acre',
		'AL' => 'Alagoas',
		'AP' => 'Amapá',
		'AM' => 'Amazonas',
		'BA' => 'Bahia',
		'CE' => 'Ceará',
		'ES' => 'Espírito Santo',
		'GO' => 'Goiás',
		'MA' => 'Maranhão',
		'MT' => 'Mato Grosso',
		'MS' => 'Mato Grosso do Sul',
		'MG' => 'Minas Gerais',
		'PA' => 'Pará',
		'PB' => 'Paraíba',
		'PR' => 'Paraná',
		'PE' => 'Pernambuco',
		'PI' => 'Piauí',
		'RJ' => 'Rio de Janeiro',
		'RN' => 'Rio Grande do Norte',
		'RS' => 'Rio Grande do Sul',
		'RO' => 'Rondônia',
		'RR' => 'Roraima',
		'SC' => 'Santa Catarina',
		'SP' => 'São Paulo',
		'SE' => 'Sergipe',
		'TO' => 'Tocantins',
		'DF' => 'Distrito Federal') ;
}

function selectTipoLogradouro($name, $value, $label){
	$return = '<p><label for="'.$name.'">'.$label.'</label></p>' ;
	$return .= '<select name="'.$name.'" id="'.$name.'" >' ;
	$tipos = array( "RUA","AVENIDA","AEROPORTO","ALAMEDA","AREA","CAMPO","CHACARA","COLONIA", "CONDOMINIO","CONJUNTO","DISTRITO","ESPLANADA","ESTACAO","ESTRADA","FAVELA","FAZENDA", "FEIRA","JARDIM","LADEIRA","LAGO","LAGOA","LARGO","LOTEAMENTO","MORRO", "NUCLEO","PARQUE","PASSARELA","PATIO","PRACA","QUADRA","RECANTO","RESIDENCIAL", "RODOVIA","SETOR","SITIO","TRAVESSA","TRECHO","TREVO","VALE","VEREDA", "VIA","VIADUTO","VIELA","VILA") ;
	foreach ( $tipos as $tipo ){
		$return .= '<option value="'.$tipo.'" '.($value==$tipo?'selected':'').'>'.$tipo.'</option>' ;
	}
	$return .= '</select>' ;
	return $return ;
}

function optionsGravacao() {
	$query = query("select id, descricao from gravacao");
}

function optionsCor() {
}

function inputFone( $name, $value, $label ){
	$return = pLabel($name,$label) ;
	$return .= '<input class="text" type="text" name="'.$name.'" id="'.$name.'" value="'.$value.'" size="20" maxlength="18"/>' ;
	//$return .= '<script language="javascript">MaskInput(g(\''.$name.'\'), "(99)9999-9999");</script>' ;
	$return .= '<script language="javascript">$("#'.$name.'").mask("(99)9999-9999");</script>' ;
	return $return ;
}

function inputCnpj( $name, $value, $label ){
	$return = pLabel($name,$label) ;
	$return .= '<input class="text" type="text" name="'.$name.'" id="'.$name.'" value="'.$value.'" size="20" maxlength="18"/>' ;
	//$return .= '<script language="javascript">MaskInput(g(\''.$name.'\'), "99.999.999/9999-99");</script>' ;
	$return .= '<script language="javascript">$("#'.$name.'").mask("99.999.999/9999-99");</script>' ;
	return $return ;
}

function bandeira_br() {
	return '<img src="'. PATH_SITE .'admin/assets/bandeiras/br.png" />' ;
}

function bandeira_es() {
	return '<img src="'. PATH_SITE .'admin/assets/bandeiras/es.png" />' ;
}

function bandeira_in() {
	return '<img src="'. PATH_SITE .'admin/assets/bandeiras/us.png" />' ;
}

function h1($s) {
	return '<h1>'.$s.'</h1>';
}

function inputDisplay( $value, $label ) {
	return pLabel('',$label) . '<br />'.nl2br($value);
}

function tag( $tag, $content='' ){
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

?>