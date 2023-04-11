function rebind_img_js_detalhe(){

    $('img.js-detalhe').hover(
    // $('.container-produtos img.js-detalhe').hover(
        function(){

            var obj = $(this);

            if(obj.data('src1')){
                var indice = new Number(obj.data('indice'));
                var src = 'src' + indice;
                var newImg = new Image;

                if(!obj.data(src)){
                    src = 'src1';
                    indice = 1;
                }
                else {
                    indice ++;
                }

                obj.data('indice', indice);

                obj.animate({opacity:.9},100);

                if(!obj.data('load')){

                    newImg.onload = function() {
                        obj.data('load',1);
                        obj.attr('src', this.src);
                        obj.animate({opacity:1},100);
                    };

                    newImg.src = obj.data(src);

                }
                else {
                    obj.attr('src', obj.data(src));
                    obj.animate({opacity:1},100);
                }

            }
        }
        ,function(){
            var obj = $(this);
            if(obj.data('src1')){
                obj.attr('src', obj.data('src'));
            }
        }
    );

   /* $("img.lazy").lazyload({
        effect:"fadeIn"
        ,threshold:200
    });*/

}

function formataMoeda(objTextBox, e){

    var SeparadorMilesimo = '.' ;
    var SeparadorDecimal = ',' ;
    var sep = 0;
    var key = '';
    var i = j = 0;
    var len = len2 = 0;
    var strCheck = '0123456789';
    var aux = aux2 = '';

    //var whichCode = (window.Event) ? e.which : e.keyCode;

    var whichCode = (document.all) ? e.keyCode : e.which ;

    //alert(whichCode);

    if ((whichCode == 13) || (whichCode == 0) || (whichCode == 8))
        return true;

    key = String.fromCharCode(whichCode); // Valor para o c�digo da Chave
    if (strCheck.indexOf(key) == -1) return false; // Chave inv�lida
    len = objTextBox.value.length;
    for(i = 0; i < len; i++)
        if ((objTextBox.value.charAt(i) != '0') && (objTextBox.value.charAt(i) != SeparadorDecimal)) break;
    aux = '';
    for(; i < len; i++)
        if (strCheck.indexOf(objTextBox.value.charAt(i))!=-1) aux += objTextBox.value.charAt(i);
    aux += key;
    len = aux.length;
    if (len == 0) objTextBox.value = '';
    if (len == 1) objTextBox.value = '0'+ SeparadorDecimal + '0' + aux;
    if (len == 2) objTextBox.value = '0'+ SeparadorDecimal + aux;
    if (len > 2) {
        aux2 = '';
        for (j = 0, i = len - 3; i >= 0; i--) {
            if (j == 3) {
                aux2 += SeparadorMilesimo;
                j = 0;
            }
            aux2 += aux.charAt(i);
            j++;
        }
        objTextBox.value = '';
        len2 = aux2.length;
        for (i = len2 - 1; i >= 0; i--)
            objTextBox.value += aux2.charAt(i);
        objTextBox.value += SeparadorDecimal + aux.substr(len - 2, len);
    }
    return false;
}

function formataMoedaValor(valor){
    var SeparadorMilesimo = '.' ;
    var SeparadorDecimal = ',' ;
    var sep = 0;
    var key = '';
    var i = j = 0;
    var len = len2 = 0;
    var strCheck = '0123456789';
    var aux = aux2 = '';

    valor = valor.replace(".","");

    aux = valor;
    len = aux.length;
    if (len == 0) valor = '';
    if (len == 1) valor = '0'+ SeparadorDecimal + '0' + aux;
    if (len == 2) valor = '0'+ SeparadorDecimal + aux;
    if (len > 2) {
        aux2 = '';
        for (j = 0, i = len - 3; i >= 0; i--) {
            if (j == 3) {
                aux2 += SeparadorMilesimo;
                j = 0;
            }
            aux2 += aux.charAt(i);
            j++;
        }
        valor = '';
        len2 = aux2.length;
        for (i = len2 - 1; i >= 0; i--)
            valor += aux2.charAt(i);
        valor += SeparadorDecimal + aux.substr(len - 2, len);
    }
    return valor;
}

function formata(v) {
    var s = new String(v);
    if(s.indexOf('.')==-1) {
        return float2moeda(s)+',00';
    }
    else {
        var x = s.split('.');
        if(x[1].length>=2){
            return float2moeda(x[0])+','+x[1].substr(0,2);
        }
        else{
            return float2moeda(x[0])+','+x[1]+'0';
        }
    }
}

