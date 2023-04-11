<?php

class categoria extends base {

    var $id;
    var $categoria_id;
    var $st_ativo;
    var $st_lista_menu;
    var $st_fixo;
    var $referencia;
    var $nome;
    var $nome_es;
    var $nome_in;
    var $tag_nome;
    var $descricao;
    var $descricao_es;
    var $descricao_in;
    var $ordem;
    var $nivel;
    var $imagem_tema;
    var $imagem_tema_mobile;
    var $imagem_icone;
    var $data_cadastro;
    var $fator_id;
    var $tabelapreco;
    //var $st_tabelapreco;

    const WIDTH_BANNER_DESK = 850;
    const HEIGHT_BANNER_DESK = 260;
    
    const WIDTH_BANNER_MOBILE = 480;
    const HEIGHT_BANNER_MOBILE = 300;
    
    const WIDTH_ICONE = 103;
    const HEIGHT_ICONE = 103;

    const TIPO_BANNER = 'categoriabanner';
    const QTD_BANNER = 6;
    const PATH_IMAGEM_TEMA = 'img/categorias/tema/';

    const QTD_CATEGORIAS_PRINCIPAIS = 10;
    const QTD_CATEGORIAS_PRINCIPAIS_MOBILE = 50;


    public function validaDados(&$erro=array()){
        if($this->nome==''){
            $erro[] = 'Nome n&atilde;o pode estar vazio';
        }
        return sizeof($erro)==0;
    }
    public function getDescricaoHtml(){
        return nl2br($this->descricao);
    }
    public function getLink(){
        $slug = array();
        if($this->categoria_id){
            $trilha = $this->trilha();
            foreach($trilha as $t){
                $slug[] = $t->tag_nome;
            }
        }
        else {
            $slug[] = $this->tag_nome;
        }
        return site_url().INDEX."brindes/".join('/',$slug);
    }
    public function getNomeTag(){
        return stringAsTag($this->nome);
    }
    public function salva(){
        if(!$this->categoria_id) $this->categoria_id = NULL;
        // $this->tag_nome = strtolower(str_replace(' ','-',$this->nome));
        $this->tag_nome = stringAsTag("{$this->nome}");

        if(parent::salva()){

            if(!is_dir($dir='img/categorias/tema/'))mkdir($dir,0777,true);

            // $file_imagem_tema = (isset($_FILES['imagem_tema_'.$this->id])?$_FILES['imagem_tema_'.$this->id]:(isset($_FILES['imagem_tema_0'])?$_FILES['imagem_tema_0']:""));
			// $file_imagem_tema_mobile = (isset($_FILES['imagem_tema_mobile_'.$this->id])?$_FILES['imagem_tema_mobile_'.$this->id]:(isset($_FILES['imagem_tema_mobile_0'])?$_FILES['imagem_tema_mobile_0']:""));
            // $file_imagem_icone = (isset($_FILES['imagem_icone_'.$this->id])?$_FILES['imagem_icone_'.$this->id]:(isset($_FILES['imagem_icone_0'])?$_FILES['imagem_icone_0']:""));

            // if(isset($file_imagem_tema['tmp_name'])){
            if(file_tratamento( ($this->id?'imagem_tema_'.$this->id:"imagem_tema_0") , $msg, $file)){

                list($width, $height) = getimagesize($file['tmp_name']);
                if($width!=self::WIDTH_BANNER_DESK||$height!=self::HEIGHT_BANNER_DESK){
                    $_SESSION['erro'] = tag('p','A imagem ('.$file['name'].') deve ter '.self::WIDTH_BANNER_DESK.'x'.self::HEIGHT_BANNER_DESK.' pixels');
                    return true;
                }

                $image_name = stringAsTag($this->nome.' '.$this->id).'.jpg' ;
                
                foreach ( array('img/categorias/tema/') as $path ){
                    $path_fisico = "{$path}{$image_name}";
                    @unlink($path_fisico);
                    copy($file['tmp_name'], $path_fisico);
                    
                    query("UPDATE categoria SET imagem_tema = '{$image_name}' WHERE id = {$this->id} ");
                }
                
                $this->imagem_tema = $image_name;
            }
			
			// if(isset($file_imagem_tema_mobile['tmp_name'])){
            if(file_tratamento( ($this->id?'imagem_tema_mobile_'.$this->id:"imagem_tema_mobile_0") , $msg, $file)){

                list($width, $height) = getimagesize($file['tmp_name']);
                if($width!=self::WIDTH_BANNER_MOBILE||$height!=self::HEIGHT_BANNER_MOBILE){
                    $_SESSION['erro'] = tag('p','A imagem ('.$file['name'].') deve ter '.self::WIDTH_BANNER_MOBILE.'x'.self::HEIGHT_BANNER_MOBILE.' pixels');
                    return true;
                }

                $image_name = stringAsTag($this->nome.' '.$this->id).'-m.jpg' ;

                foreach ( array('img/categorias/tema/') as $path ){
                    $path_fisico = "{$path}{$image_name}";
                    @unlink($path_fisico);
                    copy($file['tmp_name'], $path_fisico);
                    query("UPDATE categoria SET imagem_tema_mobile = '{$image_name}' WHERE id = {$this->id} ");
                }

                $this->imagem_tema_mobile = $image_name;
                
            }
            
            // if(isset($file_imagem_icone['tmp_name'])){
                if(file_tratamento( ($this->id?'imagem_icone_'.$this->id:"imagem_icone_0") , $msg, $file)){
                    list($width, $height) = getimagesize($file['tmp_name']);
                    if($width!=self::WIDTH_ICONE||$height!=self::HEIGHT_ICONE){
                        $_SESSION['erro'] = tag('p','A imagem ('.$file['name'].') deve ter '.self::WIDTH_ICONE.'x'.self::HEIGHT_ICONE.' pixels');
                        return true;
                    }
                    
                    $image_name = stringAsTag($this->nome.' '.$this->id).'.jpg' ;
                    foreach ( array('img/categorias/icone/') as $path ){
                        $path_fisico = "{$path}{$image_name}";
                        @unlink($path_fisico);
                        copy($file['tmp_name'], $path_fisico);
                        query("UPDATE categoria SET imagem_icone = '{$image_name}' WHERE id = {$this->id} ");
                    }
                    $this->imagem_icone = $image_name;
                }         
                
            if(request("excluir_banner_tema_".$this->id)){
                if( file_exists( $path = 'img/categorias/tema/'.$this->imagem_tema ) ) unlink($path);
                query("UPDATE categoria SET imagem_tema = '' WHERE id = {$this->id}");
                $this->imagem_tema = "";
                unset($path);
            }
            if(request("excluir_banner_tema_mobile_".$this->id)){
                if( file_exists( $path = 'img/categorias/tema/'.$this->imagem_tema_mobile ) ) unlink($path);
                query("UPDATE categoria SET imagem_tema_mobile = '' WHERE id = {$this->id}");
                $this->imagem_tema_mobile = "";
                unset($path);
            }
            if(request("excluir_imagem_icone_".$this->id)){
                if( file_exists( $path = 'img/categorias/icone/'.$this->imagem_icone ) ) unlink($path);
                query("UPDATE categoria SET imagem_icone = '' WHERE id = {$this->id}");
                $this->imagem_icone = "";
                unset($path);
            }

            /** tabela de preço aplicação nos produtos */
            if($this->fator_id) query("UPDATE item SET item.codigo_2 = {$this->fator_id} WHERE item.id IN (SELECT itemcategoria.item_id FROM itemcategoria WHERE itemcategoria.categoria_id = {$this->id}) ");

            return true;
        }
        return false;
    }

