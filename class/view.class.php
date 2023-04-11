<?php

abstract class view{

	private	$form = array('name' => "form1",'id' => "form1",'method' => "post",'action' => "a",'target' => "_self",'extra' => " ");

	private $action;
	public $erros;
	public $sucessos;

	function __construct(){
		$this->serFormAttr('action', $_SERVER['PHP_SELF']);
		$this->action = request('action');
		$this->callMethod();
	}

	abstract function body();

	public function head(){
		?>
		<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
 "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
		<html>
			<head>
				<script language="javascript">
					function enviar(action){
						document.getElementById('action').value = action;
						document.forms[0].submit();
					}
				</script>
				<script type="text/javascript" src="../js/geral.js"></script>
				<script type="text/javascript" src="../js/jquery.js"></script>
				<script type="text/javascript" src="../js/jquery.form.js"></script>
				<link rel="stylesheet" href="../css/admin.css" type="text/css" media="screen" />
				<link rel="stylesheet" href="../css/admin-print.css" type="text/css" media="print" />
				<title><?= SITE_TITLE ?> - Ajung Site Manager</title>
				<meta name="content" content="Content-type:text/html; charset:UTF-8" />
			</head>
			<body>
				<div id="principal">
					<div id="topo" style="background-image:url('images/bg_top_admin.jpg')">
						<img src="images/logo_ajung_admin.jpg" id="logo_admin"/>
						<a href="<?php echo PATH_SITE ; ?>admin/"><img src="<?php echo PATH_SITE ; ?>admin/images/logo_cliente_admin.jpg" border="0"></a>
					</div>
					<div id="ident">
						<p><?php echo $_SESSION['CADASTRO']->nome ?> - <?php echo $_SESSION['CADASTRO']->email ?> <small><a href="login.php">(sair)</a></small></p>
					</div>
					<?php
					ob_start();
					echo '<div id="menu-produtos-geral">';
					$this->monta_menu();
					echo '</div>';
					$return = ob_get_contents();
					ob_end_clean();
					print preg_replace('/\s\s+/','',$return) ;
					$this->openForm();
					?>
					<input type="hidden" name="action" id="action" />
					<?php
	}

	private function monta_menu($modulo_id='0',$class='top'){
		//printr($_SESSION);

		if(@$_SESSION['ADMINUSUARIO']){

			$query = query($sql="SELECT modulo.* FROM modulo, permissao WHERE modulo.modulo_id = {$modulo_id} AND modulo.id = permissao.modulo_id AND permissao.usuario_id = ".intval(decode($_SESSION['ADMINUSUARIO']->id))." AND status = 'A' ORDER BY ordem, nome");
			//print $sql;
			echo '<ul>';
			while($fetch=fetch($query)){
				echo '<li class="'.$class.'"><a href="'.($fetch->arquivo!=''?$fetch->arquivo:'#').'">'.$fetch->nome.'</a>';
				$sql="SELECT modulo.* FROM modulo, permissao WHERE modulo.modulo_id = {$fetch->id} AND modulo.id = permissao.modulo_id AND permissao.usuario_id = ".intval(decode($_SESSION['ADMINUSUARIO']->id))." AND status = 'A' ORDER BY ordem, nome";
				if(rows(query($sql))>0){
					$this->monta_menu($fetch->id,'sub');
				}
				echo '</li>';
			}
			echo '</ul>';

		}
		else {
			echo '<ul>';
			echo '<li class="top"><a href="cad.pedidos.php">Pedidos</a></li>';
			echo '<li class="top"><a href="cad.clientes.php">Clientes</a></li>';
			//echo '<li class="top"><a href="#">Newsletter</a><ul>';
			//echo '<li class="sub"><a href="cad.crm.php">Enviar e-mails</a></li>';
			//echo '</ul></li></ul>';
			echo '</ul>';
		}
	}

	public function salvar_avancar(){
		$this->depoisDeSalvar(null, 'avancar');
	}

	public function salvar_retroceder(){
		$this->depoisDeSalvar(null, 'retroceder');
	}

	public function salvar_sair(){
		$this->salvar() ;
		$this->depoisDeSalvar(null, 'sair');
	}

	public function salvar_novo(){
		$this->salvar('novo') ;
	}

	public function cabecalho() {
		?>
		<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
		<html>
			<head>
				<script language="javascript">
					function enviar(action){
						document.getElementById('action').value = action;
						document.forms[0].submit();
					}
				</script>
				<script type="text/javascript" src="../js/geral.js"></script>
				<script type="text/javascript" src="../js/jquery.js"></script>
				<script type="text/javascript" src="../js/ajax.js"></script>
				<script type="text/javascript" src="../js/jquery.form.js"></script>
				<link rel="stylesheet" href="../css/admin.css" type="text/css" media="screen" />
				<title><?= SITE_TITLE ?> - Ajung Site Manager</title>
				<meta name="content" content="Content-type:text/html; charset:UTF-8" />
			</head>
			<body>
				<div id="principal">
				<?= $this->openForm(); ?>
				<input type="hidden" name="action" id="action" />
		<?php
	}