function float2moeda(num){
    x = 0;

    if(num < 0){
        num = Math.abs(num);
        x = 1;
    }

    if(isNaN(num)) num = "0";

    num = Math.floor((num*100+0.5)/100).toString();

    for (var i = 0; i < Math.floor((num.length-(1+i))/3); i++)

        num = num.substring(0,num.length-(4*i+3))+'.'+num.substring(num.length-(4*i+3));
    ret = num;

    if (x == 1) ret = ' - ' + ret;

    return ret;
}

function toFloat(str){
    str = new String(str);
    str = new Number(str.replace(',','.'));
    return str;
}

$(document).ready(function(){

	// Fecha os alertas
	var display = $('.alertas').css('display');

	if(display == 'block'){
		setTimeout(function(){
			$('.alertas').hide('slow');
		},6000)
	}

	function redimensionar(){

		var height = $('.site').height();

		if(height < 640){
			var cH = $(document).height();
			$('.site').css('height', cH-303);
		}

		/*
		console.log('Largura do document: ' + $(document).width());
		console.log('Largura do window: ' + $(window).width()); // tamanho da janela
		console.log('Tamanho da imagem: ' + $('div.container-categorias div.det').width());
		*/

		var tamanhoJanela = $(window).width();

		if( tamanhoJanela > 1470){
			$('div.container-categorias div.det').css('right', 0);
		}
		else {
			var tamanhoImagem = $('div.container-categorias div.det').width();
			var tamanhoSite = 980;
			var alinhamento = 14;
			var right = (tamanhoImagem - ((tamanhoJanela - tamanhoSite) / 2)) * -1 ;
			right = right + alinhamento;
			$('div.container-categorias div.det').css('right', right );
		}

	}

	$(window).bind('resize', function(){
		redimensionar();
	});

	// window.onresize = redimensionar;
	redimensionar();

    $('#menunav .menuprodutos').bind('click', function(){
        if($('#menunav .menuprodutos').data('click')){
            return true;
        }
        $('#menunav .menuprodutos').data('click', true);
        $('#menunav2').slideToggle();
        return false;
    });

    rebind_img_js_detalhe();

    function swiperightHandler( event ){
        // console.log(1);

        var obj = $(event.target);
        // console.log(obj);

        if(obj.data('src1')){
            var indice = new Number(obj.data('indice'));
            var src = 'src' + indice;
            var newImg = new Image;

            if(!obj.data(src)){
                src = 'src1';
                indice = 1;
            }
            else {
                indice ++;
            }

            obj.data('indice', indice);

            newImg.onload = function() {
                obj.attr('src', this.src);
                obj.animate({opacity:1},100);
            };

            obj.animate({opacity:.9},100);
            newImg.src = obj.data(src);

        }

    }

    $('img.js-detalhex').on('swiperight', swiperightHandler);

    /*
    var delay=1000, setTimeoutConst;
    $('img.js-detalhe').bind('mousemove',
        function(){

            var obj = $(this);

            if(obj.data('src1')){

                setTimeoutConst = setTimeout(function(){

                    var indice = new Number(obj.data('indice'));
                    var src = 'src' + indice;
                    var newImg = new Image;

                    if(!obj.data(src)){
                        src = 'src1';
                        indice = 1;
                    }
                    else {
                        indice ++;
                    }

                    obj.data('indice', indice);

                    newImg.onload = function() {
                        obj.attr('src', this.src);
                    }

                    newImg.src = obj.data(src);

                }, delay);

            }
        }
    );
    */

    $('.abre-mobile').bind('click',function(){
        if($('#menu-mobile').hasClass('hidden-xs')){
            $('#menu-mobile').hide().removeClass('hidden-xs');
            $('#menu-mobile').slideDown();
        }
        else {
            $('#menu-mobile').slideUp('fast');
            $('#menu-mobile').addClass('hidden-xs');
        }
    });
	
	
	$("._count").each(function(){
		setCounts(this);	
	});
	
	$("._count").bind("keyup",function(){
		setCounts(this);
		if(_caracs>=_count)this.value = this.value.substring(0,_count);
	});

});

