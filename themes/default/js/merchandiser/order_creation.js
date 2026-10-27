$(document).ready(function(){					   
	$('input').attr('autocomplete','off');	
	
	
	
	$("#color-select").multiselect({ checkAll : function(event,ui) {
														
			$("#color-select option").each(function(){
													
				$("."+$(this).text().replace(" ","_")+"_tr").removeClass("initial-hide");
			});
		   
		},
		uncheckAll : function(event,ui){
			
			$("#color-select option").each(function(){
													
				$("."+$(this).text().replace(" ","_")+"_tr").addClass("initial-hide");
			});	
	
		},
	});
	
	customer_name = document.getElementById("customer_name").value;
						
	if( (customer_name.toLowerCase().indexOf("bodek") !== -1) )    {
		
		$(".freight_change").text("/Piece");
	}
	
	
	
	
	
	$(".order_piece").each(function(){

		total = total1 = totalsp = 0;
		
		total_id = $(this).attr("alt"); 
		
		$(".order_piece").each(function(){
										
			this_alt = $(this).attr("alt");
			if(this_alt == total_id)
				total = total + Number(this.value);		
				
			
			this_id = $(this).attr("id");
			part_name = this_id.split("[pieces]");
			fob = part_name[0]+"[fob]";
			sp = part_name[0]+"[selling_price]";
	
			fob_val = document.getElementById(fob);
			sp_val = document.getElementById(sp);
			
			if(this_alt == total_id)
				total1 = total1 + (Number(this.value) * fob_val.value);
				totalsp = totalsp + (Number(this.value) * sp_val.value);
			
		});
		
		$("#"+total_id+"_piece").text(total);
		$("#"+total_id+"_price").text(total1.toFixed(2));
		$("#"+total_id+"_sprice").text(totalsp.toFixed(2));
		
		piece_total = 0;
		
		$(".piece_total").each(function(){
						
			piece_total = piece_total + Number(this.innerHTML);		
		});
		
		$("#subtotal_piece").text(piece_total);
		
		price_total = 0;
		
		$(".price_total").each(function(){
	
			price_total = price_total + Number(this.innerHTML);		
		});
		
		$("#subtotal_value").text(price_total.toFixed(2));
		
		sprice_total = 0;
		
		$(".sprice_total").each(function(){
	
			sprice_total = sprice_total + Number(this.innerHTML);		
		});
		
		$("#subtotal_sellingvalue").text(sprice_total.toFixed(2));
		
		$(".admin_entry").each(function(){
			
			ship_mode = document.getElementById("ship_mode").value;
		
			this_id = $(this).attr("id");
			
			//if((ship_mode.indexOf("sea") !== -1) || (ship_mode.indexOf("SEA") !== -1)) {
				
				subtotal_sp = $("#subtotal_sellingvalue").text();
				subtotal_fob = $("#subtotal_value").text();
				
				$("#"+this_id+"_value").val(((this.value * Number(subtotal_fob))/100).toFixed(2));
				
			//}
				customer_name = document.getElementById("customer_name").value;
				
				if((ship_mode.indexOf("collect") !== -1) || (ship_mode.indexOf("COLLECT") !== -1) || (customer_name.toLowerCase().indexOf("bodek") !== -1)) {
					
					subtotal_piece = $("#subtotal_piece").text();
					if(this_id == "freight") {
						$("#"+this_id+"_value").val((this.value * Number(subtotal_piece)).toFixed(2));
					}
				}
			
			salescom_percent = document.getElementById("salesmen_comission").value;
			salescom_value = document.getElementById("salesmen_comission_value");
			
			salescom_value.value = (((this.value * Number(subtotal_sp))/100).toFixed(2));
			
			
					
		});
		
		calculateNetProfit();
		
	});
	

});

