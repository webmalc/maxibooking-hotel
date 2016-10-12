get = new String(window.location);
x = get.indexOf('?');
gets = new Array();
if(x !== -1) {
    l = get.length;
    get = get.substr(x+1, l-x);
    l = get.split('&');
    x = 0;
    c  = l.length;
    gets = new Array(c);
    for(i in l) {
      get = l[i].split('=');
      gets[decodeURIComponent(get[0])] = decodeURIComponent(unescape(get[1]));
      x++;
    }
}

var filters         = false;
var hotelData       = false;
var postRoomType    = gets['roomType'] !== undefined ? gets['roomType'] : 0;
var postHotel       = gets['hotel'] !== undefined ? gets['hotel'] : 0;
var postAdults      = gets['adults'] !== undefined ? parseInt(gets['adults']) : -1;
var postChildrens   = gets['childrens'] !== undefined ? parseInt(gets['childrens']) : -1;
var postDateBegin   = gets['begin'] !== undefined ? gets['begin'] : '';
var postDateEnd     = gets['end'] !== undefined ? gets['end'] : '';
var postDiscount    = gets['discount'] !== undefined ? gets['discount'] : '';
var selectHotel     = 0;
var selectRoomType  = 0;
var selectDiscount  = '0#0';
var postAges = [];
for(i=1;i<=5;i++) postAges[i] = gets['age_'+i] !== undefined ? parseInt(gets['age_'+i]) : -1;

$(function(){
	initForm();

    $('.hinform a').click(function() {
        ga('send', 'event', 'Бронирование', 'Онлайн-оплата');
        yaCounter10885255.reachGoal('bron_pay_online');

        window.location.href = $(this).attr('href');

        return false;
    });

    $('#dataTable').find('.btn-send').click(function() {
        ga('send', 'event', 'Бронирование', 'Сервисы бронирования', 'Шаг 1');
        yaCounter10885255.reachGoal('bron_step1');
    });

    $('input[name=c_submit]').click(function() {
        ga('send', 'event', 'Бронирование', 'Сервисы бронирования', 'Шаг 2');
        yaCounter10885255.reachGoal('bron_step2');
    });
})

