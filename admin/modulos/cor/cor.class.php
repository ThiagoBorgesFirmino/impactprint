<?php

class cor extends base {

    var
        $id
    ,$corgrupo_id
    ,$st_ativo
    ,$st_fixo
    ,$referencia
    ,$nome
    ,$nome_es
    ,$nome_in
    ,$descricao
    ,$descricao_es
    ,$descricao_in
    ,$imagem
    ,$data_cadastro;

    private
        $objFile;

    public function salva(){
        $return = false;
        if(parent::salva()){
            if($this->objFile){
                $this->imagem = "{$this->nome}.jpg";
                $upload = new upload();
                $upload->setFile($this->objFile);
                $upload->diretorio = $_SERVER['DOCUMENT_ROOT'].PATH_SITE.PATH_IMG_COR;
                //$upload->grava($this->imagem);
                move_uploaded_file($upload->objFile['tmp_name'],"img/cores/{$this->imagem}");
                unset($upload);
                query("update ".$this->get_table_name()." set imagem = '{$this->imagem}' where id = {$this->id}");
            }
            $return = true;
        }
        modulo_cache::model('cor',$this->id,'clear');
        return $return;
    }

    public function setFile($file){
        if($file['tmp_name']!=''){
            $this->objFile = $file;
        }
    }

    static function opcoesByItem($item){
        $return = array();
        if($item->id>0){

            $query = query($sql="SELECT
									DISTINCT
									cor.*
								FROM
									cor
								INNER JOIN itemcor ON(
									cor.id = itemcor.cor_id
									AND itemcor.item_id = {$item->id}
								)
								WHERE
									1=1
								AND cor.st_ativo = 'S'
								ORDER BY
									cor.nome
								");

            while($fetch=fetch($query)){
                $fetch->nome_tag = stringAsTag($fetch->nome);
                $return[$fetch->id] = $fetch->nome;
            }
        }
        return $return;
    }

    public function validaDados(&$erro=array()){

        if(!is_set($this->referencia)){
            $erro[] = tag("p","Digito o código da cor {$this->nome}");
        }else{
            $cor = new cor(array('referencia'=>$this->referencia));
            if($cor->id){
                $erro[] = tag("p","Já existe uma cor com esse código {$this->referencia}. ({$cor->nome})");
            }
        }



        return true;
    }

    public function getCoresByItem(item $item){
        return $this->getCoresByItemId($item->id);
    }

    public function getCoresByItemId($item_id){

        $item = new item($item_id);

        $return = array();
        if($item_id>0){

            $query = query($sql="SELECT
									DISTINCT
									cor.*
									,item.id item_id
								FROM
									cor
								INNER JOIN item ON (
									cor.id = item.cor_id
								AND item.referencia = '{$item->referencia}'
								AND item.st_ativo = 'S'
								AND item.preco > 0
								AND item.imagem <> ''
								AND item.qtd_estoque > 0
								)
								WHERE
									1=1
								AND cor.st_ativo = 'S'
								ORDER BY
									cor.nome
								");

            while($fetch=fetch($query)){
                $fetch->nome_tag = stringAsTag($fetch->nome);
                $return[] =	$fetch;
            }

        }
        return $return;
    }

    // Exclui
    public function exclui(){
        $id = intval($this->id);
        $sql = "SELECT * FROM item WHERE cor_id = {$id}";
        if(rows(query($sql))>0){
            throw new Exception('Existem itens associados a este registro não é possível excluir');
        }
        /*
		$sql = "SELECT * FROM pedidoitem WHERE cor_id = {$id}";
		if(rows(query($sql))>0){
			throw new Exception('Existem pedidos associados a este registro não é possível excluir');
		}
        */
        return parent::exclui();
    }

    static function opcoes(){
        $return = array();
        $query = query("SELECT * FROM cor WHERE st_ativo = 'S' ORDER BY nome");
        while($fetch=fetch($query)){
            $return[$fetch->id] = $fetch->nome;
        }
        return $return;
    }
}
