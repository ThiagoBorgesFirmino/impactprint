<?php

/**********************

Criado em:
Ultima alteração: 23/07/2010

Change log:
23-07-2010 - melhoria na funcao formata_data_br

**********************/

function money($valor){
	//printr( $valor);
	//$val = str_replace(".",",",$valor);
	//$val = str_replace(",",".",$val);
	//$val = $valor;
	//printr($val);
	//printr(to_float($val));
	// printr(number_format( $val, 2, ",", '.') );
	// printr("----------------");
		
	return number_format(floatval($valor), 2, ",", '.') ;
}

function percent($valor){
	return number_format( $valor, 1, ".", '') . '%'  ;
}

function formata_cep($cep){
	if ( strlen($cep) == 8 )
		return substr($cep,0,5) . '-' . substr($cep,5,3) ;
	return $cep ;
}

function formata_cpf($cpf){
	$cpf = getNumbers($cpf);
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

function formata_data_br($datetime){

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

function formata_datahora_br($datetime){
	if( preg_match('/([0-9]{4})-([0-9]{2})-([0-9]{2}) ([0-9]{2}):([0-9]{2}):([0-9]{2})/', $datetime, $matches) ){
		list(,$ano,$mes,$dia,$hora,$minuto,$segundo)=$matches;
		
		if($hora == "00" 
		&& $minuto == "00" 
		&& $segundo == "00" ){
			return $dia.'/'.$mes.'/'.$ano;
		}
		
		return $dia.'/'.$mes.'/'.$ano .' ' .$hora.':'.$minuto.':'.$segundo	;
	}
	return '';
}


// SUPOE-SE QUE A DATA ESTA VINDO NO FORMATDO BRASILEIRO = DD/MM/YYYY
// RETORNA ALGO COMO 30 de junho
function formata_data_extenso($data_br){
	if($data_br){
		list($dd, $mm, $yyyy) = explode('/', $data_br);
		$mm = get_mes($mm);
		return "{$dd} de {$mm} {$yyyy}";
	}
}

function get_mes($m){
	$meses = array(
				'1'=>'Janeiro'
				,'Fevereiro'
				,'Março'
				,'Abril'
				,'Maio'
				,'Junho'
				,'Julho'
				,'Agosto'
				,'Setembro'
				,'Outubro'
				,'Novembro'
				,'Dezembro'
			);

	return @$meses[intval($m)];
}
function get_mes_abreviado($m){
	$meses = array(
				'1'=>'Jan'
				,'Fev'
				,'Mar'
				,'Abr'
				,'Mai'
				,'Jun'
				,'Jul'
				,'Ago'
				,'Set'
				,'Out'
				,'Nov'
				,'Dez'
			);

	return @$meses[intval($m)];
}
function Meses($m){
	$meses = array(
				'1'=>'Janeiro'
				,'2'=>'Fevereiro'
				,'3'=>'Março'
				,'4'=>'Abril'
				,'5'=>'Maio'
				,'6'=>'Junho'
				,'7'=>'Julho'
				,'8'=>'Agosto'
				,'9'=>'Setembro'
				,'10'=>'Outubro'
				,'11'=>'Novembro'
				,'12'=>'Dezembro'
			);

	return @$meses[intval($m)];
}

function getStAtivoFormatado($st_ativo){
    return ( $st_ativo=='S'?"<img src='".PATH_SITE."admin/assets/bola_verde.png'  />" : "<img src='".PATH_SITE."admin/assets/bola_vermelha.png'  />" );
}

function mask($val, $mask)
{
    $maskared = '';
    $k = 0;
    for($i = 0; $i<=strlen($mask)-1; $i++)
    {
        if($mask[$i] == '#')
        {
            if(isset($val[$k]))
                $maskared .= $val[$k++];
        }
        else
        {
            if(isset($mask[$i]))
                $maskared .= $mask[$i];
        }
    }
    return $maskared;
}
