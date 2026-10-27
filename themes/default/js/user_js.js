	$(document).ready(function() {
		$('.ask').jConfirmAction();
		$('.date').datetimepicker({
			showSecond: true,
			timeFormat: 'HH:mm:ss',
			dateFormat: 'yy-mm-dd',
		
		});
		$( ".task-comments-alone" ).click(function() {
			$( "#toggle-comment" ).toggle("fast");
		});
		
		$( ".attachment-head" ).click(function() {
			$( "#toggle-attachments" ).toggle("fast");
		});
		
		$( ".history-head" ).click(function() {
			$( "#toggle-history" ).toggle("fast");
		});
		
		$('#mynotif-head').click(function() {
		  $('#mynotif-child').toggle('fast');
		});
		
		$('#myassigned-head').click(function() {
		  $('#myassigned-child').toggle('fast');
		});
		
		$('#mymonitor-head').click(function() {
		  $('#mymonitor-child').toggle('fast');
		});
		
		$(".multi-select").multiselect().multiselectfilter();
	
		
	});
	
	
	
	$(function() {
        $( "#accordion-open,#accordion-inprogress,#accordion-completed,#accordion-reopen,#accordion-closed" ).accordion({
            heightStyle: "content",
			collapsible: true,
			active: false
        });

    });
	
	$(function(){
		$('form.uniForm').uniform({
		  prevent_submit : true
		});
	});	
	
	 window.dropDownValidate = function(field, caption) {
	 
        if ("Select" == field.val()) {
          return caption + ' Select a value';
        }

       else{
          return true;
        }
  
      }
	  
	  // increase the default animation speed to exaggerate the effect
    $.fx.speeds._default = 400;
    $(function() {
		 
		$( "#progress-slider").on('slidestop', function( event ) {
			var slider_value=$("#progress-slider").slider("value");
			var task_status = $("#task-status").val();
			
			if(slider_value > 0 && task_status == 1) {
				$("#task-status").val(2);
			}
		});
		
		$('#task-status').change(function() {
			 progress_slider = $( "#progress-slider" ).slider( "value" );
			 if(this.value == 1 && progress_slider > 0) {
			 	alert("Already task has some progress, cannot move to Open status");
				$("#task-status").val(2);
			 }
		});
		
        $( "#add-comment,#add-attachment,#assign-to-me, #change-progress" ).dialog({
            autoOpen: false,
            show: "explode",
            hide: "puff"
        });
 
        $( "#add-comment-open" ).click(function() {
            $( "#add-comment" ).dialog( "open" );
            return false;
        });
		
		$( "#add-attachment-open" ).click(function() {
            $( "#add-attachment" ).dialog( "open" );
            return false;
        });
		
		$( "#assign-to-me-open" ).click(function() {
            $( "#assign-to-me" ).dialog( "open" );
            return false;
        });
		
		$( "#change-progress-open" ).click(function() {
            $( "#change-progress" ).dialog( "open" );
            return false;
        });		
    });
	  	
		bkLib.onDomLoaded(function() {
			 new nicEditor({iconsPath : '<?= $theme_path; ?>/js/nicEditorIcons.gif', buttonList : ['fontSize','bold','italic','underline','strikeThrough','html','left','center','right','justify','indent','outdent','link','unlink','forecolor','bgcolor']}).panelInstance('comment-summary');
		}); 