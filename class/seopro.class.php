<?php
    if(!defined("PATH_IMG"))define("PATH_IMG",PATH_IMG_ABS );
    class seopro extends base {
        var $id;
        var $st_ativo;
        var $modelo;
        var $modelo_id;
        var $description;
        var $keywords;
        var $url;
        var $url_tag;
        var $title;
        var $metodo;
        var $tag_nome;
        var $args;
        var $data_cadastro;
       
        var $face_title;
        var $face_description;
        var $face_img;
       
        var $twitter_title;
        var $twitter_description;
        var $twitter_img;
        
        public function getEdit($opts=array()){

            $opts["description"] = !isset($opts["description"]) ? true : $opts["description"];
            $opts["keywords"] = !isset($opts["keywords"]) ? true : $opts["keywords"];
            $opts["url_tag"] = !isset($opts["url_tag"]) ? true : $opts["url_tag"];
            $opts["title"] = !isset($opts["title"]) ? true : $opts["title"];

            $edit = "<div class='well'>";
            $edit .= tag("h2","SEO");
            $edit .= (isset($opts["description"])&&$opts["description"]) ? textArea( "seopro[description]", $this->description, "Description") : "" ;
            $edit .= (isset($opts["keywords"])&&$opts["keywords"]) ? inputSimples("seopro[keywords]",$this->keywords,"Keywords",160,160) : "" ;
            if( isset($opts["url_tag"]) && $opts["url_tag"] ){
                $edit .=  pLabel("seopro[url_tag]","URL") . tag("table",
                                tag("tr",
                                    tag("td",tag("span style='font-size:14px;'",config::get("URL")))
                                    .tag("td style='width:450px;'",'<input type="text" name="seopro[url_tag]" value="'.$this->url_tag.'" class="form-control">')
                                )
                            );

                $edit .= tag("p style='color:red;padding:10px;font-size:12px;border:1px solid #dedede;margin:3px;'","O valor para sua URL deve seguir o seguinte formato : "
                    ."<br> - Não conter caracteres especiais (ex.: ã,á,%,@,#, etc ...); "
                    ."<br> - Caracteres devem estar em minúsculo (ex.: Forma erradao = /CANETA-PLÁSTICA ; Forma correta = /caneta-plastica);"
                    ."<br> - Em casos de nome composto separar por '-' ( ex.: caneta-plastica);"
                    ."Por medida de segurança o sistema irá tratar a url para manter o formato correto."
                );
            }
            
            $edit .=  (isset($opts["title"])&&$opts["title"]) ? inputSimples("seopro[title]",$this->title,"Title",160,160) : "" ;
            $edit .= "</div>";
            
            $edit .= "<div class='well'>";
            $edit .= tag("h2","Compartilhar Facebook");
            $edit .= inputSimples("seopro[face_title]", $this->face_title, "Title Facebook:");
            $edit .= textArea("seopro[face_description]", $this->face_description, "Facebook Description:");
            $edit .= inputFile("seopro_img_facebook", $this->face_img, "Facebook Image (1200x630px)");
            $edit .= "<br />";
            $_src = 'img/compartilhar/'.$this->face_img;
            if($this->face_img != '' && file_exists($_src)){
                $edit .= "<img src='".PATH_SITE."{$_src}' width='500'>";
            }
            //printr($_src);
            $edit .= "</div>";
            
            $edit .= "<div class='well'>";
            $edit .= tag("h2","Compartilhar Twitter");
            $edit .= inputSimples("seopro[twitter_title]", $this->twitter_title, "Title Twitter:");
            $edit .= textArea("seopro[twitter_description]", $this->twitter_description, "Twitter Description:");
            $edit .= inputFile("seopro_img_twitter", $this->twitter_img, "Twitter Image (1024x512px)");
            $edit .= "<br />";
            $_src = 'img/compartilhar/'.$this->twitter_img;
            if($this->face_img != '' && file_exists($_src)){
                $edit .= "<img src='".PATH_SITE."{$_src}' width='400'>";
            }
            $edit .= "</div>";
            
            return $edit;
        }  
        
        public static function validaTamanho(&$erros='', &$seopro,$facebook=true){
            //VERIFICA SE O DIRETORIO EXISTE E SE N EXISTIR CRIA
            if(!is_dir(PATH_IMG)) mkdir(PATH_IMG, 0777, true);
                
            if(!is_dir(PATH_IMG.'compartilhar/'))  mkdir(PATH_IMG.'compartilhar/', 0777, true);
            // ** //

            $_width  = "";
            $_height = "";

            //VERIFICA SE IMG DO FACE OU TWITTER//
            if($facebook){
                $_width  = 1200;
                $_height = 630;
                $tipo_rede = 'facebook';
                $img = $seopro->face_img;
                $varimg = 'face_img'; 
            }else{
                $_width  = 1024;
                $_height = 512;
                $tipo_rede = 'twitter';
                $img = $seopro->twitter_img;
                $varimg = 'twitter_img';
            }

            //pega o campo do request 
            $file_imagem = @$_FILES['seopro_img_'.$tipo_rede];
            //**//

            if($file_imagem){
                if($file_imagem['name']!='') {
                    if($file_imagem['name']!= '' && !isImagemJPG($file_imagem['name'])){
                        $erros .= tag('p', 'A imagem '.$file_imagem['name'].' deve ser .JPG');
                    }
                    
                    //RETIRAR IMG IGUAL
                    if($img != '' && file_exists("img/compartilhar/{$img}")){
                        if(!unlink("img/compartilhar/{$img}")){
                            $erros .= tag('p', 'A imagem já existia aconteceu algum erro ao excluir');
                        }
                    }
                    
                    list($width, $height) = getimagesize($file_imagem['tmp_name']);
                    
                    /* Verificar o tamanho */
                    if($width!=$_width||$height!=$_height){
                        $erros .= tag('p','A imagem ('.$file_imagem['name'].') deve ter '.$_width.'x'.$_height.'px');
                    }
                    
                    //Define novo nome da img 
                    $image_name = $seopro->modelo.'-'.$seopro->modelo_id.'-'.$tipo_rede.'-'.time().'.jpg' ;

                    // Salva imagem original
                    $path_original = "img/compartilhar/{$image_name}";
                    $si = new SimpleImage($file_imagem['tmp_name']);
                    $si->save($path_original, 100);
                    $seopro->$varimg =  $image_name;
                }
            }
        }

        public static function validaSalva($opts=array(),$modelo_config=array(),&$erros=""){
            
            $seopro = new seopro($opts);       
            $seopro->set_by_array(request("seopro"));

            $seopro->st_ativo  = "S";
        
            if(isset($opts["modelo"])) $seopro->modelo = $opts["modelo"];
            if(isset($opts["modelo_id"])) $seopro->modelo_id = $opts["modelo_id"];
            if(isset($opts["metodo"])) $seopro->metodo = $opts["metodo"];

            $seopro->metodo    = (isset($modelo_config["metodo"])?$modelo_config["metodo"]:"");
            $seopro->tag_nome  = (isset($modelo_config["tag_nome"])?$modelo_config["tag_nome"]:"");

            if($seopro->metodo == "index"){
                $seopro->url = config::get("URL").$seopro->metodo;
            }else{
                $seopro->url_tag =  strtolower((strClearCharSpc($seopro->url_tag)));
                if($seopro->url_tag!="")$seopro->url = config::get("URL").$seopro->url_tag;
            }

            $seopro->args = (isset($modelo_config["args"])?$modelo_config["args"]:"");

            if( $seopro->url!="" && !$seopro->valida_unico('url') ){
                $erros .= tag("p class='alert alert-error'","URL já existe.");
            }
            
            if($seopro->face_description != '') $seopro->face_description = preg_replace('/\s/',' ',$seopro->face_description);
            
            if($seopro->twitter_description != '') $seopro->twitter_description = preg_replace('/\s/',' ',$seopro->twitter_description);

            //FACE
            self::validaTamanho($erros, $seopro, true);
            //TWITTER
            self::validaTamanho($erros, $seopro, false);
            // printr($erros);
            // die();
            
            if($erros=="") $seopro->salva();
            else throw new Exception($erros);

            if(isset($_SESSION["SEO"]))unset($_SESSION["SEO"]);
            
            return $seopro;
        }

        public static function getLinkNovidades(){
            if(isset($_SESSION["SEO"]["novidades"]))return $_SESSION["SEO"]["novidades"];
            $opts = array();        
            $opts["modelo"]   = "seopro";
            $opts["metodo"]   = "brindes";
            $opts["args"]     = "Novidades";
            $seopro = new seopro($opts);
            if($seopro->url!="")return $_SESSION["SEO"]["novidades"]=$seopro->url;
            return $_SESSION["SEO"]["novidades"]=config::get("URL")."brindes/Novidades";
        }
        public static function getLinkPromocoes(){
            if(isset($_SESSION["SEO"]["promocoes"]))return $_SESSION["SEO"]["promocoes"];
            $opts = array();        
            $opts["modelo"]   = "seopro";
            $opts["metodo"]   = "brindes";
            $opts["args"]     = "Promocoes";
            $seopro = new seopro($opts);
            if($seopro->url!="")return $_SESSION["SEO"]["promocoes"]=$seopro->url;
            return $_SESSION["SEO"]["promocoes"]=config::get("URL")."brindes/Promocoes";
        }
        public static function getLinkSobre(){
            if(isset($_SESSION["SEO"]["sobre"]))return $_SESSION["SEO"]["sobre"];
            $opts = array();        
            $opts["modelo"]   = "seopro";
            $opts["metodo"]   = "sobre";
            $opts["args"]     = "";
            $seopro = new seopro($opts);
            if($seopro->url!="")return $_SESSION["SEO"]["sobre"]=$seopro->url;
            return $_SESSION["SEO"]["sobre"]=config::get("URL")."sobre";
        }
        public static function getLinkContato(){
            if(isset($_SESSION["SEO"]["contato"]))return $_SESSION["SEO"]["contato"];
            $opts = array();        
            $opts["modelo"]   = "seopro";
            $opts["metodo"]   = "contato";
            $opts["args"]     = "";
            $seopro = new seopro($opts);
            if($seopro->url!="")return $_SESSION["SEO"]["contato"]=$seopro->url;
            return $_SESSION["SEO"]["contato"]=config::get("URL")."contato";
        }
        public static function getLinkProdutos(){
            if(isset($_SESSION["SEO"]["brindes"]))return $_SESSION["SEO"]["brindes"];
            $opts = array();        
            $opts["modelo"]   = "seopro";
            $opts["metodo"]   = "brindes";
            $opts["args"]     = "";
            $seopro = new seopro($opts);
            if($seopro->url!="")return $_SESSION["SEO"]["brindes"]=$seopro->url;
            return $_SESSION["SEO"]["brindes"]=config::get("URL")."brindes";
        }
        public static function getLinkLogin(){
            if(isset($_SESSION["SEO"]["login"]))return $_SESSION["SEO"]["login"];
            $opts = array();        
            $opts["modelo"]   = "seopro";
            $opts["metodo"]   = "login";
            $opts["args"]     = "";
            $seopro = new seopro($opts);
            if($seopro->url!="")return $_SESSION["SEO"]["login"]=$seopro->url;
            return $_SESSION["SEO"]["login"]=config::get("URL")."login";
        }
        public static function getLinkCadastro(){
            if(isset($_SESSION["SEO"]["cadastro"]))return $_SESSION["SEO"]["cadastro"];
            $opts = array();        
            $opts["modelo"]   = "seopro";
            $opts["metodo"]   = "cadastro";
            $opts["args"]     = "";
            $seopro = new seopro($opts);
            if($seopro->url!="")return $_SESSION["SEO"]["cadastro"]=$seopro->url;
            return $_SESSION["SEO"]["cadastro"]=config::get("URL")."cadastro";
        }
    }
