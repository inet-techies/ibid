$(document).ready(function(){					   
	$('input').attr('autocomplete','off');						   
});

$(function() {
		   
	// $("select").multiselect();
		   
	function displayResult(item, val, text,element) {
		$('input[name="style['+$(element).attr('id') +']"] ').val(val);
		$(element).val(val);
		$(element).css('border-color','');
		$(element).tooltip('hide');
			
	}

	var cur_element;
	
	cur_element = $("#ship_mode, #currency");
		
	cur_element.typeahead({
		ajax:  { url: BASE_URL+'api/get_options/' , triggerLength: 1, timeout: 500 ,preDispatch : { id: 'giveid' } }, 
		display: 'value',
		val: 'value',
		itemSelected: displayResult
	});
	
	$("#consignee, #vendor, #ship_to, #employee").each(function() {
													 
		var $this = $(this);
		$this.typeahead({	
	
			ajax:  { url: BASE_URL+'api/get_details/' , triggerLength: 1, timeout: 500 ,preDispatch : { id: 'giveid' } }, 
			display: 'value',
			val: 'id',
			itemSelected: function(item, val, text,element) {
							document.getElementById($this.attr("id")+"_id").value = val;
							$(element).css('border-color','');
							$(element).tooltip('hide');
						 }
		});
	});
});


function removeColors() {
	$(".initial-hide").remove();								   
	return true;
}

function calculateNetProfit() {
	
	subtotal_sp = document.getElementById("subtotal_sellingvalue").innerHTML;
	freight = document.getElementById("freight_value").value;
	duty = document.getElementById("duty_value").value;
	vendor_comission = document.getElementById("vendor_comission_value").value;
	buyer_comission = document.getElementById("buyer_comission_value").value;
	salesmen_comission = document.getElementById("salesmen_comission_value").value;
	subtotal_p = document.getElementById("subtotal_value").innerHTML;
	
	net_profit = Number(subtotal_sp) - (Number(freight) + Number(duty) + Number(vendor_comission) + Number(buyer_comission) + Number(salesmen_comission) + Number(subtotal_p));
	
	profit_percent = (net_profit * 100)/Number(subtotal_sp);
	
	marker = (6*net_profit)/100;
	blueberry = (4*net_profit)/100;
	
	$("#net_profit").val(net_profit.toFixed(2));
	$("#profit").val(profit_percent.toFixed(2));
	$("#marker").val(marker.toFixed(2));
	$("#blueberry").val(blueberry.toFixed(2));
	
	
	
}

$("#ship_mode").live("change", function(){
										
	if((this.value.indexOf("sea") !== -1) || (this.value.indexOf("SEA") !== -1)) {
		
		$(".ship_mode_change").text("%");
	} else {
		$(".ship_mode_change").text("");
	}
										
});

$(".admin_entry").live("keyup", function(){
										  
	ship_mode = document.getElementById("ship_mode").value;
	
	this_id = $(this).attr("id");
	
	if((ship_mode.indexOf("sea") !== -1) || (ship_mode.indexOf("SEA") !== -1)) {
		
		subtotal = $("#subtotal_sellingvalue").text();
		
		$("#"+this_id+"_value").val((this.value * Number(subtotal))/100);
		
	} else {
		
		subtotal = $("#subtotal_piece").text();
		
		$("#"+this_id+"_value").val((this.value * Number(subtotal)));
	}
	
	calculateNetProfit();
});

$(".admin_entry").live("change", function(){

	ship_mode = document.getElementById("ship_mode").value;
	
	this_id = $(this).attr("id");
	
	if((ship_mode.indexOf("sea") !== -1) || (ship_mode.indexOf("SEA") !== -1)) {
		
		subtotal = $("#subtotal_sellingvalue").text();
		
		$("#"+this_id+"_value").val((this.value * Number(subtotal))/100);
		
	} else {
		
		subtotal = $("#subtotal_piece").text();
		
		$("#"+this_id+"_value").val((this.value * Number(subtotal)));
	}
	
	calculateNetProfit();
});

$(".order_piece").live("keyup", function(){

	total = total1 = 0;
	
	total_id = $(this).attr("alt"); 
	
	$(".order_piece").each(function(){
									
		this_alt = $(this).attr("alt");
		if(this_alt == total_id)
			total = total + Number(this.value);		
			
		
		this_id = $(this).attr("id");
		part_name = this_id.split("[pieces]");
		fob = part_name[0]+"[fob]";

		fob_val = document.getElementById(fob);
		if(this_alt == total_id)
			total1 = total1 + (Number(this.value) * fob_val.value);	
		
		
	});
	
	$("#"+total_id+"_piece").text(total);
	$("#"+total_id+"_price").text(total1);
	
	piece_total = 0;
	
	$(".piece_total").each(function(){
					
		piece_total = piece_total + Number(this.innerHTML);		
	});
	
	$("#subtotal_piece").text(piece_total);
	
	price_total = 0;
	
	$(".price_total").each(function(){

		price_total = price_total + Number(this.innerHTML);		
	});
	
	$("#subtotal_value").text(price_total);
	
});

