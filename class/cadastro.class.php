<?php

/**********************

Criado em:
Ultima alteração: 10/08/2010

Change log:
10-08-2010 - alteração nas propriedes, adicionado comoconheceu_id
23-07-2010 - alteração no metodo salva, convertendo data nascimento para o formato do banco
23-07-2010 - adicionado metodo getStNewsletterChecked

**********************/

class cadastro extends base {

	var
		$id
		,$st_ativo
		,$st_fixo
		,$st_admin
		,$st_aparece_mapa
		
		,$st_recebe_post
		
		,$tipocadastro_id
		,$comoconheceu_id
		,$especifique
		
		,$cadastro_id
		,$tipo_pessoa
		,$nome
		,$empresa
		,$nome_fantasia
		,$cpf
		,$cnpj
		,$fone_com
		,$fone_res
		,$fone_cel
		,$fone_nextel
        ,$skype

		,$email
		
		,$login
		,$senha
		
		,$rg
		,$rg_emissor
		,$sexo
		,$data_nascimento
		,$inscricao_estadual
		,$inscricao_municipal
		,$bv
		,$como_encontrou
		,$logradouro
		,$numero
		,$complemento
		,$bairro
		,$cidade
		,$cep
		,$uf
		
		,$mapa_imagem
		,$mapa_email
		,$mapa_logradouro
		,$mapa_numero
		,$mapa_complemento
		,$mapa_bairro
		,$mapa_cidade
		,$mapa_cep
		,$mapa_uf
		,$mapa_website
		,$mapa_fone1
		,$mapa_fone2
		,$mapa_fone3
		
		,$imagem
		
		,$tabela
		,$over

		,$data_cadastro
        ,$site
        ,$st_pode_excluir_produtos
    ;

	const TIPOCADASTRO_ADMINISTRADOR = '1';
	const TIPOCADASTRO_CLIENTE = '2';
	const TIPOCADASTRO_VENDEDOR = '3';
	const TIPOCADASTRO_LOJA = '4';
	const TIPOCADASTRO_CLIENTE_ESPECIAL = '7';
	const TIPOCADASTRO_CLIENTESSATISFEITOS = '9';

	public function salva(){
		$this->data_nascimento = to_bd_date($this->data_nascimento);
		return parent::salva();
	}
	 
	public function exclui(){
		if($this->tipocadastro_id == self::TIPOCADASTRO_VENDEDOR){
			query("UPDATE pedido SET vendedor_id = ".cadastro::vendedorPadrao()." WHERE vendedor_id = {$this->id}");
			query("UPDATE pedido SET cadastro_id = ".cadastro::vendedorPadrao()." WHERE cadastro_id = {$this->id}");
			query("DELETE FROM permissao WHERE cadastro_id = {$this->id}");
			return parent::exclui();
		}
        elseif($this->tipocadastro_id == self::TIPOCADASTRO_ADMINISTRADOR){
            query("UPDATE cmsitem SET autor_id = NULL WHERE autor_id = {$this->id}");
            query("DELETE FROM permissao WHERE cadastro_id = {$this->id}");
            return parent::exclui();
        }
        else {
            return parent::exclui();
        }
	}
	
	public function validaClienteSemLogin(& $erros=array()){
		
		if(!is_email($this->email) ){
			$erros['email'] = 'Digite seu e-mail corretamente';
		}
		
		return sizeof($erros)==0;
	}
	

	
	public function valida(){
		$erros='';
		if(!is_set($this->nome)){
			$erros .= tag("p","Digite seu nome");
		}
		if(!is_email($this->email) ){
			$erros .= tag("p",'Digite seu e-mail corretamente');
		}
		if(!is_set($this->empresa)){
			$erros .= tag("p","Digite sua empresa");
		}
		if(!is_set($this->fone_com)){
			$erros .= tag("p","Digite seu telefone");
		}
        if(!is_cnpj($this->cnpj)){
            $erros .= tag("p","Digite seu CNPJ corretamente");
        }
        if(!is_set($this->comoconheceu_id)){
            $erros .= tag("p","Selecione seu ramo de atuação");
        }
		if(!is_set($this->login)){
			$erros .= tag("p","Digite seu login");
		}
		if(!is_set($this->senha)){
			$erros .= tag("p","Digite sua senha");
		}
        else {
			if($this->senha != request('confirma_senha')){
				$erros .= tag("p","A confirmação de senha está errada");
			}
		}
		
		if($erros!=''){
			$_SESSION['erro'] = $erros;
			return false;
		}
		return true;
	}

