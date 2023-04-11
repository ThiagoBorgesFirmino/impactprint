<?php

class modulo_sobreempresa extends modulo_admin {

    public $arquivo = 'sobreempresa';
	
	/* habilitar informações para a página da empresa */
	const TEXTO_EMPRESA = false;
	const BANNER_EMPRESA = true;
	const BANNER_MOBILE_EMPRESA = true;

    // Pesquisa
    public function pesquisa(){

        if(request('popup')){
            $t = new TemplateAdminPopup('admin/tpl.admin-cadastro-generico.html');
        }
        else {
            $t = new TemplateAdmin('admin/modulos/'.$this->arquivo.'/tpl.'.$this->arquivo.'-admin.html');
        }

        $t->h1 = h1($this->modulo->nome);		

		$sobreempresa = new cmsitem(["tipo"=>"sobreempresa_principal" ]);

		/* Imagem banner */
		$width  = 1140;
		$height = 440;

		/* Imagem banner mobile*/
		$mobile_width=358;
		$mobile_height=240;
		
		/* Imagens destaque */
		$destaque_width= 358;
		$destaque_height= 240;
		
		/* Imagem destaque maior */
		$destaque_meio_width=358;
		$destaque_meio_height=240;

		if(request('action')=='salvar'){
			
			if(request("sobreempresa"))$sobreempresa->set_by_array(request("sobreempresa"));
			
			$upload_path = "upload/sobreempresa";
			
			if(!is_dir($upload_path)){
				mkdir($upload_path,0777,true);
			}
			
			$erro   = "";				
			
			if(file_tratamento("banner_sobreempresa", $msg, $file)){
				if(!isImagemJPG($file["name"])){
					$erro .= tag("p","A imagem precisa ser JPG");
				}else{
					list($W,$H) = getimagesize($file['tmp_name']);
					if($W!=$width || $H!=$height){
						$erro .= tag("p","A imagem precisa ter tamanho igual a {$width}x{$height}px");
					}else{
						$filename = "sobreempresa_".$file['name'];
						if(!move_uploaded_file($file["tmp_name"],$upload_path."/{$filename}")){
							$erro .= tag("p","Ocorreu um ao tentar subir a imagem.");
						}
						$sobreempresa->img1 = $filename;
					}
				}
			}

			if(file_tratamento("banner_esq_sup", $msg, $file)){
				
				if(!isImagemJPG($file["name"])){
					$erro .= tag("p","A imagem precisa ser JPG");
				}else{
					
					list($W,$H) = getimagesize($file['tmp_name']);
					if($W!=$destaque_width || $H!=$destaque_height){
						$erro .= tag("p","A imagem precisa ter tamanho igual a {$destaque_width}x{$destaque_height}px");
					}else{
						
						$filename = "sobreempresa_".$file['name'];
						if(!move_uploaded_file($file["tmp_name"],$upload_path."/{$filename}")){
							$erro .= tag("p","Ocorreu um ao tentar subir a imagem.");
						}
						
						$sobreempresa->img2 = $filename;
					}
				}
			}

			if(file_tratamento("banner_esq_inf", $msg, $file)){
				if(!isImagemJPG($file["name"])){
					$erro .= tag("p","A imagem precisa ser JPG");
				}else{
					list($W,$H) = getimagesize($file['tmp_name']);
					if($W!=$destaque_width || $H!=$destaque_height){
						$erro .= tag("p","A imagem precisa ter tamanho igual a {$destaque_width}x{$destaque_height}px");
					}else{
						$filename = "sobreempresa_".$file['name'];
						if(!move_uploaded_file($file["tmp_name"],$upload_path."/{$filename}")){
							$erro .= tag("p","Ocorreu um ao tentar subir a imagem.");
						}
						$sobreempresa->img3 = $filename;
					}
				}
			}

			if(file_tratamento("banner_direita_sup", $msg, $file)){
				if(!isImagemJPG($file["name"])){
					$erro .= tag("p","A imagem precisa ser JPG");
				}else{
					list($W,$H) = getimagesize($file['tmp_name']);
					if($W!=$destaque_width || $H!=$destaque_height){
						$erro .= tag("p","A imagem precisa ter tamanho igual a {$destaque_width}x{$destaque_height}px");
					}else{
						$filename = "sobreempresa_".$file['name'];
						if(!move_uploaded_file($file["tmp_name"],$upload_path."/{$filename}")){
							$erro .= tag("p","Ocorreu um ao tentar subir a imagem.");
						}
						$sobreempresa->img5 = $filename;
					}
				}
			}

			if(file_tratamento("banner_meio", $msg, $file)){
				if(!isImagemJPG($file["name"])){
					$erro .= tag("p","A imagem precisa ser JPG");
				}else{
					list($W,$H) = getimagesize($file['tmp_name']);
					if($W!=$destaque_meio_width || $H!=$destaque_meio_height){
						$erro .= tag("p","A imagem precisa ter tamanho igual a {$destaque_meio_width}x{$destaque_meio_height}px");
					}else{
						$filename = "sobreempresa_".$file['name'];
						if(!move_uploaded_file($file["tmp_name"],$upload_path."/{$filename}")){
							$erro .= tag("p","Ocorreu um ao tentar subir a imagem.");
						}
						$sobreempresa->img4 = $filename;
					}
				}
			}

			

			$sobreempresa->tipo = "sobreempresa_principal";
			//printr($sobreempresa);die();
			if(!$sobreempresa->salva()){
				//printr(mysql_error());
				$erro .= tag("p","Houve um erro tentar salvar os dados.");
			}
			
			if($sobreempresa->id){
			
				/* Novo topico */
				$topic = request("novo_topico");
				if(
					(isset($topic["conteudo"])&&$topic["conteudo"]!="")
				){
					$novo_topico = new cmsitem();
					$novo_topico->set_by_array($topic);	
					$novo_topico->cmsitem_id = $sobreempresa->id;					

					if(!$novo_topico->salva()){
						printr(mysql_error());
						$erro .= tag("p","Houve um erro tentar salvar os dados (<strong>{$novo_topico->titulo}</strong>).");
					}					
				}
								
				/* update */
				$_topicos  = request("topicos");
				foreach((is_array($_topicos)?$_topicos:[]) as $key=>$value){
					$topico = new cmsitem($key);
					$topico->set_by_array($value);			
					$topico->cmsitem_id = $sobreempresa->id;					
				
							
					if(!$topico->salva()){
						$erro .= tag("p","Houve um erro tentar salvar os dados (<strong>{$topico->titulo}</strong>).");
					}
						
				}				
			}
			
			
			$excluir = is_array(request("excluir"))?request("excluir"):[];
			
			if(sizeof($excluir)>0)query("DELETE FROM cmsitem WHERE id IN(".join(",",$excluir).")");
			
			
			if($erro!="")$_SESSION["erro"] = $erro;
			$_SESSION["sucesso"]=tag("p","Dados salvos com sucesso!");
		}
		
		$query = query($sql="SELECT * FROM cmsitem WHERE cmsitem_id = ".(isset($sobreempresa->id)?$sobreempresa->id:0)." AND tipo = 'Topico' ORDER BY ordem DESC");
		$qtd_topico = 0;
		while($fetch=fetch($query)){
			$t->topico = $fetch;
			//if($fetch->img1!="")$t->parseBlock("BLOCK_IMG_TOPICO",true);
			$t->parseBlock("BLOCK_TOPICOS",true);
			$qtd_topico++;
		}
		
		$t->sobreempresa = $sobreempresa;
		// $t->sobreempresa_especial = $sobreempresa_especial;
		// $t->sobreempresa_rodape = $sobreempresa_rodape;
		//$t->linkyoutube = $linkyoutube;
		if($sobreempresa->img1!="")$t->parseBlock("BLOCK_IMG_EMPRESA");
		if($sobreempresa->img2!="")$t->parseBlock("BLOCK_IMG_EMPRESA1");
		if($sobreempresa->img3!="")$t->parseBlock("BLOCK_IMG_EMPRESA2");
		if($sobreempresa->img4!="")$t->parseBlock("BLOCK_IMG_EMPRESA3");
		if($sobreempresa->img5!="")$t->parseBlock("BLOCK_IMG_EMPRESA4");
		if($sobreempresa->img6!="")$t->parseBlock("BLOCK_IMG_EMPRESA5");
		//if($sobreempresa->img2!="")$t->parseBlock("BLOCK_IMG_VIDEO");
		$t->tamanho_banner = (object)["width"=>$width,"height"=>$height];
		$t->tamanho_banner_mobile = (object)["width"=>$mobile_width,"height"=>$mobile_height];
		$t->tamanho_destaque = (object)["width"=>$destaque_width,"height"=>$destaque_height];
		$t->tamanho_destaque_m = (object)["width"=>$destaque_meio_width,"height"=>$destaque_meio_height];

		for($i = 1; $i < 5; $i++){
			$_custom = 'custom'.$i;
			$t->$_custom = textAreaCount("sobreempresa[{$_custom}]", $sobreempresa->$_custom, '', 35, 5, 310);
		}

		// //$t->imagem_video_tamanho = (object)["width"=>$img_video_width,"height"=>$img_video_height];
		// $t->tamanho_banner_topico = (object)["width"=>$topic_width,"height"=>$topic_height];
		
		$t->ordem_topic_novo = $qtd_topico;
		
		// if(self::TEXTO_EMPRESA)$t->parseBlock("BLOCK_CAD_EMPRESA_TEXTO");
		$t->parseBlock("BLOCK_CAD_EMPRESA_TEXTO");
		if(self::BANNER_EMPRESA)$t->parseBlock("BLOCK_CAD_EMPRESA_BANNER");
		if(self::BANNER_MOBILE_EMPRESA)$t->parseBlock("BLOCK_CAD_EMPRESA_BANNER_MOBILE");

		$this->adm_instance->montaMenuSimples($t);
        $this->adm_instance->montaMenu($t);
        $this->adm_instance->show($t);
    }

