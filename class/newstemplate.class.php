<?php

class newstemplate extends base {

	var
		$id
		,$st_ativo
		,$nome
		,$html;

	static function opcoes(){

		$return = array();
		$query = query($sql="select * from newstemplate order by nome");
		while($fetch=fetch($query)){
			$return[$fetch->id] = $fetch->nome;
		}
		return $return;

	}
	
	
	public function updateNewsComunicado(){
		$htmlComunicado = '		
		<html>
			<head>
				<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1" />
				<title>{config->EMPRESA}</title>

			</head>


			<body>
				<center>
					<div style="width:600px;border:0px solid; padding:0 10px 18px 10px;">

						<table width="566px;" cellpadding="0" cellspacing="0">
							
							<tr>
								<td rowspan="2" width="75px">
									<img src="{config->URL}/img/logo-site.jpg" style="float:left;" />
								</td>
								<td style="text-align:right; padding-top:10px;" valign="top">
									<font color="#4d9eb0" face="verdana">
										<strong style="font-size:14px">
											<a href="mailto:{config->EMAIL_CONTATO}" style="font-size:14px; font-family:verdana; color:#4d9eb0;">{config->EMAIL_CONTATO}</a><br />{config->TELEFONE}
										</strong>
									</font>
								</td>
							</tr>
							
							<tr>
								<td valign="bottom">		
									<font color="#cce1e5" face="verdana">
										<strong style="font-size:30px">
									{textolinha->titulo}
										</strong>
									</font>
								</td>
							</tr>
							
						</table>
						
						
						<div style="border:8px solid #cce1e5; width:540px; padding:5px;">
							<!-- 
							{config->URL_COMPACTADA} -->
							<font color="#4d9eb0" face="verdana" style="font-size:14px">
								{textogrande->chamada}
							</font>
							<br />
							<!-- BEGIN BLOCK_IMAGEM_1 -->

							<a href=\"{imagem->href_1}\">
							<img border=\"0\" src=\"{imagem->src_1}\" /></a>

							<!-- END BLOCK_IMAGEM_1 -->

							<!-- {config->TELEFONE} -->
							
						
						</div>
					</div>
				</center>
			</body>
		</html>	';

		query("update newstemplate set html = '{$htmlComunicado}' where id = 2");
	}
	