	public function validaCliente(& $erros=array()){
		$algumErro = false;
		
		//printr($this);
	
		
		if(!is_set($this->nome) ){
			$erros['nome'] = 'Digite seu nome';
		}				
		if($this->nome == 'Nome') {
			$erros['nome'] = 'Digite seu nome';
		}
		
		if(!is_email($this->email) ){
			$erros['email'] = 'Digite seu e-mail corretamente';
		}	
		
		if(!($this->tipocadastro_id == 4)){
			if($this->email =='E-mail') {
				$erros['email'] = 'Digite seu e-mail corretamente';
			}
			
			if(rows(query("SELECT * FROM cadastro WHERE tipocadastro_id = '".self::TIPOCADASTRO_CLIENTE."' AND email = '{$this->email}' ".($this->id>0?"AND id <> {$this->id}":"")))>0){
				$erros['email'] = 'Já existe um cliente cadastrado com este e-mail';
			}	
		}
		
		// Checa confirmacao da senha
		if(@$_REQUEST['Estalogado'] == 'nao'){
			if(array_key_exists('senha1',$_REQUEST)){			
				if(encode(request('senha1'))!=$this->senha){
					$erros['senha'] = 'A senha e a confirmação não conferem';
				}
			}		
			
			if(!is_set($this->senha)){
				$erros['senha'] = 'Digite sua senha';
			}
		}
		
		if($this->senha == ''){
			$erros['senha'] = 'Digite sua senha';
		}
		
		//if(array_key_exists('senha1',$_REQUEST)){			
			if(encode(request('senha1'))!=$this->senha){
				$erros['senha'] = 'A senha e a confirmação não conferem';
			}
		//}
		
		if($this->empresa == 'Empresa' || !is_set($this->empresa))  {
			$erros['empresa'] = 'Digite sua empresa';
		}
		
		if(!is_set($this->fone_com) ){
			$erros['fone_com'] = 'Digite seu telefone corretamente';
		}	
		
		if($this->fone_com =='Telefone'){
			$erros['fone_com'] = 'Digite seu telefone corretamente';
		}
		
		if(!is_set($this->logradouro)){
			$erros['logradouro'] = 'Digite seu endereço corretamente';
		}
		
		if(!is_set($this->numero)){
			$erros['logradouro'] = 'Digite o numero corretamente';
		}
		
		if(!is_set($this->bairro)){
			$erros['bairro'] = 'Digite o seu bairro corretamente';
		}
		if(!is_set($this->cep)){
			$erros['cep'] = 'Digite o seu CEP corretamente';
		}
		
		if(!is_set($this->cidade) ){
			$erros['cidade'] = 'Digite sua cidade corretamente';
		}
		
		if(!is_set($this->uf) ){
			$erros['uf'] = 'Selecione seu estado corretamente';
		}

		// if($this->uf =='' && $this->tipocadastro_id != 4){
			// $erros['uf'] = 'Selecione seu estado corretamente';
		// }
		
		// if($this->tipocadastro_id == 4 && $this->mapa_uf ==''){
			// $erros['mapa_uf'] = 'Selecione seu estado corretamente';
		// }
		
		if(!($this->tipocadastro_id == 4)){
			
			if(!is_set($this->comoconheceu_id)){
				//$erros['comoconheceu_id'] = 'Informe como nos conheceu';
			}		
			if($this->comoconheceu_id == 'outro'){
				if(!is_set($this->especifique) || ($this->especifique == 'Especifique')){
					//$erros['especifique'] = 'Especifique como nos conheceu';
				}	
				
			}
		}
	
		return sizeof($erros)==0;
	}

