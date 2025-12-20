$(document).ready(function(){
    $("body").on("click","#tracking_search",function(){
		
		if(($("#aof_tracking_no").val() == "") && ($("#customerName").val() == ""))
        {
            $.growl({message: "Please Enter AOF tracking number or Select Customer Name."},{type: "warning"});
            return false;
        }
		
        var trackingObject = [];
        trackingObject.data = {};
        trackingObject.url =  '/bank/trackingdetails';
        trackingObject.data['aof_tracking_no'] = $("#aof_tracking_no").val();
        if($("#customerName").val() != "")
        {
            trackingObject.data['customerName'] = $("#customerName").val();
        }else{
             trackingObject.data['customerName'] = $("#aof_tracking_no").val();
        }
        trackingObject.data['functionName'] = 'TrackingDetailsCallBack';

        crudAjaxCall(trackingObject);				
				
        return false;
    });

	$("body").on("click","#copy_to_clip_img",function(){
		$(this).effect( "bounce", {times:2}, 200 )
		copyToClipboard($('#aof_number_for_copy').text());
	});	


    $("body").on("click","#viewform",function(){
        if(($("#aof_tracking_no").val() == "") && ($("#customerName").val() == ""))
        {
            $.growl({message: "Please Enter AOF tracking number or Select Customer Name."},{type: "warning"});
            return false;
        }
        var formObject = [];
        formObject.data = {};
        formObject.url =  '/bank/formdetails';
        formObject.data['aof_tracking_no'] = $("#aof_tracking_no").val();
        if($("#customerName").val() != "")
        {
            formObject.data['customerName'] = $("#customerName").val();
        }
        formObject.data['functionName'] = 'FormDetailsCallBack';

        crudAjaxCall(formObject);
        return false;
    });

    $("body").on("change","#customerName",function(){
        if($(this).val() != '')
        {
            $("#aof_tracking_no").val('');
            return false;
        }
    })

    $("body").on("focusout","#aof_tracking_no",function(){
        if($(this).val() != '')
        {
            $("#customerName").val('').trigger('change');
            return false;
        }
    })
});

function setClipboardCode(){
		var aof_to_copy = $('#customerName').val();
		if(aof_to_copy == ''){
			aof_to_copy = $('#aof_tracking_no').val();
		}
		$('#aof_number_for_copy').text(aof_to_copy);
		$('#copy_to_clip_img').show();
		var formId = $('#formId_forCopy').val();
		if(formId != ''){
			$('#aof_number_for_copy').attr('title',formId);
		}else{
			$('#aof_number_for_copy').attr('title','');
		}
}

function copyToClipboard(textToCopy) {
    // navigator clipboard api needs a secure context (https)
    if (navigator.clipboard && window.isSecureContext) {
        // navigator clipboard api method'
        return navigator.clipboard.writeText(textToCopy);
    } else {
        // text area method
        let textArea = document.createElement("textarea");
        textArea.value = textToCopy;
        // make the textarea out of viewport
        textArea.style.position = "fixed";
        textArea.style.left = "-999999px";
        textArea.style.top = "-999999px";
        document.body.appendChild(textArea);
        textArea.focus();
        textArea.select();
        return new Promise((res, rej) => {
            // here the magic happens
            document.execCommand('copy') ? res() : rej();
            textArea.remove();
        });
    }
}