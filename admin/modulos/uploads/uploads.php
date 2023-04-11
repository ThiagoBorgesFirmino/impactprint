<?php
    class modulo_uploads extends modulo_admin{
        public $arquivo = 'uploads';

        public function pesquisa(){
            if(request('popup')) $t = new TemplateAdminPopup('admin/tpl.admin-cadastro-generico.html');
            else $t = new TemplateAdmin('admin/tpl.admin-cadastro-generico.html');

            if(request("action")=="salvar"){
                if(file_tratamento("arquivo_up",$msg,$file)){
                    $ext = explode(".",$file["name"]);
                    if(strtolower($ext[sizeof($ext)-1])!="csv"){
                        $_SESSION["erro"] = tag("p","O arquivo precisa ser .csv");
                    }else{
                        try{
                            $this->upReferencias($file);
                            $_SESSION["sucesso"] = tag("p","Dados importados com sucesso.");
                        }
                        catch(Exception $e){
                            $_SESSION["erro"] = $e->getMessage();
                        }
                    }
                }else{
                    if($msg!="")$_SESSION["erro"] = $msg;
                }
            }
    
            $t->h1 = h1($this->modulo->nome);

            $edicao = '';    

            $edicao .= tag("div class='well'",
                tag("h2","Atualizar Códigos  pelo Código do Fornecedor, Categoriza e Atualiza status.")
                .tag("p",tag("a class='btn' target='_blank' href='".PATH_SITE."admin/modulos/uploads/exemplo.csv'","exemplo.csv"))
                .tag("label for='arquivo_up' class='btn btn-primary'",tag("span id='_arquivo_bt'","Arquivo CSV")."&nbsp;&nbsp;&nbsp;&nbsp;<i id='_cog' style='font-size:20px;display:none;' class='fa fa-cog fa-spin fa-3x fa-fw' aria-hidden='true'></i>")
                .tag("p style='display:none;'","Mensagem ...")
                .tag("span style='display:none;'","<input type='file' name='arquivo_up' id='arquivo_up' onchange='javascript: fileChange(this);' />")
                .tag("script","
                    function fileChange(obj){
                        var nome = 'Nenhum arquivo selecionado.';
                        if(obj.files.length > 0) nome = obj.files[0].name;
                        // $('#corporativo_anexo').value(obj.files[0].name);
                        $('#_arquivo_bt').html(nome);
                        // $('#_cog').show();
                    }
                ")
            );

            $t->edicao = $edicao;
    
            $this->adm_instance->montaMenuSimples($t);
            $this->adm_instance->montaMenu($t);
            $this->adm_instance->show($t);
        }
        
        public function editar($t){}

        public function upReferencias($file){
            $linhas = file($file["tmp_name"]);
            for($i=0; $i<sizeof($linhas); $i++){

                if($i>0){
                    $arr = list($codigo_fornecedor,$referencia,$categoria,$status) = explode(';', $linhas[$i]);                   

                    if($codigo_fornecedor!=''){
                        $item = new item(array('fornecedor_1'=>$codigo_fornecedor));	
                        
                        if($item->id){
                        
                            if($referencia!="") $item->referencia 		  = $this->converte($referencia);
                            if($status!="" && in_array($status,array("S","N"))) $item->st_ativo = $status;
                            
                            if(!$item->atualiza()){                           
                                throw new Exception("Salvando item: ".mysql_error());                         
                            }

                            $cats = explode(">>",$categoria);
                            $cat_id = array("null",0);
                            foreach($cats as $value){
                                $tag_nome = stringAsTag($value=trim($value));
                                if($tag_nome!=""){
                                    $categoria = new categoria(array("tag_nome"=>$tag_nome,"categoria_id"=>$cat_id));
                                    if(!$categoria->id){
                                        $categoria->nome = $value;
                                        $categoria->st_ativo = "S";
                                        $categoria->categoria_id = (is_array($cat_id)?null:$cat_id);
                                        if(!$categoria->salva()){
                                            throw new Exception("Salvando categoria: ".mysql_error());         
                                        }
                                    }
                                    $itemcategoria = new itemcategoria( array("item_id"=>$item->id,"categoria_id"=>$categoria->id) );
                                    $itemcategoria->item_id = $item->id;
                                    $itemcategoria->categoria_id = $categoria->id;
                                    if(!$itemcategoria->salva()){
                                        throw new Exception("Salvando ItemCategoria: ".mysql_error());         
                                    }
                                    $cat_id = $categoria->id;
                                }
                            }
                        }
                    }

                }

            }
        }

        public function converte($str){
            $str = str_replace("{{barra}}","/",$str);
            return addslashes(trim(iconv('iso-8859-1','utf-8//translit', $str)));
        }
    }