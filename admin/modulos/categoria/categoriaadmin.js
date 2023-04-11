

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

function PreviewTabelaItem(fator_id,item_id,url){
    $.ajax({
        url : url
        ,data : {"fator_id" : fator_id, "item_id" : item_id}
        ,beforeSend : function(){
            $("#tabeladeprecos").html("Aguarde ...");
        }
        ,success : function(out){
           $("#tabeladeprecos").html(out);
        }
    });
}