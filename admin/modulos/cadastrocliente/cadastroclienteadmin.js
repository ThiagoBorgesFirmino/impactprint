/** MÁSCARACA PARA TELEFONE/CELULAR */
function mascPhone(a,c){
    
    mask = '#### ####-####';
    var patrn=/^\([1-9]/;
    8!=a.keyCode&&9!=a.keyCode&&((""==c.value||14<=c.value.length)&&$("#"+c.id).keyup(function(){
        14==this.value.length&&"-"!=this.value.charAt(9)&&(this.value=this.value.replace("-",""),this.value=[this.value.slice(0,9),"-",this.value.slice(9)].join(""));
        15==this.value.length&&"-"!=this.value.charAt(10)&&(this.value=this.value.replace("-",""),this.value=[this.value.slice(0,10),"-",this.value.slice(10)].join(""))
    }),
    ""==c.value?c.value="(":1==c.value.length&&"("!=c.value[0]?c.value="("+c.value:3==c.value.length&&(c.value+=")"),c.value.match(patrn)?$("#"+c.id).attr("maxlength","15"):$("#"+c.id).attr("maxlength","14"));
    window.event?_TXT=a.keyCode:a.which&&(_TXT=a.which);if(47<_TXT&&58>_TXT){
        var f=c.value.length,d=mask.substring(0,1),f=mask.substring(f);f.substring(0,1)!=d&&(c.value+=f.substring(0,1));return!0;
    }
    return 8!=_TXT?!1:!0;
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