	public function validaRevenda(& $erros=array()){
		$algumErro = false;
		//printr($this);
		if(!is_set($this->nome) ){
			$erros['cadastro']['nome'] = 'Digite seu nome';
		}
		if(!is_set($this->empresa) ){
			$erros['cadastro']['empresa'] = 'Digite sua empresa';
		}
		/*if(!is_set($this->comoconheceu_id) ){
			$erros['cadastro']['comoconheceu_id'] = 'Informe como nos conheceu';
		}*/
		if(!is_cnpj($this->cnpj) ){
			$erros['cadastro']['cnpj'] = 'Digite seu CNPJ corretamente';
		}
		if(!is_set($this->logradouro) ){
			//$erros['cadastro']['logradouro'] = 'Digite o seu endereço corretamente';
		}
		if(!is_set($this->numero) ){
			//$erros['cadastro']['numero'] = 'Digite o numero do seu endereço corretamente';
		}
		if(!is_set($this->cep) ){
			$erros['cadastro']['cep'] = 'Digite seu cep corretamente';
		}
		if(!is_set($this->email) ){
			$erros['cadastro']['email'] = 'Digite seu e-mail corretamente';
		}
		if(!is_set($this->fone_com) ){
			$erros['cadastro']['fone_com'] = 'Digite seu telefone comercial corretamente';
		}
		if(!is_set($this->fone_cel) ){
			//$erros['cadastro']['fone_cel'] = 'Digite seu fone celular corretamente';
		}
		if(rows(query("SELECT * FROM cadastro WHERE tipocadastro_id = '".self::TIPOCADASTRO_CLIENTE."' AND email = '{$this->email}' ".($this->id>0?"AND id <> {$this->id}":"")))>0){
			$erros['cadastro']['email'] = 'Já existe um cliente cadastrado com este e-mail';
		}
		
		if(!is_set($this->cidade) ){
			//$erros['cadastro']['cidade'] = 'Digite sua cidade corretamente';
		}
		if(!is_set($this->uf) ){
			//$erros['cadastro']['uf'] = 'Selecione seu estado corretamente';
		}
		
		if(!is_set($this->senha) ){
			$erros['cadastro']['senha'] = 'Digite sua senha';
		}
		return sizeof($erros)==0;
	}

	public function getNomeAbreviado(){
		if(trim(substr($this->nome,0,strpos($this->nome,' ')))!=''){
			return substr($this->nome,0,strpos($this->nome,' '));
		}
		return $this->nome;
	}

	public function getStNewsletterChecked(){
		$return = '';
		if($this->id){
			$newscadastro = new newscadastro(array('email'=>$this->email,'st_ativo'=>'S'));
			$return = $newscadastro->id?'checked':'';
		}
		return $return;
	}
	
	public function getCadastroCliente(){
		
		$sql = '
			SELECT * 
			FROM 
				cadastro 
			WHERE 
				tipocadastro_id = '.tipocadastro::getId('CLIENTEEMPRESA').'
			AND	
				st_ativo = "S"
			AND	
				imagem <> ""
			ORDER BY	
				nome
		';
		
		//printr($sql);
		
		$query = query($sql);
	
		$return = array ();
	
		while ($fetch = fetch($query)){
		
			$return[] = $fetch;	
		}
		
		return $return;
	}

	private function auth($email, $senha, $tipocadastro_id){

		$email = limpa($email);
		$senha = limpa($senha);

		if($email!=''&&$senha!=''){

			$this->get_by_id(
				array(
					'email;login' => $email
					,'senha' => encode($senha)
					,'tipocadastro_id' => intval($tipocadastro_id)
				)
			);

			return $this->id;

		}

		return false;
	}

