$(function () {
    var tabContainers = $('div.tabs > div');
var hotel = window.location.hash.replace('#','');
    tabContainers.hide().filter(':first').show();

    $('div.tabs ul.tabNavigation a').click(function () {
        tabContainers.hide(); 
        tabContainers.filter(this.hash).show();
        $('div.tabs ul.tabNavigation a').removeClass('selected'); 
        $(this).addClass('selected'); 
        return false;
    }).filter(':first').click();
setTimeout(function(){
$('div.tabs ul.tabNavigation a[href="/#'+hotel +'"]').click();
}, 0);
});
$('.deeper').hover(function(){
		$(this).children('.subul').show();
  }, function() {
    $(this).children('.subul').hide();
});

$('#az').click(function(){$('.prstbl').hide();$('#taz').show();});
$('#al').click(function(){$('.prstbl').hide();$('#tazlnd').show();});
$('#rio').click(function(){$('.prstbl').hide();$('#trio').show();});



function bron_begin() {
    c = $('input[id^="brr_param_"]:checked').length;
    if(!c) {
	alert('Пожалуйста, выберите желаемый заезд');
	return;
    }
    $.post("/services/bron.php", { 'r' : Math.random(), 'action' : 'bron_begin', 'brr_name1' : $('#brr_name1').val(), 'brr_name2' : $('#brr_name2').val(), 'brr_phone1' : $('#brr_phone1').val(), 'brr_email' : $('#brr_email').val(), 'brr_coment' : $('#brr_coment').val(), 'params' : $('input[id^="brr_param_"]:checked').val(), 'subscribe' : $('#brr_subscribe').attr('checked') ? 1 : 0  }, function(res) {
	if(res.indexOf("success")>=0) {
        alert('Ваша заявка успешно отправлена.');
         $('#brr_name1').val('');
         $('#brr_name2').val('');
         $('#brr_phone1').val('+7');
         $('#brr_email').val('');
         $('#brr_coment').val('');

        return;
	    res = res.split('#');
	    if(res[1].length) window.location.href = '/services/mb-search/?s=1&bn=' + res[1]; else window.location.href = '/services/mb-search/?s=1';
	} else {
	    alert(res);
	}
    });
}   
$(document).ready(function(){  

$(".mstatus").fadeOut(5000);                                       
$('.view-source .hide').hide();      
$('.view-source a').on('click',function(e){         
if($(this).parent().find('.hide.show').length){             
$(this).parent().find('.hide').hide(500);             
$(this).parent().find('.hide').removeClass('show');             
$(this).html('Читать полностью');       
}else{         
$(this).parent().find('.hide').show(500);         
$(this).parent().find('.hide').addClass('show');         
$(this).html('Свернуть текст');       
}       
return false;    
});

$('.inpagegal').children('a').each(function(){
								var hrff = $(this).attr('href');
								$(this).css({'background': 'url(' + hrff + ')no-repeat', 'background-size': 'cover', 'background-position': 'center', 'text-decoration': 'none'});
});
}); 

$('#subrio').hover(function(){$('#subrioul').show();}, function() {$('#subrioul').hide();});
$('#subazlnd').hover(function(){$('#subazlndul').show();}, function() {$('#subazlndul').hide();});
$('#subaz').hover(function(){$('#subazul').show();}, function() {$('#subazul').hide();});
$('#conts').hover(function(){$('#subconts').show();}, function() {$('#subconts').hide();});


(function(){ var widget_id = 'ENduJgScgS';
var s = document.createElement('script'); s.type = 'text/javascript'; s.async = true; s.src = '//code.jivosite.com/script/widget/'+widget_id; var ss = document.getElementsByTagName('script')[0]; ss.parentNode.insertBefore(s, ss);})();