$(".amend_max").live("change keyup", function(){
	
	if(Number(this.value) > Number($(this).attr('max'))) {
		$(this).addClass("req-field");	
	} else {
		$(this).removeClass("req-field");
		
		name = $(this).attr('name');		
		origname = "orig"+name;
		origval = $('input[name="'+origname+'"]').attr('alt');
		
		if(this.value) {
			newval = (Number(origval)- Number(this.value));
			$('input[name="'+origname+'"]').val(newval);
		} else {
			$('input[name="'+origname+'"]').val(origval);
		}	
		
		
	}
});

   
$("#color-select").live("multiselectclick", function(event, ui){
				
	if(ui.checked) {
	
		$("."+ui.text.replace(" ","_")+"_tr").removeClass("initial-hide");
		$("#subtotal_value_td,#subtotal_piece_td,#subtotal_sellingvalue_td,#subtotal_sellingvalue_td1").removeClass("initial-hide");
	
	} else {
		
		$("."+ui.text.replace(" ","_")+"_tr").addClass("initial-hide");
	}

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
	
	validate = 0;
	$(".required").each(function(){
				
		if(!$(this).val()) {

			$(this).addClass("req-field");
			if(validate == 0){
				$(this).focus();	
			}
			validate++;
			
		} else {
		
			$(this).removeClass("req-field");
		}
	
	});
	
	if(validate > 0) {
	
		return false;	
	}
	
	$(".initial-hide").remove();								   
	return true;
}

function calculateNetProfit() {
	
	subtotal_fob = document.getElementById("subtotal_sellingvalue").innerHTML;
	subtotal_sp = document.getElementById("subtotal_sellingvalue").innerHTML;
	freight = document.getElementById("freight_value").value;
	duty = document.getElementById("duty_value").value;
	vendor_comission = document.getElementById("vendor_comission_value").value;
	buyer_comission = document.getElementById("buyer_comission_value").value;
	salesmen_comission = document.getElementById("salesmen_comission_value").value;
	subtotal_p = document.getElementById("subtotal_value").innerHTML;
	ship_mode = "";
	ship_mode = document.getElementById("ship_mode").value;
	
	fr = Number(freight);
	if(ship_mode && (ship_mode.indexOf("air") !== -1 || ship_mode.indexOf("AIR") !== -1)) {
		
		fr = 0;
	}
	
	net_profit = (Number(subtotal_fob) - Number(salesmen_comission)) - (Number(subtotal_p)-(fr + Number(duty) + Number(vendor_comission) + Number(buyer_comission)));
	
	cp = Number(fr) + Number(duty) + Number(vendor_comission) + Number(buyer_comission) + Number(subtotal_p);
	
	profit_percent = (net_profit/cp)*100;
	
	marker_percent = document.getElementById("marker").value;
	blueberry_percent = document.getElementById("blueberry").value;
	
	marker = (marker_percent*net_profit)/100;
	blueberry = (blueberry_percent*net_profit)/100;
	
	$("#net_profit").val(net_profit.toFixed(2));
	$("#profit").val(profit_percent.toFixed(2));
	$("#marker_value").val(marker.toFixed(2));
	$("#blueberry_value").val(blueberry.toFixed(2));
	
	
}

$("#marker,#blueberry").live("keyup", function(){
					
	this_id = $(this).attr("id");
	net_profit = document.getElementById("net_profit").value;
	
	new_val = ($(this).val()*net_profit)/100;
	$("#"+this_id+"_value").val(new_val.toFixed(2));
	
	
});

$("#ship_mode").live("change", function(){
										
	customer_name = document.getElementById("customer_name").value;
						
	$(".ship_mode_change").text("%");
	
	if( (this.value.indexOf("collect") !== -1) || (this.value.indexOf("COLLECT") !== -1) || (customer_name.toLowerCase().indexOf("bodek") !== -1) )    {
		
		$(".freight_change").text("/Piece");
	} //else {
		
//	}
	$(".admin_entry").each(function(){
			
			ship_mode = document.getElementById("ship_mode").value;
		
			this_id = $(this).attr("id");
			
			//if((ship_mode.indexOf("sea") !== -1) || (ship_mode.indexOf("SEA") !== -1)) {
				
				subtotal_sp = $("#subtotal_sellingvalue").text();
				subtotal_fob = $("#subtotal_value").text();
				
				$("#"+this_id+"_value").val(((this.value * Number(subtotal_fob))/100).toFixed(2));
				
			//}
			customer_name = document.getElementById("customer_name").value;
	
	
			if((ship_mode.indexOf("collect") !== -1) || (ship_mode.indexOf("COLLECT") !== -1) || (customer_name.toLowerCase().indexOf("bodek") !== -1)) {
				
				subtotal_piece = $("#subtotal_piece").text();
				if(this_id == "freight") {
					$("#"+this_id+"_value").val((this.value * Number(subtotal_piece)).toFixed(2));
				}
			}
			
			salescom_percent = document.getElementById("salesmen_comission").value;
			salescom_value = document.getElementById("salesmen_comission_value");
			
			salescom_value.value = ((this.value * Number(subtotal_sp))/100);
			
		calculateNetProfit();
	});
});

$(".admin_entry").each(function(){
		
		ship_mode = document.getElementById("ship_mode").value;
	
		this_id = $(this).attr("id");
		
		//if((ship_mode.indexOf("sea") !== -1) || (ship_mode.indexOf("SEA") !== -1)) {
			
			subtotal_sp = $("#subtotal_sellingvalue").text();
			subtotal_fob = $("#subtotal_value").text();
			
			$("#"+this_id+"_value").val(((this.value * Number(subtotal_fob))/100).toFixed(2));
			
		//}
		customer_name = document.getElementById("customer_name").value;
		
		if((ship_mode.indexOf("collect") !== -1) || (ship_mode.indexOf("COLLECT") !== -1) || (customer_name.toLowerCase().indexOf("bodek") !== -1)) {
				
				subtotal_piece = $("#subtotal_piece").text();
				if(this_id == "freight") {
					$("#"+this_id+"_value").val((this.value * Number(subtotal_piece)).toFixed(2));
				}
			}
		
		salescom_percent = document.getElementById("salesmen_comission").value;
		salescom_value = document.getElementById("salesmen_comission_value");
		
		salescom_value.value = ((this.value * Number(subtotal_sp))/100);
		
	calculateNetProfit();
});


$(".order_piece").live("keyup", function(){

	total = total1 = totalsp = 0;
	
	total_id = $(this).attr("alt"); 
	
	$(".order_piece").each(function(){
									
		this_alt = $(this).attr("alt");
		if(this_alt == total_id)
			total = total + Number(this.value);		
			
		
		this_id = $(this).attr("id");
		part_name = this_id.split("[pieces]");
		fob = part_name[0]+"[fob]";
		sp = part_name[0]+"[selling_price]";

		fob_val = document.getElementById(fob);
		sp_val = document.getElementById(sp);
		
		if(this_alt == total_id)
			total1 = total1 + (Number(this.value) * fob_val.value);
			totalsp = totalsp + (Number(this.value) * sp_val.value);
		
	});
	
	$("#"+total_id+"_piece").text(total);
	$("#"+total_id+"_price").text(total1.toFixed(2));
	$("#"+total_id+"_sprice").text(totalsp.toFixed(2));
	
	piece_total = 0;
	
	$(".piece_total").each(function(){
					
		piece_total = piece_total + Number(this.innerHTML);		
	});
	
	$("#subtotal_piece").text(piece_total);
	
	price_total = 0;
	
	$(".price_total").each(function(){

		price_total = price_total + Number(this.innerHTML);		
	});
	
	$("#subtotal_value").text(price_total.toFixed(2));
	
	sprice_total = 0;
	
	$(".sprice_total").each(function(){

		sprice_total = sprice_total + Number(this.innerHTML);		
	});
	
	$("#subtotal_sellingvalue").text(sprice_total.toFixed(2));
	
	$(".admin_entry").each(function(){
		
		ship_mode = document.getElementById("ship_mode").value;
	
		this_id = $(this).attr("id");
		
		//if((ship_mode.indexOf("sea") !== -1) || (ship_mode.indexOf("SEA") !== -1)) {
			
			subtotal_sp = $("#subtotal_sellingvalue").text();
			subtotal_fob = $("#subtotal_value").text();
			
			$("#"+this_id+"_value").val(((this.value * Number(subtotal_fob))/100).toFixed(2));
			
		//}
			customer_name = document.getElementById("customer_name").value;
			
			if((ship_mode.indexOf("collect") !== -1) || (ship_mode.indexOf("COLLECT") !== -1) || (customer_name.toLowerCase().indexOf("bodek") !== -1)) {
				
				subtotal_piece = $("#subtotal_piece").text();
				if(this_id == "freight") {
					$("#"+this_id+"_value").val((this.value * Number(subtotal_piece)).toFixed(2));
				}
			}
		
		salescom_percent = document.getElementById("salesmen_comission").value;
		salescom_value = document.getElementById("salesmen_comission_value");
		
		salescom_value.value = (((this.value * Number(subtotal_sp))/100).toFixed(2));
		
		
				
	});
	
	calculateNetProfit();
	
});

$(".admin_entry").live("change", function(){
		
		ship_mode = document.getElementById("ship_mode").value;
	
		this_id = $(this).attr("id");
		
		//if((ship_mode.indexOf("sea") !== -1) || (ship_mode.indexOf("SEA") !== -1)) {
			
			subtotal_sp = $("#subtotal_sellingvalue").text();
			subtotal_fob = $("#subtotal_value").text();
			
			$("#"+this_id+"_value").val(((this.value * Number(subtotal_fob))/100).toFixed(2));
			
		//}
		customer_name = document.getElementById("customer_name").value;
		
		if((ship_mode.indexOf("collect") !== -1) || (ship_mode.indexOf("COLLECT") !== -1) || (customer_name.toLowerCase().indexOf("bodek") !== -1)) {
			
			subtotal_piece = $("#subtotal_piece").text();
			
			$("#"+this_id+"_value").val((this.value * Number(subtotal_piece)).toFixed(2));
		}
		
		salescom_percent = document.getElementById("salesmen_comission").value;
		salescom_value = document.getElementById("salesmen_comission_value");
		
		salescom_value.value = (((this.value * Number(subtotal_sp))/100).toFixed(2));
		
		calculateNetProfit();
				
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
	
	$("#"+total_id+"_price").text(total.toFixed(2));
	
	price_total = 0;
	
	$(".price_total").each(function(){

		price_total = price_total + Number(this.innerHTML);		
	});
	
	$("#subtotal_value").text(price_total.toFixed(2));
	
	$(".admin_entry").each(function(){
		
		ship_mode = document.getElementById("ship_mode").value;
	
		this_id = $(this).attr("id");
		
		//if((ship_mode.indexOf("sea") !== -1) || (ship_mode.indexOf("SEA") !== -1)) {
			
			subtotal_sp = $("#subtotal_sellingvalue").text();
			subtotal_fob = $("#subtotal_value").text();
			
			$("#"+this_id+"_value").val(((this.value * Number(subtotal_fob))/100).toFixed(2));
			
		//}
			customer_name = document.getElementById("customer_name").value;
			
			if((ship_mode.indexOf("collect") !== -1) || (ship_mode.indexOf("COLLECT") !== -1) || (customer_name.toLowerCase().indexOf("bodek") !== -1)) {
				
				subtotal_piece = $("#subtotal_piece").text();
				if(this_id == "freight") {
					$("#"+this_id+"_value").val((this.value * Number(subtotal_piece)).toFixed(2));
				}
			}
		
		salescom_percent = document.getElementById("salesmen_comission").value;
		salescom_value = document.getElementById("salesmen_comission_value");
		
		salescom_value.value = (((this.value * Number(subtotal_sp))/100).toFixed(2));
		
		
				
	});
	
	calculateNetProfit();
	
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
	
	$("#"+total_id+"_sprice").text(total.toFixed(2));
	
	price_total = 0;
	
	$(".sprice_total").each(function(){

		price_total = price_total + Number(this.innerHTML);		
	});
	
	$("#subtotal_sellingvalue").text(price_total.toFixed(2));
	
	$(".admin_entry").each(function(){
		
		ship_mode = document.getElementById("ship_mode").value;
	
		this_id = $(this).attr("id");
		
		//if((ship_mode.indexOf("sea") !== -1) || (ship_mode.indexOf("SEA") !== -1)) {
			
			subtotal_sp = $("#subtotal_sellingvalue").text();
			subtotal_fob = $("#subtotal_value").text();
			
			$("#"+this_id+"_value").val(((this.value * Number(subtotal_fob))/100).toFixed(2));
			
		//}
			customer_name = document.getElementById("customer_name").value;
			
			if((ship_mode.indexOf("collect") !== -1) || (ship_mode.indexOf("COLLECT") !== -1) || (customer_name.toLowerCase().indexOf("bodek") !== -1)) {
				
				subtotal_piece = $("#subtotal_piece").text();
				if(this_id == "freight") {
					$("#"+this_id+"_value").val((this.value * Number(subtotal_piece)).toFixed(2));
				}
			}
			
		salescom_percent = document.getElementById("salesmen_comission").value;
		salescom_value = document.getElementById("salesmen_comission_value");
		
		salescom_value.value = (((this.value * Number(subtotal_sp))/100).toFixed(2));
		
		
				
	});
	
	calculateNetProfit();
	
});
