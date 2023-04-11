<?php

class modulo_contato extends modulo_admin {

    public $arquivo = 'contato';

    static function widget(){
        $t = new Template(__DIR__.'/tpl.contato.html');
        $t->config = new config();
        return $t->getContent();
    }

    static function widget_rodape(){
        $t = new Template(__DIR__.'/tpl.widget.html');
        $t->index = INDEX;
        // $t->config = new config();
        return $t->getContent();
    }

    static function enviar($rodape=false){

        if(request('token')!=token_ok()){
            throw new Exception('Requisi��o inv�lida');
        }

        $contato = new stdclass();
        $contato = (object)request('contato');

        /*
        <fieldset>
            <input type="text" name="contato[nome]" value="{contato->nome}" placeholder="Nome ou Raz�o Social*" />
        </fieldset>
        <fieldset>
            <input type="email" name="contato[email]" value="{contato->email}" placeholder="E-mail*" />
        </fieldset>
        <fieldset>
            <input type="text" name="contato[endereco]" value="{contato->endereco}" placeholder="Endere�o" />
        </fieldset>
        <fieldset>
            <input type="tel" name="contato[cep]" value="{contato->cep}" placeholder="Cep" />
        </fieldset>
        <fieldset>
            <input type="tel" name="contato[telefone]" value="{contato->telefone}" placeholder="Telefone" />
        </fieldset>
        <fieldset>
            <input type="text" name="contato[departamento]" value="{contato->departamento}" placeholder="Departamento" />
        </fieldset>
        <fieldset>
            <textarea name="contato[mensagem]" value="{contato->mensagem}" placeholder="Mensagem*">{contato->mensagem}</textarea>
            </fieldset>
            */
        $erro = array();
        
        if($rodape){
        
            if($contato->email==''){
                $erro["email"] = 'Digite seu e-mail.';
            }
            // 
            elseif(!is_email(strip_tags($contato->email))){
                $erro["email"] = 'Digite seu e-mail corretamente.';
            }
            if($contato->mensagem==''){
                $erro["mensagem"] = 'Digite sua mensagem.';
            }
    
            if(sizeof($erro)>0){
                throw new Exception(join("<br>", $erro));
            }
    
            $contato->mensagem = nl2br($contato->mensagem);
    
            $html =
            "
            <table cellspancing='0' cellpadding='0' border='0' width='600px' style='margin:0 auto;text-align:center;background:#FFF;padding-top:25px;padding-bottom:25px;border-top-left-radius:10px;border-top-right-radius:10px;'>
                <tr>
                    <td>
                        <a href='{config->URL}' title='{config->EMPRESA}'><img src='{config->URL}img/logo/logo.png' alt='{config->EMPRESA}' style='margin-bottom:15px;width:40%;'/></a>
                        <hr style='width:97%;margin:0 auto;'></hr>
                    </td>
                </tr>
            </table>
            <table cellspancing='0' cellpadding='0' border='0' width='600px' style='margin:0 auto;text-align:center;background:#FFF;padding-top:25px;padding-bottom:25px;'>
                <tr>
                    <td>
                        <strong>E-mail</strong>
                        <a href='mailto:".@$contato->email."'>".@$contato->email."</a>
                    </td>
                </tr>
                <tr>
                    <td>
                        <strong>Mensagem</strong><br/>
                        <span>".@$contato->mensagem."</span>
                    </td>
                </tr>
            <table>
            <table cellspancing='0' cellpadding='0' border='0' width='600px' style='margin:0 auto;background-color:#FFF;border-top:1px solid #CCC;padding-left:15px;padding-right:15px;padding-top:10px;padding-bottom:10px;'>
                <tr>
                    <td>
                        <span style='font-family: Open Sans', sans-serif;font-weight:400;color:#666666 !important;font-size:14px;'>Atenciosamente</span><br/>
                        <span style='font-family: 'Open Sans', sans-serif;font-weight:400;color:#074182 !important;font-size:16px;'>Equipe {config->EMPRESA}</span>
                    </td>
                    <td rowspan='2' style='text-align:right'>
                        <img src='{config->URL}img/logo/logo.png' alt=' style='width:60px !important;'>
                    </td>
                </tr>
                <tr>
                    <td>
                        <span style='font-family: 'Open Sans', sans-serif;font-weight:400;color:#666666 !important;font-size:14px;'>Tel.: {config->TELEFONE}</span><br/>
                        <a href='mailto:{config->EMAIL_CONTATO}' style='font-family: 'Open Sans', sans-serif;font-weight:400;color:#666666 !important;font-size:14px;'>{config->EMAIL_CONTATO}</a>
                    </td>
                </tr>
            </table>
            <table cellspancing='0' cellpadding='0' border='0' width='600px' style='margin:0 auto;background-color:#666;padding:5px 0px;border-bottom-left-radius:10px;border-bottom-right-radius:10px;'>
                <tr>
                    <td style='width:15px;height:100%;'>&nbsp;</td>
                    <td style='width:60%;'>
                        <span style='font-family: 'Open Sans', sans-serif;font-weight:400;color:#FFF !important;font-size:14px;'>{config->EMPRESA} Todos os direitos reservados.</span>
                    </td>
                    <td  style='text-align:right;padding:0 12px;'>
                        <span style='font-family: 'Open Sans', sans-serif;font-weight:400;color:#FFF !important;font-size:14px;display:block;text-align:right'>Desenvolvimento <a href='http://ajung.com.br' style='color:#FFF;'>A. Jung</a></span>
                    </td>
                    <td style='width:15px;height:100%;'>&nbsp;</td>
                </tr>
            </table>
            ";
    
            $email = new email();
            $email->addTo(config::get('EMAIL_CONTATO'),config::get('EMPRESA'));
            $email->addHtml($html);
            $email->send("Contato - {$contato->email}");
    
        }else{

            if($contato->nome==''){
                $erro["nome"] = 'Digite seu nome.';
            }
                //printr($contato);
            if($contato->email==''){
                $erro["email"] = 'Digite seu e-mail.';
            }
            // 
            elseif(!is_email(strip_tags($contato->email))){
                $erro["email"] = 'Digite seu e-mail corretamente.';
            }
            if($contato->mensagem==''){
                $erro["mensagem"] = 'Digite sua mensagem.';
            }

            if(sizeof($erro)>0){
                throw new Exception(join("<br>", $erro));
            }
            // if(sizeof($erro)>0){
                // //$out["status"] = false;
                // $out["msg"] = join("<br>",$erro);
            // //	$out["error"] = $erro;
                // throw new Exception(json_encode($out));
            // }

            $contato->mensagem = nl2br($contato->mensagem);

            $html =
            "<!DOCTYPE html PUBLIC '-//W3C//DTD XHTML 1.0 Transitional//EN' 'http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd'>
            <html xmlns='http://www.w3.org/1999/xhtml'>
            <head>
                <meta http-equiv='Content-Type' content='text/html; charset=iso-8859-1' />
                <link href='http://fonts.googleapis.com/css?family=Oxygen:400,300,700' rel='stylesheet' type='text/css'>
                <title>Proposta</title>
                <style>
                    body {margin:0px;padding:0px;background:#ececec;}
                    a{text-decoration: none;}
                </style>
            </head>
            <body>
	            <center>
                        <table cellspancing='0' cellpadding='0' border='0' width='600px' style='margin:0 auto;text-align:center;background:#FFF;padding-top:25px;padding-bottom:25px;border-top-left-radius:10px;border-top-right-radius:10px;'>
                            <tr>
                                <td>
                                    <a href='".config::get('url')."' title='".config::get('EMPRESA')."'><img src='".config::get('URL')."img/logo/logo.png' alt='".config::get('EMPRESA')."' style='margin-bottom:15px;width:40%;'/></a>
                                    <hr style='width:97%;margin:0 auto;'></hr>
                                </td>
                            </tr>
                        </table>
                        <table cellspancing='0' cellpadding='0' border='0' width='600px' style='margin:0 auto;text-align:left;background:#FFF;padding-top:25px;padding-bottom:25px;'>
                            <tr>
                                <td>
                                    <p><strong>Nome:</strong> ".@$contato->nome."</p>
                                    <p><strong>E-mail</strog> <a href='mailto:".@$contato->email."'>".@$contato->email."</a></p>
                                    <p><strong>Telefone:</strong> ".@$contato->fone_com."</p>
                                    <p><strong>Departamento:</strong> ".@$contato->departamento."</p>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <strong>Mensagem</strong><br/>
                                    <span>".@$contato->mensagem."</span>
                                </td>
                            </tr>
                        <table>
                        <table cellspancing='0' cellpadding='0' border='0' width='600px' style='margin:0 auto;background-color:#FFF;border-top:1px solid #CCC;padding-left:15px;padding-right:15px;padding-top:10px;padding-bottom:10px;'>
                            <tr>
                                <td>
                                    <span style='font-family: Open Sans', sans-serif;font-weight:400;color:#666666 !important;font-size:14px;'>Atenciosamente</span><br/>
                                    <span style='font-family: 'Open Sans', sans-serif;font-weight:400;color:#074182 !important;font-size:16px;'>Equipe ".config::get('EMPRESA')."</span>
                                </td>
                                <td rowspan='2' style='text-align:right'>
                                    <img src='".config::get('url')."img/logo/logo-rodape-email.png' alt=' style='width:60px !important;'>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <span style='font-family: 'Open Sans', sans-serif;font-weight:400;color:#666666 !important;font-size:14px;'>Tel.: ".config::get('TELEFONE')."</span><br/>
                                    <a href='mailto:".config::get('EMAIL_CONTATO')."' style='font-family: 'Open Sans', sans-serif;font-weight:400;color:#666666 !important;font-size:14px;'>".config::get('EMAIL_CONTATO')."</a>
                                </td>
                            </tr>
                        </table>
                        <table cellspancing='0' cellpadding='0' border='0' width='600px' style='margin:0 auto;background-color:#666;padding:5px 0px;border-bottom-left-radius:10px;border-bottom-right-radius:10px;'>
                            <tr>
                                <td style='width:15px;height:100%;'>&nbsp;</td>
                                <td style='width:60%;'>
                                    <span style='font-family: 'Open Sans', sans-serif;font-weight:400;color:#FFF !important;font-size:14px;'>".config::get('EMPRESA')." Todos os direitos reservados.</span>
                                </td>
                                <td  style='text-align:right;padding:0 12px;'>
                                    <span style='font-family: 'Open Sans', sans-serif;font-weight:400;color:#FFF !important;font-size:14px;display:block;text-align:right'>Desenvolvimento <a href='http://ajung.com.br' style='color:#FFF;'>A. Jung</a></span>
                                </td>
                                <td style='width:15px;height:100%;'>&nbsp;</td>
                            </tr>
                        </table>
                    </center>
                </body>
            </html>";

            $email = new email();
            $email->addTo(config::get('EMAIL_CONTATO'),config::get('EMPRESA'));
            $email->addHtml($html);
            $email->send("Contato - {$contato->nome}");
        }

    }
}