	public function toolbar() {
		?>
		<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
		<html>
			<head>
				<script language="javascript">
					function enviar(action){
						document.getElementById('action').value = action;
						document.forms[0].submit();
					}
				</script>
				<script type="text/javascript" src="../js/geral.js"></script>
				<script type="text/javascript" src="../js/jquery.js"></script>
				<script type="text/javascript" src="../js/ajax.js"></script>
				<script type="text/javascript" src="../js/jquery.form.js"></script>
				<link rel="stylesheet" href="../css/admin.css" type="text/css" media="screen" />
				<link rel="stylesheet" href="../css/print.css" type="text/css" media="print" />
				<title><?= SITE_TITLE ?> - Ajung Site Manager</title>
				<meta name="content" content="Content-type:text/html; charset:UTF-8" />
			</head>
			<body>
				<div id="principal">

				<div id="menu-topo">
					<div style="float:left">
						<input type="button" class="button button-cancelar" value="sair" onclick="self.close()"/>
						<input type="button" class="button button-gravar" value="salvar" onclick="enviar('salvar')"/>
						<input type="button" class="button button-gravar" value="salvar & sair" onclick="enviar('salvar_sair')"/>
						<input type="button" class="button button-gravar" value="salvar & novo" onclick="enviar('salvar_novo')"/>
					</div>
					<div style="float:right">
						<input type="button" class="button button-retroceder" value="<" onclick="enviar('salvar_retroceder')"/>
						<input type="button" class="button button-avancar" value=">" onclick="enviar('salvar_avancar')"/>
					</div>
					<br clear="all"/>
				</div>
				<br clear="all"/>
				<?= $this->openForm(); ?>
				<input type="hidden" name="action" id="action" />
		<?php
	}

	public function foot(){
		?>
					</form>
					<div style="clear:both"></div>
				</div>
				<script language="javascript">
				function posicionaMenu(){
					if(!document.getElementById('menu-produtos-geral')){
						return;
					}
					a = document.getElementById('menu-produtos-geral').childNodes[0];
					if(a){
						b = a.childNodes ;
						for ( var i = 0, c = b.length ; i < c ; i ++ ){
							if ( b[i].tagName == 'LI' ){
								// acertar posicao do primeiro ul dentro do li, se existir
								d = b[i].getElementsByTagName('ul') ;
								if (d.length > 0 ){
									d = d[0];
									t = findPos(b[i]) ;
									d.style.left = new String(t[0] + 15 ) + 'px';
									d.style.top  = new String(t[1] + 28)  + 'px';
									b[i].onmouseover = function(){ this.className+=" over"; }
									b[i].onmouseout  = function(){ this.className = this.className.replace(" over", "") }
								}
							}
						}
					}
				}
				posicionaMenu();
				window.onresize = posicionaMenu ;
				</script>
			</body>
		</html>
		<?
	}

	public function serFormAttr($attr, $val){
		$this->form[$attr] = $val;
	}


	private final function openForm(){
		$this->mostraErros();
		$this->mostraSucessos();
		?>
		<form name="<?= $this->form['name']; ?>" id="<?= $this->form['id']; ?>" method="<?= $this->form['method']; ?>"
			 action="<?= $this->form['action']; ?>" <?= $this->form['extra'];	?> >
		<?
	}

	private function callMethod(){
		if( method_exists($this, $m = $this->action) ){
			$this->$m();
		}
		else{
			$this->body();
		}
	}

	function mostraErros() {
		if( sizeof($this->erros) > 0 ){
			?>
			<div class="erro">
				<?php
				foreach ( $this->erros as $erro ){
					print '<p>'.$erro.'</p>' ;
				}
				?>
			</div>
			<?php
		}
	}

	function mostraSucessos() {
		if( sizeof($this->sucessos) > 0 ){
			?>
			<div class="sucesso">
				<?php
				foreach ( $this->sucessos as $sucesso ){
					print '<p>'.$sucesso.'</p>' ;
				}
				?>
			</div>
			<?php
		}
	}

	public function depoisDeSalvar($obj=null, $praOndeVou){

		$next_id = 0;
		$last_id = 0;

		// posso pegar tanto do objeto, quanto imaginando que está vindo um request
		if($obj){
			$next_id = $obj->get_next_id();
			$last_id = $obj->get_last_id();
		}
		else {
			$next_id = request('next_id');
			$last_id = request('last_id');
		}

		switch( $praOndeVou ) {
			case 'avancar':
				//echo js('parent.opener.history.go(0)');
				echo js('parent.opener.document.getElementById("buttonBuscar").click()');

				redireciona($_SERVER['REQUEST_URI'].'?action=editar&id='.$next_id,0);
			break ;
			case 'retroceder':
				echo js('parent.opener.document.getElementById("buttonBuscar").click()');
				redireciona($_SERVER['REQUEST_URI'].'?action=editar&id='.$last_id,0);
			break ;
			case 'novo':
				//echo js('parent.opener.history.go(0)');
				echo js('parent.opener.document.getElementById("buttonBuscar").click()');
				redireciona($_SERVER['REQUEST_URI'].'?action=editar&id=0',0);
			break ;
			case 'sair':
				//echo js('parent.opener.history.go(0)');
				echo js('parent.opener.document.getElementById("buttonBuscar").click()');
				echo js('self.close()');
			break ;
			default:
				//echo js('parent.opener.history.go(0)');
				echo js('parent.opener.document.getElementById("buttonBuscar").click()');

				$_REQUEST['id'] = $obj->id;
				$this->editar();

			break ;
		}
	}
}

?>