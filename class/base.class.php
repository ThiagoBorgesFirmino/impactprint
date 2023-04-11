<?php
/*@author: Felipe Gregorio*/
class base {

	function __construct($id=0) {
		//print tag('br');
		//print $id;
		if(!$id){
			$this->id = 0;
			return;
		}
		$this->get_by_id($id);
	}

	
	public function __call($metodo, $args) {
		// handler de metodo get_by_<nome_do_campo_no_model>
		if(preg_match('/get_by_([a-z_]{1,})/',$metodo,$match)) {		
			$this->_get_by($match[1], $args[0]);
		}
		else {
			print "<h3 style='background-color:red;color:white'>{$metodo}</h3>";
		}
	}

	public function get_by_id($id){
		if(is_array($id)){
			$where = array();
			foreach ($id as $key=>$value){
				
				$arr = explode(';',$key);
				
				// IN
				if(is_array($value)){
					$newvalue = array();
					foreach($value as $_value){
						$newvalue[] = "'{$_value}'";
					}
					
					if(sizeof($arr)>1){
						$where[] = "and (";
						$cont = 0;
						foreach($arr as $_key){
							$where[] .= " ".($cont>0?" OR ":" ")." {$_key} IN (".join(',', $newvalue).") ";
							$cont++;
						}
						$where[] .= ")";
					}else{
						$where[] = "and {$key} IN (".join(',', $newvalue).")";
					}
				}
				else {
					if(sizeof($arr)>1){
						$where[] = "and (";
						$cont = 0;
						foreach($arr as $_key){
							$where[] .= " ".($cont>0?" OR ":" ")." {$_key} = '{$value}' ";
							$cont++;
						}
						$where[] .= ")";
					}else{
						if ($value == 'null' || $value == null || empty($value) || !isset($value) || $value == "") {
							$where[] = " and ({$key} is null or {$key} = '0') ";
						} else $where[] = "and {$key} = '{$value}'";
					}
				}
			}
			$this->set_vars($sql=$this->get_sql(join(' ',$where)));
			//printr($sql);
		}
		else{
			$this->set_vars($sql=$this->get_sql('and id='.intval($id)));
		}
		//printr($sql);
	}

	public function load_by_fetch($fetch){
		foreach ( get_object_vars($fetch) as $key_name => $key_value ){
			$this->$key_name = $key_value;
		}
	}

	// $c = nome do campo , $v = valor
	private function _get_by($c,$v) {
		$this->set_vars( $sql=$this->get_sql("and {$c} = '{$v}'") );
		//printr($sql);
	}

	public function get_parent($model) {
		//$cadastro = $pedido->get_model('cadastro');
		$key = $model.'_id';
		$obj = new $model();
		if($value=$this->$key){
			$obj = new $model();
			$obj->get_by_id($value);
			return $obj;
		}
		return $obj;
	}

	public function get_childs($model, $filtro=null, $orderby=null) {
		#select * from pedido_item where pedido_id = x
		$return = array();
		$query = query("select * from {$model} where {$this->get_table_name()}_id = {$this->id}  {$filtro} {$orderby}");
		while($fetch=fetch($query)){
			$obj = new $model();
			//printr($fetch);
			foreach ( get_object_vars($fetch) as $key_name => $key_value ){
				$obj->$key_name = $key_value;
			}
			$return[] = $obj;
		}
		return $return;
	}

	public function get_related_objects(){
		$return = array();
		$query = query("show tables");
		$tables_in = "Tables_in_".BD_DATABASE;
		while($fetch=fetch($query)){
			/*
			$obj_name = $fetch->$tables_in;
			$obj = new $obj_name();

			foreach (get_class_vars(get_class($obj)) as $var_key => $var_value ){
				$key = $this->get_table_name()."_id";
				if( $var_key == $this->get_table_name()."_id" ){
					//printr($obj);
					$childs = $this->get_childs($obj_name);
					if( sizeof($childs) > 0 ){
						$return = array_merge($return, $childs);
					}
				}
			}
			*/
		}
		return $return;
	}

	public function amigavel($plural=false){
		$table_name = $this->get_table_name();
		return ucwords(str_replace('_',' ',$table_name));
	}

	public function get_table_name(){
		return get_class($this);
	}

	## teria que ser private ...

	public function set_vars( $sql ){
		$query = query($sql) ;
		$object = fetch($query) ;
		if ( $object){
			foreach ( get_object_vars($object) as $var_name => $var_value ){
				$this->$var_name = $object->$var_name ;
			}
		}
	}

	public function reset_vars(){
		foreach ( get_class_vars(get_class($this)) as $var_name => $var_value ){
			$this->$var_name='';
		}
	}

	public function set_by_request(){
		foreach ( get_class_vars(get_class($this)) as $var_name => $var_value ){
			if(	  @$_REQUEST[$var_name]
				||@$_REQUEST[$var_name]==="0"
				||@$_REQUEST[$var_name]===""){
				$this->$var_name = request($var_name) ;
			}
		}
	}

