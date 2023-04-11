<?php

class item extends base {

	var
		$id
		,$itemsku_id
		,$cor_id
        ,$itemclasse_id
		,$st_ativo
		,$st_destaque
		,$st_lancamento
		,$st_amamos
		,$splash_id
		,$referencia
		,$nome
		,$tag_nome
		,$nome_es
		,$nome_in
		,$chamada
		,$chamada_es
		,$chamada_in
		,$descricao
		,$descricao_es
		,$descricao_in
		,$seo_keywords
		,$preco
		,$preco_de
		,$peso
		,$garantia
		,$energia
		,$disponibilidade
		,$qtd_minima
		,$largura
		,$altura
		,$profundidade
		,$diametro
		,$imagem
        ,$imagem_d1
        ,$imagem_d2
        ,$imagem_d3
        ,$imagem_d4
        ,$imagem_d5
        ,$imagem_d6
        ,$imagem_d7
        ,$imagem_d8
        ,$imagem_d9
        ,$imagem_d10
        ,$imagem_d11
        ,$imagem_d12
        ,$imagem_d13
        ,$imagem_d14
        ,$imagem_d15
        ,$imagem_d16
        ,$imagem_d17
        ,$imagem_d18
        ,$imagem_d19
        ,$imagem_d20
		,$fornecedor_1
		,$fornecedor_2
		,$fornecedor_3
		,$codigo_1
		,$codigo_2
		,$codigo_3
		,$preco_1
		,$preco_2
		,$preco_3
		,$data_1
		,$data_2
		,$data_3
		,$data_cadastro

		,$infoadicional1
		,$infoadicional2
		,$infoadicional3

		,$infoadicional1_link
		,$infoadicional2_link
		,$infoadicional3_link

		,$infoadicional1_tooltipe
		,$infoadicional2_tooltipe
		,$infoadicional3_tooltipe

		,$tabela1
		,$tabela2
		,$tabela3

		,$tabela1_st
		,$tabela2_st
		,$tabela3_st

        ,$medida_gravacao
        ,$tamanho_total
        ,$chamada_cotacao
		;

	// public function __get($value){
		// list($function, $valor) = explode("__",$value);

		// if(method_exists($this,$function)){
			// return $this->$function($value);
		// }
	// }

	// public function formatado($value){
		// return money($this->$value);
	// }


	public function getLink(){
		$opts = array();
        $opts["modelo"]    = "item";
        $opts["modelo_id"] = $this->id;
        $seopro = new seopro($opts);
        if($seopro->id && $seopro->url!="")return $seopro->url;
        return site_url().INDEX."brinde/{$this->tag_nome}";
	}

    public function getLinkImagem(){
        return site_url().PATH_SITE."img/produtos/original/{$this->imagem}";
	}
	
	public function getLinkImagemProduto(){
        return site_url().PATH_SITE."img/produtos/{$this->imagem}";
    }


	public function getPrecoFormatado(){
		return money($this->preco);
	}

	public function getCalculaSubTotal($qtd){
		return '';
	}

	public function getPrecoDeFormatado(){
		return money($this->preco_de);
	}

	public function getUrlKeywords(){
		if($this->seo_keywords!=''){
			return stringAsTag($this->seo_keywords.' '.$this->nome.' '.$this->referencia);
		}
		return stringAsTag($this->nome.' '.$this->referencia);
	}

	public function getNomeAbreviado(){
		$abrev = TAM_NOME;
		if(strlen($this->nome)>$abrev){
			return substr($this->nome,0,$abrev).'...';
		}
		return $this->nome;
	}

	public function getChamadaAbreviado(){
		$abrev = 34;
		if(strlen($this->chamada)>$abrev){
			return substr($this->chamada,0,$abrev).'...';
		}
		return $this->chamada;
	}

	public function getDescricaoAbreviada(){
		$abrev = 108;
		if(strlen($this->descricao)>$abrev){
			return substr($this->descricao,0,$abrev).'...';
		}
		return $this->descricao;
	}

