<?php

class cmsitem extends base {

    var $id;
    var $st_ativo;
    var $autor_id;
    var $cmsitem_id;
    var $ordem;

    var $tipo;

    var $titulo;
    var $chave;
    var $conteudo;

    var $img1;
    var $img2;
    var $img3;
    var $img4;
    var $img5;
    var $img6;

    var $titulo1;
    var $titulo2;
    var $titulo3;
    var $titulo4;
    

    var $custom1;
    var $custom2;
    var $custom3;
    var $custom4;
    var $custom5;
    var $custom6;
    var $custom7;
    var $custom8;
    var $custom9;
    var $custom10;

    var $data_cadastro;
    var $data_alteracao;
    var $data_publicacao;
    var $data_expiracao;
    //var $data_depoimento;

    private $_autor;

    public function salva(){
        $ret = null;

        // $this->chave = butil::stringAsTag($this->titulo);
        // $this->chave = $this->titulo;
        // $this->chave = butil::convert_accented_characters($this->titulo);

        $this->chave = substr(butil::url_title_2($this->titulo),0,190);
        return parent::salva();
    }

    protected function salvaImagem($key, $label){

        $file = @$_FILES['file_'.$key];

        $image_name = butil::url_title_2($label.' '.$this->chave.' '.$this->id).'.png';

       // $folder = PATH_RAIZ.DIRECTORY_SEPARATOR."upload".DIRECTORY_SEPARATOR."{$this->tipo}";
        $folder = "upload/{$this->tipo}";                 
       
        if(!file_exists($folder)){
            mkdir($folder,0777,true);
        }

        $path_fisico = $folder."/".$image_name;
      
        if($file['name']!='') {
            @unlink($path_fisico);
            $si = new SimpleImage($file['tmp_name']);
            $si->save($path_fisico,100);
            query($sql="UPDATE cmsitem SET {$key} = '{$path_fisico}' WHERE id = {$this->id}");
            $this->$key = $path_fisico;
        }

        $url = @$_REQUEST['file_'.$key];
        if($url != '' && @parse_url($url)){
            @unlink($path_fisico);
            $contents = @file_get_contents($url);
            if(!$contents){
                die($url);
            }
            file_put_contents($path_fisico, $contents);
            query($sql="UPDATE cmsitem SET {$key} = '{$path_fisico}' WHERE id = {$this->id}");
            $this->$key = $path_fisico;
        }
    }

    public function excluiImagem($key){

        $img = $this->$key;
      
        if($img != ''){
            @unlink('upload/'.$img);
            query($sql="UPDATE cmsitem SET {$key} = NULL WHERE id = {$this->id}");
        }

        $this->$key = '';
    }

    public function get_table_name(){
        return 'cmsitem';
    }

    public function validaDados(){
        if(!$this->titulo){
            throw new Exception('Título inválido');
        }
    }

    public function getDataPublicacaoFormatado(){
        return formata_data_br(substr($this->data_publicacao,0,10));
    }

    public function getDataHoraPublicacaoFormatado(){
        list($data, $hora) = explode(' ',butil::formata_datahora_br($this->data_publicacao));
        list($dia, $mes, $ano) = explode('/', $data);
        list($hora, $minuto, $segundo) = explode(':', $hora);
        // $ano = substr($ano,2,2);
        return "{$dia}/{$mes}/{$ano} {$hora}:{$minuto}";
    }

    public function getHoraPublicacaoFormatado(){
        list($data, $hora) = explode(' ',butil::formata_datahora_br($this->data_publicacao));
        list($dia, $mes, $ano) = explode('/', $data);
        list($hora, $minuto, $segundo) = explode(':', $hora);
        // $ano = substr($ano,2,2);
        return "{$hora}:{$minuto}";
    }

    public function getDataPublicacaoExtenso(){
        return formata_data_extenso(formata_data_br($this->data_publicacao));
    }

    public function getDiaPublicacao(){
        list($yyyy, $mm, $dd) = explode('-', $this->data_publicacao);
        return substr($dd,0,2);
    }

    public function getMesPublicacao(){
        list($yyyy, $mm, $dd) = explode('-', $this->data_publicacao);
        return $mm;
    }

    public function getAnoPublicacao(){
        list($yyyy, $mm, $dd) = explode('-', $this->data_publicacao);
        return $yyyy;
    }

