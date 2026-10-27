// JavaScript Document
function isNumeric(evt)
{
	evt = (evt) ? evt : event;
	var characterCode = (evt.charCode) ? evt.charCode : ((evt.keyCode) ? evt.keyCode : ((evt.which) ? evt.which : 0));
	if ((characterCode < 48 || characterCode > 57)&&(characterCode != 43) && (characterCode != 45)&& (characterCode != 8)&& (characterCode != 46) && (characterCode != 39) && (characterCode != 37) && (characterCode != 9	) && (characterCode!=38)) 
	{
		
			return false;
	}
	return true;
}
function ValidateSpecialChar(evt) 
{
	evt = (evt) ? evt : event;
	var characterCode = (evt.charCode) ? evt.charCode : ((evt.keyCode) ? evt.keyCode : ((evt.which) ? evt.which : 0));
	if (characterCode > 32 && (characterCode < 65 || characterCode > 90) &&	(characterCode < 97 || characterCode > 122) && (characterCode < 48 || characterCode > 57) && (characterCode != 46) && (characterCode != 45) && (characterCode != 95) && (characterCode!=38)) 
	{
	
		return false;
	}
	return true;
 }
 function ValidateFabricName(evt) 
{
	evt = (evt) ? evt : event;
	var characterCode = (evt.charCode) ? evt.charCode : ((evt.keyCode) ? evt.keyCode : ((evt.which) ? evt.which : 0));
	if (characterCode > 32 && (characterCode < 65 || characterCode > 90) &&	(characterCode < 97 || characterCode > 122) && (characterCode < 48 || characterCode > 57) && (characterCode != 46) &&(characterCode !=37) && (characterCode!=38)) 
	{
	
		return false;
	}
	return true;
 }
function validateAlphabets(evt)
{
		evt = (evt) ? evt : event;
		
		var characterCode = (evt.charCode) ? evt.charCode : ((evt.keyCode) ? evt.keyCode : ((evt.which) ? evt.which : 0));
	if (characterCode > 32 && (characterCode < 65 || characterCode > 90) && 
	(characterCode < 97 || characterCode > 122) && (characterCode != 46) && (characterCode != 8) && (characterCode != 39) && (characterCode != 37) && (characterCode!=38)) {

				return false;
			}
			return true;
 }

$(document).ready(function(){	
		
	$('form').submit(function(){
		var result=0;
		$(".mandatory").each( function() 
		{
    		if($(this).val()=='')
    		{		
				$(this).css('border','1px solid red');	
				$(this).attr('title','Field Required');								
		  		$(this).tooltip('fixTitle').tooltip('show');
				  result=1;
   			} 
			else{
				$(this).css('border','1px solid #CCCCCC');
				$(this).tooltip('hide');
			}	
			
		});	
		if(result==1)
		   return false; 
		 else
		   return true; 
	});
	$('.mandatory').blur(function () {
								  
			var element=$(this).attr('id');	
			if(! $(this).val()){	
				$(this).css('border','1px solid red');	
				$(this).attr('title','Field Required');								
		  		$(this).tooltip('fixTitle').tooltip('show');
			}	
			else{				
				
				if(element=="email")
				{
					var x=$(this).val();
					var atpos=x.indexOf("@");
					var dotpos=x.lastIndexOf(".");
					var a="";
					if (atpos<1 || dotpos<atpos+2 || dotpos+2>=x.length)
					  {
						$(this).css('border','1px solid red');	
						$(this).attr('title','Enter Valid Email');								
						$(this).tooltip('fixTitle').tooltip('show');
						
					}	
					else
					{
						$(this).css('border','1px solid #CCCCCC');
						$(this).tooltip('hide');
					}				
					  
				}
				else{
					$(this).css('border','1px solid #CCCCCC');
						$(this).tooltip('hide');
				}
					
			}		
		
	});
	
	
	
});