	public function getInformacoesTecnicasFrontend(){

		$caracteristicas = $this->getInformacoesTecnicasArray();

		if(sizeof($caracteristicas) == 0) return '';

		$html_caracteristicas = "";
		foreach($caracteristicas as $caracteristica){

			if(sizeof(explode(':',$caracteristica))==2){
				list($label,$conteudo) = explode(':',$caracteristica);
				$caracteristica = "<strong>{$label}:</strong> {$conteudo}";
			}
			elseif(sizeof(explode(':',$caracteristica))==3){
				list($label,$conteudo1,$conteudo2) = explode(':',$caracteristica);
				$caracteristica = "<strong>{$label}:</strong> {$conteudo1}: {$conteudo2}";
			}

			$html_caracteristicas .= "<li><p>{$caracteristica}</p></li>";
		}

		if(@$this->img_gravacao != ''){
			$path = "img/produtos/img_grav/{$this->img_gravacao}";
			$html = '
				<div class="container">
					<!-- <h4>Descrição técnica do produto - '.$this->referencia.'</h4> -->
					<hr>
					<br clear="all">
					<div class="row margin_0">
						<div class="col-md-8 col-sm-12 col-xs-12">
							<br clear="all">
							<img src="'.PATH_SITE.$path.'"
							alt="Detalhe"
							class="img-responsive"
							style="max-width:100%;"
							/>
						</div>
						<div class="col-md-4 col-sm-12 col-xs-12">
							<div class="_desc">
								<ul>
								'.$html_caracteristicas.'
								</ul>
						
						</div>
					</div>
				</div>
			</div>
			';
		}
		else {
			$html = '
		
				<div class="" style="max-width:500px; margin-right:-146px;" >
					<!-- <h4>Descrição técnica do produto - '.$this->referencia.'</h4> -->
					<div>
						<div class="col-md-6 col-sm-6 col-xs-6 padding_0" style="font-size:20px;">
							<div class="">
								<ul>
								'.$html_caracteristicas.'
								</ul>
							
						</div>
					</div>
				</div>
			</div>
			';
		}
		return $html;
	}

	public function getInformacoesTecnicasArray(){

		$caracteristicas = array();

		if($this->descricao_es != ''){
			foreach(explode("\n",$this->descricao_es) as $linha){
				$linha = trim($linha);
				if($linha==''){
					continue;
				}
				$caracteristicas[] = $linha;
			}
		}

		if($this->descricao_in != '') {
			$i = 0;
			foreach (explode("\n", trim($this->descricao_in)) as $linha) {
				$linha = trim($linha);
				if ($linha == '') {
					continue;
				}
				if($i == 0){
					$caracteristicas[] = "Área de gravação:";
				}
				$caracteristicas[] = $linha;
				$i ++;
			}
		}

		// if(trim($this->desc_refil) != ''){
		// 	$caracteristicas[] = "Refil: {$this->desc_refil}";
		// }

		return $caracteristicas;

	}

	public function getChamadaFront(){
        $max = 50;
        $cut = 50;
		if($this->chamada==""){
			if(strlen($this->descricao)>$max){
				return substr($this->descricao,0,$cut).' ...';
			}
			return $this->descricao;
		}
		else {
			if(strlen($this->chamada)>$max){
				return substr($this->chamada,0,$cut).' ...';
			}
			return $this->chamada;
		}
	}

	public function getDescricaoAbreviado(){
		$abrev = 200;
		if(strlen($this->descricao)>$abrev){
			return substr($this->descricao,0,$abrev).'...';
		}
		return $this->descricao;
	}

