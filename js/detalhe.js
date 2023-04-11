if($(".img_detalhe").size()<2){
    $(".bt_img_detalhe").remove();
}

$(".img_detalhe, .imagem_cor").bind("click",function(){
    trocaImagemPrincipal(this);
});

$(".seta_mobile").bind('click',function(){

    var obj = $(this);
    var indice = $(".js-imagemprincipal").data("indice");
    var imagens = [];

    $('.img_detalhe').each(function(){
        imagens[imagens.length] = this;
    });

    if(obj.hasClass('next')){
        indice ++;
        if(indice == imagens.length){
            indice = 0;
        }
    }

    if(obj.hasClass('prev')){
        indice --;
        if(indice == -1){
            indice = imagens.length-1;
        }
    }

    for(var i=0;i<imagens.length;i++){
        if(i==indice){
            trocaImagem($(imagens[i]));
            $(".js-imagemprincipal").data('indice', i);
            break;
        }
    }
});

$(".fancybox").fancybox({
    padding: 0,
    openEffect : 'elastic',
    openSpeed  : 350,
    closeEffect : 'elastic',
    closeSpeed  : 350,
    closeClick : true
});

$(".rede_indique").bind("click",function(){
    $(".indique_box").fadeIn();
    return false;
});

$("#form_indique").ajaxForm({
    dataType:  "json",
    beforeSubmit : function(){
        $(".msg_indique").fadeIn();
        $(".msg_indique").html("Enviando...");
    },
    success : function(out){
        if(out[0]==0){
            $(".msg_indique").html(out[1]);
            //$(".c_mensagem").animate({"left":0},400);
        }
        if(out[0]==1){
            $(".msg_indique").html(out[1]);
            //$(".block_login .l_mensagem").animate({"left":0},400);
            //setTimeout(function(){location.reload();},1000);
        }
        //setTimeout(function(){$(".block_login .l_mensagem").animate({"left":"100%"},300);},2500);
    }
});

function trocaImagemPrincipal(obj){

    $("#imagem_principal").attr("src","{path}/img/loading.gif");

    _src    = $(obj).data("src");
    _timsrc = $(obj).data("timsrc");
    _indice = $(obj).data("indice");

    if($(obj).data("corid")){
        $("#item_cor_id").val($(obj).data("corid"));
    }
    if($(obj).data("itemid")){
        $("#item_id").val( $(obj).data("itemid") );
    }
    if($(obj).data("itemreferencia")){
        //$("#item_referencia").html( $(obj).data("itemreferencia") );
    }
    if($(obj).data("preco")){
        if($("#item_preco")){
            $("#item_preco").html( $(obj).data("preco") );
        }
    }

    setTimeout(function(){
        $("#imagem_fancy").attr("href",_src);
        $("#imagem_principal").attr("src",_timsrc);
        $("#imagem_principal").data("indice",_indice);
    },500);
}

function pinterestShare(url, media, description, winWidth, winHeight) {
    var winTop = (screen.height / 2) - (winHeight / 2);
    var winLeft = (screen.width / 2) - (winWidth / 2);
    window.open('http://br.pinterest.com/pin/create/button/?&url=' + url + '&media=' + media + '&description=' + description, 'sharer', 'top=' + winTop + ',left=' + winLeft + ',toolbar=0,status=0,width='+winWidth+',height='+winHeight);
    // http://br.pinterest.com/pin/create/button/?&url=
}

$('.pinterest').bind('click', function(){
    pinterestShare('{config->URL}index.php/detalhe/{produto->tag_nome}', '{config->URL}img/produtos/2/{produto->imagem}', '{produto->nome}', 720, 290);
    return false;
});

// Compartilhamento facebook
// function fbNewPost(url){
   
//     var alt = 600;
//     var larg = 800;
//     var faceUrl = "https://www.facebook.com/sharer/sharer.php?u=" + url + "&amp;src=sdkpreparse";
//     window.open(faceUrl,'popup','width=' + alt +','+ 'height='+ larg);
// }


// $('.js-fb-share').bind('click', function(){
//     fbShare($('#urlshare').val(), EMPRESA, 'Facebook share popup', $('#urlshareimg').val(), 600, 350);
//     // fbShare('{config->URL}index.php/detalhe/{produto->tag_nome}', '{config->EMPRESA}', 'Facebook share popup', '{config->URL}img/produtos/2/{produto->imagem}', 600, 350);
//     return false;
// });

// $('.js-twitter-share').bind('click', function(){
//     var winWidth = 600;
//     var winHeight = 350;
//     var winTop = (screen.height / 2) - (winHeight / 2);
//     var winLeft = (screen.width / 2) - (winWidth / 2);
//     window.open('https://twitter.com/intent/tweet?text=Vejam esse brinde incrível '+$('#urlshareimg').val()+'&source=webclient', 'sharer', 'top=' + winTop + ',left=' + winLeft + ',toolbar=0,status=0,width='+winWidth+',height='+winHeight);
//     return false;
// });

// $('.js-google-share').bind('click', function(){
//     var winWidth = 600;
//     var winHeight = 350;
//     var winTop = (screen.height / 2) - (winHeight / 2);
//     var winLeft = (screen.width / 2) - (winWidth / 2);
//     window.open('https://plus.google.com/share?url='+$('#urlshareimg').val(), 'sharer', 'top=' + winTop + ',left=' + winLeft + ',toolbar=0,status=0,width='+winWidth+',height='+winHeight);
//     return false;
// });


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