function setCounts(el){
	_count = eval($(el).data("count"));
	_caracs = eval(el.value.length);
	_name = el.name.replace(/[\[\]]/g,"_");
	_rest = (_count-_caracs)<0?0:(_count-_caracs);
	if($("."+_name).size()>0){
		$("."+_name).html("Caracteres restantes : "+_rest+"");
	}else{
		$(el).after("<p class='"+_name+"'>Caracteres restantes : "+_rest+"</p>");			
	}	
}
$(document).ready(function(){
	//$( '.s_tooltipe' ).tooltip();

	
	
	$(".rfpop").bind("click",function(){
		box_msg = $("<div class='box_msg'></div>");
		url = $(this).attr("href");
		$.ajax({
			url : url,
			success : function(out){
				box_msg.append(out);				
				$('body').append(box_msg[0]);
				box_msg.show();
				
				setTimeout(function(){					
					wid = box_msg.children('div').innerWidth();
					fechar = "<span class='box_msg_fechar'><span class='msg_fechar'>FECHAR</span></span>";
					box_msg.children('div.rf_pop').prepend(fechar);
				
					box_msg.children('div.rf_pop').children('span').children('.msg_fechar').bind("click",function(){
						$(this).parent().parent().parent().remove();
					});			
				},300);
			}
		});
		return false;
	});
});
function setFormularios(fomulario){
	//Formulario Envios
	//$("#cPagamento").ajaxForm({
	$(fomulario).ajaxForm({
		dataType:  "json",
		beforeSubmit : function(){
			setMensagem("Aguarde ...");
			$(".ajx").removeClass("q_form_erro");
			$(".ajx").attr('title','');	
			
			$(".s_form_ajax").each(function(){
				if(this){
					_label = $(this).children('label');				
					for(i=0; i<_label.length; i++){
						elem = $(_label[i]).children('.ajx');
						name  = elem.attr('name');
						value = elem.val();	
						
						er = /[\[,\]]/g;
						if(er.test(name)){				
							id = name.substring(name.indexOf("[")+1,name.indexOf("]"));		
						}else{						
							id = name.replace(er,"_");		
						}					
						elem.addClass('ajx_'+id);
					}
				}
			});	
		},
		success : function(out){
			
			if(out['refresh']){
				location.reload();
			}
		
			if(out['status']==0){			
				if(out['erros']){
					$.map(out['erros'], function(value, index){
						array[index] = value;
						obj = $(".ajx_"+index);
						obj.addClass("q_form_erro");
						obj.attr("title",value);
						obj.bind('focus',function(){
							if($(this).hasClass('ajx_formapagamento_id')){
								$(".ajx_formapagamento_id").removeClass("q_form_erro");$(".ajx_formapagamento_id").attr("title",'');
							}else{
								$(this).removeClass("q_form_erro");$(this).attr("title",'');
							}
						});
						obj.bind('click',function(){
							if($(this).hasClass('ajx_formapagamento_id')){
								$(".ajx_formapagamento_id").removeClass("q_form_erro");$(".ajx_formapagamento_id").attr("title",'');
							}else{
								$(this).removeClass("q_form_erro");$(this).attr("title",'');
							}
						});
					});
				}
				
				if(out['script']){
					setTimeout( function(){
						eval(out['script']);
					},400);
				}

				
				// a Cielo recomenda que se apague as informa??es do cart?o caso d? algum erro
                $('[name="gateway[parcelas]"]').val(1);
                $('[name="gateway[nome_titular]"]').val('');
                $('[name="gateway[numero_cartao]"]').val('');
                $('[name="gateway[mes_validade]"]').val('');
                $('[name="gateway[ano_validade]"]').val('');
                $('[name="gateway[cod_seguranca]"]').val('');
				
				setMensagem(out['msg']);
				//btFinalizaCompra();
			}			
			if(out['status']==1){
			
				if(out['script']){
					eval(out['script']);
				}
				
				setMensagem(out['msg']);
				setTimeout(function(){
					if(out['redireciona']){
						location.href = out['redireciona'];
					}else{
						location.reload();
					}
				},1400);
			}
			
			if(out['closemsg']){
				closeMessagem();
			}
			
		}
	}); 	
}