function initForm() {
	
	if(postHotel == 0) {
		if(window.location.href.indexOf('azovland')!=-1) postHotel = 2; else  
		if(window.location.href.indexOf('kazantip_klub')!=-1) postHotel = 3; else
		if(window.location.href.indexOf('misovoe')!=-1) postHotel = 4;
	}
	if($('#dateBegin').length==0) {
		setTimeout('initForm',10);
		return;
	}
	
	$('#dateBegin').mask('99.99.9999');
    $('#dateEnd').mask('99.99.9999');
    datePicker();
    
    $("select#hotel").chosen({disable_search_threshold: 999}).change(function(){  reSetRoomType(); reSetDiscount(); selectHotel = $('#hotel').val(); $('#dateBegin, #dateEnd').datepicker("remove"); datePicker(); });
    $("select#roomType").chosen({disable_search_threshold: 999});
    $("select#discount").chosen({disable_search_threshold: 999});//.val(postDiscount).trigger("chosen:updated");
    
    reSetHotel();
    reSetRoomType(); 
    reSetDiscount();
    
    if(postAdults >= 0) $('#adults').val(postAdults);
    if(postChildrens >= 0) $('#childrens').val(postChildrens);
    if(postDateBegin != '') $('#dateBegin').val(postDateBegin).datepicker("update", postDateBegin );
    if(postDateEnd != '') $('#dateEnd').val(postDateEnd).datepicker("update", postDateEnd );
    
    for(i=1;i<=5;i++) if(postAges[i] >= 0) $('#age_'+i).val(postAges[i]);
    
    $('.mb-filters').css('opacity','1');
    
    $('#hotel').trigger("change");
    
    $('#aznew_childs_age_close').click(function(){
    	$('#aznew_childs_age_area').stop().fadeOut(100);
    	$('#aznew_childs_age').removeClass('active');
    });
    $('#aznew_childs_age').click(function(){
    	if(!$('#aznew_childs_age_area').is(':visible')) $('#aznew_childs_age_area').css({'top':'-25px','opacity':'0'}).show().animate({'top':'-21px','opacity':'1'},200); else $('#aznew_childs_age_area').stop().fadeOut(100);
    	$('#aznew_childs_age').toggleClass('active');
    });
    
    /*���������� ���������� �������� � �����*/
    if($('#adults').length)
    	{
	    var defaultChild = $('#childrens').val(),
	    defaultAdult = $('#adults').val(),
	    allChild = $('.aznew_bron_childrens_all').children(),
	    allAge = $('#aznew_childs_age_ages').children(),
	    allAdult = $('.aznew_bron_adults_all').children(),
	    noChild = $('.aznew_bron_childrens_not'),
	    elCountAdult = $('#aznew_count_adult'),
	    elCountChild = $('#aznew_count_child');
	    
	    $('#adults').on('click change',function(){
	    	v = parseInt($(this).val());
	    	if(v <= 0) v = 1; else
	    	if(v >= 6) v = 6;
	    	$(this).val(v);
	    	allAdult.eq(v-1).addClass('activ').prevAll().addClass('activ')
	    	allAdult.eq(v-1).nextAll().removeClass('activ');
	    });
	    $('#childrens').on('click change',function(){
			  $('#aznew_childs_age_block').removeClass('hide');	
	    	v = parseInt($(this).val());
	    	if(v < 0) v = 0; else
	    	if(v >= 5) v = 5;
	    	$(this).val(v);
	    	allChild.removeClass('activ');
	    	if(v>0) {
		    	allChild.eq(v-1).addClass('activ').prevAll().addClass('activ');
		    	allAge.eq(v-1).show().prevAll().show();
		    	allAge.eq(v-1).nextAll().hide();
	    	}
	    	if(v == 0) {
	    		$('#aznew_childs_age_area').stop().fadeOut(100);
	    		$('#aznew_childs_age').removeClass('active');
	    	} else
		    if(!$('#aznew_childs_age_area').is(':visible')) {
		    	$('#aznew_childs_age_area').css({'top':'-25px','opacity':'0'}).show().animate({'top':'-21px','opacity':'1'},200);
		    	$('#aznew_childs_age').addClass('active');
		    }
	    });
	    $('#aznew_childs_age_close').click(function(){$('#aznew_childs_age_block').addClass('hide');});
	    allAdult.eq(defaultAdult-1).addClass('activ').prevAll().addClass('activ');
	    elCountAdult.text(defaultAdult);
	    
	    if(defaultChild == 0)
	    	{
	    	noChild.addClass('activ');
	    	
	    	}
	    else
	    	{
	    	allChild.eq(defaultChild-1).addClass('activ').prevAll().addClass('activ');
	    	allAge.eq(defaultChild-1).show().prevAll().show();
	    	allAge.eq(defaultChild-1).nextAll().hide();
	    	}
	    elCountChild.text(defaultChild);
	    
	    allAdult.on('click',function(){
	    		
	    		//$('#adults').val($('.aznew_bron_adults.activ').length);
	    		$('#adults').val($(this).attr('rel'));
	    		//elCountAdult.text($('#adults').val());
	    		$(this).addClass('activ').prevAll().addClass('activ');
	    		$(this).nextAll().removeClass('activ');
	    	
	    	});
	    
	    allChild.on('click',function(){
    		
    		//noChild.removeClass('activ');
    		//$('#childrens').val($('.aznew_bron_childrens.activ').length);
	    	$('#childrens').val($(this).attr('rel')).trigger('change');
    		//elCountChild.text($('#childrens').val());
	    	$(this).addClass('activ').prevAll().addClass('activ');
    		$(this).nextAll().removeClass('activ');
    		});
	    
	    noChild.on('click',function(){
    		//$(this).addClass('activ');
    		allChild.removeClass('activ');
    		$('#childrens').val('0').trigger('change');
    		elCountChild.text($('#childrens').val());
    		});
	    
	    $('.aznew_bron_adults_all').hover(
	    		function(){
	    			//allAdult.removeClass('activ');
	    			
	    		},
	    		function(){
	    			var $this = allAdult.eq($('#adults').val() - 1);
	    			$this.addClass('activ').prevAll().addClass('activ');
	    			$this.nextAll().removeClass('activ');
	    			elCountAdult.text($('#adults').val());
	    	});
	    
	    $('.aznew_bron_adults').hover(
	    		function(){
	    			//$(this).addClass('activ').prevAll().addClass('activ');
		    		//$(this).nextAll().removeClass('activ');
	    			elCountAdult.text($(this).attr('rel'));
	    	},function(){});
	    
	    
	    $('.aznew_bron_childrens_all').hover(
	    		function(){
	    			//allChild.removeClass('activ');
	    		},
	    		function(){
	    			if($('#childrens').val() == 0)
	    				{
	    				allChild.removeClass('activ');
	    				}
	    			else
	    				{
	    				var $this = allChild.eq($('#childrens').val() - 1);
						$this.addClass('activ').prevAll().addClass('activ');
						$this.nextAll().removeClass('activ');
	    				}
	    		elCountChild.text($('#childrens').val());
			    	
	    	});
	    
	    $('.aznew_bron_childrens').hover(
	    		function(){
	    			//$(this).addClass('activ').prevAll().addClass('activ');
	        		//$(this).nextAll().removeClass('activ');
	    			elCountChild.text($(this).attr('rel'));
	    	},function(){});
    	}
    
    	
    //////////////////////////////////////////
    
    mbShowResults();
}


