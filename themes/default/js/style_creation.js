function cloneDatas(asc,value,text){
			console.log("table");
			var table = $("table#measurment");
			var len = table.find("tbody tr").length;
			for(var i =0 ; i <= len; i++){
			if(asc=="value") 
			table.find("tbody tr:eq("+i+") td:eq(1)").after("<td id='size_m"+value+"'><input class='textInput input-mini' type='text' size='5' name='main["+i+"]["+text +"]'/></td>");
			else
			table.find("tbody tr:eq("+i+") td#size_m"+asc).after("<td id='size_m"+value+"'><input class='textInput input-mini' type='text' size='5' name='main["+i+"]["+text +"]'/></td>");
			}																	
		
}
$(document).ready(function() {
$("form").submit(function(){

var res = true;
var size_len = $("#size-select").multiselect('getChecked').length;
var color_len = $("#color-select").multiselect('getChecked').length;
$("form div.control-group").removeClass('error');
$("form div.control-group span.help-inline").remove();
$("form span.text-error").remove();
var j = 0;

	/* var ms = false;
	
	$("table#measurment input:text").each(function(i){  console.log($(this).attr('name'));  if($(this).val() == '') {  $(this).css('border-color','#f00');   ms = true;  res =false;  }        }); // 
	
	//console.log(ms);
	
	if(ms)			$("div#add-ms").before('<span class="text-error">Please Add measurment points </span>'); */
	
	if(size_len == 0) {  $("#size-select").parent().append('<span class="text-error"> please select sizes </span>');res = false; }
	
	if(color_len == 0) {  $("#color-select").parent().append('<span class="text-error"> please select colors </span>'); res = false; }

	

return res;

});

$("a#remove-measure").click(function(){
var table = $("table#measurment");
var len = $(table).find("tbody tr").length;
if(len > 1)
$(table).find("tbody tr:last").remove();
if(len==2) $(this).hide(); 
return false;


});


$("a#add-measure").click(function(){
			var table = $("table#measurment");
			var measure = $(table).find("tbody tr:first").clone();
			var len = $(table).find("tbody tr").length;
			measure.find("input:text").each(function(i){
			var prev,next,get_name ;
			prev = $(this).attr('name');
			$(this).val('');
			next = prev.replace('0',len);
			$(this).attr('name',next);
			});
			table.find("tbody").append(measure);
			var len = $(table).find("tbody tr").length;
			if(len > 1) { $("a#remove-measure").fadeIn();    }
			
			else $("a#remove-measure").hide();  
			
			return false;
});

$("#create-form-button").click(function(){
			$("div#create-form").slideToggle();
});
//console.log($("#color-select").multiselect('isOpen'));
$("#color-select").multiselect({ checkAll : function(event,ui) {
        var i =0;
		var check = $("#colors");
		for(i=0; i< colours.length; i++){
		if(check.find("td#cl_"+colours[i].id).length == 0) 
				$("#colors").append("<td id= 'cl_" +colours[i].id + "'>" + colours[i].value + "</td>");
		} 
		},
		uncheckAll : function(event,ui){
		
		for(var i=0; i< colours.length; i++){
				$("#colors").find("td#cl_"+colours[i].id).remove();
		} 
		
		}

		});
$("#size-select").multiselect({ checkAll : function(event,ui) {
        var i =0;
		var check = $("#sizes");
		for(i=0; i< sizes.length; i++){
				if(check.find("td#sz_"+sizes[i].id).length == 0) {
						$("#sizes").append("<td id= 'sz_" + sizes[i].id + "'>" + sizes[i].value + "</td>");
						$("#prices").append("<td id= 'pr_" + sizes[i].id  + "'><input class='textInput input-mini' type='text' size='5' name='size["+sizes[i].value+"][prices]'/></td>");
						$("#sp_sizes").append("<td id= 'spsz_" + sizes[i].id  + "'>" + sizes[i].value + "</td>");
						$("#sp_prices").append("<td id= 'sp_" + sizes[i].id  + "'><input class='textInput input-mini' type='text' size='5' name='size["+sizes[i].value+"][selling_price]'/></td>");
						$("table#measurment").find("thead tr").append("<th id='size_"+sizes[i].id +"'>"+sizes[i].value+"</th>");
						//
						var len = $("table#measurment").find("tbody tr").length;
						if(len > 1){
						var asc = i > 0 ? sizes[i-1].id : "value" ;
							cloneDatas(asc,sizes[i].id,sizes[i].value);
						} else {
						$("table#measurment").find("tbody tr:first").append("<td id='size_m"+sizes[i].id +"'><input class='textInput input-mini' type='text' size='5' name='main[0][size][size]["+sizes[i].value+"]'/></td>");
						}
								
						}
				}
		$("a#add-measure").show();
		},
		uncheckAll : function(event,ui){
		
		for(var i=0; i< sizes.length; i++){
				$("#sizes").find("td#sz_"+sizes[i].id).remove();
				$("#prices").find("td#pr_"+sizes[i].id).remove();
				$("#sp_sizes").find("td#spsz_"+sizes[i].id).remove();
				$("#sp_prices").find("td#sp_"+sizes[i].id).remove();
				$("th#size_"+sizes[i].id).remove();
				$("td#size_m"+sizes[i].id).remove();
				
		}

},
		});

$(".ui-multiselect-menu").css('width', '180px');

$("#color-select").live("multiselectclick", function(event, ui){
	if(ui.checked) {
		$("#colors").append("<td id= 'cl_" + ui.value + "'>" + ui.text + "</td><input type='hidden' name='style_colour[]' value='"+ui.text+"'/>");
		
	} else {

		$("#cl_" + ui.value).remove();
	}

});

$("#size-select").live("multiselectclick", function(event, ui){

    var selected = $("#size-select").multiselect('getChecked');
    var asc ='';
	
	if(ui.checked) {
		for(var i= 0; i < selected.length; i++){
//console.log(selected[i]);
		if(ui.value == selected[i].value) {
				if(asc == '')  {
				$("tr#sizes").find("td:first").after("<td id= 'sz_" + ui.value + "'>" + ui.text + "</td>");
				$("tr#prices").find("td:first").after("<td id= 'pr_" + ui.value + "'><input class='textInput input-mini' type='text' size='5' name='size["+ui.text+"][prices]'/></td>");
				$("tr#sp_sizes").find("td:first").after("<td id= 'spsz_" + ui.value + "'>" + ui.text + "</td>");
				$("tr#sp_prices").find("td:first").after("<td id= 'sp_" + ui.value + "'><input class='textInput input-mini' type='text' size='5' name='size["+ui.text+"][selling_price]'/></td>");
				$("table#measurment").find("thead th:eq(1)").after("<th id='size_"+ui.value+"'>"+ui.text+"</th>");
				var len = $("table#measurment").find("tbody tr").length;
				if(len > 1){
					cloneDatas("value",ui.value,ui.text);
				}  else {
				$("table#measurment").find("tr td:eq(1)").after("<td id='size_m"+ui.value+"'><input class='textInput input-mini' type='text' size='5' name='main[0][size]["+ui.text+"]'/></td>");
				}
				}
				else 
				{ 
					$("td#sz_"+asc).after("<td id= 'sz_" +ui.value + "'>" + ui.text+ "</td>");
					$("td#pr_"+asc).after("<td id= 'pr_" + ui.value  + "'><input class='textInput input-mini' type='text' size='5' name='size["+ui.text+"][prices]'/></td>");
					$("td#spsz_"+asc).after("<td id= 'spsz_" + ui.value  + "'>" +  ui.text + "</td>");
					$("td#sp_"+asc).after("<td id= 'sp_" + ui.value + "'><input class='textInput input-mini' type='text' size='5' name='size["+ui.text+"][selling_price]'/></td>");
					var len = $("table#measurment").find("tbody tr").length;
					if(len > 1){
					$("table#measurment").find("thead tr:first th#size_"+asc).after("<th id='size_"+ui.value+"'>"+ui.text+"</th>");
					cloneDatas(asc,ui.value,ui.text);
					}  else {
					$("table#measurment").find("thead tr:first th#size_"+asc).after("<th id='size_"+ui.value+"'>"+ui.text+"</th>");
					$("table#measurment").find("tbody tr:first td#size_m"+asc).after("<td id='size_m"+ui.value+"'><input class='textInput input-mini' type='text' size='5' name='main[0][size]["+ui.text +"]'/></td>");
					}
				}
											}
			asc = selected[i].value;
		}
		
		$("a#add-measure").show();
	} else {

		$("#sz_" + ui.value).remove();
		$("#pr_" + ui.value).remove();
		$("#sp_" + ui.value).remove();
		$("#spsz_" + ui.value).remove();
		$("th#size_"+ui.value).remove();
		//$("th#size_""+sizes[i].value+"</th>");
		$("td#size_m"+ui.value).remove();
		//$("table#measurment").find("tbody tr").append("<td id='size_m"+sizes[i].id +"'><input class='textInput input-mini' type='text' size='5' name='main[0][size]["+sizes[i].value+"]'/></td>");
				
	}
	
	

}); 

$("table#measurment input:text").keyup(function(event){
if($(this).val() !='') $(this).css('border-color','');
else $(this).css('border-color','#f00');
});




});
$(function() {




function displayResult(item, val, text,element) {
//     alert('input[name="' +$(element).attr('id') + ' " ');
		console.log($('input[name="' +$(element).attr('id') + ' "] '));
	$('input[name="style['+$(element).attr('id') +']"] ').val(val);
	$(element).val(val);
	$(element).css('border-color','');
	$(element).tooltip('hide');
	//$(element).parents().eq(2).removeClass('error');
	//$(element).find('span.help-inline').remove();
	
}


    var cache = {};
	var cur_element;
	cur_element = $("#style_no ,#fabric_name, #fabric_weight, #shipping_terms, #style_desc, #fabric_desc, #fabric_content, #fabric_sp_finish");
	
	//alert(cur_element.attr('id'));
	cur_element.typeahead({
	     ajax:  { url: BASE_URL+'api/get_options/' , triggerLength: 1, timeout: 500 ,preDispatch : { id: 'giveid' } }, 
	    display: 'value',
		val: 'value',
		itemSelected: displayResult
		});
	
	
	$("#customer").typeahead({
	     ajax:  { url: BASE_URL+'api/get_details/' , triggerLength: 1, timeout: 500 ,preDispatch : { id: 'giveid' } }, 
	    display: 'value',
		val: 'id',
		itemSelected: function(item, val, text,element) {
					$('input[name="style[customer_id]"] ').val(val);
					//$(element).val(val);
					$(element).css('border-color','');
					$(element).tooltip('hide');
}
		});
  });