function setFormAjax(form){
	$(form).ajaxForm({
		dataType:  "json",
		beforeSubmit : function(){
			//setMensagem("Aguarde...");
			$(".ajx").removeClass("q_form_erro");
			$(".ajx").attr('title','');	
			
			$(".s_form_ajax").each(function(){
				if(this){
					_label = $(this).children('label');				
					for(i=0; i<_label.length; i++){
						elem = $(_label[i]).children('.ajx');
						name  = elem.attr('name');
						value = elem.val();	
						
						er = /[\[,\]]/g;
						if(er.test(name)){				
							id = name.substring(name.indexOf("[")+1,name.indexOf("]"));		
						}else{						
							id = name.replace(er,"_");		
						}					
						_cl = 'ajx_'+id;
						console.log(_cl);
						elem.addClass(_cl);
					}
				}
			});	
		},
		success : function(out){
			
			if(out['refresh']){
				location.reload();
			}
		
			if(out['status']==0){
				_msg = "Preencha os campos corretamente";
				if(out['erros']){
					$.map(out['erros'], function(value, index){
						//array[index] = value;
						obj = $(".ajx_"+index);						
						obj.addClass("q_form_erro");
						obj.attr("title",value);
						$("#"+index+"_msg").html(value);
						obj.bind('focus',function(){
							$(this).removeClass("q_form_erro");$(this).attr("title",'');
						});
						obj.bind('click',function(){							
							$(this).removeClass("q_form_erro");$(this).attr("title",'');
						});

						/*if(index=='cor_gravacao'){
							_msg =value;					
						}*/
					});
				}
				
				$("#_msg_finaliza").html("<div class='container_erro'><p>"+_msg+"</p></div>").fadeIn();
				
				setTimeout( function(){
					$("#_msg_finaliza").html("").fadeOut();
				},2300);
				//setMensagem(out['msg']);
				//btFinalizaCompra();
			}			

			_return = 1;
			if(out['status']==1){
				if(out['sucesso']){
					$.map(out['sucesso'], function(value, index){
						//array[index] = value;
						obj = $(".ajx_"+index);						
						obj.addClass("q_form_sucesso");
						obj.attr("title",value);
						$("#"+index+"_msg").html(value);
						obj.bind('focus',function(){
							$(this).removeClass("q_form_erro");$(this).attr("title",'');
						});
						obj.bind('click',function(){							
							$(this).removeClass("q_form_erro");$(this).attr("title",'');
						});
					});
				}
				
				//setMensagem(out['msg']);
				/*setTimeout(function(){
					if(out['redireciona']){
						location.href = out['redireciona'];
					}else{
						location.reload();
					}
				},1400);*/
			}

			$(".select_cor").each(function(){				
				if(this.value=="" || this.value<=0){
					$("#_msg_finaliza").html("<div class='container_erro'><p>Selecione a cor para os produtos</p></div>").fadeIn();
					setTimeout( function(){
						$("#_msg_finaliza").html("").fadeOut();
					},2300);
					_return = 0;
				}
			});
			
			$(".select_grav").each(function(){
				if(this.value=="" || this.value<=0){
					$("#_msg_finaliza").html("<div class='container_erro'><p>Selecione a grava��o para os produtos</p></div>").fadeIn();
					setTimeout( function(){
						$("#_msg_finaliza").html("").fadeOut();
					},2300);
					_return =  0;
				}
			});
			
			if(_return){
				if(out['redireciona']){
					location.href = out['redireciona'];
				}
			}
			if(out['js_function']){
				eval(out['js_function']);
			}
			
		}
	}); 
	
}
function setMensagem(msg){
	$(".g_mensagem .g_content ._view").html(msg);
	$(".g_mensagem").fadeIn('fast');
	// setTimeout( function(){
		// $(".g_mensagem").fadeOut('fast');
		// $(".g_mensagem .g_content ._view").html("");
	// },3000 );
}

