$('.js-categoria input').bind('click', function(){
    var elem = $(this);
    if(elem[0].checked){
        $('.js-categoria-'+elem.val()).show();
    }
    else {
        elem.parent('label').parent('li').find('ul li label input').attr('checked', false);
        elem.parent('label').parent('li').find('ul li label').hide();
        elem.parent('label').parent('li').find('ul li').hide();
    }
});

$('.js-categoria input').each(function(i, obj){
    if(obj.checked){
        $('.js-categoria-'+obj.value).show();
    }
});

$('.js-alterar-imagem').bind('click', function(){
    var obj = $(this);
    $('.js-file').slideUp();
    if($('#'+obj.data('imagem')).html()==''){
        $('#'+obj.data('imagem')).html($('#'+obj.data('imagem')).data('html'));
    }
    $('#'+obj.data('imagem')).slideToggle();
    return false;
});

function cancelar_img(){
    $('.js-file').slideUp();
}

$('.js-nova-variacao').bind('click', function(){
    $('#nova_variacao').val(1);
    enviar('salvar');
});

$('.js-excluir-variacao').bind('click', function(){
    if(confirm('Tem certeza que deseja excluir?')){
        $('#variacao_id_excluir').val($(this).data('id'));
        enviar('excluir_variacao');
    }
    return false;
});