/**
 * ����� ��� ���� ������ � �������
 *
 * @returns {{
 *     inDates: Array,
  *    outDates: Array
 * }}
 */
function getAllDates()
{
    var inDates = [];
    var outDates = [];
    var minInDate = new Date('2222-12-22');
    var minOutDate = new Date('2222-12-22');
    var dateTmp = new Date();
    var maxInDate = new Date(dateTmp.getFullYear(), dateTmp.getMonth(), dateTmp.getDate(), 0, 0, 0, 0);
    var maxOutDate = new Date(dateTmp.getFullYear(), dateTmp.getMonth(), dateTmp.getDate(), 0, 0, 0, 0);

    $.each(filters['dates'], function(key_hotel, val_hotel)
    {
    	if(selectHotel > 0 && selectHotel != key_hotel) return;
    	$.each(filters['dates'][key_hotel]['in'], function(key, val)
        {
            var tmpDate = new Date(val);
            var addDate = new Date(tmpDate.getFullYear(), tmpDate.getMonth(), tmpDate.getDate(), 0, 0, 0, 0);

            inDates[inDates.length] = addDate.valueOf();

            if(addDate < minInDate){
                minInDate = addDate;
            }

            if(addDate > maxInDate){
                maxInDate = addDate;
            }
        });

        $.each(filters['dates'][key_hotel]['out'], function(key, val)
        {
            var tmpDate = new Date(val);
            var addDate = new Date(tmpDate.getFullYear(), tmpDate.getMonth(), tmpDate.getDate(), 0, 0, 0, 0);

            outDates[outDates.length] = addDate.valueOf();

            if(addDate < minOutDate){
                minOutDate = addDate;
            }

            if(addDate > maxOutDate){
                maxOutDate = addDate;
            }

        });
    });

    return {
        'inDates':  inDates.sort(),
        'outDates': outDates.sort(),

        'minInDate': minInDate,
        'maxInDate': maxInDate,

        'minOutDate': minOutDate,
        'maxOutDate': maxOutDate
    };
}

/**
 * datePicker
 */
function datePicker()
{
	var allDates = getAllDates();
	
	$('#dateBegin').datepicker({
        format: 'dd.mm.yyyy',
        language: 'ru',
        autoclose: true,
        startDate: allDates['minInDate'],
        endDate: allDates['maxInDate'],
        beforeShowDay: function(date)
        {
			if($.inArray(date.valueOf(), allDates['inDates']) < 0){
                return 'disabled';
            }else{
                return '';
            }
        }
    });

    $('#dateEnd').datepicker({
        format: 'dd.mm.yyyy',
        language: 'ru',
        autoclose: true,
        weekStart: 1,
        startDate: allDates['minOutDate'],
        endDate: allDates['maxOutDate'],
        beforeShowDay: function(date)
        {
            if($.inArray(date.valueOf(), allDates['outDates']) < 0){
                return 'disabled';
            }else{
                return '';
            }
        }
    });

    // $('#dateBegin').datepicker('update', allDates['minInDate']);
    $('#dateBegin').datepicker('update', (postDateBegin != ''?postDateBegin:allDates['minInDate']) );
    // $('#dateEnd').datepicker('update', allDates['minOutDate']);
    $('#dateEnd').datepicker('update', (postDateEnd != ''?postDateEnd:allDates['minOutDate']) );

    //$('#dateBegin').datepicker('update', '');
    //$('#dateEnd').datepicker('update', '');

    $('#dateBegin').datepicker().on('changeDate', function(e){

        var dateBeginDateTmp = $('#dateBegin').datepicker('getDate');
        var newDateForEnd = false;

        $('#dateEnd').datepicker('startDate', dateBeginDateTmp);

        $.each(allDates['outDates'], function(key, val){
            var tmpCheckdate = new Date(val);

            if(tmpCheckdate > dateBeginDateTmp){
                newDateForEnd = tmpCheckdate;

                return false;
            }
        });

        if(newDateForEnd !== false){
            $('#dateEnd').datepicker('update', newDateForEnd);
        }

        $('#dateEnd').datepicker('show');

    });

}