    public function editar($t){

        // Se for pop-up, carrega template de pop-up
        if(request('pop')){
            $t = new TemplateAdminPop('admin/tpl.admin-cadastro-generico.html');
        }

        $t->h1 = h1($this->modulo->nome);

        $sobreempresa = new pagina(intval(request('id')));

        if(substr(request('action'),0,6)=='salvar'){
			// your request
        }

        if(request('action')=='sair'){
            $this->afterSave('sair','sobreempresa',$sobreempresa);
        }

        $t->parseBlock('BLOCK_TOOLBAR');

        $edicao = '';
		
        $t->edicao = $edicao;
		
        $this->adm_instance->show($t);
    }

    static function widget(&$obj=""){

        $t = new Template('admin/modulos/sobreempresa/tpl.sobreempresa-site.html') ;
       
        $t->path = PATH_SITE;
		
		$sobreempresa = new cmsitem(["tipo"=>"sobreempresa_principal"]);
		
        $opts = array('order_by'=>'ordem','where'=>['cmsitem_id'=>$sobreempresa->id]);
        $itens = get_cmsitem('',$opts);

		$t->sobreempresa = $sobreempresa;
		
        $i = 0;
		$tem_topico = false;
        foreach($itens as $item){
            $t->sobreempresa_topico = $item;
			if(($i % 2) == 0){
				
            	$t->parseBlock('BLOCK_TOPICOS',true);
			}else{
				
				$t->parseBlock('BLOCK_TOPICOS_2',true);
			}
			$t->parseBlock('BLOCK_TESTE_MASTER', true);
            $i ++;
			$tem_topico = true;
        }
		
		if($tem_topico)$t->parseBlock("BLOCK_TOPICOS_CONTENT");
		if(self::BANNER_EMPRESA)$t->parseBlock("BLOCK_EMPRESA_BANNER");
		if(self::BANNER_MOBILE_EMPRESA)$t->parseBlock("BLOCK_EMPRESA_BANNER_MOBILE");
		if(self::TEXTO_EMPRESA)$t->parseBlock("BLOCK_EMPRESA_TEXTO");

		
		$obj = $sobreempresa;
        return $t->getContent();

    }

}