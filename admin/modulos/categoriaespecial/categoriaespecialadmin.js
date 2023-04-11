$('.js-alterar-imagem').bind('click', function(){
    var obj = $(this);
    var file = $('#'+obj.data('imagem'));
    // $('.js-file').slideUp();
    if(file.html()==''){
        file.html(obj.data('file'));
    }
    file.toggle();
    /*
    if($('#'+obj.data('imagem')).html()==''){
        $('#'+obj.data('imagem')).html($('#'+obj.data('imagem')).data('html'));
    }
    $('#'+obj.data('imagem')).slideToggle();
    */
    return false;
});