/**
 * dataTable
 */
function createDataTable()
{
    if($('#dataTable').length > 0){
        $('#dataTable').dataTable({
            "oLanguage": {
                "sProcessing":   "Подождите...",
                "sLengthMenu":   "Показать _MENU_ записей",
                "sZeroRecords":  "Записи отсутствуют.",
                "sInfo":         "Записи с _START_ до _END_ из _TOTAL_ записей",
                "sInfoEmpty":    "Записи с 0 до 0 из 0 записей",
                "sInfoFiltered": "(отфильтровано из _MAX_ записей)",
                "sInfoPostFix":  "",
                "sSearch":       "Поиск:",
                "sUrl":          "",
                "oPaginate": {
                    "sFirst": "Первая",
                    "sPrevious": "Предыдущая",
                    "sNext": "Следующая",
                    "sLast": "Последняя"
                }
            },
            "aaSorting": [[0, 'asc']],
            "bRetrieve":true,
            "bDestroy":true,
            "aoColumnDefs": [
                {"aTargets": [ "sortme"], "bSortable": true },
                {"aTargets": [ 'nosort' ], "bSortable": false }
            ]
        });
    }
}


/**
 * ����� �������� ������� �������
 */
function showList(){
    $('#divForReserve').hide();
    $('#dataTable_wrapper').fadeIn('fast');
}

/**
 * ����� ��� �������
 *
 * @param itemNumber
 */
function mbReserve(itemNumber)
{
	$("#brr_name1").focus();
	$('html, body').animate({"scrollTop":($("#brr_name1").offset().top-100)+'px'},"slow");
}

function mbSubmitReserve()
{
    var name = $('#reserveName').val();
    var email = $('#reserveEmail').val();
    var phone = $('#reservePhone').val();

    if(!name){
        alert('Укажите своё имя');
        return;
    }

    if( !isValidEmailAddress(email) ) {
        alert('Некорректный Email');
        return;
    }

    if(!phone){
        alert('Укажите номер телефона для связи');
        return;
    }
    
    params = $('#formReserve').serialize();
    
    $('#formReserve, #formReserve input, #formReserve textarea').attr('disabled','disabled');
    $('.btn-bron').attr('disabled','disabled').html('Пожалуйста, подождите...');
    $('.btn-link').css('opacity','0');
    
    $.get( "http://" + mb_host + "/services/mb-search/results.php?" + params, function(data) {
        data = data.split('#');
        $('#formReserve, #formReserve input, #formReserve textarea').removeAttr('disabled');
        $('.btn-bron').removeAttr('disabled').html('Отправить');
        if(data[0] == 'success') {
            $('#mb_results').html('<div class="alert alert-success">' + data[1] + '</div>');
        }
        alert(data[1]);
    });
}

/**
 * �������� ���������� ������
 *
 * @param emailAddress
 * @returns {boolean}
 */
