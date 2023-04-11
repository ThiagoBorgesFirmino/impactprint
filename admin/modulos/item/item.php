<?php

class modulo_item extends modulo_admin {

    public $arquivo = 'item';

    // Pesquisa
    public function pesquisa(){

        if(request('popup')){
            $t = new TemplateAdminPopup('admin/tpl.admin-cadastro-generico.html');
        }
        else {
            $t = new TemplateAdmin('admin/tpl.admin-cadastro-generico.html');
        }

        $t->h1 = h1($this->modulo->nome);

        if(request('action')=='editar'
            ||substr(request('action'),0,6)=='salvar'){
            $this->editar($t);
            return;
        }

        if(request('action')=='excluir'){
            $item = new item(intval(request('id')));
            if($item->id){
                $item->exclui();
            }
        }

        if(request('action')=='excluir_variacao'){
            $item = new item(intval(request('variacao_id_excluir')));
            if($item->id){
                $item->exclui();
            }
            return $this->editar($t);
        }

        $grid = new grid();

        $sql =
        '
        SELECT
            item.id
            -- ,concat(\'<img style="max-width:100px" src="'.INDEX.'timthumb/timthumb.php?height=100&width=100&src='.PATH_SITE.'img/produtos/\',item.imagem,\'"/>\') Imagem
            -- ,concat(\'<img style="max-width:100px" src="'.PATH_SITE.'img/produtos/\',item.imagem,\'"/>\') Imagem
            ,item.imagem Imagem
            ,item.referencia Referencia
            ,item.nome Nome
            ,item.fornecedor_1 Codigo_Fornecedor
            ,item.fornecedor_2 Fornecedor

            -- ,item.tipo_produto TIPO_PRODUTO
            ,item.descricao Descricao
            ';

        if(modulo_itemclasse::ATIVO){
            $sql .=
            '
            ,itemclasse.nome Classe_produtos
            ,item.itemclasse_id
            ';
        }

        $sql .=
        '
            -- ,CASE WHEN item.st_amamos = \'S\' THEN \'Sim\' ELSE \'Nao\' END Lancamentos
            -- ,CASE WHEN item.st_destaque = \'S\' THEN \'Sim\' ELSE \'Nao\' END Promocao
            ,item.st_ativo Status

        FROM
            item
        LEFT JOIN itemclasse ON (
            itemclasse.id = item.itemclasse_id
        )
        WHERE
            1=1
        AND ( itemsku_id = 0 OR itemsku_id is NULL )
        ' ;

        $grid->sql = $sql;

        $filtro = new filtro();

        $filtro->add_input('Referencia','Referência:');
        $filtro->add_input('Nome','Nome:');
        $filtro->add_input('Descricao','Descrição:');
        $filtro->add_status('Status','Status:');
        $filtro->add_categoria('categoria_id');
        $filtro->add_itemcor('cor_id');
        $filtro->add_input('Fornecedor','Fornecedor:');
        // $filtro->add_select('Lancamentos','Em lançamento:', array('Sim'=>'Sim','Nao'=>'Não'));
        // $filtro->add_select('Promocao','Em promoção:', array('Sim'=>'Sim','Nao'=>'Não'));

        if(modulo_itemclasse::ATIVO){
            $filtro->add_select('itemclasse_id ','Classe produtos:', itemclasse::opcoes());
        }

        $filtro->excel = $this->adm_instance->boxExpExcel($sql,$this->arquivo,$filtro);

        
        $grid->functions["Imagem"] = function($item){
            $ret = "";
           
             if($item->Imagem !="" && file_exists("img/produtos/".$item->Imagem)){
                 
                 $ret = "<img style='max-width:100px' src='".PATH_SITE."img/produtos/".$item->Imagem."'>";
             
             }else{
 
                 $ret = "<img style='max-width:100px' src= '".PATH_IMG."produtos/".$item->Imagem."'>";
                 // $ret = "<img style='max-width:100px' src='".PATH_SITE."img/produtos/".$item->Imagem."'>";
 
             }
                 
             return $ret;
         
         };


        $grid->metodo = $this->arquivo;
        $grid->filtro = $filtro ;
        //$grid->botao_excluir = $_SESSION['CADASTRO']->st_pode_excluir_produtos == 'S' ;
        $grid->nao_aparece = 'itemclasse_id';

        $edicao = '';
        $edicao .= $grid->render();

        $t->edicao = $edicao;

        $this->adm_instance->montaMenu($t);
        $this->adm_instance->show($t);
    }

    public function editar($t){

        // Se for pop-up, carrega template de pop-up
        if(request('pop')){
            $t = new TemplateAdminPop('admin/tpl.admin-cadastro-generico.html');
        }

        $t->h1 = h1($this->modulo->nome);

        $item = new item(intval(request('id')));

        if(substr(request('action'),0,6)=='salvar'){

            //$erros = array();

            $next = substr(request('action'),7,strlen(request('action')));

            $item->set_by_array(@$_REQUEST['item']);

            $item->preco = toFloat($item->preco);
            $item->preco_de = toFloat($item->preco_de);
            $item->preco_1 = toFloat($item->preco_1);
            $item->preco_2 = toFloat($item->preco_2);
            $item->preco_3 = toFloat($item->preco_3);

            if(intval($item->itemclasse_id)==0){
                $item->itemclasse_id = 'null';
            }

            if(intval($item->splash_id)==0){
                $item->splash_id = 'null';
            }

            $item->qtd_minima = intval($item->qtd_minima);

            try {

                if(!$item->valida_atualizacao($erro)){
                    throw new Exception(join('<br>', $erro));
                }

                if(!$item->salva()){
                    throw new Exception('Falha ao salvar os dados');
                }

                $item->seopro_url = seopro::validaSalva(array("modelo"=>"item","modelo_id"=>$item->id) , array("metodo"=>"brinde","tag_nome"=>$item->tag_nome), $erros)->url;
                $this->excluir_imagem($item);

                $item = new item($item->id);

                $this->salva_categoria($item);
                $this->salva_caracteristica($item);
                $this->salva_variacao_preco($item);

                $variacoes = request('variacao');
                foreach(is_array($variacoes)?$variacoes:array() as $variacao_id => $variacao_item){

                    $itemvariacao = new item($variacao_id);
                    $cor = new cor($variacao_item['cor_id']);

                    $itemvariacao->largura = $item->largura;
                    $itemvariacao->diametro = $item->diametro;
                    $itemvariacao->profundidade = $item->profundidade;
                    $itemvariacao->medida_gravacao = $item->medida_gravacao;
                    $itemvariacao->tamanho_total = $item->tamanho_total;
                    $itemvariacao->altura = $item->altura;
                    $itemvariacao->peso = $item->peso;
                    $itemvariacao->itemsku_id = $item->id;
                    $itemvariacao->referencia = $item->referencia.'-'.$cor->referencia;
                    $itemvariacao->nome = $item->nome.' '.$cor->nome;
                    $itemvariacao->seo_keywords = $item->seo_keywords;
                    $itemvariacao->cor_id = $variacao_item['cor_id'];
                    $itemvariacao->st_ativo = $variacao_item['st_ativo'];

                    $campos = array(
                        'imagem'
                        ,'imagem_d1'
                        ,'imagem_d2'
                        ,'imagem_d3'
                        ,'imagem_d4'
                        ,'imagem_d5'
                        ,'imagem_d6'
                        ,'imagem_d7'
                        ,'imagem_d8'
                        ,'imagem_d9'
                        ,'imagem_d10'
                        ,'imagem_d11'
                        ,'imagem_d12'
                        ,'imagem_d13'
                        ,'imagem_d14'
                        ,'imagem_d15'
                        ,'imagem_d16'
                    );

                    foreach($campos as $campo){
                        $_FILES['file_'.$campo] = @$_FILES["file_variacao_{$variacao_id}_{$campo}"];
                    }

                    $itemvariacao->salva();
                }

                if(request('incluir_nova_variacao')){

                    $variacoes = $item->id > 0 ? results($sql="SELECT * FROM item WHERE itemsku_id = ".intval($item->id)) : array();

                    $campos = array(
                        'imagem'
                        ,'imagem_d1'
                        ,'imagem_d2'
                        ,'imagem_d3'
                        ,'imagem_d4'
                        ,'imagem_d5'
                        ,'imagem_d6'
                        ,'imagem_d7'
                        ,'imagem_d8'
                        ,'imagem_d9'
                        ,'imagem_d10'
                        ,'imagem_d11'
                        ,'imagem_d12'
                        ,'imagem_d13'
                        ,'imagem_d14'
                        ,'imagem_d15'
                        ,'imagem_d16'
                    );

                    // Se nao tiver nenhuma variacao, coloca o item principal como a primeira
                    if(sizeof($variacoes)==0){


                        $itemvariacao = new item();
                        $cor = new cor($item->cor_id);
                        $itemvariacao->largura = $item->largura;
                        $itemvariacao->diametro = $item->diametro;
                        $itemvariacao->profundidade = $item->profundidade;
                        $itemvariacao->medida_gravacao = $item->medida_gravacao;
                        $itemvariacao->tamanho_total = $item->tamanho_total;
                        $itemvariacao->altura = $item->altura;
                        $itemvariacao->peso = $item->peso;
                        $itemvariacao->itemsku_id = $item->id;
                        $itemvariacao->referencia = $item->referencia.'-'.$cor->referencia;
                        $itemvariacao->nome = $item->nome.' '.$cor->nome;
                        $itemvariacao->seo_keywords = $item->seo_keywords;
                        $itemvariacao->cor_id = $item->cor_id;
                        $itemvariacao->st_ativo = 'S';
                        $itemvariacao->st_amamos = 'N';
                        foreach($campos as $campo){
                            $itemvariacao->$campo = $item->$campo;
                        }
                        $itemvariacao->salva();
                    }

                    $variacao_item = request('nova');
                    $itemvariacao = new item();
                    $cor = new cor($variacao_item['cor_id']);
                    $itemvariacao->largura = $item->largura;
                    $itemvariacao->diametro = $item->diametro;
                    $itemvariacao->profundidade = $item->profundidade;
                    $itemvariacao->medida_gravacao = $item->medida_gravacao;
                    $itemvariacao->tamanho_total = $item->tamanho_total;
                    $itemvariacao->altura = $item->altura;
                    $itemvariacao->peso = $item->peso;
                    $itemvariacao->referencia = $item->referencia.'-'.$cor->referencia;
                    $itemvariacao->nome = $item->nome.' '.$cor->nome;
                    $itemvariacao->seo_keywords = $item->seo_keywords;
                    $itemvariacao->itemsku_id = $item->id;
                    $itemvariacao->cor_id = $variacao_item['cor_id'];
                    $itemvariacao->st_ativo = 'S';
                    $itemvariacao->st_amamos = 'N';
                    foreach($campos as $campo){
                        $_FILES['file_'.$campo] = @$_FILES["file_nova_{$campo}"];
                    }
                    $itemvariacao->salva();
                }

                if(request('principal')){

                    $principal = new item(request('principal'));

                    $campos = array(
                        'cor_id'
                        ,'imagem'
                        /*
                        ,'imagem_d1'
                        ,'imagem_d2'
                        ,'imagem_d3'
                        ,'imagem_d4'
                        ,'imagem_d5'
                        ,'imagem_d6'
                        ,'imagem_d7'
                        ,'imagem_d8'
                        ,'imagem_d9'
                        ,'imagem_d10'
                        ,'imagem_d11'
                        ,'imagem_d12'
                        ,'imagem_d13'
                        ,'imagem_d14'
                        ,'imagem_d15'
                        ,'imagem_d16'
                        */
                    );

                    $tmp = array();
                    foreach($campos as $campo){
                        $tmp[] = "{$campo} = '{$principal->$campo}'";
                    }

                    query($sql="UPDATE item SET ".join(',',$tmp)." WHERE id = {$item->id}");
                    $item = new item($item->id);

                }

                // Video
                if(request('linkyoutube')){
                    $youtubevideo = new youtubevideo(array('item_id'=>$item->id));
                    $youtubevideo->st_ativo = request('linkyoutube_status');
                    $youtubevideo->validaDadosSalva($item->id,request('linkyoutube'));
                }

                $_SESSION['sucesso'] = 'Dados salvos com sucesso';

            }
            catch (Exception $ex){
                $_SESSION['erro'] = $ex->getMessage();
            }

            if(trim(@$next)!=''){
                $this->afterSave($next,$this->arquivo);
                return;
            }
        }

        if(request('action')=='sair'){
            $this->afterSave('sair',$this->arquivo,$item);
        }

        $t->parseBlock('BLOCK_TOOLBAR');

        //if($_SESSION["CADASTRO"]->email=="dev@ajung.com.br")$t->parseBlock('BLOCK_TOOLBAR');

        $edicao = '';

        $edicao .= inputHidden('id', $item->id);
        $edicao .= inputHidden('item[id]', $item->id);

        ob_start();
        ?>
        <ul class="nav nav-tabs">
            <li class="active"><a href="#dados" data-toggle="tab">Dados</a></li>
            <li><a href="#categorias" data-toggle="tab">Categorias</a></li>
            <li><a href="#caracteristicas" data-toggle="tab">Gravações</a></li>
            <li><a href="#imagens" data-toggle="tab">Imagens</a></li>
            <li><a href="#seopro" data-toggle="tab">SEO</a></li>
        </ul>
        <div class="tab-content">
            <div class="tab-pane active" id="dados">
                <?php $this->dados($item); ?>
            </div>
            <div class="tab-pane" id="categorias">
                <?php $this->categorias($item); ?>
            </div>
            <div class="tab-pane" id="caracteristicas">
                <?php $this->caracteristicas($item); ?>
            </div>
            <div class="tab-pane" id="imagens">
                <?php $this->imagens($item); ?>
            </div>
            <div class="tab-pane" id="seopro">
                <?php $this->seo($item); ?>
            </div>
        </div>

        <div class="modal fade" id="esqueciSenha" tabindex="-1" role="dialog" aria-labelledby="esqueciSenha">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true"></span></button>
                        <h4 class="modal-title">Nova imagem</h4>
                    </div>
                    <div class="modal-body">
                        <div class="alert" id="senhamsg" style="display: none"></div>
                        <form action="{index}senha" method="post" class="form-senha js-input">
                            <input type="hidden" name="enviar" value="1" />
                            <div class="form-group">
                                <label for="email" class="control-label">Digite seu e-mail de cadastro:</label>
                                <input type="text" name="email" class="form-control" value="" />
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-block btn-primary" onclick="$('.form-senha').submit();">ENVIAR</button>
                    </div>
                </div>
            </div>
        </div>

        <?php
        $x = ob_get_contents();
        ob_end_clean();
        $edicao .= $x;

        $edicao .= tag('br clear="all"');

        $t->edicao = $edicao;

        $opts = array(
            'include_js' => PATH_SITE."admin/modulos/{$this->arquivo}/itemadmin.js?".time()
        );

        // $this->adm_instance->montaMenu($t);
        $this->adm_instance->show($t, $opts);
    }

    private function dados(&$item){

        $edicao = '';

        $edicao .=
        '<div class="well">

            <legend>Dados básicos</legend>

            <div class="row">
                <div class="col-sm-6">';

        $edicao .= inputSimples('item[referencia]', $item->referencia, 'Referencia:', 30, 50); //tag('p','A referencia precisa estar no formato `LLNNNCC`, onde <br />LL = letras(2x), <br />NNN = números(3x), <br />CC = código da cor(2x)')
        $edicao .= textAreaCount('item[nome]', $item->nome, ' Nome:', 35, 2);
        $edicao .= select('item[st_ativo]', $item->st_ativo, 'Ativo?:', array('S'=>'Sim','N'=>'Nao'));
        $edicao .= tag('p',tag('small','Produtos inativos n&atilde;o aparecem no catalogo do site')); ;

        $edicao .= inputSimples('item[fornecedor_2]', $item->fornecedor_2, 'Fornecedor:');
        $edicao .= inputSimples('item[fornecedor_1]', $item->fornecedor_1, 'Código do Fornecedor:');

        if(modulo_itemclasse::ATIVO){
            $edicao .= select('item[itemclasse_id]', $item->itemclasse_id, 'Classe de produtos:', itemclasse::opcoes(array('blank'=>1)));
        }

        if($this->config->HABILITA_QUANTIDADE_MINIMA=='S'){
            $edicao .= inputSimples('item[qtd_minima]', $item->qtd_minima, ' Quantidade mínima:', 30, 50);
        }

        if($this->config->HABILITA_PRECO=='S'){
            // $edicao .= inputDecimal('item[preco]', $item->preco, ' Preço:');
            // $edicao .= inputDecimal('item[preco_de]', $item->preco_de, ' Preço DE:');
            $edicao .= tag('table class="table table-bordered" style="margin-top:10px;background-color:#ededed;border:2px solid #CCC;"',
                    tag('tr',
                        tag('th colspan="2" style="background-color:#CCC;"',
                            'Preço Promocional - Marketing'
                        )
                    )
                    .tag('tr',
                        tag("td style='padding:3px;'",inputDecimal('item[preco]', money($item->preco), 'Preço de Venda'))
                            .tag("td style='padding:3px;'",inputDecimal('item[preco_de]', money($item->preco_de), 'Preço De (aparece <strike>riscado</strike>):'))
                    )
                .tag("tr",
                    tag("td style='padding:3px;' colspan='2'",
                        inputSimples('item[infoadicional1]', $item->infoadicional1, ' Info. Adicional <small>(Ex.: Este valor é apenas para quantidades acima de 100.)</small>:', 30, 50)
                    )
                )
            );
        }

       // $edicao .= textArea('item[chamada]', $item->chamada, bandeira_br() . ' Chamada:', 35, 2);
        // $edicao .= textAreaCount('item[chamada]', $item->chamada, bandeira_br() . ' Chamada:', 35, 2);

        if($this->config->HABILITA_ESPANHOL=='S'){
            $edicao .= textArea('item[chamada_es]', $item->chamada_es, bandeira_es() . ' Chamada:', 35, 2);
        }

        if($this->config->HABILITA_INGLES=='S'){
            $edicao .= textArea('item[chamada_in]', $item->chamada_in, bandeira_in() . ' Chamada:', 35, 2);
        }

        $edicao .= textArea('item[descricao]', $item->descricao, bandeira_br() . ' Descricao:', 35, 4);

        if($this->config->HABILITA_ESPANHOL=='S'){
            $edicao .= textArea('item[descricao_es]', $item->descricao_es, bandeira_es() . ' Descricao:', 35, 4);
        }

        if($this->config->HABILITA_INGLES=='S'){
            $edicao .= textArea('item[descricao_in]', $item->descricao_in, bandeira_in() . ' Descricao:', 35, 4);
        }

        $edicao .= inputSimples('item[seo_keywords]', $item->seo_keywords, ' SEO Keywords:', null);
       // $edicao .= inputSimples('item[chamada_cotacao]', $item->chamada_cotacao, ' Chamada da cotação:', null);

        $edicao .= '
            </div>
            <div class="col-sm-6">';

        $edicao .= inputSimples('item[altura]', $item->altura, 'Altura ( cm ):', 15, 100);
        $edicao .= inputSimples('item[largura]', $item->largura, 'Largura ( cm ):', 15, 100);
        $edicao .= inputSimples('item[profundidade]', $item->profundidade, 'Profundidade ( cm ):', 15, 100);
        $edicao .= inputSimples('item[diametro]', $item->diametro, 'Diâmetro ( cm ):', 15, 100);
        $edicao .= inputSimples('item[peso]', $item->peso, 'Peso ( Kg ):', 15, 10);
        $edicao .= inputSimples('item[garantia]', $item->garantia, 'Garantia:', 15, 200) ;
        $edicao .= inputSimples('item[energia]', $item->energia, 'Energia ( ex: 110V ):', 15, 200);
        //$edicao .= inputSimples('item[disponibilidade]', $item->disponibilidade, 'Disponibilidade:', 15, 10) ;
        $edicao .= inputSimples('item[medida_gravacao]', $item->medida_gravacao, 'Medidas para gravação (CxL) cm:', 15, 200) ;
        $edicao .= inputSimples('item[tamanho_total]', $item->tamanho_total, 'Tamanho total (CxD):', 15, 200);
       // $edicao .= select('item[disponibilidade]',$item->disponibilidade, 'Disponibilidade:',array('S'=>'Disponível','N'=>'Indisponível'));

        $edicao .= '
                </div>
            </div>
        </div>';

        if($this->config->HABILITA_SPLASH=='S'){
            $edicao .= '<div class="well">';
            $edicao .= tag('legend', 'Splash');
            $edicao .= select('item[splash_id]', $item->splash_id, 'Splash:', splash::opcoes(), true);
            $edicao .= '</div>';
        }

        if($this->config->HABILITA_COR=='S' && false){
            $query = query("SELECT item.*, cor.nome cornome FROM item INNER JOIN cor ON ( item.cor_id = cor.id AND cor.st_ativo = 'S') where item.itemsku_id = {$item->id}");
            $variacoes = '<table class="table table-bordered table-striped table-hover">';
            $variacoes .= tag("tr",
                tag("th","Referencia")
                .tag("th","Nome")
                .tag("th","Status")
                .tag("th","Editar")
                .tag("th","Excluir")
            );
            while($fetch=fetch($query)){
                $variacoes .= tag("tr id='row_variacao{$fetch->id}'",
                    tag("td",$fetch->referencia)
                    .tag("td",$fetch->cornome)
                    .tag("td",($fetch->st_ativo=="S"?"Ativo":"Inativo"))
                    .tag("td","<span style='cursor:pointer;' class='add_variacao' data-id='{$fetch->id}' data-itemid='{$item->id}'><img src='".PATH_SITE."img/assets/edit.png' alt='Editar' /></span>")
                    .tag("td","<span style='cursor:pointer;' class='excluir_variacao' data-id='{$fetch->id}'><img src='".PATH_SITE."img/assets/cursor_x.png' alt='Excluir' /></span>")
                );
            }
            $variacoes .= "</table>";

            $edicao .= tag("div class='well' style='position:relative;'",
                tag("legend","Variações de Cor")
                .tag("div", "&nbsp;".(rows($query)>0?$variacoes:"") )
                .( $item->id ?
                    tag("span class='bt_afirmar btn btn-primary add_variacao' data-id='0' data-itemid='{$item->id}'", "Add nova")
                    : tag("span class='bt_negar btn' ", "Salve o produto, para liberar a adição das variações.")
                )
                .tag("div id='show_variacao'","&nbsp;")

                .tag("script","
					$(document).ready(function(){
						$('.add_variacao').bind('click',function(){
							_id = $(this).data('id');
							_itemid = $(this).data('itemid');
							$.ajax({
								url : '".PATH_SITE."admin.php/addVariacaoItem/'
								,data : {id : _id, itemid : _itemid}
								,success : function(out){
									$('#show_variacao').html(out);
								}
							});
						});

						$('.excluir_variacao').bind('click',function(){
							id = $(this).data('id');
							$.ajax({
								url : '".PATH_SITE."admin.php/excluirItemVariacao/'
								,dataType : 'json'
								,data : {item_id : id}
								,success : function(out){
									if(out['status']==1){
										$('#row_variacao'+id).remove();
									}
									if(out['status']==0){
										alert(out['msg']);
									}
								}
							});
						});
					});
				")
            );
        }

        // $edicao .= '<div class="well">';
        // $edicao .= tag("legend","Vídeo");
        // $edicao .= tag("p","Adicione o link do video do youtube.");

        // $youtubevideo = new youtubevideo(array('item_id'=>$item->id));

        // $edicao .= inputSimples("linkyoutube",(request('linkyoutube')?request('linkyoutube'):$youtubevideo->original_url),'Link Youtube',200,200);
        // $edicao .= select("linkyoutube_status",$youtubevideo->st_ativo,'Status',array("S"=>"Ativo","N"=>"Inativo"));

        // if($youtubevideo->id){
            // $_src = encode($youtubevideo->url.'?rel=0&version=3&loop=1&autoplay=1');
            // $edicao .= '
            // <div class="box_video">
                // <img src="'.$youtubevideo->thumbnail.'" width="240px" height="135px" class="thumbvideo" id="thumbvideo" data-src="'.$_src.'" />
            // </div>
            // <script>

				// $("#thumbvideo").bind("click",function(){
					// _src = $(this).data("src");
					// $.ajax({
						// url : "'.PATH_SITE.'ajax.php/getItemVideo/"
						// ,data : {src : _src}
						// ,success : function(out){
							// $.fancybox(
								// out,
							// {
								// padding     : 0,
								// openEffect  : "elastic",
								// openSpeed   : 350,
								// closeEffect : "elastic",
								// closeSpeed  : 350,
								// closeBtn    : false
							// });
						// }
					// });
				// });
				// </script>'
            // ;
        // }

        // $edicao .= '</div>';

        // $edicao .= '<div class="well">';
        // $edicao .= '<legend>Teaser</legend>';
        // $edicao .= textArea('item[infoadicional1]', $item->infoadicional1, '', 35, 4);
        // $edicao .= '</div>';

        print $edicao;

    }

    private function categorias(&$item){
        $edicao = '';
        $ident = 0;
        $categorias = results($sql="SELECT categoria.* FROM categoria WHERE categoria.st_lista_menu = 'S' ORDER BY ordem, nome");
        $especiais = results($sql="SELECT categoria.* FROM categoria WHERE categoria.st_lista_menu = 'N' ORDER BY ordem, nome");
        $itemcategorias = results($sql="SELECT id, categoria_id FROM itemcategoria WHERE item_id = {$item->id}");
        $edicao .= '<div class="well">';
        $edicao .= '<div class="row">';
        $edicao .= '<div class="col-sm-6">';
        $edicao .= '<legend>Categorias</legend>';
        $edicao .= $this->itemCategoriaProcessa(0, $ident, intval($item->id), $item, $itemcategorias, $categorias);
        $edicao .= '</div>';
        $edicao .= '<div class="col-sm-6">';
        $edicao .= '<legend>Categorias Especiais</legend>';
        $edicao .= $this->itemCategoriaProcessa(0, $ident, intval($item->id), $item, $itemcategorias, $especiais);
        $edicao .= '</div>';
        $edicao .= '</div>';
        $edicao .= '</div>';
        print $edicao;
    }

    private function caracteristicas(&$item){
        $edicao = '';


        if(modulo_gravacao::ATIVO){

            $edicao .= '<div class="well">';
            $edicao .= tag('legend','Gravação');
            $edicao .= '<div class="row"><div class="col-md-12"><ul style="list-style-type:none">';
            $query = query($sql=
            "
            SELECT
                caracvalor.*
                ,CASE WHEN itemcarac.id > 0 THEN 'checked' ELSE '' END checked
            FROM
                caracvalor
            LEFT JOIN itemcarac ON (
                caracvalor.id = itemcarac.caracvalor_id
            AND itemcarac.item_id = {$item->id}
            )
            WHERE
                caracvalor.carac_id = ".modulo_gravacao::CARAC_ID."
            ORDER BY
                caracvalor.nome
            "
            );

            while($fetch=fetch($query)){
                $gravacao = $fetch;
                $edicao .= "
                <li>
                    <label class='checkbox' for='caracvalor_2_{$gravacao->id}'>
                        <input type='checkbox' name='caracvalor[2][]' id='caracvalor_2_{$gravacao->id}' value='{$gravacao->id}' {$gravacao->checked} onclick='gravacao_idClick({$gravacao->id})'/>
                        {$gravacao->nome}
                    </label>
                </li>";
            }

            $edicao .= '</ul></div></div>';
            $edicao .= '</div>';
        }

        // if(modulo_variacaopreco::ATIVO){

            // $edicao .= '<div class="well">';
            // $edicao .= tag('legend', 'Variação de preço');
            // $edicao .= '<div class="row"><div class="col-md-12">';

            // $sql =
            // "
			// SELECT
				// variacaopreco.qtd_1
				// ,variacaopreco.qtd_2
				// ,ifnull(preco.preco,0) preco
				// ,preco.id
			// FROM
				// variacaopreco
			// LEFT OUTER JOIN preco ON (
                // preco.item_id = {$item->id}
            // AND preco.qtd_1 = variacaopreco.qtd_1
            // AND preco.qtd_2 = variacaopreco.qtd_2
			// )
			// ORDER BY
				// variacaopreco.qtd_1
				// ,variacaopreco.qtd_2
			// ";

            // $query = query($sql);

            // $i = 0;

            // $edicao .= '<table class="table table-bordered table-striped table-hover">';
            // $edicao .= '<tr>';

            // $edicao .= tag('th','Quantidade 1');
            // $edicao .= tag('th','Quantidade 2');
            // $edicao .= tag('th','Preço');

            // $edicao .= '</tr>';

            // $contador = 0;

            // while($fetch=fetch($query)){

                // $edicao .= inputHidden("preco[{$contador}][item_id]",$item->id) ;

                // $edicao .= tag('tr',
                    // tag('td', inputReadOnly("preco[{$contador}][qtd_1]",$fetch->qtd_1,'',20,60))
                    // .tag('td', inputReadOnly("preco[{$contador}][qtd_2]",$fetch->qtd_2,'',20,60))
                    // .tag('td', inputDecimal("preco[{$contador}][preco]",money($fetch->preco),'',20,60)));

                // $contador ++;
            // }

            // $edicao .= '</table>';
            // $edicao .= '</div></div>';
            // $edicao .= '</div>';
        // }


        print $edicao;
    }

    private function imagens(&$item){

        $edicao = '';

        $path_img = 'img/produtos/';
        $date = base64_encode(date("H:i:s"));

        $edicao .= '<div class="well">';
        $edicao .= '<legend class="alert alert-info" role="alert"><i class="glyphicon glyphicon-info-sign"></i> Tamanho máximo de imagem: 1000px x 1000px. </legend>';
        
        if($item->id){
           if($_SESSION["CADASTRO"]) $edicao .= '<p><a href="#" data-toggle="modal" data-target="#novaImagem" class="btn btn-default"><span class="glyphicon glyphicon-plus"></span> Nova imagem</a></p>';
        }
        $edicao .= '<table class="table table-bordered table-striped table-hover">';

        $variacoes = $item->id > 0 ? results($sql="
        SELECT item.*
        FROM item
         LEFT JOIN cor ON ( cor.id = item.cor_id )
        WHERE item.itemsku_id = ".intval($item->id)."
        ORDER BY cor.nome
        ") : array();

        $edicao .= $this->imagemitem($item, 'item', 'file', true, sizeof($variacoes)==0);

        if($item->id > 0){
            echo '<input type="hidden" name="variacao_id_excluir" id="variacao_id_excluir" value="0" />';
            foreach($variacoes as $variacao){
                $edicao .= $this->imagemitem($variacao, "variacao[{$variacao->id}]", "file_variacao_{$variacao->id}", false);
            }
        }

        $edicao .= '</table>';
        $edicao .= '</div>';

        ?>
        <div class="modal fade" id="novaImagem" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true"></span></button>
                        <h4 class="modal-title">Nova imagem</h4>
                    </div>
                    <div class="modal-body">
                        <table class="table table-bordered table-striped table-hover">
                            <input type="hidden" name="incluir_nova_variacao" id="nova_variacao" value="0" />
                        <?php
                        print $this->imagemitem(new item(), 'nova', 'file_nova', true, true);
                        ?>
                        </table>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-block btn-primary js-nova-variacao">Salvar</button>
                    </div>
                </div>
            </div>
        </div>
        <?php

        print $edicao;
    }

    private function imagemitem(&$item, $nome='item', $file='file', $principal = true, $altera_cor=true){

        $edicao = '';

        $path_img = 'img/produtos/';
        $date = base64_encode(date("H:i:s"));

        $imagem = 'imagem';

        $edicao .= '<tr>';
        $edicao .= '<td width="300px" class="text-center">';

        $campos = array(
            'imagem'
            // ,'imagem_d1'
            // ,'imagem_d2'
            // ,'imagem_d3'
            // ,'imagem_d4'
            // ,'imagem_d5'
            // ,'imagem_d6'
            // ,'imagem_d7'
            // ,'imagem_d8'
            // ,'imagem_d9'
            // ,'imagem_d10'
            // ,'imagem_d11'
            // ,'imagem_d12'
            // ,'imagem_d13'
            // ,'imagem_d14'
            // ,'imagem_d15'
            // ,'imagem_d16'
        );

        if($nome=='item'){
            $campos = array(
                'imagem'
                ,'imagem_d1'
                ,'imagem_d2'
                ,'imagem_d3'
                ,'imagem_d4'
                ,'imagem_d5'
                ,'imagem_d6'
                ,'imagem_d7'
            );
        }

        if($nome=='nova'){
            $campos = array(
                'imagem'
                // ,'imagem_d1'
                // ,'imagem_d2'
            );
        }

        foreach($campos as $campo){

            $i = preg_replace('/\D/', '',$campo);
            $i = $i == 0 ? 1 : $i + 1;

            if(@$item->$campo != ''){
               
                if(file_exists("img/produtos/".$item->$campo)){
                    $imagem_produto = PATH_SITE.$path_img.$item->$campo;
                }else{
                    $imagem_produto = PATH_IMG."produtos".DIRECTORY_SEPARATOR.$item->$campo;
                }
               

                $edicao .= tag('a target="_blank" href="'.PATH_SITE.$path_img.$item->$campo.'?a='.$date . '"'
                    ,tag("img class='js-alterar-imagem' data-imagem='img_{$file}_{$campo}' width='60px' src='".$imagem_produto."?a={$date}.'")
                );
            }
            else {
                $edicao .= "<a style='width:60px;height:60px;' class='btn btn-sm btn-default js-alterar-imagem' data-imagem='img_{$file}_{$campo}'><span class='glyphicon glyphicon-picture'></span> ({$i})</a> ";
            }

            $html_alterar_imagem = '';
            if(@$item->$campo != ''){
                $html_alterar_imagem .= "<p><strong>Alterar imagem {$i}</strong></p>";
            }
            else {
                $html_alterar_imagem .= "<p><strong>Selecione uma nova imagem {$i}</strong></p>";
            }
            $html_alterar_imagem .= inputFile("{$file}_{$campo}", '', '');
            if(@$item->$campo != ''){
                $html_alterar_imagem .= '<br><p class="text-danger"><input type="checkbox" name="excluir_imagem['.$item->id.'][]" value="'.$campo.'"/> Excluir</p>';
            }
            $html_alterar_imagem .= '<br><a href="#" onclick="cancelar_img()" data-imagem="img_'.$file.'_'.$campo.'"> Cancelar</a>';
            // $html_alterar_imagem = str_replace('"', '\"', $html_alterar_imagem);

            $edicao .= "<div id='img_{$file}_{$campo}' style='display:none' class='alert alert-info text-left js-file' data-html='{$html_alterar_imagem}'>";
            $edicao .= "</div>";
        }
        $edicao .= '&nbsp;</td>';

        $edicao .= '<td>';
        if($altera_cor){
            $edicao .= select("{$nome}[cor_id]",$item->cor_id,'Cor',cor::opcoes());
        }
        else {
            $cores = cor::opcoes();
            $edicao .= "<p>Cor principal: ".@$cores[$item->cor_id]."</p>";
        }
        $edicao .= "<div style='display:none' id='{$file}_{$imagem}_marca'>";
        $edicao .= checkbox("habilita_marca_dagua_{$imagem}", 1, 'Aplicar marca dagua?');
        $edicao .= "</div>";
        $edicao .= '&nbsp;</td>';

        $edicao .= '<td width="200px">';
        if(!$principal){
            $edicao .= select("{$nome}[st_ativo]", $item->st_ativo, 'Ativo?:', array('S'=>'Sim','N'=>'Nao'));
        }
        $edicao .= '&nbsp;</td>';

        $edicao .= '<td width="140px"><div class="text-left">';
        if(!$principal){
            $edicao .= radio("principal", $item->id, 'Principal?<br />');
        }
        $edicao .= '&nbsp;</div></td>';

        $edicao .= '<td width="140px"><div class="text-center">';
        if(intval($item->itemsku_id) > 0){
            // $edicao .= checkbox("imagem_excluir[]", $imagem, 'Excluir variação?<br />');
            $edicao .= '<a href="#" class="btn btn-sm btn-danger js-excluir-variacao" data-id="'.$item->id.'"><span class="glyphicon glyphicon-trash"></span></a>';
        }
        $edicao .= '&nbsp;</div></td>';

        $edicao .= '</tr>';

        return $edicao;
    }

    private function salva_categoria(&$item){

        $categoria_ids = request('categoria_id');

        $atualizados = array(0);
        foreach(is_array($categoria_ids) ? $categoria_ids : array() as $categoria_id){
            $itemcategoria = new itemcategoria();
            $itemcategoria->reset_vars();
            $itemcategoria->get_by_id(array('item_id'=>$item->id,'categoria_id'=>$categoria_id));
            if(!@$itemcategoria->id){
                $itemcategoria->categoria_id = $categoria_id;
                $itemcategoria->item_id = $item->id;
                $itemcategoria->salva();
            }
            $atualizados[] = $itemcategoria->id;
        }
        query('DELETE FROM itemcategoria WHERE item_id = '. $item->id . ' AND id NOT IN ('.join(',',$atualizados).')');
    }

    private function salva_caracteristica(&$item){

        $caracvalor = request('caracvalor');

        foreach(is_array($caracvalor) ? $caracvalor : array() as $carac_id => $arrcaracvalor_id){

            $atualizados = array(0);
            $gravacoes_atualizadas = array(0);
            foreach($arrcaracvalor_id as $caracvalor_id){

                $itemcarac = new itemcarac();
                $itemcarac->reset_vars();
                $itemcarac->get_by_id(array('carac_id'=>$carac_id, 'item_id'=>$item->id, 'caracvalor_id'=>$caracvalor_id));
                if(!@$itemcarac->id){
                    $itemcarac->carac_id = $carac_id;
                    $itemcarac->caracvalor_id = $caracvalor_id;
                    $itemcarac->item_id = $item->id;
                    $itemcarac->salva();
                }
                $atualizados[] = $itemcarac->id;
                $gravacoes_atualizadas[] = $caracvalor_id;
            }

            query('DELETE FROM itemcarac WHERE carac_id = '.$carac_id.' AND item_id = '. $item->id . ' and ID not in ('.join(',',$atualizados).')');
        }

    }

    private function salva_variacao_preco(&$item){

        $debug = false;

        // Salva variação de preço
        if(modulo_variacaopreco::ATIVO){

            $atualizados = array(0);
            foreach(is_array(@$_REQUEST['preco'])?$_REQUEST['preco']:array() as $id => $arrpreco){

                $preco = new preco(
                    array(
                        'item_id' => $item->id
                        ,'qtd_1' => $arrpreco['qtd_1']
                        ,'qtd_2' => $arrpreco['qtd_2']
                    )
                );

                if($debug){
                    printr($preco);
                    printr($item);
                }

                $preco->set_by_array($arrpreco);
                $preco->item_id = $item->id;
                $preco->preco = tofloat($preco->preco);

                $erro = array();
                if($preco->validaDados($erro)){
                   // $preco->preco = tofloat($preco->preco);
                    $preco->salva();
                    $atualizados[] = $preco->id;
                }
                else{
                    $_SESSION['erro'] = join('<br />', $erro);
                }
            }

            query('DELETE FROM preco WHERE item_id = '.$item->id.' and ID not in ('.join(',',$atualizados).')');

        }
    }

    private function excluir_imagem(){
        $excluir = request('excluir_imagem');
        foreach(is_array($excluir)?$excluir:array() as $id => $campos){
            $tmp = new item($id);
            foreach($campos as $campo) {
                if ($tmp->$campo) {
                    query("UPDATE item SET {$campo} = NULL WHERE id = {$id}");

                    // for($i=1;$i<=3;$i++){
                        if (file_exists("img/produtos/{$tmp->$campo}")){
                            unlink("img/produtos/{$tmp->$campo}");
                        }
                    // }
                }
            }
        }
    }

    public function itemCategoriaProcessa($id=0, &$ident=0, $item_id=0, &$item, &$itemcategorias, &$categorias){

        $results = array();
        foreach($categorias as $tmp){
            if(intval($tmp->categoria_id)==$id){
                $results[] = $tmp;
            }
        }

        $edicao = '';

        if(sizeof($results)>0){

            $edicao .= '<ul style="list-style-type:none">';
            for($i=0,$n=sizeof($results);$i<$n;$i++){

                $fetch = $results[$i];
                
                $checked = '';

                // Se estiver editado e ja tiver clicado no salvar
                if(request('categoria_id')){
                    $checked = in_array($fetch->id, $_REQUEST['categoria_id'])?'checked':'';
                }
                else {
                    foreach($itemcategorias as $itemcategoria){
                        if($itemcategoria->categoria_id == $fetch->id){
                            $checked = 'checked';
                            break;
                        }
                    }
                }

                $edicao .= '<li class="js-categoria js-categoria-'.$fetch->categoria_id.'" style="'.(intval($fetch->categoria_id)=='0'?'':'display:none').'">';
                $edicao .= '<label class="checkbox js-categoria js-categoria-'.$fetch->categoria_id.'" style="font-weight:normal;'.(intval($fetch->categoria_id)=='0'?'':'display:none').'">'.str_repeat('&nbsp;',0).' <input type="checkbox" '.($checked).' name="categoria_id[]" value="'.$fetch->id.'"> '.$fetch->nome.'</label>';

                $ident ++ ;
                $edicao .= $this->itemCategoriaProcessa($fetch->id, $ident, $item_id, $item, $itemcategorias, $categorias);
                $ident -- ;

                $edicao .= '</li>';

            }
            $edicao .= '</ul>';

        }

        return $edicao;

    }

    public function popula_original(){

        set_time_limit(0);
        ini_set('memory_limit', '-1');
        ob_implicit_flush(true);

        $dir = dir('img/produtos');

        while($file = $dir->read()){

            if($file[0]=='.'){
                continue;
            }

            if(is_file($path='img/produtos/'.$file)){

                $path_original = 'img/produtos/'.$file;

                if(!file_exists($path_original)){
                    copy($path, $path_original);
                }
            }
        }
        print 'Feito';
    }

    public function popula_marcadagua(){

        set_time_limit(0);
        ini_set('memory_limit', '-1');
        ob_implicit_flush(true);

        $dir = dir('img/produtos/');

        while($file = $dir->read()){

            if($file[0]=='.'){
                continue;
            }

            if(is_file($path_original='img/produtos/'.$file)){

                $path_marca_dagua = "img/produtos/marcadagua/{$file}";

                if(!file_exists($path_marca_dagua)){
                    try {
                        $si = new SimpleImage($path_original);
                        if($si){
                            $si->overlay('img/marcadagua.png', 'center', .7);
                            $si->save($path_marca_dagua, 100);
                        }
                    }
                    catch(Exception $ex){
                        print "<br>Problema imagem: ".$ex->getMessage();
                    }
                }
            }
        }
        print 'Feito';
    }

    public function atualiza_slug(){
        $arritens = results($sql="SELECT * FROM item");
        foreach($arritens as $tmpitem){
            $item = new item();
            $item->load_by_fetch($tmpitem);
            if($item->itemsku_id>0){
                $tmp = null;
                foreach($arritens as $pesquisa){
                    if($item->itemsku_id == $pesquisa->id){
                        $cor = new cor($item->cor_id);
                        $item->referencia = $pesquisa->referencia.'-'.$cor->referencia;
                        $item->nome = $pesquisa->nome.' '.$cor->nome;
                        $item->seo_keywords = $pesquisa->seo_keywords;
                        break;
                    }
                }
            }

            $item->salva();
            printr($item);
        }
    }

    public function seo($item){
        $opts = array();
        $opts["modelo"]    = "item";
        $opts["modelo_id"] = $item->id;
        $seopro = new seopro($opts);
        print $seopro->getEdit();
    }

}