	public function getInfosAdicionais(){
		return "
		<ul>
			".($this->infoadicional1!=''?"<li>{$this->infoadicional1}".($this->infoadicional1_link!=''?"&nbsp;<span><a href='{$this->infoadicional1_link}' target='_blank' class='_tooltipe' title='{$this->infoadicional1_tooltipe}'><img src='".PATH_SITE."img/assets/interrogacao.png' style='display:block;' width='18px' /></a></span>"
				:($this->infoadicional1_tooltipe!=""?"&nbsp;<span><a class='_tooltipe' title='{$this->infoadicional1_tooltipe}'><img src='".PATH_SITE."img/assets/interrogacao.png' style='display:block;' width='18px' /></a></span>":""))
				."</li>":"")."
			".($this->infoadicional2!=''?"<li>{$this->infoadicional2}".($this->infoadicional2_link!=''?"&nbsp;<span><a href='{$this->infoadicional2_link}' target='_blank' class='_tooltipe' title='{$this->infoadicional2_tooltipe}'><img src='".PATH_SITE."img/assets/interrogacao.png' style='display:block;' width='18px' /></a></span>"
				:($this->infoadicional2_tooltipe!=""?"&nbsp;<span><a class='_tooltipe' title='{$this->infoadicional2_tooltipe}'><img src='".PATH_SITE."img/assets/interrogacao.png' style='display:block;' width='18px' /></a></span>":""))
				."</li>":"")."
			".($this->infoadicional3!=''?"<li>{$this->infoadicional3}".($this->infoadicional3_link!=''?"&nbsp;<span><a href='{$this->infoadicional3_link}' target='_blank' class='_tooltipe' title='{$this->infoadicional3_tooltipe}'><img src='".PATH_SITE."img/assets/interrogacao.png' style='display:block;' width='18px' /></a></span>"
				:($this->infoadicional3_tooltipe!=""?"&nbsp;<span><a class='_tooltipe' title='{$this->infoadicional3_tooltipe}'><img src='".PATH_SITE."img/assets/interrogacao.png' style='display:block;' width='18px' /></a></span>":""))
				."</li>":"")."
		</ul>
		";
	}


	public function getEspecificacoes(){

        /*
        <dl class="dl-horizontal">
        <dt>...</dt>
        <dd>...</dd>
        </dl>
        */

		$spec= false;

		$return= "
		<div class='col-md-9 col-sm-12 col-xs-12 dl-horizontal table-precos table-condensed padding_0 especificacao'>";
		$return .="<h6>Especificações</h6>";
            if($this->altura!=''){
				$return .= $this->getEspecificacoesLabel("Altura", "{$this->altura}cm");
				$spec = true;
			}
			if($this->largura!=''){
				$return .=$this->getEspecificacoesLabel("Largura", "{$this->largura}cm");
				$spec = true;
			}
			if($this->profundidade!=''){
				$return .=$this->getEspecificacoesLabel("Profundidade", "{$this->profundidade}cm");
				$spec = true;
			}
			if($this->diametro!=''){
				$return .=$this->getEspecificacoesLabel("Diâmetro", "{$this->diametro}cm");
				$spec = true;
			}
			if($this->energia!=''){
				$return .=$this->getEspecificacoesLabel("Energia", "{$this->energia}");
				$spec = true;
			}
			if($this->garantia!=''){
				$return .=$this->getEspecificacoesLabel("Garantia", "{$this->garantia}");
				$spec = true;
			}
			if($this->medida_gravacao!=''){
				$return .=$this->getEspecificacoesLabel("Medidas gravação (CxL)", "{$this->medida_gravacao}");
				$spec = true;
			}
			if($this->tamanho_total!=''){
				$return .=$this->getEspecificacoesLabel("Tamanho total (CxL)", "{$this->tamanho_total}");
				$spec = true;
			}
			if($this->peso>0){
				$return .=$this->getEspecificacoesLabel("Peso (gr)", "{$this->peso}");
				$spec = true;
			}
			if($this->disponibilidade=='N'){
				$return .=$this->getEspecificacoesLabel("Disponibilidade", "Indisponível");
				$spec = true;
			}
        $return .="</div>";

		return ($spec? $return:'');
	}

    private function getEspecificacoesLabel($label, $value){
        return
        "
		<p>{$label}: <span style='font-weight:400;color: #666666;'>{$value}</span></p>
        ";
    }

	public function salva2(){

        $tag_nome = str_replace(" ","-",$this->nome."-".$this->referencia);
		$tag_nome = strtolower($tag_nome);
		$this->tag_nome = $tag_nome;

		$this->tabela1 = toFloat($this->tabela1);
		$this->tabela2 = toFloat($this->tabela2);
		$this->tabela3 = toFloat($this->tabela3);

		$this->tabela1_st = toFloat($this->tabela1_st);
		$this->tabela2_st = toFloat($this->tabela2_st);
		$this->tabela3_st = toFloat($this->tabela3_st);

		return parent::salva();
	}

	public function salva(){

		$erros = array();
		// $tag_nome = str_replace(" ","-",$this->nome."-".$this->referencia);
		// $tag_nome = strtolower($tag_nome);

        $this->tag_nome = stringAsTag("{$this->nome} {$this->referencia} {$this->seo_keywords} {$this->id}");

        // $this->tag_nome = $tag_nome;

		$this->tabela1 = toFloat($this->tabela1);
		$this->tabela2 = toFloat($this->tabela2);
		$this->tabela3 = toFloat($this->tabela3);

		$this->tabela1_st = toFloat($this->tabela1_st);
		$this->tabela2_st = toFloat($this->tabela2_st);
		$this->tabela3_st = toFloat($this->tabela3_st);

		if($this->st_amamos!='S'){
			//query("DELETE from mosaico WHERE item_id = {$this->id}");
		}

        if(!parent::salva()){
            return false;
        }

        $_width  = 1000;
        $_height = 1000;

        $opacidade = config::get($key_opacidade = 'MARCA_DAGUA_OPACIDADE');

        foreach(range(0,20) as $i){
        // foreach ( array(@$_FILES['file_imagem'], @$_FILES['file_imagem_d1'], @$_FILES['file_imagem_d2'], @$_FILES['file_imagem_d3'], @$_FILES['file_imagem_d4'], @$_FILES['file_imagem_d5'], @$_FILES['file_imagem_d6'], @$_FILES['file_imagem_d7']) as $file_imagem ){

            if($i==0){
                $imagem = 'imagem';
                $extensao = '';
            }
            else {
                $imagem = 'imagem_d'.$i;
                $extensao = 'd'.$i;
            }

            $file_imagem = @$_FILES['file_'.$imagem];

			if(@$file_imagem['name']!= '' && !isImagemJPG($file_imagem['name'])){
				$erros[] = 'A imagem '.$file_imagem['name'].' deve ser .JPG';
				continue;
			}

            $image_name = stringAsTag($this->nome).'-'.$this->id.$extensao.'-'.time().'.jpg' ;

            if(@$file_imagem['name']!=''){
				
				list($width, $height) = getimagesize($file_imagem['tmp_name']);

				// VERIFICA SE O TAMANHO DA IMAGEM ULTRAPASSA O PERMITIDO PELO SITE 
				if($width > $_width || $height > $_height){
                    $_SESSION['erro'] = tag('p','A imagem ('.$file_imagem['name'].') deve ter no máximo '.$_width.'px'.$_height.'px');
					continue;
				}
				
				// VERIFICA SE A IMAGEM TEM ALTURA X LARGURA IGUAIS 
				if(!isImagemQuadrada($file_imagem['tmp_name'])){
					$_SESSION['erro'] = tag('p','A imagem ('.$file_imagem['name'].') deve conter ALTURA x LARGURA de tamanhos iguais.');
					continue;
				}

				if(!is_dir('img/produtos/')) mkdir('img/produtos/',0777,true);

				if(file_exists("img/produtos/{$this->$imagem}") && $this->$imagem!=""){
					unlink("img/produtos/{$this->$imagem}");
				}

                // Salva imagem original
                $path_original = "img/produtos/{$image_name}";
                $si = new SimpleImage($file_imagem['tmp_name']);
                $si->save( $path_original, 100);
                //$si->save(PATH_IMG."produtos/{$image_name}", 75);

				if(config::get("TIMTHUMB_HABILITADO")!="S"){
					for($i=1;$i<=3;$i++){

						$dest = "img/produtos/{$i}/{$image_name}";

						$tamanho = config::get($key_tamanho = 'IMG'.$i.'_TAMANHO');
						$marca = config::get($key_marca = 'IMG'.$i.'_HABILITA_MARCA_DAGUA');

						$si = new SimpleImage($path_original);

						if($marca == 'S'){
							$si->overlay('img/marcadagua/marcadagua.png', 'center', $opacidade);
						}

						$si->resize($tamanho, $tamanho);
						$si->save($dest,75);
					}
				}

                /*
                // Salva marca dagua
                $path_marca_dagua = "img/produtos/marcadagua/{$image_name}";
                $si->overlay('img/marcadagua.png', 'center', .8);
                $si->save($path_marca_dagua, 100);

                // Caminho
                $path_final = "img/produtos/{$image_name}";

                if($marca_dagua_ativa == 'S'){
                    copy($path_marca_dagua, $path_final);
                }
                else {
                    copy($path_original, $path_final);
                }
                */

                query("UPDATE item SET {$imagem} = '{$image_name}' WHERE id = {$this->id}");

                /*
                // Salva imagem original
                $path_original = "img/produtos/original/{$image_name}";
                copy($file_imagem['tmp_name'], $path_original);

                // Salva imagem da marcadagua
                $path_marca_dagua = "img/produtos/marcadagua/{$image_name}";
                $img_marca_dagua = "img/marcadagua.png";

                $watermark = imagecreatefrompng($img_marca_dagua);
                $image = imagecreatefromjpeg($path_original);

                imagecopymerge_alpha($image, $watermark, 0, 0, 0, 0, $width, $height, 0);
                imagejpeg($image, $path_marca_dagua);
                */

                /*
                $path_fisico = "img/produtos/{$image_name}";
                $path_original = "img/produtos/{$image_name}";

                @unlink($path_fisico);

                copy($file_imagem['tmp_name'], $path_fisico);
                copy($file_imagem['tmp_name'], $path_fisico);

                query("UPDATE item SET {$imagem} = '{$image_name}' WHERE id = {$this->id}");

                if(request("habilita_marca_dagua_{$imagem}")){

                    $path_marca_dagua = "img/marcadagua.png";

                    $watermark = imagecreatefrompng($path_marca_dagua);
                    $image = imagecreatefromjpeg($path_fisico);

                    imagecopymerge_alpha($image, $watermark, 0, 0, 0, 0, $width, $height, 0);
                    imagejpeg($image, $path_fisico);

                }
                */



            }
        }

        $cor_ids = request('cor_id');
        $atualizados = array(0);

        foreach(is_array($cor_ids)?$cor_ids:array() as $cor_id){

            $itemcor = new itemcor();
            $itemcor->reset_vars();
            $itemcor->get_by_id(array('item_id'=>$this->id, 'cor_id'=>$cor_id));
            $itemcor->cor_id = $cor_id;
            $itemcor->item_id = $this->id;
            $itemcor->st_ativo = $_REQUEST['cor'][$cor_id]['st_ativo'];

            $itemcor->st_default = @$_REQUEST['st_default']==$cor_id?'S':'N';

            if(@$_FILES['file_cor_'.$cor_id]){
                $itemcor->set_file($_FILES['file_cor_'.$cor_id]);
            }

            $itemcor->salva();
            $atualizados[] = $itemcor->id;

            //printr($itemcor);
            //printr($_FILES);
        }

        query('DELETE FROM itemcor WHERE item_id = '. $this->id . ' and ID not in ('.join(',',$atualizados).')');

		if(sizeof($erros)>0){
			$_SESSION['erro'] = tag('p',join('<br>',$erros));
		}
        return true;
	}

	public function exclui(){
		$rows = rows(query("SELECT * FROM pedidoitem WHERE item_id = {$this->id}"));
		if($rows>0){
			$_SESSION['erro'] = tag('p',"Não é possível excluir o item \"{$this->nome}\", há pedidos relacionados.<br />Sugestão: inative esse produto.");
		}
        else {
			if($this->id){
				query("DELETE FROM itemcarac WHERE item_id = {$this->id}");
				query("DELETE FROM itemcategoria WHERE item_id = {$this->id}");
				query("DELETE FROM itemcor WHERE item_id = {$this->id}");
				//query("DELETE FROM mosaico WHERE item_id = {$this->id}");
				query("DELETE FROM produtoexclusivo WHERE item_id = {$this->id}");
				query("DELETE FROM youtubevideo WHERE item_id = {$this->id}");
				if(parent::exclui()){
					$_SESSION['sucesso'] = tag('p','Item excluído com sucesso!');
				}
			}
		}
	}

	public function valida_atualizacao(& $erros){

		$erros = array();

		if(trim($this->referencia)==""){
			$erros[] = 'Preencha a referencia';
		}
		// elseif(!preg_match('/^[A-Z]{2}[0-9]{3}[0-9]{2}$/', strtoupper($this->referencia), $matches)){
			// $erros[] = 'A referencia precisa estar no formato `LLNNNCC`';
		// }

        /*
		if(!$this->valida_unico('referencia')){
			$erros[] = "Esta referencia {$this->referencia} j&aacute; existe no banco de dados";
		}
        */

		if(trim($this->nome)==""){
			$erros[] = 'Preencha o nome do produto';
		}
        else {
            /*
			$tag_nome = str_replace(" ","-",$this->nome);
			$tag_nome = strtolower($tag_nome);
			$this->tag_nome = $tag_nome;
			if(!$this->valida_unico('tag_nome')){
				$erros[] = "Já existe um produto com este nome - {$this->tag_nome}.";
			}
            */
		}

		if(trim($this->tabela1)==""){
			// $erros[] = 'Preencha a Tabela 1 com um valor maior que zero.';
		}

		// if(trim($this->tabela1_st)==""){
			// $erros[] = 'Preencha a Tabela 1 ST com um valor maior que zero.';
		// }
		// if(trim($this->tabela2)==""){
			// $erros[] = 'Preencha a Tabela 2 com um valor maior que zero.';
		// }
		// if(trim($this->tabela3)==""){
			// $erros[] = 'Preencha a Tabela 3 com um valor maior que zero.';
		// }
		/**
		Valida imagem principal e detalhes
		**/

		// foreach ( array(@$_FILES['file_imagem'], @$_FILES['file_imagem_d1'], @$_FILES['file_imagem_d2'], @$_FILES['file_imagem_d3'], @$_FILES['file_imagem_d4'], @$_FILES['file_imagem_d5'], @$_FILES['file_imagem_d6'], @$_FILES['file_imagem_d7']) as $file_imagem ){
			// if($file_imagem['name']!=''){

				// if(!isImagemPNG($file_imagem['name'])){
					// $erros[] = 'A imagem '.$file_imagem['name'].' deve ser .PNG';
					// continue;
				// }

				// // if(!isImagemComTamanhoMinimo($file_imagem['tmp_name'],1000,1000)){
					// // $erros[] = 'A imagem '.$file_imagem['name'].' deve ter no minimo 1000px por 1000px';
					// // continue;
				// // }

				// if(!isImagemQuadrada($file_imagem['tmp_name'])) {
					// $erros[] = 'A imagem '.$file_imagem['name'].' ser quadrada';
					// continue;
				// }
			// }
		// }

		/**
		Valida opções de cor
		**/

		$query = query($sql="select cor.id from cor");
		while($fetch=fetch($query)){

			$file_imagem = @$_FILES['file_imagem_cor_'.$fetch->id];

			if(@$file_imagem['name']!='' ) {

				if(!isImagemPNG($file_imagem['name'])){
					$erros[] = 'A imagem '.$file_imagem['name'].' deve ser .JPEG';
					continue;
				}

				if(!isImagemComTamanhoMinimo($file_imagem['tmp_name'],1000,1000)){
					$erros[] = 'A imagem '.$file_imagem['name'].' deve ter 1000px por 1000px';
					continue;
				}

				if(!isImagemQuadrada($file_imagem['tmp_name'])) {
					$erros[] = 'A imagem '.$file_imagem['name'].' ser quadrada';
					continue;
				}
			}
		}

		$this->peso = to_float(@$this->peso);
		return sizeof($erros)==0;
	}

	public function itensCorByReferencia(){
		$return = array();
		$query = query("SELECT * FROM item WHERE referencia LIKE '%".$this->getGlobalReferencia()."%'");
		while($fetch=fetch($query)){
			$return[$fetch->id] = new item($fetch->id);
		}
		return $return;
	}

	public function validaVariacao(&$erro=''){
		if(!is_set($this->referencia)){
			$erro .= tag('p','Digite a referencia');
		}
		$query = query("SELECT * FROM item WHERE itemsku_id > 0 ".($this->id>0?" AND id != {$this->id}":"")." AND referencia = '{$this->referencia}'");
		if(rows($query)>0){
			$erro .= tag("p","J&aacute; existe um item cadastrado com essa referencia ( {$this->referencia} ).");
		}

		if(!$this->cor_id){
			$erro .= tag("p","Selecione uma cor.");
		}

		return ($erro=="");
	}

	public function getExplodeReferencia(){
		// L -> Letras
		// N -> Tres números no meio
		// C -> Dois últimos números (Código da cor)
		$return = array();
		$return['L'] = substr(strtoupper($this->referencia),0,2);
		$return['N'] = substr(strtoupper($this->referencia),2,3);
		$return['C'] = substr(strtoupper($this->referencia),5);

		return $return;
	}

	public function getGlobalReferencia(){
		$arr_ref = $this->getExplodeReferencia();
		return $arr_ref['L'].$arr_ref['N'];
	}

	public function getCorReferencia(){
		$arr_ref = $this->getExplodeReferencia();
		return $arr_ref['C'];
	}

	public function getCor(){
		return new cor($this->cor_id);
	}

	public function getVideo(){
		$youtubevideo = new youtubevideo(array("item_id"=>$this->id));
		$video = '';

		if($youtubevideo->id && $youtubevideo->st_ativo=="S"){

            // printr($youtubevideo);
            // "https://www.youtube.com/embed/{$youtubevideo->youtube_id}?rel=0&controls=0&showinfo=0&modestbranding=1";

			$_src = encode("https://www.youtube.com/embed/{$youtubevideo->youtube_id}?rel=0&controls=0&showinfo=0&modestbranding=1");
			$video = '<img src="'.PATH_SITE.'img/assets/youtube_icon.png" class="link-detalhe img_video" id="thumbvideo" data-src="'.$_src.'" />'
			;

		}


		return $video;
	}

	public function temVariacao(){
		$query = query("SELECT id FROM item WHERE itemsku_id ={$this->id}");
		return rows($query)>0;
	}

	static function qtdItensSite(){
		return rows(query("SELECT * FROM item WHERE itemsku_id = 0 OR itemsku_id is NULL"));
	}

	static function opcoesCor($item_id){
		$query = query("SELECT item.*, cor.nome cornome FROM item INNER JOIN cor ON ( item.cor_id = cor.id AND cor.st_ativo = 'S') where item.itemsku_id = {$item_id}");
		$return = array();
		while($fetch=fetch($query)){
			$return[$fetch->id] = $fetch->cornome;
		}
		return $return;
	}

    public function getTagImagem($pasta, $size, $qtd=20){

		// $principal = 'imagem';

		//if(!is_dir(PATH_SITE."img/produtos/{$pasta}"))$pasta = "original";
		$absolute_path = getcwd();
        $this->imagem_principal = 'imagem';

        if(IS_MOBILE==1 && $this->imagem_d1 != ''){
            $this->imagem_principal = 'imagem_d1';
        }

		$imgattr = '';
		$imgattr2 = '';
        $x = 0;
        $src = '';
		$srcbase = '';

        foreach(range(0,$qtd) as $i) {

            if ($i == 0) {
                $imagem = 'imagem';
            }
            else {
                $imagem = 'imagem_d' . $i;
            }

            if(isset($this->$imagem) && $this->$imagem != ''){

                if($this->imagem_principal == $imagem){

                    $principal = $imagem;

                    if($_SERVER['SERVER_NAME']=='localhost'){
						/*$src = PATH_SITE."img/produtos/{$pasta}/{$this->$imagem}";-*/
						
						if(file_exists($absolute_path."/img/produtos/{$this->imagem}")){
							$src = PATH_SITE."img/produtos/{$this->imagem}";
						}else{
							$src = PATH_IMG."produtos".DIRECTORY_SEPARATOR."{$this->$imagem}";
						}
                        // $src = PATH_SITE."img/produtos/{$this->$imagem}";
						//$srcbase = PATH_SITE."img/icons/loading.gif";
                    }
                    else {
						/*$src = PATH_SITE."timthumb/timthumb.php?src=".PATH_SITE."img/produtos/{$pasta}/{$this->$imagem}&w={$size}";*/
						if(file_exists($absolute_path."/img/produtos/{$this->imagem}")){
							// $src = PATH_SITE."img/produtos/{$this->imagem}";
							$src = PATH_SITE."timthumb/timthumb.php?src=".PATH_SITE."img/produtos/{$this->$imagem}&w={$size}";
						}else{
							$src = PATH_IMG."produtos/{$this->$imagem}?w={$size}";
							// $src = PATH_IMG."produtos".DIRECTORY_SEPARATOR."{$this->$imagem}";
						}
						//$srcbase = PATH_SITE."img/icons/loading.gif";
                    }

                    $imgattr .= " data-original='{$src}' data-src='{$src}' ";
					$imgattr2 .= "src='{$srcbase}' data-original='{$src}' data-src='{$src}' ";

                }
                else {

                    $x ++;
                    if($_SERVER['SERVER_NAME']=='localhost'){
                        /*$imgattr .= " data-src{$x}='".PATH_SITE."img/produtos/{$pasta}/{$this->$imagem}' ";*/
						// $imgattr .= " data-src{$x}='".PATH_SITE."img/produtos/{$this->$imagem}' ";
						if(file_exists($absolute_path."/img/produtos/{$this->imagem}")){
							$imgattr .= " data-src{$x}='".PATH_SITE."img/produtos/{$this->$imagem}' ";
						}else{
							$imgattr .= " data-src{$x}='".PATH_IMG."/produtos/{$this->$imagem}' ";
						}
						/*$imgattr2 .= "src='{$srcbase}' data-src{$x}='".PATH_SITE."img/produtos/{$pasta}/{$this->$imagem}' ";*/
						$imgattr2 .= "src='{$srcbase}' data-src{$x}='".PATH_SITE."img/produtos/{$this->$imagem}' ";
                    }
                    else {
						if(file_exists($absolute_path."/img/produtos/{$this->imagem}")){
							$imgattr .= " data-src{$x}='".PATH_SITE."timthumb/timthumb.php?src=".PATH_SITE."img/produtos/{$this->$imagem}&w={$size}' ";
						}else{
							$imgattr .= " data-src{$x}='".PATH_IMG."produtos/{$this->$imagem}?w={$size}' ";
						}
                        /*$imgattr .= " data-src{$x}='".PATH_SITE."timthumb/timthumb.php?src=".PATH_SITE."img/produtos/{$pasta}/{$this->$imagem}&w={$size}' ";*/
						/*$imgattr2 .= "src='{$srcbase}' data-src{$x}='".PATH_SITE."timthumb/timthumb.php?src=".PATH_SITE."img/produtos/{$pasta}/{$this->$imagem}&w={$size}' ";*/
						$imgattr2 .= "src='{$srcbase}' data-src{$x}='".PATH_SITE."timthumb/timthumb.php?src=".PATH_SITE."img/produtos/{$this->$imagem}&w={$size}' ";
                    }
                }
            }
        }

        // Listagem, com lazy
        if($pasta == 1){

            return "<img class='media-object js-detalhe lazy img-responsive' {$imgattr} data-indice='1' src='{$src}' alt='{$this->nome}' style='width:100%;' />";
        }
        // Detalhe ou zoom, sem lazy
        else {
            return "<img class='media-object img-responsive lazy imagem_principal' {$imgattr} src='{$src}' data-indice='1' alt='{$this->nome}' style='width:100%;' />";
        }
    }

    public function getTagImagemListagem(){
		$tamanho = config::get('IMG1_TAMANHO');
		$pasta = 1;
		//$pasta = ( config::get("IMG1_HABILITA_MARCA_DAGUA")=="S" ? 1 : (config::get("TIMTHUMB_HABILITADO")=='S' ? "original" : 1) );
		return $this->getTagImagem( $pasta, $tamanho, 1);
    }

    public function getTagImagemDetalhe(){
		$tamanho = config::get('IMG2_TAMANHO');
		$pasta = 2;
		//$pasta = ( config::get("IMG2_HABILITA_MARCA_DAGUA")=="S" ? 2 : (config::get("TIMTHUMB_HABILITADO")=='S' ? "original" : 2) );
        return $this->getTagImagem($pasta, $tamanho);
    }

    public function getTagImagemZoom(){
		$tamanho = config::get('IMG3_TAMANHO');
		$pasta = 3;
		// $pasta = ( config::get("IMG3_HABILITA_MARCA_DAGUA")=="S" ? 3 : (config::get("TIMTHUMB_HABILITADO")=='S' ? "original" : 3) );
        return $this->getTagImagem($pasta, $tamanho);
	}
	
	public function getTagImagemListagemSemLazy(){
		$tamanho = config::get('IMG1_TAMANHO');
        return $this->getTagImagem(1, $tamanho, 1, true);
    }

    public function valida_unico($nome_campo){

        $rows = rows(query($SQL="select id from {$this->get_table_name()} where 1=1 and ifnull(itemsku_id,0) = 0 ".($this->id>0?" and id != {$this->id}":"")." and {$nome_campo} = '{$this->$nome_campo}'"));

        if($rows==0){
            return true;
        }

        return false;
    }

    public function getInfoadicionalTooltip(){
        return $this->infoadicional1;
        return nl2br($this->infoadicional1);
    }

    public function getCategoriaPrincipal(){
        $tmp = fetch(query($sql=
        "
        SELECT categoria.* FROM categoria
        WHERE categoria.id IN (SELECT categoria_id FROM itemcategoria WHERE item_id = {$this->id})
        AND categoria.st_ativo = 'S'
        AND categoria.st_fixo = 'N'
        ORDER BY categoria.categoria_id DESC
        "));

        // printr($sql);
        // printr($tmp);

        if($tmp){
            $categoria = new categoria();
            $categoria->load_by_fetch($tmp);
            return $categoria;
        }
	}

	public function getDimensoes(){
		$dimensoes = "";
		if($this->altura!="")$dimensoes .= ($dimensoes!=""?" x ":"") . $this->altura." mm ";
		if($this->largura!="")$dimensoes .= ($dimensoes!=""?" x ":"") . $this->largura." mm ";
		if($this->profundidade!="")$dimensoes .= ($dimensoes!=""?" x ":"") . $this->profundidade." mm ";
		if($this->diametro!="")$dimensoes .= " Diâmetro : ".$this->diametro." mm ";

		return $dimensoes;
	}

	static function precoSiteAtivo()
	{
		return config::get('HABILITA_PRECO') == 'S';
	}

	public function getPrecoDetalhe()
	{
		$preco = '';
		if (self::precoSiteAtivo()) {
			if ($this->preco > 0) {
				$preco .= '<div class="preco_det col-md-12 col-sm-12  col-xs-12">';
				if ($this->preco_de > 0) $preco .= '<span class="de">De</span> <span class="preco_de">R$ ' . $this->getPrecoDeFormatado() . '</span>&nbsp;&nbsp;&nbsp;';
				if($this->preco> 0)$preco .= '<span class="preco">R$' . $this->getPrecoFormatado() . '</span>';
				if ($this->infoadicional1 != "") $preco .= "<br/><small class='texto_preco'>". $this->infoadicional1 ."</small>";
				$preco .= '</div>';
			}
			else{
				$preco .= "<div class='sem_preco'></div>";
			}
		}
		return $preco;
	}

}