function isValidEmailAddress(emailAddress){
    var pattern = new RegExp(/^((([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+(\.([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+)*)|((\x22)((((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(([\x01-\x08\x0b\x0c\x0e-\x1f\x7f]|\x21|[\x23-\x5b]|[\x5d-\x7e]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(\\([\x01-\x09\x0b\x0c\x0d-\x7f]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]))))*(((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(\x22)))@((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?$/i);
    return pattern.test(emailAddress);
}


/**
 * ������������� ��������� �����
 */
function reSetHotel()
{
    selectHotel = $('#hotel').val();
    $('#hotel').html('');
    
    var hotelHTML = "<option value='0'>Все пансионаты</option>";
    $.each(filters['hotel'], function(key, val){
    	hotelHTML += "<option value='" + key + "'>" + val + "</option>";
    });
    
    $('#hotel').html(hotelHTML).trigger("chosen:updated");

    if($("#hotel [value='"+selectHotel+"']").length > 0){
        $('#hotel').val(selectHotel).trigger("chosen:updated");
    }

    $('#hotel').val(postHotel).trigger("chosen:updated");
    
    reSetRoomType();
    reSetDiscount();
}

/**
 * ������������� ��������� ���� �������
 */
function reSetRoomType()
{
    selectRoomType = $('#roomType').val();
    $('#roomType').html('');

    var roomTypeHTML = getDataForFoodOrRoomType('roomType');

    $('#roomType').html(roomTypeHTML).trigger("chosen:updated");

    if($("#roomType [value='"+selectRoomType+"']").length > 0){
        $('#roomType').val(selectRoomType).trigger("chosen:updated");
    }

    $('#roomType').val(postRoomType).trigger("chosen:updated");
    //postRoomType = 0;    
}

/**
 * ������������� ��������� ������
 */
function reSetDiscount()
{
    selectDiscount = $('#discount').val();
    $('#discount').html('');

    var discountHTML = getDataForDiscount();

    $('#discount').html(discountHTML).trigger("chosen:updated");

    if($("#discount [value='"+selectDiscount+"']").length > 0){
        $('#discount').val(selectDiscount).trigger("chosen:updated");
    }

    $('#discount').val(postDiscount).trigger("chosen:updated");
    //postRoomType = 0;    
}


/**
 * ������ ��� ������
 *
 * 
 * @returns {string}
 */
function getDataForDiscount()
{
	var objectName = 'discount';
    var hotelID = $('#hotel').val();

    var typeObject = new Object();
    var dataHTML = "" +
        "<option value='0#0'>Выберите скидку</option>"
    ;
    
    if(filters[objectName][0] !== undefined)
    $.each(filters[objectName][0], function(key, val){
        typeObject[key] = val;
    });
    
    if(hotelID != 0){
    	if(filters[objectName][hotelID] !== undefined)
    		{
		        $.each(filters[objectName][hotelID], function(key, val){
		            typeObject[key] = val;
		        });
    		}
    }else{
        typeObject[0] = 'Для расчета стоимости с учетом скидок в поле Пансионат выберите, пожалуйста, конкретный объект. Скидки в  парк-отель "РИО" - до 18 %, в п-ты "Азовский" и "АзовЛенд" - до 14%.';
    }
    $.each(typeObject, function(key, val){
        dataHTML += "<option value='" + key + "'>" + val + "</option>";
    });

    return dataHTML;
}

/**
 * ����� ������ ��� ����� ������� � ����� �������
 *
 * @param objectName
 * @returns {string}
 */
function getDataForFoodOrRoomType(objectName)
{
    var hotelID = $('#hotel').val();

    var typeObject = new Object();
    var dataHTML = "" +
        "<option value='0'>Все типы номеров</option>"
    ;

    if(hotelID != 0){
        $.each(filters[objectName][hotelID], function(key, val){
            typeObject[key] = val;
        });
    }

    $.each(typeObject, function(key, val){
        dataHTML += "<option value='" + key + "'>" + val + "</option>";
    });

    return dataHTML;
}

function mbSubmitFormFilters()
{
    if(!$('#dateBegin').val()){
        alert('Для поиска нужно указать Дату заезда');
        return;
    }

    if(!$('#dateEnd').val()){
        alert("Для поиска нужно указать дату отъезда");
        return;
    }

    $('#formFilters').submit();
}

function mbShowResults(){
	h = window.location.search;
	if(h.indexOf('hotel')>=0) {
		$('#mb_results').show();
		$.getScript("http://" + mb_host + "/services/mb-search/result2_js.php"+h+"&search=1");
	}
}	


function print_r(arr, level) {
    var print_red_text = "";
    if(!level) level = 0;
    var level_padding = "";
    for(var j=0; j<level+1; j++) level_padding += "    ";
    if(typeof(arr) == 'object') {
        for(var item in arr) {
            var value = arr[item];
            if(typeof(value) == 'object') {
                print_red_text += level_padding + "'" + item + "' :\n";
                print_red_text += print_r(value,level+1);
            }
            else
                print_red_text += level_padding + "'" + item + "' => \"" + value + "\"\n";
        }
    }

    else  print_red_text = "===>"+arr+"<===("+typeof(arr)+")";
    return print_red_text;
}