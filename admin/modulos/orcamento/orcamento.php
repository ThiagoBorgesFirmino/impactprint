<?php

class modulo_orcamento extends modulo_admin {

    public $arquivo = 'orcamento';

    // Pesquisa
    public function pesquisa(){

        if(request('popup')){
            $t = new TemplateAdminPopup('admin/tpl.admin-cadastro-generico.html');
        }
        else {
            $t = new TemplateAdmin('admin/tpl.admin-cadastro-generico.html');
        }

        if(request('action')=='editar'
            ||substr(request('action'),0,6)=='salvar'){
            $this->editar($t);
            return;
        }

        if(request('action')=='excluir'){
            $pedido = new pedido(intval(request('id')));
            $pedido->exclui();
        }

        if(request('action')=='pedidosAlterarPedidoStatus'){
            $pedido = new pedido(intval(request('id')));
            query($sql="UPDATE pedido SET pedidostatus_id = ".intval($_REQUEST['pedido']['pedidostatus_id']). " WHERE id = ". intval($pedido->id));
            printr($sql);
            $this->editar($t);
            return;
        }

        if(request('action')=='pedidosAlterarVendedor'){

            $pedido = new pedido(intval(request('id')));

            query($sql="UPDATE pedido SET vendedor_id = ".intval($_REQUEST['pedido']['vendedor_id'])." WHERE id = ". intval($pedido->id));
            query($sql="UPDATE cadastro SET cadastro_id = ".intval($_REQUEST['pedido']['vendedor_id'])." WHERE id = ". intval($pedido->cadastro_id));

            $pedido->vendedor_id = intval($_REQUEST['pedido']['vendedor_id']);

            $vendedor = new cadastro(intval($_REQUEST['pedido']['vendedor_id']));
            if($vendedor->id){
                if($_SERVER['SERVER_NAME']=='localhost'){
                    $email = new email();
                    $email->addTo('fgregorio@gmail.com');
                    $email->addHtml("Olá {$vendedor->nome},<br /> Há um novo orçamento para você. Confira no <a href='".config::get('URL')."admin.php/orcamento/'>admin</a>.");
                    $email->send("Novo Orçamento - ".config::get('EMPRESA'));
                }
                else {
                    $email = new email();
                    $email->addTo($vendedor->email);
                    $email->addHtml("Olá {$vendedor->nome},<br /> Há um novo orçamento para você. Confira no <a href='".config::get('URL')."admin.php/orcamento/'>admin</a>.");
                    $email->send("Novo Orçamento - ".config::get('EMPRESA'));
                }
            }

            $_SESSION['sucesso'] = tag("p","Representante atualizado.");
            $this->editar($t);
            return;
        }

        if(request('action')=='pedidosCriarProposta'){
            $enct = "";// json

            $pedido = new pedido(intval(request('id')));
            $proposta = new proposta();

            $proposta->pedido_id = $pedido->id;
            $proposta->propostastatus_id = 1;
            $proposta->html = '';

            $info = $_REQUEST['proposta'];
            $info['obs'] = nl2br($info['obs']);
            $info['obs_adicionais'] = nl2br($info['obs_adicionais']);
            $info['saudacao_personalizada'] = nl2br($info['saudacao_personalizada']);

            // Complementa dados iniciais da empresa usando as opcoes da configuracao
            $info['empresa_empresa'] = config::get('RAZAO_SOCIAL');
            $info['empresa_nome_fantasia'] = config::get('EMPRESA');
            $info['empresa_cnpj'] = config::get('CNPJ');
            $info['empresa_inscricao_estadual'] = config::get('IE');
            $info['empresa_logradouro'] = config::get('LOGRADOURO');
            $info['empresa_numero'] = config::get('NUMERO');
            $info['empresa_cidade'] = config::get('CIDADE');
            $info['empresa_uf'] = config::get('ESTADO');
            $info['empresa_cep'] = config::get('CEP');
            $info['empresa_telefone'] = config::get('TELEFONE');
            $info['empresa_email'] = config::get('EMAIL_CONTATO');
            $info['total'] = money($proposta->getTotalProposta($info));
            
            foreach($info['item'] as $key=>$value){               
                //$PATH_ABS = 'PATH_ABS';
                if( file_tratamento( "pedidoitem_aplicacao_".$value["pedidoitem_id"] , $msg, $file) ){
                    $path_aplicacao = "proposta/aplicacao/";
                    $path_aplicacao_relativo = "proposta/aplicacao/";
                    if(!is_dir($path_aplicacao))mkdir($path_aplicacao,777,true);
                    if(!isImagemJPG($file["name"])){$_SESSION["erro"] = tag("p","A imagem {$file['name']} precisa ser jpg.");$this->editar($t);return;}
                    //if(!isImagemComTamanho($file['tmp_name'], 1000,1000)){$_SESSION["erro"] = tag("p","A imagem {$file['name']} precisa ter tamanho 1000 x 1000 px.");$this->editar($t);return;}
                    // $aplicacao = $path_aplicacao.date("His").$file["name"];
                    list($width, $height) = getimagesize($file['tmp_name']);
				    if($width>1000 || $height>1000){
                        $_SESSION["erro"] = tag("p","A imagem {$file['name']} precisa ter tamanho menor 1000 x 1000 px.");
                        $this->editar($t);return;
                    }else{
                        $aplicacao = $path_aplicacao.($filename = $pedido->id.$key.trim($info['item'][$key]["referencia"]).".jpg");
                      
                        if(file_exists( $aplicacao  ) )unlink($aplicacao);
                        
                        $info['item'][$key]["aplicacao"] = config::get("URL").$path_aplicacao_relativo.$filename;
                        if(!copy($file["tmp_name"],$aplicacao)){
                            die("Erro ao tentar salvar imagem {$file['name']}.");
                        }
                    }

                }
            }
            
            $proposta->encpt_type = $enct;            
            
            if( $enct=="json" ) $proposta->info = json_encode($info);			
            else $proposta->info = serialize($info);		

            if(!$proposta->salva()){ 
                $_SESSION["erro"] = tag("p","Ocorreu um erro ao tentar criar proposta.");$this->editar($t);return;
            }
            
            $_SESSION['sucesso'] = tag('p', "Sua proposta foi criada, o numero dela é {$proposta->numero}, ela está na lista de propostas criadas");
            $this->editar($t);
            return;
        }

        if(request('action')=='pedidosEnviarProposta'){

            // Manda proposta para o cliente

            $pedido = new pedido(intval(request('id')));
            $proposta = new proposta(intval(request('proposta_id')));
            $cadastro = new cadastro($pedido->cadastro_id);
            $vendedor = new cadastro($pedido->vendedor_id);
            //printr($proposta);
            //printr($_REQUEST);
            //return;
            $proposta->data_envio = bd_now();
            if($pedido->pedidostatus_id == 1)
            {
                $pedido->pedidostatus_id = 2;
                $pedido->salva();
            }
            elseif($pedido->pedidostatus_id == 2){
                $pedido->pedidostatus_id = 3;
                $pedido->salva();
            }
            $proposta->salva();

            $e = new email();

            // Orcamento Enviado
            $e->addTo($cadastro->email, $cadastro->nome);
            $e->addReplyTo($vendedor->email, $vendedor->nome);
            $e->addCc($vendedor->email, $vendedor->nome);
            //$e->addBcc($this->config->EMAIL_ADMINISTRATIVO, $this->config->EMPRESA);

            // $e->addHtml($proposta->processa_html(true));
            $e->addHtml($proposta->processa_html_v2(true));
            $e->send("Proposta {$proposta->numero} Ref. Orçamento {$pedido->id}");

            $_SESSION['sucesso'] = tag('p', 'Sua proposta foi enviada para o email '.$cadastro->email);

           // Altera status do orcamento para proposta enviada
           $pedidostatus = new pedidostatus(array('descricao'=>'Proposta Enviada'));
           //printr($pedidostatus->id);
           if($pedidostatus->id){
               query("UPDATE pedido SET pedidostatus_id = {$pedidostatus->id} WHERE id = {$pedido->id}");
            }

            // Altera status da proposta
            $propostastatus = new propostastatus(array('descricao'=>'Aguardando resposta'));
            if($propostastatus->id){
                query("UPDATE proposta SET propostastatus_id = {$propostastatus->id} WHERE id = {$proposta->id}");
            }

            $t = new TemplateAdmin('admin/tpl.admin-cadastro-orcamento.html') ;
            $this->editar($t);
            return;
        }

        if(request('action')=='pedidosVerProposta'){

            $proposta = new proposta(intval(request('proposta_id')));

            print tag('div class="preview_action"',
                tag('a class="voltar" href="'.PATH_SITE.'admin.php/orcamento/?action=editar&id='.$proposta->pedido_id.'&pop=1"','voltar para o orçamento')
                .tag('a class="imprimir" href="javascript:window.print()"','imprimir'));

            // print $proposta->processa_html(true);
            print $proposta->processa_html_v2();
            die();
        }

        if(request('action')=='pedidosEditarProposta'){
            $proposta = new proposta(intval(request('proposta_id')));
            $this->setLocation('propostas/?action=editar&id='.$proposta->id.'&pop=1');
            // $proposta = new proposta(intval(request('proposta_id')));
            // print $proposta->html;
            die();
        }
        if(request('action')=='propostaExcluir'){
            $pedido = new pedido(intval(request('id')));
            $proposta = new proposta(intval(request('proposta_id')));
            $proposta->exclui();
            header('location:'.PATH_SITE.'admin.php/orcamento/?action=editar&id='.$pedido->id.'&pop=1');
        }

        if(request('action')=='pedidosAddPedidoItem'){
            
            $pedidoitem = new pedidoitem();
            $pedidoitem->set_by_array($_REQUEST['pedidoitem']);

            $item = new item($pedidoitem->item_id);
            if(request('cor_id')){
                $item = new item(request('cor_id'));
                $pedidoitem->item_id = $item->id;
            }

            $objs = array(
                new gravacao(intval(request('gravacao_id')))
            ,$item
            ,new materia_prima(intval(request('materia_prima_id')))
            ,new cor($item->cor_id)
            );

            $o = new stdClass();

            foreach ($objs as $obj){
                //printr($obj);
                $class_name = get_class($obj);
                foreach(get_object_vars($obj) as $key=>$value){
                    $p = $class_name.'_'.$key;
                    $o->$p = $obj->$key;
                }
            }


            foreach (is_array(@$_REQUEST['pedidoitem_info'])?$_REQUEST['pedidoitem_info']:array() as $key => $value){
                //printr($obj);
                $class_name = 'info';
                $p = $class_name.'_'.$key;
                $o->$p = $value;
            }
           
            $pedidoitem->info = serialize($o);

            $query = query("SELECT * FROM pedidoitem WHERE pedido_id = {$pedidoitem->pedido_id}");
            $propostaindice = rows($query);

            $pedidoitem->salva();

            foreach(get_class_vars(get_class($item)) as $key=>$value){
                if(!property_exists($pedidoitem,$key)){
                    $pedidoitem->$key = $item->$key;
                }
            }
            
            $itemPai = new item($item->itemsku_id);
            if(!$itemPai->id){
                $itemPai = $item;
            }

            $final = '';
            $resp = '';
            

            if($itemPai->largura){
                $resp .= ' Largura '.$itemPai->largura.PHP_EOL;
            }else{
                $resp .= '';
            }
            if($itemPai->altura){
                $resp .= ' Altura '.$itemPai->altura.PHP_EOL;
            }else{
                $resp .= '';
            }
            if($itemPai->profundidade){
                $resp .= ' Profundidade '. $itemPai->profundidade.PHP_EOL;
            }else{
                $resp .= '';
            }
            if(strlen($resp) <= 5){
               
            }else{
               $final .= ' Dimensões:'.PHP_EOL.$resp;
            }
            
             $cor = new cor($item->cor_id);
            // if($cor->id){
            //     $final .= ' Cor: '. $cor->nome.PHP_EOL;
            // }

            $descric = '';
            if($itemPai->descricao){
                $descric = 'Descrição: '.$itemPai->descricao;
            }else{
                $descric='';
            }

            $out = array();
            $absolute_path = PATH_SITE;
            if(file_exists($absolute_path."/img/produtos/{$item->imagem}")){
                $imagem_produto = PATH_SITE."img/produtos/{$item->imagem}";
            }else{
                $imagem_produto = PATH_IMG."produtos".DIRECTORY_SEPARATOR.$item->imagem;
            }


            $linha_info = '<tr id="linhaInfo_'.$pedidoitem->id.'">
				<td>'.$pedidoitem->referencia.'</td>
				<td><img src="'.PATH_SITE.'timthumb/timthumb.php?src='.$imagem_produto.'&w=80" /></td>
				<td>'.$item->nome.'</td>
				<td>'.$pedidoitem->getInfoHtml().' </td>
				<td>
					QTD: '.$pedidoitem->item_qtd.'<br />
				<!--	QTD2: '.$pedidoitem->item_qtd2.' -->
				</td>
				<td><a href="javascript:delPedidoItem('.$pedidoitem->id.')">Excluir</a></td>
			</tr>';

            $linha_edit = '<tr id="linhaEdit_'.$pedidoitem->id.'" data-row="'.$propostaindice.'">
				<td><input size="7" type="text" name="proposta[item]['.$propostaindice.'][referencia]" value="'.$item->referencia.'"/></td>
				<td><img src="'.PATH_SITE.'timthumb/timthumb.php?src='.$imagem_produto.'&w=80" /></td>
				<td><input size="15" type="text" name="proposta[item]['.$propostaindice.'][nome]" value="'.$itemPai->nome.'"/></td>
				<td>
					<textarea name="proposta[item]['.$propostaindice.'][descricao]" cols="25" rows="6"> '.$descric.'
'.$final.'
                    '.$pedidoitem->getInfoTxt().' 
					</textarea>
                </td>
                <td>
                    <input size="10" type="text" name="proposta[item]['.$propostaindice.'][item_qtd]" value="'.$pedidoitem->item_qtd.'" onkeyup="updateSubTotal(this.parentNode.parentNode,\'\')" /><br />
                    <input size="10" type="text" name="proposta[item]['.$propostaindice.'][item_qtd2]" value="'.$pedidoitem->item_qtd2.'" onkeyup="updateSubTotal(this.parentNode.parentNode,\'2\')" /><br />
                    <input size="10" type="text" name="proposta[item]['.$propostaindice.'][item_qtd3]" value="'.$pedidoitem->item_qtd3.'" onkeyup="updateSubTotal(this.parentNode.parentNode,\'3\')" /><br />
                  
				</td>
				<td>
					<!-- <a class="tabelaInfo" href="'.PATH_SITE.'admin.php/tabelaInfo/'.$item->id.'" > Tabela(info) </a> -->
                    <input size="10" type="text" name="proposta[item]['.$propostaindice.'][preco]" value="'.$item->getPrecoFormatado().'" onkeypress="return formataMoeda(this,event);" onkeyup="updateSubTotal(this.parentNode.parentNode,\'\')" /><br />
                     
                    <input size="10" type="text" name="proposta[item]['.$propostaindice.'][preco2]" value="'.$item->getPrecoFormatado().'" onkeypress="return formataMoeda(this,event);" onkeyup="updateSubTotal(this.parentNode.parentNode,\'2\')" /><br />

                    <input size="10" type="text" name="proposta[item]['.$propostaindice.'][preco3]" value="'.$item->getPrecoFormatado().'" onkeypress="return formataMoeda(this,event);" onkeyup="updateSubTotal(this.parentNode.parentNode,\'3\')" /><br />
				</td>
				
				<td>
                    <input size="10" type="text" name="proposta[item]['.$propostaindice.'][sub_total]" readonly value="'.$pedidoitem->info_sub_total.'" /><br />
                    <input size="10" type="text" name="proposta[item]['.$propostaindice.'][sub_total2]" readonly value="'.$pedidoitem->info_sub_total2.'" /><br />
                    <input size="10" type="text" name="proposta[item]['.$propostaindice.'][sub_total3]" readonly value="'.$pedidoitem->info_sub_total3.'" /><br />

					<input type="hidden" name="proposta[item]['.$propostaindice.'][imagem]" value="'.PATH_SITE.'timthumb/timthumb.php?src='.$imagem_produto.'&w=120" />
					<input type="hidden" name="proposta[item]['.$propostaindice.'][item_id]" value="'.$item->id.'" />
                    <input type="hidden" name="proposta[item]['.$propostaindice.'][gravacao_id]" value="'.$pedidoitem->gravacao_id.'" />
                    <input type="hidden" name="proposta[item]['.$propostaindice.'][gravacao_nome]" value="'.$pedidoitem->gravacao_nome.'" />
                </td>
                
                <td>
                <input type="hidden" name="proposta[item]['.$propostaindice.'][pedidoitem_id]" value="'.$pedidoitem->id.'" />
                <div class="pedidoitem_aplicacao" id="pedidoitem_aplicacao_container_'.$pedidoitem->id.'" style="text-align:center;width:100px;">
                    <label for="pedidoitem_aplicacao_'.$pedidoitem->id.'" style="cursor:pointer;">
                        <img src="'.PATH_SITE.'img/assets/upload_icon.png" style="width:20%;" />
                    </label>
                    <input onchange="javascript:insereImagem(this,"'.$pedidoitem->id.'" );" type="file" name="pedidoitem_aplicacao_'.$pedidoitem->id.'" id="pedidoitem_aplicacao_'.$pedidoitem->id.'" style="display:none;" /> 
                </div>
            </td>
            <td><a href="javascript:delPedidoItem('.$pedidoitem->id.')">Excluir</a></td>
            </tr>
            <sriptp>
            
    function insereImagem(obj,id){
        $(".nome_imagem_"+id).remove();
        $("#pedidoitem_aplicacao_container_"+id).append("<p class="nome_imagem_"+id+"">"+obj.files[0].name+"</p>");
    }

            </script>';

            $out['status'] = 1;
            $out['linha_info'] = $linha_info;
            $out['linha_edit'] = $linha_edit;
            $out['script'] = $propostaindice>=1?'':'location.reload()';
            echo json_encode($out);
            die();
        }

        if(request('action')=='pedidosProposta2Venda'){

            $proposta = new proposta(intval(request('proposta_id')));

            $pedido = new pedido($proposta->pedido_id);

            $propostavenda = new propostavenda();

            // Copia propriedades da proposta para propostavenda
            foreach(get_object_vars($proposta) as $key=>$value){
                $propostavenda->$key = $value;
            }

            $info = unserialize($propostavenda->info);

            $itens = array();

            foreach($proposta->itens() as $item){
                $i = sizeof($itens);
                $itens[$i] = $item;
            }

            // printr($info);
            $info['data_emissao'] = date('d/m/Y');
            //unset($info['prazo_entrega']);
            // printr($info);
            // die();

            $info['personalizado'] = $pedido->personalizado;

            $info['item'] = $itens;

            $info = serialize($info);

            $_SESSION['propostavenda'] = $propostavenda;
            $_SESSION['propostavenda']->setInfo($info);

            $this->setLocation('propostas2venda/?action=editar&id='.$proposta->id.'&pop=1');
            // $proposta = new proposta(intval(request('proposta_id')));
            // print $proposta->html;
            die();
        }

        if(request('action')=='delPedidoItem'){
            $pedidoitem = new pedidoitem(intval(request('pedidoitem_id')));
            $id = $pedidoitem->id;
            $pedidoitem->exclui();

            $out['status'] = 1;
            $out['msg'] = "Produto excluído com sucesso.";
            $out['id'] = $id;
            echo json_encode($out);
            die();
        }

        if(request('action')=='atualizaObservacao'){
            $cont = $_REQUEST['cont'];

            $id = $_REQUEST['itemId_'.$cont];

            $item = new item($id);

            $fornecedor_1 = $_REQUEST['fornecedor_1_'.$cont];
            $codigo_1 = $_REQUEST['codigo_1_'.$cont];
            $preco_1 = $_REQUEST['preco_1_'.$cont];
            $data_1 = $_REQUEST['data_1_'.$cont];

            $fornecedor_2 = $_REQUEST['fornecedor_2_'.$cont];
            $codigo_2 = $_REQUEST['codigo_2_'.$cont];
            $preco_2 = $_REQUEST['preco_2_'.$cont];
            $data_2 = $_REQUEST['data_2_'.$cont];

            $fornecedor_3 = $_REQUEST['fornecedor_3_'.$cont];
            $codigo_3 = $_REQUEST['codigo_3_'.$cont];
            $preco_3 = $_REQUEST['preco_3_'.$cont];
            $data_3 = $_REQUEST['data_3_'.$cont];

            $item->fornecedor_1 = $fornecedor_1;
            $item->codigo_1 = $codigo_1;
            $item->preco_1 = $preco_1;
            $item->data_1 = $data_1;

            $item->fornecedor_2 = $fornecedor_2;
            $item->codigo_2 = $codigo_2;
            $item->preco_2 = $preco_2;
            $item->data_2 = $data_2;

            $item->fornecedor_3 = $fornecedor_3;
            $item->codigo_3 = $codigo_3;
            $item->preco_3 = $preco_3;
            $item->data_3 = $data_3;

            $item->salva();

            $t = new TemplateAdmin('admin/tpl.admin-cadastro-orcamento.html') ;
            $this->editar($t);

            return;
        }

        if(request('action')=='verCliente'){

            $_REQUEST['id'] = intval(request('cadastro_id'));
            $_REQUEST['action'] = 'editar';

            $this->clientes();
            return;
        }

        $t->h1 = h1($this->modulo->nome);

        $grid = new grid();

        $grid->sql = $sql =
            "
			SELECT
				pedido.id
				,pedido.data_cadastro data
				,cadastro.empresa
				,cadastro.nome contato
				,vendedor.nome representante
				,pedidostatus.descricao status
				,pedidoorigem.descricao origem
				,(select concat(max(data_envio)) from proposta where proposta.pedido_id = pedido.id) envio_proposta
				,(select concat(pedido.id,'-',max(numero)) from proposta where proposta.pedido_id = pedido.id) proposta
			FROM
				pedido
			INNER JOIN cadastro ON (
				pedido.cadastro_id = cadastro.id
			)
			LEFT OUTER JOIN comoconheceu ON (
				cadastro.comoconheceu_id = comoconheceu.id
			)
			LEFT OUTER JOIN pedidoorigem ON (
				pedido.pedidoorigem_id = pedidoorigem.id
			)
			INNER JOIN cadastro AS vendedor ON (
				pedido.vendedor_id = vendedor.id
			)
			INNER JOIN pedidostatus ON (
				pedido.pedidostatus_id = pedidostatus.id
			)
			".($_SESSION['CADASTRO']->tipocadastro_id==tipocadastro::getId('VENDEDOR')
                ?" AND vendedor_id = ".decode($_SESSION['CADASTRO']->id)." "
                :""	)."
			ORDER BY
				pedido.id DESC
			";