	public function authCliente($email, $senha){
		return $this->auth($email, $senha, self::TIPOCADASTRO_CLIENTE);
	}
	
	public function authClienteEspecial($email, $senha){
		return $this->auth($email, $senha, self::TIPOCADASTRO_CLIENTE_ESPECIAL);
	}

	public function authVendedor($email, $senha){
		return $this->auth($email, $senha, self::TIPOCADASTRO_VENDEDOR);
	}

	public function getVendedor(){
		$cadastro = new cadastro($this->cadastro_id);
		return $cadastro;
	}

	static function vendedorPadrao(){
		$id = query_col("SELECT id FROM cadastro WHERE tipocadastro_id = ".tipocadastro::getId('VENDEDOR')." AND st_fixo = 'S'");
		if(!$id){
			//printr(tipocadastro::getId('VENDEDOR'));
			if(DEBUG=='1'){
				print "E necessario que se cadastro um vendedor com st_fixo='S' para ser o vendedor padrao";
				return 0;
			}
		}
		
		return $id;
	}

    static function vendedorFila(){

    }

	static function opcoesVendedor(){

		$opcoes = array();

		$query = query($sql="SELECT id, nome FROM cadastro WHERE tipocadastro_id = ".self::TIPOCADASTRO_VENDEDOR." ORDER BY st_fixo, nome");

		while($fetch=fetch($query)){
			$opcoes[$fetch->id] = $fetch->nome;
		}

		return $opcoes;
	}

	public function enviaEmailBemVindo(){
		$tEmail = new Template("tpl.email-novo-cadastro-modelo-2.html");
		$this->senha = decode($this->senha); 
		$tEmail->cadastro = $this;
		$tEmail->config = new config();
		
		$email = new email();		
		$email->addTo($this->email,$this->nome);
		$email->addHtml($tEmail->getContent());
		$email->send("Novo Cadastro - ".config::get('EMPRESA'));
	}
	
	static function totalClientesSite(){
		return rows(query("SELECT * FROM cadastro WHERE tipocadastro_id = 2"));
	}

    protected function validaUnicoCadastro($campo){
        if(rows(query("SELECT 1 FROM cadastro
        WHERE
            tipocadastro_id = {$this->tipocadastro_id}
        AND {$campo} = '{$this->$campo}'
        ".($this->id>0?"AND id <> {$this->id}":"")))>0){
            return false;
        }
        return true;
    }

    public function getCnpjFormatado(){
        return mask($this->cnpj,'##.###.###/####-##');
    }
	public function validaClienteCheckout(& $erros=array()){

		$algumErro = false;

		if(!is_set($this->nome) || ($this->nome == 'Nome*')){
			$erros['nome'] = "Digite seu nome";
		}
		
		if(!is_email($this->email)){
			$erros['email'] = "Digite seu e-mail corretamente";
		}
		if(rows(query("SELECT * FROM cadastro WHERE tipocadastro_id = '".self::TIPOCADASTRO_CLIENTE."' AND email = '{$this->email}' ".(@$this->id>0?"AND id <> {$this->id}":"")))>0){
			$erros['email'] = "E-mail j&aacute; cadastrado";
		}
		
		if(strtolower(@$_REQUEST['cadastro']['confirma_email']) != strtolower($this->email)){
			$erros['confirma_email'] = "Confirme seu e-mail";
		}
		
		if(!is_set($this->empresa) || ($this->empresa == 'Empresa*')){
			$erros['empresa'] = "Digite sua empresa";
		}
		
		if(!is_set($this->fone_res) || ($this->fone_res == 'Telefone*')){
			$erros['fone_res'] = "Digite seu telefone";
		}
		// if(!is_set($this->site) || ($this->site == 'Site*')){
		// 	$erros['site'] = "Digite seu site";
		// }
		
		return sizeof($erros)==0;
	}
}
