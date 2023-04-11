<?php
	set_time_limit(99999999);
    require "global.php";



    $indice = (isset($_GET["indice"])?$_GET["indice"]:0);

    $url_produtos = "http://baseprodutos.ajungsolutions.com/service.php?_s=produtos&indice={$indice}";



    printr("Baixando produtos XBZ.");

    printr($url_produtos);

    echo("====<br>");

    try{

		if(isset($_GET["reset"])){

            if(isset($_SESSION["cores"]))unset($_SESSION["cores"]);

        }

    	$produtos =  json_decode( getUrl($url_produtos) );

		if(isset($produtos->status) && $produtos->status){

            foreach($produtos->produtos as $key=>$prod){
				addItem($prod);			
				printr("Baixou produto {$prod->referencia}");
			}
			
			gonext($indice);

            die("----- >>>");
		}
		
		die("-- FIM do DOWNLOAD -- ");
	
	}catch(Exception $e){

        printr($e->getMessage());

    }

	function getUrl($url){

		$s = curl_init(); 
		
		echo $url;

		curl_setopt($s,CURLOPT_URL,$url);  

		curl_setopt($s,CURLOPT_RETURNTRANSFER,true); 

		$result = curl_exec($s);

		printr("-- CURL --");

		curl_close($s);

		return $result;

    }
	
	function addItem($prod,$pai=""){

		$url_cores = "http://baseprodutos.ajungsolutions.com/service.php?_s=cores";

		if(!isset($_SESSION["cores"]))$_SESSION["cores"] = $cores = json_decode( getUrl($url_cores) );

        else $cores = $_SESSION["cores"];

		$item = new item(array("referencia"=>trim($prod->referencia)) );

		if(!$item->id){
			$item->load_by_fetch($prod);

			$item->id = 0;

			$item->qtd_minima = '1';

		}

		printr($item);
		printr($prod);
		die();

		$item->descricao_es = $item->descricao;

		$item->descricao = $prod->descricao;

		$item->st_ativo = $prod->st_ativo; 

		$item->referencia = trim($prod->referencia);

		$item->fornecedor_1 = $prod->referencia;

		$item->fornecedor_2 = $prod->fornecedor_2;

		$item->codigo_1 = $prod->id;  

		$item->imagem = $prod->imagem;
		$item->imagem_d1 = $prod->imagem_d1;
		$item->imagem_d2 = $prod->imagem_d2;
		$item->imagem_d3 = $prod->imagem_d3;
		$item->imagem_d4 = $prod->imagem_d4;
		$item->imagem_d5 = $prod->imagem_d5;
		$item->imagem_d6 = $prod->imagem_d6;
		$item->imagem_d7 = $prod->imagem_d7;
		$item->imagem_d8 = $prod->imagem_d8;
		$item->imagem_d9 = $prod->imagem_d9;
		$item->imagem_d10 = $prod->imagem_d10;
		$item->imagem_d11 = $prod->imagem_d11;
		$item->imagem_d12 = $prod->imagem_d12;
		$item->imagem_d13 = $prod->imagem_d13;
		$item->imagem_d14 = $prod->imagem_d14;
		$item->imagem_d15 = $prod->imagem_d15;
		$item->imagem_d16 = $prod->imagem_d16;
		$item->imagem_d17 = $prod->imagem_d17;
		$item->imagem_d18 = $prod->imagem_d18;
		$item->imagem_d19 = $prod->imagem_d19;
		$item->imagem_d20 = $prod->imagem_d20;

		$_cor = "";

		foreach($cores as $k=>$v){

			$v = (object) $v;

			if($v->id == $prod->cor_id){           

				$_cor=$v;

				break;

			}

		}

		if(is_object($_cor)){

			$cor = new cor( array('nome'=>$_cor->nome) );

			$_id = $cor->id;

			$cor->load_by_fetch($_cor);

			$cor->id = $_id;

			if(!$cor->salva()){

				printr("#1 Erro ao tentar salvar cor {$cor->nome}. <br> :: ".mysql_error());

			}                    

			$item->cor_id = $cor->id;                   

		}

		if(!(isset($pai) && is_object($pai) && isset($pai->id))) $pai = new item(array("codigo_1"=>$prod->itemsku_id));

		if($pai != ''){
			$item->itemsku_id  = $pai->id;
		}

		if(!$item->salva()){

			throw new Exception("#2 Erro ao tentar salvar novo item {$item->referencia}. <br> :: ".mysql_error());

		}

		if(isset($prod->imagens) )	downloadImagem($prod->imagens,'',$item,$pai);

		categorizarItem($item,$prod);

		variacoesItem($item,$prod);

	}

	function downloadImagem($imagens,$cor=false,$item='',$pai=''){
		
		$path = "img/produtos/";
		
		$path_asia = "C:/xampp/htdocs/crazyideas/img/img_spot/";
		
		if($item->itemsku_id != ''){
			$cor = new cor (intval($item->cor_id));
			$namecor = $cor->nome;
		}else{
			$namecor = '';
		}
		
		if($cor)$path = "img/cores/";
		
		$i = 0;
		
		foreach($imagens as $key=>$value){

            $arr = explode("/",$value);
            $image_name = $arr[sizeof($arr)-1];
			printr("# --> {$image_name}");
			$cor = new cor(intval($item->cor_id));
			if($cor->id){
				$corname = '-'.$cor->nome;
			}else{
				$corname = '';
			}
			
			$asia_name = $item->nome.$corname.'-'.$item->referencia.'-'.$i.substr(trim($image_name),-4,4);
			printr("----antes replace".$asia_name);
			$asia_name = str_replace(array("à","á","a","ã"),'a',$asia_name);
			$asia_name = str_replace(array("Á","À","A","Ã"),'A',$asia_name);
			$asia_name = str_replace(array("É","È","E"),'E',$asia_name);
			$asia_name = str_replace(array("é","è","e"),'e',$asia_name);
			$asia_name = str_replace(array("í","ì","i"),'i',$asia_name);
			$asia_name = str_replace(array("Í","Ì","I"),'I',$asia_name);
			$asia_name = str_replace(array('ó', 'ò', 'õ', 'ô'),'o',$asia_name);
			$asia_name = str_replace(array('Ó', 'Ò', 'Õ', 'Ô'),'O',$asia_name);
			$asia_name = str_replace(array('ú', 'ù', 'û'),'u',$asia_name);
			$asia_name = str_replace(array('Ú', 'Ù', 'Û'),'U',$asia_name);
			$asia_name = str_replace(array('/'),'',$asia_name);
			$asia_name = str_replace(array(' '),'-',$asia_name);
			$asia_name = str_replace(array('ç'),'c',$asia_name);
			$asia_name = str_replace(array('Ç'),'C',$asia_name);
			$asia_name = str_replace(array(';'),'',$asia_name);
			printr('NOME DO PRODUTO ------>'.$asia_name);
			$po = "{$path}{$image_name}";
			$po2 = "{$path_asia}{$asia_name}";

			$i ++;
            if( file_exists($po) )continue;
			
			printr("IMG");
			printr($value);
			$r = $value;
			
			$img = file_get_contents(trim($value));
			
            if(!$img){
                printr( "({$key})Nao baixou {$value}".PHP_EOL."<br>");
                die($key);
                continue;
            }
			
			if( !file_put_contents($po,$img ) ){
				
                printr( "Falha ao copiar {$value}".PHP_EOL."<br>");
                die($key ." -- ". $po2 );
                continue;
			}
		}
    }
	
	function categorizarItem($item,$prod){

		if(isset($prod->categorias)){

			foreach($prod->categorias as $key=>$value){

				printr($value->tag_nome);

				$categoria = new categoria(array("tag_nome"=>$value->tag_nome));

				if(!$categoria->id){

					$categoria->load_by_fetch($value);

					$categoria->id = 0;

					if(!$value->categoria_id){

						$categoria->categoria_id = null;

					}

					if(!$categoria->salva()){

						printr(mysql_error());

						die("# ERRO categoria salva.");

					}

				}

				$itemcategoria = new itemcategoria( array("categoria_id"=>$categoria->id,"item_id"=>$item->id) );

				$itemcategoria->categoria_id = $categoria->id;

				$itemcategoria->item_id = $item->id;

				if(!$itemcategoria->salva()){

					printr(mysql_error());

					die("# ERRO itemcategoria salva.");

				}

			}

		}

	}
	
	function variacoesItem($item,$prod){

		if(isset($prod->variacoes)){

			foreach($prod->variacoes as $k=>$v){

				addItem($v,$item);

			}

		}

	}

	function gonext($indice){

        $indice++;

		print '<meta http-equiv="refresh" content=".5; url=download_base.php?indice='.$indice.'">'; 

    }

