$('.js-imagem').bind('click', function(){

    var obj = $(this);

    // alert(1);

    var params = {
        url: path + 'admin.php/importaimagem/analise'
        ,data: {imagem: obj.data('imagem') }
        ,type: 'POST'
        ,success: function(out){
            $('.js-analise').html(out);
        }
    }

    $.ajax(params);

});

$('.js-salvar button').bind('click', function(){

    var obj = $(this);

    var params = {
        url: path + 'admin.php/importaimagem/salvar'
        ,data: $('.js-analise *').serialize()
        ,type: 'POST'
        ,success: function(out){
            alert(out);
        }
    };

    $.ajax(params);

});

var bind_detalhe = function(){

}