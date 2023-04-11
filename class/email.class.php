<?php
/**********************

Criado em:
Ultima alteração: 23/07/2010

Change log:
23-07-2010 - melhoria na funcao send, se for debug da um window.open

**********************/

require_once('class.phpmailer.php');

class email
{

    public $debug = false;
	public $phpMailer;
	
    public $parts;
	public $headers;
	
	public $toMail;
	public $toName;

    /*
     * Método construtor
     */
    function __construct()
    {
	
		$this->phpMailer = new PHPMailer();
		
        $this->parts = array();
        $this->boundary = md5(time());

		$this->to = "";
    }

    /*
     * Adiciona HTML
     */
    function addHtml($body)
    {
		$body = mb_convert_encoding($body, "ISO-8859-1", "UTF-8" );
		//$this->phpMailer->Body = $body;
		$this->phpMailer->MsgHTML($body);
	}

	/*
     * Adiciona TO
     */
	 
	 /*Adiciona anexo*/
	 function AddAttachment($caminhoAnexo){
		$this->phpMailer->AddAttachment($caminhoAnexo);
	 }
	 
	 function AddEmbeddedImage($anexo){
		$this->phpMailer->AddEmbeddedImage($anexo);
	 }
	 
    function addTo($email, $nome=null)
    {		
		$nome =  mb_convert_encoding($nome, "ISO-8859-1", "UTF-8");
		$this->phpMailer->AddAddress($email, $nome);
    }

	/*
     * Adiciona CC
     */
    function addCc($email, $nome=null)
    {
		$this->phpMailer->AddCC($email, $nome);
    }

	/*
     * Adiciona BCC
     */
    function addBcc($email, $nome=null)
    {
		$this->phpMailer->AddBCC($email, $nome);
    }

	/*
     * Adiciona BCC
     */
    function addReplyTo($email, $nome=null)
    {
		$this->phpMailer->AddReplyTo($email, $nome);
    }


    /*
     * Envia Email
     */
    function send($subject)
    {

        $from = config::get('EMPRESA');
        $from = mb_convert_encoding($from, "ISO-8859-1", "UTF-8");

		$this->phpMailer->IsSMTP(); // set mailer to use SMTP
		$this->phpMailer->SMTPAuth = true; // turn on SMTP authentication
        $this->phpMailer->SMTPSecure = 'tls';
		$this->phpMailer->Host = config::get('SMTP');
		$this->phpMailer->Username = config::get('SMTP_EMAIL');
		$this->phpMailer->Password = config::get('SMTP_SENHA');
		$this->phpMailer->Port = config::get('SMTP_PORTA');
		$this->phpMailer->From = config::get('SMTP_EMAIL');
        $this->phpMailer->FromName = $from;
        $this->phpMailer->SMTPDebug = 0;

        if($this->debug){
            $this->phpMailer->SMTPDebug = 0;
        }

		$this->phpMailer->IsHTML(true);  // set email format to HTML

		$subject = mb_convert_encoding($subject, "ISO-8859-1", "UTF-8");
		
		$this->phpMailer->Subject = $subject;

		$result = $this->phpMailer->send();

        if($this->debug){
            printr("result");
            printr($result);
            printr("phpMailer");
            printr($this->phpMailer);
        }
    }

	private function makeMailAddr($email, $nome=null)
	{
		if($nome!=''){
			return '"'.$nome.'" <'.$email.'>';
		}
		return '<'.$email.'>';
	}

}