    public function getMesPublicacaoAbreviado(){
        list($yyyy, $mm, $dd) = explode('-', $this->data_publicacao);
        $meses = butil::arrayMeses();
        $mm = $meses[intval($mm)-1];
        return strtoupper(substr($mm,0,3));
    }

    public function getDataHoraFormatado(){
        list($data, $hora) = explode(' ',butil::formata_datahora_br($this->data_publicacao));
        list($dia, $mes, $ano) = explode('/', $data);
        list($hora, $minuto, $segundo) = explode(':', $hora);
        $ano = substr($ano,2,2);
        return "{$dia}/{$mes}/{$ano} - {$hora}h{$minuto}";
    }

     /** Departamentos */
     static function departamentos($cmsitem_id=0){
		$sql = "SELECT DISTINCT departamento.id,departamento.nome FROM departamento";
		if($cmsitem_id>0){
			$sql = "SELECT DISTINCT departamento.id,departamento.nome,departamento.chave
				,CASE WHEN cmsitem.id > 0 THEN 'S' ELSE 'N' END checked
				FROM departamento 
				LEFT JOIN cmsitemdepartamento ON(cmsitemdepartamento.departamento_id = departamento.id) 
				LEFT JOIN cmsitem ON(cmsitemdepartamento.cmsitem_id = cmsitem.id AND cmsitem.id = {$cmsitem_id})";
		}

		$sql .= " ORDER BY departamento.id";

		$query = query($sql);

		$ret = "<div id='_departamentos'>";
		while($fetch=fetch($query)){
			$checked = ( (isset($fetch->checked) && $fetch->checked=='S') ? "checked" :"" );
			$ret .= tag("label style='display:block;padding:3px;'","<input type='checkbox' {$checked} name='departamento[{$fetch->id}]' value='{$fetch->nome}' /> {$fetch->nome}");
		}
		$ret .= "</div>";

		$ret .= tag("table title='Adicionar Novo Departamento'", 
					tag("tr", tag("td", inputSimples("departamento_novo","",""))
						.tag("td", tag("span class='btn btn-primary' onclick='javascript :addDepartamento(document.getElementById(\"departamento_novo\"));'","<i class='fa fa-plus'> </i>"))
					)
				);

		$ret .= tag("script","
			function addDepartamento(elem){
				$.ajax({
					url : '".PATH_SITE."ajax.php/addcmsdepartamento/'
					,dataType : 'json'
					,data : {'dep':elem.value}
					,success : function(out){
						if(!out.status) alert(out.msg);
						else $('#_departamentos').append(out.html);
					}
				});
			}
		");
		
		return tag("div class='well'",tag("h2","Departamentos").$ret);
	}

	public function salvaDepartamentos(){
		if(request("departamento")){
			$atualizados = array();
            
            $departamentos_key = "";
            $departamentos = "";
			foreach( request("departamento") as $key=>$value ){

				$cmsitemdepartamento = new cmsitemdepartamento(array("cmsitem_id"=>$this->id,"departamento_id"=>$key));
				
				$cmsitemdepartamento->cmsitem_id = $this->id;
				$cmsitemdepartamento->departamento_id = $key;
				$cmsitemdepartamento->salva();

                $departamentos_key .= strtolower( stringAsTag($value) )." ";
                $departamentos .= $value." ";
				$atualizados[$cmsitemdepartamento->id] = $cmsitemdepartamento->id;
			}

			$sql="delete from cmsitemdepartamento where id not in (".join(",",$atualizados).") AND cmsitem_id = {$this->id}";
			query($sql);
            unset($atualizados);

            $this->custom4 = $departamentos;
            $this->custom5 = $departamentos_key;
            $this->atualiza();
		}else{
			query("delete from cmsitemdepartamento where cmsitem_id = {$this->id}");
		}
	}

	public function getDepartamentos(){
		$query = query("SELECT blogdepartamento.id,blogdepartamento.nome,blogdepartamento.chave FROM blogdepartamento INNER JOIN postblogdepartamento ON (postblogdepartamento.blogdepartamento_id = blogdepartamento.id AND postblogdepartamento.post_id = {$this->id})");
		$ret = array();
		while($fetch=fetch($query)) $ret[$fetch->id] = $fetch;
		return $ret;
    }
    /** fim departamentos */

}