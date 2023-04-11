
$(document).ready(function(){
    setFormAjax(document.getElementById('formCadastro'));

    let cart_inputs = document.querySelectorAll(".qtd-pedido"),
    updated = true

    function updateCart(){
        let ids = document.querySelectorAll(".item_excluir.btn:not(.excluir_mobile)"),
        qtds = document.querySelectorAll(".qtd-pedido"),
        data = []

        ids.forEach((id, index) => {
            data.push({id: id.dataset.id, qtd: qtds[index].value, qtd2: qtds[1].value, qtd3: qtds[2].value})
        });

        $.ajax({  
            type: 'POST',  
            url: 'index.php/atualizar_carrinho', 
            data: {data},
            success: (message) => {
                window.onbeforeunload = null
            }
        });
    }

    cart_inputs.forEach(input => {
        input.onchange = () => {
            updated = false
        }
    });

    window.onbeforeunload = () => {
        if (!updated) {
            updateCart()
        }
    }

    $('.abrir_formulario').bind('click',function(e){

        if (!updated){
            if(window.confirm(`Salvar alterações do carrinho?`)){
                updateCart()
            } else {
                return false
            }
        } 

        $('.d_cadastro').fadeIn("fast");
        $('.abrir_formulario').fadeOut();

    });

    // bt enviar
    $('.fc_enviar').bind('click',function(){
        //redireciona = $(this).data('redir');
        //$("#fc_redireciona").val(redireciona);

        $(".qtdmin-msg").remove();
        _qtdmin = false;
        $(".qtd_minima").each(function(e){
            val = this.value;
            min = $(this).data("qtdminima");
            if(val!="" && val<min){
                $(this).addClass("r-alert");
                $(this).after("<span class='qtdmin-msg'>Min."+min+"</span>");
                _qtdmin = true;
            }
        });

        if(_qtdmin){
            alert("Corrija as quantidades.");
            ypos=$("#d_container_pedido").position().top;
            $("html, body").animate({ scrollTop: ypos }, 1000);
            return false;
        }

        $("#formCadastro").submit();
    });


    $('.btn-finalizar-email').bind('click', function(){
        $('.d-car-email').fadeOut('fast');
        $('.d-car-cadastro').fadeIn();
    });


    $('[name="cadastro[cnpj]"]').mask('?99.999.999/9999-99');
    $('[name="cadastro[fone_com]"]').mask('?(99)9999-99999');
    $('[name="cadastro[email]"]').bind('change', function(){
        var txtemail = $('[name="cadastro[email]"]');
        var txtlogin = $('[name="cadastro[login]"]')
        if( txtemail.val() != '' && txtlogin.val() == ''){
            txtlogin.val(txtemail.val());
        }
    });

    $('#formlogin').ajaxForm({
        dataType: 'json'
        ,beforeSend: function(){
            $('#formlogin').attr('disabled', true).css('opacity', .7);
            $('#d-erro-1').hide().html('');
        }
        ,success: function(out){
            if (out.status){
                window.location.href = new String(out.url_redirect);
            }
            else {
                // set_erro();
                $('#d-erro-1').html(out.msg).slideDown();
                $('#formlogin').attr('disabled', false).css('opacity', 1);
            }
        }
    });

    $('#formcadastro').ajaxForm({
        dataType: 'json'
        ,beforeSend: function(){
            $('#formcadastro').attr('disabled', true).css('opacity', .7);
            $('#d-erro-2').hide().html('');
        }
        ,success: function(out){
            if (out.status) {
                window.location.href = new String(out.url_redirect);
            }
            else {
                // set_erro();
                $('#d-erro-2').html(out.msg).slideDown();
                $('#formcadastro').attr('disabled', false).css('opacity', 1);
            }
        }
    });

    $(".quantidade_item").bind("blur",function(){

        item_id   = $(this).data('id');
        unique_id = $(this).data('unique_id');
        qtd_indice = $(this).data('qtd_indice');

        if(document.getElementById("item_preco_"+item_id)){
            item_preco = eval($("#item_preco_"+item_id).data('preco').replace(",","."));
        }

        qtd = eval($(this).val());

        if(  $(this).data('qtd_minima') ){
            if( $(this).val() < $(this).data('qtd_minima') ){
                qtd = $(this).data('qtd_minima');
                $(this).val(qtd);
            }
        }else {
            if( ! $(this).val() ){
                qtd = 1;
                $(this).val(qtd);
            }
        }

        _url = "{index}pedido_alt/?ajax=1&qtd="+qtd+"&unique_id="+unique_id+"&token={token}&qtd_indice="+qtd_indice;
        $.ajax({
            url : _url,
            success : function(out){
            }
        });

        if(document.getElementById("item_preco_"+item_id)){
            sub_total = item_preco*qtd;
            sub_total = formata(sub_total.toFixed(2));
            $("#item_subtotal_"+item_id).html("R$ "+sub_total);
            $("#item_subtotal_hidden_"+item_id).val(sub_total);
        }
    });

    $('.item_excluir').bind('click',function(){
				item_id = $(this).data('id');

        $.ajax({
                  //verificar o {index}
                 url : "index.php/itemExcluir",
                data : {item : item_id},
            dataType : 'json',
             success : function(out){
                if(out[0]==0){
                   alert("Ocorreu um erro ao tentar remover o produto, tente novamente.");
                }
                if(out[0]==1){
                    $("._produto_"+item_id).remove();
                }
                if(out[0]==2){
                    location.reload();
                }
            }

        });
        return false;
    });

    setTimeout(function(){
        _top = $('#menu_pedido').position().top+$('header').height();
        $('html,body').animate({scrollTop:_top}, 600);
    },140);

    function qtd1_(id){
        document.getElementById("qtd1mobile_"+id).value = document.getElementById("qtd_1_"+id).value;
    }
     function qtd2_(id){
        document.getElementById("qtd2mobile_"+id).value = document.getElementById("qtd_2_"+id).value;
    }
    function qtd1_mobile_(id){
        document.getElementById("qtd_1_"+id).value = document.getElementById("qtd1mobile_"+id).value;
    }
    function qtd2_mobile_(id){
        document.getElementById("qtd_2_"+id).value = document.getElementById("qtd2mobile_"+id).value;
    }

    function gradeProdutos(){
        /*
        _sizeLi = $(".titulos_pedido ul li").size();
        _widthUl = $(".titulos_pedido ul").width();
        _result = _widthUl/_sizeLi;
        _result = (_result*100)/_widthUl;
        $(".titulos_pedido ul li").css("width",_result+"%");
        $(".p_item").css("width",_result+"%");
        */
    }
});

$("#cad_fone_res").bind('input propertychange',function(){

    var texto = $(this).val();

    texto = texto.replace(/[^\d]/g, '');

    if (texto.length > 0)
    {
    texto = "(" + texto;

        if (texto.length > 3)
        {
            texto = [texto.slice(0, 3), ") ", texto.slice(3)].join('');
        }
        if (texto.length > 12)
        {
            if (texto.length > 13)
                texto = [texto.slice(0, 10), "-", texto.slice(10)].join('');
            else
                texto = [texto.slice(0, 9), "-", texto.slice(9)].join('');
        }
            if (texto.length > 15)
               texto = texto.substr(0,15);
    }
   $(this).val(texto);
})