function closeMessagem(){
	$(".g_mensagem").fadeOut('fast');
	$(".g_mensagem .g_content ._view").html("");
}
function setMensagemForm(msg){
	$(".g_mensagemForm .g_content ._view").html(msg);
	$(".g_mensagemForm").fadeIn('fast');
	// setTimeout( function(){
		// $(".g_mensagem").fadeOut('fast');
		// $(".g_mensagem .g_content ._view").html("");
	// },3000 );
}
$(document).ready(function(){

	

	//ptl.part-dados-cliente-checkout.html
		var csj_count=100;
		csj_style = "<style type='text/css' id='cjs_style'></style>";
		$('head').append(csj_style);
		$(".cjs").each(function(){
			csj_class = "cjs_"+csj_count;
			obj = $(this).data();
			$(this).addClass(csj_class);
			array = $.map(obj, function(value, index){return index.replace(/_/g,"-")+":"+value+";";});
			csj_classes = "."+csj_class+"{"+array.join().replace(/,/g,"")+"}";
			$("#cjs_style").append(csj_classes);
			csj_count++;
			$('[name="cadastro[fone_res]"]').mask('?(99)9999-99999');
		});
		
		//tp.widget.html
		$('#formContatoMobile').ajaxForm({
            dataType: 'json'
            ,type: 'POST'
            ,success: function(out){
                if(out.status){
                    $('.js-contato-msg-mobile').html(out.msg).addClass('alert-success').fadeIn();
                    $('.js-contato-btn-mobile').attr('disabled', true).val('OK, MENSAGEM ENVIADA');
                }
                else {
                    $('.js-contato-msg-mobile').html(out.msg).addClass('alert-danger').fadeIn();
                    $('.js-contato-btn-mobile').attr('disabled', false).val('ENVIAR');
                }
            }
            ,beforeSend: function(){
                $('.js-contato-btn-mobile').attr('disabled', true).val('AGUARDE...');
                $('.js-contato-msg-mobile').fadeOut().removeClass('alert-danger').removeClass('alert-success');
            }
            ,error: function(){
                $('.js-contato-msg-mobile').html('Falha ao enviar mensagem').addClass('alert-danger').fadeIn();
                $('.js-contato-btn-mobile').attr('disabled', false).val('ENVIAR');
            }
		});
		
		// Compartilhamento facebook
function fbNewPost(url){
   
    var alt = 600;
    var larg = 800;
    var faceUrl = "https://www.facebook.com/sharer/sharer.php?u=" + url + "&amp;src=sdkpreparse";
    window.open(faceUrl,'popup','width=' + alt +','+ 'height='+ larg);
}

$(document).ready(function(){

    $('[name="cadastro[cnpj]"]').mask('99.999.999/9999-99');
    $('[name="cadastro[cpf]"]').mask('999.999.999-99');
    $('[name="cadastro[cep]"]').mask('99999-999');
    
    
    // CONSULTANDO CEP VIACEP 
    $("input[name='cadastro[cep]']").blur(function(){
        let cep = $(this).val();
        cep = cep.replace('-','');
        
        $.ajax({
           url : 'https://viacep.com.br/ws/'+cep+'/json/?callback=?'
           ,method : 'POST'
           ,dataType : 'json'
           ,success : function(out){
               $('input[name="cadastro[logradouro]"]').val(out.logradouro);
               $('input[name="cadastro[bairro]"]').val(out.bairro);
               $('input[name="cadastro[cidade]"]').val(out.localidade);
               $('input[name="cadastro[uf]"]').val(out.uf);
             
               $('input[name=\'filial['+i+'][numero]\']').focus();
           }
           ,beforeSend : function(){
              
               $('input[name="cadastro[logradouro]"]').val('Aguarde ...');
               $('input[name="cadastro[bairro]"]').val('Aguarde ...');
               $('input[name="cadastro[cidade]"]').val('Aguarde ...');
               $('input[name="cadastro[uf]"]').val('Aguarde ...');
           },
           error : function(){
            
               alert('Houve um erro ao buscar CEP. Preencha manualmente.');
           } 
       });
   
    });

    $('input[name="cadastro[fone_res]"]').bind("keypress",function(e){
        mascPhone(e,this);
    });

    $('input[name="cadastro[fone_com]"]').bind("keypress",function(e){
        mascPhone(e,this);
    });

    $('input[name="cadastro[fone_cel]"]').bind("keypress",function(e){
        mascPhone(e,this);
    });

});


});


// Compartilhamento de redes sociais

function fbNewPost(url){
    let alt = 600;
    let larg = 800;
    let faceUrl = "https://www.facebook.com/sharer/sharer.php?u=" + url + "&amp;src=sdkpreparse";
    window.open(faceUrl,'popup','width=' + alt +','+ 'height='+ larg);
}

function LinkedinNewPost(url){
    let alt = 600;
    let larg = 800;
    let linkedinUrl = "https://www.linkedin.com/shareArticle?mini=true&url=" + url;
    window.open(linkedinUrl,'popup','width=' + alt +','+ 'height='+ larg);
}

function whatsappNewPost(url,titulo){
    let conteudo = titulo + " " + url;
    document.getElementById("whats-share").href = "https://api.whatsapp.com/send?text=" + conteudo;
}

function twitterNewPost(url,titulo){
    let alt = 600;
    let larg = 800;
    let twitterUrl = "https://twitter.com/intent/tweet?url=" + url + "&text=" + titulo;
    window.open(twitterUrl,'popup','width=' + alt +','+ 'height='+ larg);
}