	public function updateNewsProduto(){	
		$htmlProduto='
			<html>
			<head>
				<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1" />
				<title> {config->EMPRESA}</title>
			</head>
			<style>
			*{
				font-family:Arial, Helvetica, sans-serif;
				padding:0px;
				margin:0px;
			}
			
			.d-container{
				width:600px;
				margin: auto;
				background-color:#f5f9fa;
				border:2px solid #4d9eb0;
				padding:3px;
			}
			
			table {
				width: 600px;
			}
				
			.s-topo{
				color: #{config->TELEFONE_EMAIL};
				font-weight:bold;
				font-size:16px;
				text-align:right;
				float:right;
			}
			
			.s-topo a{
				text-decoration:none;
				color:#{config->TELEFONE_EMAIL};
			}
			
			.s-topo a:hover{
				text-decoration: underline;
			}
			
			.s-titulo{
				background-color:#4d9eb0;
				float:left;
				width:100%;
				height:40px;
			}
			
			.s-titulo h1{
				color:#fff;
				font-size:20px;
				margin-top:5px;
				padding-left:10px;
				
			}
			
			.s-sub-titulo h2{
				color:#{config->SUB_TITULO};
				font-size:18px;
				margin-bottom:20px;
				padding-left:10px;
			}
			
			.d-produto{
				float:left;
				border:#000000 0px solid;
				width:195px;
				text-align:center;
			}
			
			.d-produto a {
				color: #FFFFFF;
				float: left;
				font-size: 12px;
				font-weight: bold;
				margin-bottom: 20px;
				margin-left: 10px;
				margin-top: 15px;
				padding-top: 5px;
				text-decoration: none;
				width: 175px;
			}
			
			.s-prd-titulo {
				color: #363636;
				float: left;
				font-size: 15px;
				font-weight: bold;
				height: 35px;
				width: 180px;
			}
			
			.d-produto p{
				color:#363636;
				font-size:12px;
				font-weight:bold;
				width:160px;
				margin:auto;
				margin-top:10px;
			}
			
			.d-produto a.veja_mais{
				float:left;
				width:175px;
				color: #ffffff;
				font-size:12px;
				font-weight:bold;
				text-decoration:none;
				margin-top: 15px;
				margin-left: 10px;
				margin-bottom: 20px;
				padding-top: 5px;
				background-color:#4d9eb0;
				/*background-color:#{config->BACKGROUND};*/
				
			}
			
			.d-produto a img{
				border:#FFFF66 solid 0px;
				width: 160px;
				height: 160px;
			}
			
			.s-prd-titulo{
				color:#{config->TITULO_PRODUTO};
				font-size:15px;
				font-weight:bold;
				
			}
			.rodape{
				background-color:#4d9eb0;
			}
			.s-rodape-l{
				float:left;
				color:#000;
				font-size:13px;
				font-weight: bold;
				
				
			}
			
			.s-rodape-r{
				float:right;
			}
			
			.s-rodape-r a{
				color:#000;
				font-size:13px;
				font-weight: bold;
				text-decoration: none;
			}
		</style>

		<body>
			<div class="d-container">
				<table>
					<tr>
						<td style="width:270px">
							<img src="{config->URL}/img/logo-site.jpg" />
						</td>
						<td> 
							<span class="s-topo" style="margin-top:58px;">
								{config->TELEFONE}
							</span><br clear="all" />
							<span class="s-topo">    
								<a href="{config->URL}">{config->URL}</a>
							</span>
						</td>
					</tr>
					<tr>
						<td colspan="2">
							<span class="s-titulo">
								<h1>{textolinha->titulo}</h1>
							</span>
						</td>
					</tr>
					<tr>
						<td colspan="2">
							<span class="s-sub-titulo">
								<h2>{textogrande->chamada}</h2>
							</span>
						</td>
					</tr>
					<tr>
						<td colspan="2" style="width:195px;">
							<table>
								<tr>
									<td style="width:195px;">
										<div class="d-produto">
											<a href="{item_link_1}"><img src="{item_imagem_int_1}"></a>
											<br clear="all">
											<span class="s-prd-titulo">{item_nome_1}</span>
											<span class="s-prd-titulo">R$ {item_preco_1}</span>
											<a href="{item_link_1}" class="veja_mais">Veja Mais</a>
										</div>
									</td>
									<td style="width:195px;">
										<div class="d-produto">
											<a href="{item_link_2}"><img src="{item_imagem_int_2}"></a>
											<br clear="all">
											<span class="s-prd-titulo">{item_nome_2}</span>
											<span class="s-prd-titulo">R$ {item_preco_2}</span>
											<a href="{item_link_2}" class="veja_mais">Veja Mais</a>
										</div>
									</td>
									<td style="width:195px;">
										<div class="d-produto">
											<a href="{item_link_3}"><img src="{item_imagem_int_3}"></a>
											<br clear="all">
											<span class="s-prd-titulo">{item_nome_3}</span>
											<span class="s-prd-titulo">R$ {item_preco_3}</span>
											<a href="{item_link_3}" class="veja_mais">Veja Mais</a>
										</div>
									</td>
								 </tr>      
								 <tr>
									<td style="width:195px;">
										<div class="d-produto">
											<a href="{item_link_4}"><img src="{item_imagem_int_4}"></a>
											<br clear="all">
											<span class="s-prd-titulo">{item_nome_4}</span>
											<span class="s-prd-titulo">R$ {item_preco_4}</span>
											<a href="{item_link_4}" class="veja_mais">Veja Mais</a>
										</div>
									</td>
									<td style="width:195px;">
										<div class="d-produto">
											<a href="{item_link_5}"><img src="{item_imagem_int_5}"></a>
											<br clear="all">
											<span class="s-prd-titulo">{item_nome_5}</span>
											<span class="s-prd-titulo">R$  {item_preco_5}</span>
											<a href="{item_link_5}" class="veja_mais">Veja Mais</a>
										</div>
									</td>
									<td style="width:195px;">
										<div class="d-produto">
											<a href="{item_link_6}"><img src="{item_imagem_int_6}"></a>
											<br clear="all">
											<span class="s-prd-titulo">{item_nome_6}</span>
											<span class="s-prd-titulo">R$ {item_preco_6}</span>
											<a href="{item_link_6}" class="veja_mais">Veja Mais</a>
										</div>
									</td>
								 </tr>      
							</table>
						</td>
					</tr>
					<tr>
						<td colspan="2" class="rodape">
							<span class="s-rodape-l">
								{config->TELEFONE}&nbsp;/&nbsp;(11) 5669-3678
							</span>
							<span class="s-rodape-r">
								<a href="mailto:{config->EMAIL_CONTATO}">{config->EMAIL_CONTATO}</a>
							</span>
						</td>
					</tr>
				</table>
			</div>
		</body>
		</html>
		';
		query("update newstemplate set html = '{$htmlProduto}' where id = 3");
	}
	
	
}

?>