$('img.js-detalheimg').hover(
    function(){

        var obj = $(this);

        if(obj.data('timsrc')){

            var src = obj.data('timsrc');
            var destino = $('.js-imagemprincipal img');
            var newImg = new Image;

            destino.animate({opacity:.9},100);

            if(!obj.data('load')){

                newImg.onload = function() {
                    obj.data('load',1);
                    destino.attr('src', this.src);
                    destino.attr('src', this.src);
                    destino.animate({opacity:1},100);
                };

                newImg.src = src;

            }
            else {
                destino.attr('src', src);
                destino.animate({opacity:1},100);
            }

            $("#imagem_fancy").attr("href",obj.data('src'));

        }
    }
    ,function(){
        /*
         var obj = $(this);
         if(obj.data('src1')){
         obj.attr('src', obj.data('src'));
         }
         */
    }
);

$('img.js-corimg').hover(
    function(){

        var obj = $(this);

        if($('.js-corimgall').data('clicked')){
            return;
        }

        if(obj.data('timsrc')){

            var src = obj.data('timsrc');
            var destino = $('.js-imagemprincipal img');
            var newImg = new Image;

            $('.js-corimgall').removeClass('selected').css('opacity',.8).css('border','1px solid transparent');

            obj.addClass('selected').css('opacity',1).css('border','1px solid #fff');

            destino.animate({opacity:.9},100);

            if(!obj.data('load')){

                newImg.onload = function() {
                    obj.data('load',1);
                    destino.attr('src', this.src);
                    destino.animate({opacity:1},100);
                };

                newImg.src = src;

            }
            else {
                destino.attr('src', src);
                destino.animate({opacity:1},100);
            }

            if(obj.data("corid")){
                $("#item_cor_id").val(obj.data("corid"));
            }
            if(obj.data("itemid")){
                $("#item_id").val(obj.data("itemid") );
            }
            if(obj.data("itemreferencia")){
                //$("#item_referencia").html( $(obj).data("itemreferencia") );
            }
            if(obj.data("preco")){
                if($("#item_preco")){
                    $("#item_preco").html(obj.data("preco") );
                }
            }
        }
    }
    ,function(){
    }
);

$('img.js-corimg').bind('click', function(){

    var obj = $(this);

    var src = obj.data('timsrc');
    var destino = $('.js-imagemprincipal img');
    var newImg = new Image;

    $('.js-corimgall').data('clicked',1).removeClass('selected').css('opacity',.8).css('border','1px solid transparent');
    obj.addClass('selected').css('opacity',1).css('border','1px solid #fff');

    if(obj.data("corid")){
        $("#item_cor_id").val(obj.data("corid"));
    }
    if(obj.data("itemid")){
        $("#item_id").val(obj.data("itemid") );
    }
    if(obj.data("itemreferencia")){
        //$("#item_referencia").html( $(obj).data("itemreferencia") );
    }
    if(obj.data("preco")){
        if($("#item_preco")){
            $("#item_preco").html(obj.data("preco") );
        }
    }

    destino.animate({opacity:.9},100);

    if(!obj.data('load')){

        newImg.onload = function() {
            obj.data('load',1);
            destino.attr('src', this.src);
            destino.animate({opacity:1},100);
        };

        newImg.src = src;

    }
    else {
        destino.attr('src', src);
        destino.animate({opacity:1},100);
    }

    $("#imagem_fancy").attr("href",obj.data('src'));

});

/**
 * Se o controle de cor for um select
 */

$('select.js-select-cor').bind('change', function(){

    var obj = $(this).find('option:selected');

    var src = obj.data('timsrc');
    var destino = $('.js-imagemprincipal img');
    var newImg = new Image;

    console.log(obj);
    console.log(obj.find('option:selected'));
    console.log(obj.find('option:selected').data("corid"));

    if(obj.data("corid")){
        $("#item_cor_id").val(obj.data("corid"));
    }
    if(obj.data("itemid")){
        $("#item_id").val(obj.data("itemid") );
    }
    if(obj.data("itemreferencia")){
        //$("#item_referencia").html( $(obj).data("itemreferencia") );
    }
    if(obj.data("preco")){
        if($("#item_preco")){
            $("#item_preco").html(obj.data("preco") );
        }
    }

    destino.animate({opacity:.9},100);

    if(!obj.data('load')){

        newImg.onload = function() {
            obj.data('load',1);
            destino.attr('src', this.src);
            destino.animate({opacity:1},100);
        };

        newImg.src = src;

    }
    else {
        destino.attr('src', src);
        destino.animate({opacity:1},100);
    }

    $("#imagem_fancy").attr("href",obj.data('src'));

});


function trocaImagem(obj){

    if(obj.data('timsrc')){

        var src = obj.data('timsrc');
        _timsrc = $(obj).data("timsrc");
        _indice = $(obj).data("indice");
        var destino = $('.js-imagemprincipal img');
        var newImg = new Image;

        destino.animate({opacity:.9},100);

        if(!obj.data('load')){

            newImg.onload = function() {
                obj.data('load',1);
                destino.attr('src', this.src);
                destino.attr('src', this.src);
                destino.animate({opacity:1},100);
            };

            newImg.src = src;

        }
        else {
            destino.attr('src', src);
            destino.animate({opacity:1},100);
        }

        $("#imagem_fancy").attr("href",obj.data('src'));
        $("#imagem_princiapl").attr("src",_timsrc);
        $("#imagem_princiapl").data("src",_indice);

    }

}

$(document).ready(function(){
    $("#thumbvideo").bind("click",function(){
        _src = $(this).data("src");
        $.ajax({
            url : "'.PATH_SITE.'ajax.php/getItemVideo/"
            ,data : {src : _src}
            ,success : function(out){
                $.fancybox(
                    out,
                {
                    padding     : 0,				 
                    openEffect  : "elastic",
                    openSpeed   : 350,
                    closeEffect : "elastic",
                    closeSpeed  : 350,
                    closeBtn    : false 
                });
            } 
        });
    });		
});

$('[data-toggle="tooltip"]').tooltip()