        $grid->sql = $sql;

        $filtro = new filtro();

        $filtro->add_input('id','Num. Pedido');
        $filtro->add_input('empresa','Empresa');
        $filtro->add_input('contato','Contato');
        // $filtro->add_select( "status", "Status do Orçamento", pedidostatus::opcoes(array("placeholder"=>"SELECIONAR STATUS")),false);
        // if(projeto::getLogado()->tipocadastro_id==tipocadastro::getId("Administrativo")){
        //     $filtro->add_select( "representante_id", "Representante", cadastro::opcoesVendedor(array("placeholder"=>"SELECIONAR REPRESENTANTE"),false));
        // }
        // $filtro->add_select( "origem", "Origem", pedidoorigem::opcoes(array("placeholder"=>"SELECIONAR ORIGEM")),false);
        $filtro->add_periodo('data','Per&iacute;odo');
        $grid->titulo_filtro = 'status,representante,origem';

        $grid->orderby_custom = " id DESC ";

        $grid->metodo = $this->arquivo;
        $grid->filtro = $filtro ;

        $edicao = '';

        //$edicao .= $this->boxExpExcel($sql,'Orçamentos',$filtro);
        $edicao .= $grid->render();

        $t->edicao = $edicao;

        $this->adm_instance->montaMenu($t);
        $this->adm_instance->show($t);
    }

    public function editar($t=null){

        // Se for pop-up, carrega template de pop-up
        if(request('pop')){
            $t = new TemplateAdminPop('admin/modulos/orcamento/tpl.orcamentoadmin.html');
        }else{
            $t = new TemplateAdminPop('admin/modulos/orcamento/tpl.orcamentoadmin.html');
        }

        $t->h1 = h1($this->modulo->nome);

        $pedido = new pedido(intval(request('id')));

        if(!$pedido->id){
            return $this->novo();
            // header('location:'.PATH_SITE.'admin.php/orcamentosNovo/?pop=1');
            // $this->pedidosNovo($t);
            // die();
        }

        if(substr(request('action'),0,6)=='salvar'){

            if(trim(@$next)!=''){
                $this->afterSave($next,$this->arquivo);
                return;
            }
        }

        if(request('action')=='sair'){
            $this->afterSave('sair',$this->arquivo,$orcamento);
        }
        
        $cadastro = new cadastro($pedido->cadastro_id);
        $vendedor = new cadastro($pedido->vendedor_id);
        $pedidostatus = new pedidostatus($pedido->pedidostatus_id);
        
        $t->pedido = $pedido;
        $t->cadastro = $cadastro;
        $t->vendedor = $vendedor;
        $t->pedidostatus = $pedidostatus;
      
        // OPCOES DE PROPOSTASTATUS
        foreach(pedidostatus::opcoes() as $id=>$descricao){
            $t->list_pedidostatus = new pedidostatus($id);
            $t->parseBlock('BLOCK_LIST_PEDIDOSTATUS', true);
        }

        // OPCOES DE VENDEDOR
        foreach(cadastro::opcoesVendedor() as $id=>$nome){
            $t->list_vendedor = new cadastro($id);
            $t->parseBlock('BLOCK_LIST_VENDEDOR', true);
        }

        if($_SESSION['CADASTRO']->tipocadastro_id == tipocadastro::getId('ADMINISTRATIVO')){
            $t->parseBlock('BLOCK_ALTERAR_VENDEDOR');
        }

        // Parse itens do pedido
        $i=0;
        $temItem = false;
        $cont=0;
        $absolute_path = getcwd();
        foreach($pedido->get_childs('pedidoitem') as $pedidoitem){
            $item = $pedidoitem->get_parent('item');
            
            if(file_exists($absolute_path."/img/produtos/{$item->imagem}")){
               $t->imagem_produto = PATH_SITE."timthumb/timthumb.php?src=".PATH_SITE."img/produtos/".$item->imagem."&w=80";
            }else{
                $t->imagem_produto = PATH_IMG."produtos/".$item->imagem."?w=80";
            }
            
            foreach(get_class_vars(get_class($item)) as $key=>$value){
                if(!property_exists($pedidoitem,$key)){
                    $pedidoitem->$key = $item->$key;
                }
            }

           
            if($pedidoitem->item_qtd2 == null){
                $pedidoitem->item_qtd2 = @unserialize($pedidoitem->info)->item_qtd2;
            }
          
            $final = '';
            $resp = '';
            
            $itemPai = new item($item->itemsku_id);
           
            if(!$itemPai->id){
                $itemPai = $item;
            }

            if($itemPai->largura){
                $resp .= ' Largura: '.$itemPai->largura.PHP_EOL;
            }else{
                $resp .= '';
            }
            if($itemPai->altura){
                $resp .= ' Altura: '.$itemPai->altura.PHP_EOL;
            }else{
                $resp .= '';
            }
            if($itemPai->profundidade){
                $resp .= ' Profundidade: '. $itemPai->profundidade;
            }else{
                $resp .= '';
            }
            if(strlen($resp) <= 5){
               
            }else{
               $final .= ' Dimensões:'.PHP_EOL.$resp;
            }

            $descric = '';
            if($itemPai->descricao){
                $descric = 'Descrição: '.$itemPai->descricao;
            }else{
                $descric='';
            }
            //printr($pedidoitem);
            $t->descricao = $descric;
            $t->dimensao = $final;
            $t->list_pedidoitem = $pedidoitem ;

            $cont ++;
            // $t->cont = $cont;
            $t->propostaindice = $i ++;
            $t->parseBlock('BLOCK_LIST_PEDIDOITEM', true);

            $t->parseBlock('BLOCK_LIST_PEDIDOITEMPROPOSTA', true);
            $t->parseBlock('BLOCK_LIST_PEDIDOITEMPROPOSTA_SCRIPT', true);
            $temItem = true;
        }

        if($temItem){
            $t->parseBlock('BLOCK_ITENS');

            foreach(explode("\n",$this->config->PADRAO_OPCAO_FRETE) as $opcao_frete){
                // $t->opcao_frete = $opcao_frete;]
                $t->opcao_frete = str_replace(array("<br />","\n","\r"),"", $opcao_frete);
                $t->parseBlock('BLOCK_OPCAO_FRETE', true);
            }

            foreach(explode("\n",$this->config->PADRAO_FORMA_PAGAMENTO) as $forma_pagamento){
                // $t->opcao_frete = $opcao_frete;]
                $t->opcao_forma_pagamento = str_replace(array("<br />","\n","\r"),"", $forma_pagamento);
                $t->parseBlock('BLOCK_FORMA_PAGAMENTO', true);
            }

            foreach(explode("\n",$this->config->PADRAO_PRAZO_ENTREGA) as $prazo_entrega){
                // $t->opcao_frete = $opcao_frete;]
                $t->opcao_prazo_entrega = str_replace(array("<br />","\n","\r"),"", $prazo_entrega);
                $t->parseBlock('BLOCK_PRAZO_ENTREGA', true);
            }

            foreach(explode("\n",$this->config->PADRAO_VALIDADE_PROPOSTA) as $validade_proposta){
                $t->opcao_validade_proposta = str_replace(array("<br />","\n","\r"),"", $validade_proposta);
                $t->parseBlock('BLOCK_VALIDADE_PROPOSTA', true);
            }

            if($this->config->HABILITA_VARIACAO_PRECO=='S'){
                $t->parseBlock('BLOCK_OPCAO_PRECO_SUGESTAO');
            }
            $t->parseBlock('BLOCK_CRIARPROPOSTA');
        }

        if($pedido->anexo!=''){
            $t->parseBlock('BLOCK_ANEXO');
        }
        if($pedido->obs!=''){
            $pedido->obs = nl2br($pedido->obs);
            $t->parseBlock('BLOCK_OBSERVACOES');
        }

        // PARSE PROPOSTAS DO PEDIDO
        $i=0;
        $temProposta = false;
        foreach($pedido->get_childs('proposta','','ORDER BY id DESC') as $proposta){
            $t->list_proposta = $proposta;
            $t->propostaindice = $i;
            $t->parseBlock('BLOCK_LIST_PROPOSTA', true);
            $temProposta = true;
            $i++;
        }
        if($temProposta){
            $t->parseBlock('BLOCK_PROPOSTAS');
        }

        $opts = array(
            'include_js' => PATH_SITE."admin/modulos/{$this->arquivo}/orcamentoadmin.js?".time()
        );

        $this->adm_instance->show($t, $opts);
    }

    public function novo(){

        if(request('email')){

            try {

                $cadastro = new cadastro(array('email'=>request('email'),'tipocadastro_id'=>tipocadastro::getId('CLIENTE')));

                if(!$cadastro->id){
                    //throw new Exception('Cadastro não encontrado');
                    $cadastro = new cadastro();
                    $cadastro->email = request('email');
                    $cadastro->st_ativo = 'S';
                    $cadastro->tipocadastro_id = tipocadastro::getId('CLIENTE');
                    if($_SESSION['CADASTRO_ADMIN']->tipo_cadastro == 1 || $_SESSION['CADASTRO_ADMIN']->tipo_cadastro== 3){
                        $cadastro->cadastro_id = decode($_SESSION['CADASTRO_ADMIN']->id);
                    }else{
                        $cadastro->cadastro_id = cadastro::vendedorPadrao();
                    }
                   
                    $cadastro->salva();
                }

                if(intval(request('pedidoorigem_id'))==0){
                    throw new Exception('Selecione a origem do pedido');
                }

                // Se o usuario logado for um vendedor, o cliente precisa ser dele
                if($_SESSION['CADASTRO']->tipocadastro_id==tipocadastro::getId('VENDEDOR')){
                    if($cadastro->cadastro_id == decode($_SESSION['CADASTRO']->id)){
                        // Prossegue
                        $pedido = new pedido();
                        $pedido->cadastro_id = $cadastro->id;
                        $pedido->vendedor_id = decode($_SESSION['CADASTRO']->id);
                        $pedido->pedidostatus_id = 1; // Lancado
                        $pedido->pedidoorigem_id = request('pedidoorigem_id');

                        $pedido->salva();
                        $_SESSION['sucesso'] = tag('p','Orçamento criado');
                        header('location:'.PATH_SITE."admin.php/orcamento/?id={$pedido->id}&action=editar");
                        die();
                    }
                    else {
                        throw new Exception('Este cliente não está associado ao seu cadastro');
                    }
                }
                // Nao é vendedor, acesso geral
                else {
                    $pedido = new pedido();
                    $pedido->cadastro_id = $cadastro->id;
                    $pedido->vendedor_id = cadastro::vendedorPadrao();
                    $pedido->pedidostatus_id = 1; // Lancado
                    $pedido->pedidoorigem_id = request('pedidoorigem_id');

                    // printr($pedido);
                    // die();

                    if(!$pedido->salva()){
                        printr(mysql_error(mysql_connect(BD_HOST, BD_USER, BD_PASS)));
                        die();
                    }

                    // printr($pedido);
                    // die();

                    $_SESSION['sucesso'] = tag('p','Orçamento criado');
                    header('location:'.PATH_SITE."admin.php/orcamento/?id={$pedido->id}&action=editar&pop=1");

                }
            }
            catch (Exception $ex){
                $_SESSION['erro'] = $ex->getMessage();
            }
        }else{
           if(request("enviar_novo")) $_SESSION["erro"] = tag("p","Digite um e-mail válido.");
        }

        $t = new TemplateAdmin('admin/tpl.admin-cadastro-generico.html');
        if(request('pop')){
            $t = new TemplateAdminPop('admin/tpl.admin-cadastro-generico.html');
        }
        $t->h1 = h1('Criar novo pedido de orçamento');

        $edicao = inputHidden("enviar_novo",1);
        $edicao .= inputSimples('email',request('email'),'Digite o e-email do cliente', 60, 100," onkeypress='javascript:verificaEnter(event);'");
        $edicao .= select('pedidoorigem_id',request('pedidoorigem_id'),'Origem do pedido', pedidoorigem::opcoes_admin());
        $edicao .= "<script> 
            function verificaEnter(event){
				event = event || window.event;
				var key = event.keyCode || event.which;
                if (key==13 || key==13) {
                    event.preventDefault();
                    return false;
                }
                return true;
            }
        </script>";
        $t->edicao = $edicao;

        $this->adm_instance->montaMenuSimples($t);
        $this->adm_instance->show($t);

    }

}