    // Retorna trilha de categorias
    public function treeArvore(){

        $ret = array();
        $pesquisa = $this;

        do {

            $categoria = new categoria($pesquisa->categoria_id);

            if($categoria->id){
                $ret[] = $categoria;
            }
            $pesquisa = $categoria;
        }
        while ($pesquisa->id>0);

        return array_reverse($ret);
    }

    static function opcoes($categoria_id=0,&$ident=0,&$categorias=array()){

        $return = array();

        if(sizeof($categorias)==0){
            $categorias = results($sql = "SELECT * FROM categoria ORDER BY ordem");
        }

        $results = array();

        foreach($categorias as $categoria){
            if(intval($categoria->categoria_id) == $categoria_id){
                $results[$categoria->id] = $categoria;
            }
        }

        foreach($results as $fetch){
            $return[$fetch->id] = trim(str_repeat('--',$ident).' '.$fetch->nome);
            $ident ++;
            $outros = categoria::opcoes($fetch->id, $ident, $categorias);
            if(sizeof($outros)>0){
                $return += $outros;
            }
            $ident --;
        }

        return $return;
    } 
	
	static function opcoesPai($categoria_id=0,&$ident=0,&$categorias=array()){

        // $return = array();

        if(sizeof($categorias)==0){
            $categorias = results($sql = "SELECT DISTINCT * FROM categoria WHERE categoria_id = 0 OR categoria_id IS NULL ORDER BY nome");
        }

        $results = array();

        foreach($categorias as $categoria){
            if(intval($categoria->categoria_id) == $categoria_id){
                $results[$categoria->id] = $categoria->nome;
            }
        }

        // foreach($results as $fetch){
            // $return[$fetch->id] = trim(str_repeat('--',$ident).' '.$fetch->nome);
            // $ident ++;
            // $outros = categoria::opcoes($fetch->id, $ident, $categorias);
            // if(sizeof($outros)>0){
                // $return += $outros;
            // }
            // $ident --;
        // }

        return $results;
    }

    public function trilha($includeme=true){
        $ret = array();
        if($includeme){
            $ret[] = $this;
        }
        $tmp = new categoria($this->categoria_id);
        while($tmp->id){
            $ret[] = $tmp;
            $tmp = new categoria($tmp->categoria_id);
        }
        return array_reverse($ret);
    }

    public function exclui(){
        query($sql = "DELETE FROM itemcategoria WHERE categoria_id = {$this->id}");
        return parent::exclui();
    }

}