	public function set_by_array($array){
		
		if(is_array($array)){
			$obj = get_class($this);
			foreach ( get_class_vars(get_class($this)) as $var_name => $var_value ){
				if(array_key_exists($var_name,$array)){
					//print $var_name.'<br>';
					if($obj=='pagina')
					{
						$this->$var_name = mysql_real_escape_string($array[$var_name]) ;
					}
					else 
					{
						$this->$var_name = sanit($array[$var_name]) ;
					}
					
				}
			}
		}
	}

	// retorna uma string que contem um select * from nome_da_classe que chamar
	public function get_sql( $filtro = null , $order_by = null, $group_by = null, $having = null ){ // string filtro, string order by
		return 'select * from ' . $this->get_table_name() . ' where 1=1 '.$filtro. ' ' . $order_by . ' ' . $group_by . ' ' . $having ;
	}

	public function salva() {

		$return = false;

		if(@$this->id>0){
			$acao = 'atualizacao';
			$return = $this->atualiza();
		}
		else {
			$acao = 'insercao';
			$return = $this->insere();
		}

		if( $this->get_table_name() != 'log' ){
			//$log = new _log();
			//$log->acao = $acao;
			//$log->descricao = '';
		}

		return $return;
	}

	public function insere(){

		//printr($this);


		$return = false;

		$sql = ' INSERT INTO ' . $this->get_table_name() ;
		$campos = array() ;

		
		// se existir o campo de data de cadastro na tabela, preenche com a data/hora do momento
		if(property_exists(get_class($this),'data_cadastro')){
			$this->data_cadastro = bd_now();
		}

		//var_dump($this);
		foreach ( get_class_vars(get_class($this)) as $var_name => $var_value ){
			if( $var_name != 'id'
				&& isset($this->$var_name) ){
				$campos[1][] = "`{$var_name}`" ;
				if ( $this->$var_name === "null" ){
					$campos[2][] = "null";
				}
				else{
					$campos[2][] = "'".($this->$var_name)."'";
				}
			}
		}

		$sql = $sql . "(" . join(",",$campos[1]) . ") VALUES (". join(",",$campos[2]) .")";

		// printr($_REQUEST);
		// printr($sql);
		// die();

		$return = (query($sql));
		// $this->id = mysql_insert_id();
		$this->id = pdo_lastID();
		return ($return);
	}

	public function atualiza(){

		$sql = ' UPDATE ' . $this->get_table_name() . ' SET ' ;
		$campos = array() ;

		foreach ( get_class_vars(get_class($this)) as $var_name => $var_value ){
			if( $var_name != "id" && isset($this->$var_name)){
			
				$value = $this->$var_name === 'null' ? 'null' : "'".($this->$var_name)."'";
			
				$campos[] = "`{$var_name}` = {$value}"  ;
			}
		}

		$sql = $sql . join(',',$campos) . ' WHERE id = ' . $this->id  ;
		// printr($sql) ;
		// die();
		return (query($sql));
	}

	public function exclui(){

        /*
        foreach ( $this->get_related_objects() as $obj ){
			$obj->exclui();
		}
        */
		
		$sql = 'DELETE FROM '.$this->get_table_name().' WHERE id = '.$this->id;
		return query($sql);
	}

	public function refresh(){
		if($this->id){
			$this->get_by_id($this->id);
		}
	}

	public function get_next_id(){
		$object_min_max = fetch(query('select min(id) minimo, max(id) maximo from ' . get_class($this))) ;
		$object_next = fetch(query('select id from '. get_class($this) .' where id > '. $this->id  .' limit 1'));
		if ( @$object_next->id ){
			return $object_next->id ;
		}
		else {
			return $object_min_max->minimo ;
		}
	}

	public function get_last_id(){
		$object_min_max = fetch(query('select min(id) minimo, max(id) maximo from ' . get_class($this))) ;
		$object_last = fetch(query('select id from '. get_class($this) .' where id < '. $this->id  .' order by id desc limit 1'));
		if ( @$object_last->id ){
			return $object_last->id ;
		}
		else {
			return $object_min_max->maximo ;
		}
	}

	public function array_simples() {
		$return = array();
		$query = query($sql="select * from ".$this->get_table_name(). " order by descricao");
		while($fetch=fetch($query)){
			if(property_exists(get_class($this),'titulo')){
				$return[$fetch->id] = $fetch->titulo;
			}
			else {
				$return[$fetch->id] = $fetch->descricao;
			}
		}

		return $return;
	}

	public function valida_unico($nome_campo){

		$rows = rows(query($SQL="select id from {$this->get_table_name()} where 1=1".($this->id>0?" and id != {$this->id}":"")." and {$nome_campo} = '{$this->$nome_campo}'"));

		if($rows==0){
			return true;
		}

		return false;
	}
	
	public function ultima_linha(){
		$tabela = get_class($this);
		$query = query("SELECT * FROM {$tabela} ORDER BY id DESC LIMIT 0,1");
		return $query;
	}

}

?>