$(".order_fob").live("keyup", function(){

	total = 0;
	
	total_id = $(this).attr("alt");
	
	$(".order_fob").each(function(){
		
		this_id = $(this).attr("id");
		this_alt = $(this).attr("alt");
		part_name = this_id.split("[fob]");
		piece = part_name[0]+"[pieces]";
		piece_val = document.getElementById(piece);
		if(this_alt == total_id)
			total = total + (Number(this.value) * piece_val.value);	
				
	});
	
	$("#"+total_id+"_price").text(total);
	
	price_total = 0;
	
	$(".price_total").each(function(){

		price_total = price_total + Number(this.innerHTML);		
	});
	
	$("#subtotal_value").text(price_total);
	
});

$(".order_sp").live("keyup", function(){

	total = 0;
	
	total_id = $(this).attr("alt");
	
	$(".order_sp").each(function(){
		
		this_id = $(this).attr("id");
		this_alt = $(this).attr("alt");
		part_name = this_id.split("[selling_price]");
		piece = part_name[0]+"[pieces]";
		piece_val = document.getElementById(piece);
		if(this_alt == total_id)
			total = total + (Number(this.value) * piece_val.value);	
				
	});
	
	$("#"+total_id+"_sprice").text(total);
	
	price_total = 0;
	
	$(".sprice_total").each(function(){

		price_total = price_total + Number(this.innerHTML);		
	});
	
	$("#subtotal_sellingvalue").text(price_total);
	
	$(".admin_entry").each(function(){
		
		ship_mode = document.getElementById("ship_mode").value;
	
		this_id = $(this).attr("id");
		
		if((ship_mode.indexOf("sea") !== -1) || (ship_mode.indexOf("SEA") !== -1)) {
			
			subtotal = $("#subtotal_sellingvalue").text();
			
			$("#"+this_id+"_value").val((this.value * Number(subtotal))/100);
			
		} else {
			
			subtotal = $("#subtotal_piece").text();
			
			$("#"+this_id+"_value").val((this.value * Number(subtotal)));
		}
				
	});
	
	calculateNetProfit();
	
});


   
$("#color-select").live("multiselectclick", function(event, ui){
				
	if(ui.checked) {
	
		$("."+ui.text.replace(" ","_")+"_tr").removeClass("initial-hide");
		$("#subtotal_value_td,#subtotal_piece_td,#subtotal_sellingvalue_td,#subtotal_sellingvalue_td1").removeClass("initial-hide");
	
	} else {
		
		$("."+ui.text.replace(" ","_")+"_tr").addClass("initial-hide");
	}
	
														 
	//if(ui.checked) {
//		for(i=0; i< sizes.length; i++){
//			
//			console.log(sizes[i]);
//		}
//		trindex = $("#pieces_table tr").length;
//		
//		/*$("#pieces_table").append("<tr id= 'clt_" + ui.value + "' ><td rowspan='3' id= 'cl_" + ui.value + "'><b>" + ui.text + "</b><input type='hidden' name=ordercolors["+(trindex-1)+"][color] value='"+ui.text+"'/></td><td>Pieces</td><td id= 'pi_" + ui.value + "'><input type='text' name=ordercolors["+(trindex-1)+"][fob] size='5' value=''/></td></tr><tr><td>FOB</td><td id= 'pi_" + ui.value + "'><input type='text' size='5' name=ordercolors["+(trindex-1)+"][piece] value=''/></td></tr><tr><td>Selling Price</td><td id= 'pi_" + ui.value + "'><input type='text' size='5' name=ordercolors["+(trindex-1)+"][selling_price] value=''/></td></tr>");*/
//		
//		append_data = "<tr id= 'clt_" + ui.value + "' ><td rowspan='3' id= 'cl_" + ui.value + "'><b>" + ui.text + "</b><input type='hidden' name=ordercolors[] value='"+ui.text+"'/></td><td>Pieces</td>";
//		
//		append_piece = append_fob = append_sp = "";
//		
//		for(i=0; i< sizes.length; i++){
//			
//			
//			append_piece += "<td><input type='text' name=orderdet["+ui.text+"]["+i+"][pieces] size='5' value=''/></td>";
//			append_fob += "<td><input type='text' name=orderdet["+ui.text+"]["+i+"][fob] size='5' value=''/></td>";
//			append_sp += "<td><input type='text' name=orderdet["+ui.text+"]["+i+"][selling_price] size='5' value=''/><input type='hidden' name=orderdet["+ui.text+"]["+i+"][size] value='"+sizes[i].size+"'/><input type='hidden' name=orderdet["+ui.text+"]["+i+"][color] value='"+ui.text+"'/></td>";
//		}
//		
//		append_data += append_piece;
//		append_data += "</tr><tr><td>FOB</td>";
//		append_data += append_fob;
//		append_data += "</tr><tr><td>SELLING PRICE</td>";
//		append_data += append_sp;
//		
//		$("#pieces_table").append(append_data);
//		
//	} else {
//
//		$("#clt_" + ui.value).remove();
//	}

});				   

