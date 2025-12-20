$(document).ready(function(){

	$('#amend_tracking_search').on('click',function(){
		var crf_number = $('#crf_tracking_no').val();
		var customer_name = $('#customerName').val();

		if((crf_number == "") && (customer_name == ""))
        {
            $.growl({message: "Please Enter CRF tracking number or Select Customer Name."},{type: "warning"});
            return false;
        }
		
        var trackingObject = [];
        trackingObject.data = {};
        trackingObject.url =  '/bank/amendtrackingdetails';
        trackingObject.data['crf_tracking_no'] = crf_number;
        if(customer_name != "")
        {
            trackingObject.data['customerName'] = customer_name;
        }else{
             trackingObject.data['customerName'] = crf_number;
        }
        trackingObject.data['functionName'] = 'AmedTrackingDetailsCallBack';

        crudAjaxCall(trackingObject);				
				
        return false;
	});

	$("#copy_to_clip_img").on("click",function(){
		$(this).effect( "bounce", {times:2}, 200 )
		copyToClipboard($('#crf_number_for_copy').text());
	});


	$("#amendviewform").on("click",function(){

		var crf_number = $('#crf_tracking_no').val();
		var customer_name = $('#customerName').val();
        
        if(crf_number.length == 9){
            $.growl({message: "Please Enter CRF tracking number or Select Customer Name."},{type: "warning"});
            return false;
        }

        if((crf_number == "") && (customer_name == ""))
        {
            $.growl({message: "Please Enter CRF tracking number or Select Customer Name."},{type: "warning"});
            return false;
        }

        var crfNumber = '';

        if(crf_number  == ''){
            crfNumber = customer_name;
        }else{
            crfNumber = crf_number;
        }
        var obj = [];
        obj.data = {};
        obj.url = '/bank/printrequestform';
        obj.data['crf_number'] = crfNumber;
        obj.data['printCall'] = 'N';
        obj.data['functionName'] = 'printRequestForm';
        crudAjaxCall(obj);
        return false;
    });

    $("#customerName").on("change",function(){
        if($(this).val() != '')
        {
            $("#crf_tracking_no").val('');
            return false;
        }
    });

    $("#crf_tracking_no").on("focusout",function(){
        if($(this).val() != '')
        {
            $("#customerName").val('').trigger('change');
            return false;
        }
    });

});
$('#crf_tracking_no').on('focusout',function(){
   var custId = $('#crf_tracking_no').val();
   if(custId.length == 9){
        var obj = [];
        obj.data = {};
        obj.url = '/bank/crfcustomerlist';
        obj.data['custId'] = custId;
        obj.data['functionName'] = 'crfcustomerdropdownCallBack';
        crudAjaxCall(obj);
        return false;
   }
});


function setClipboardCode(){
	var aof_to_copy = $('#customerName').val();
	if(aof_to_copy == ''){
		aof_to_copy = $('#crf_tracking_no').val();
	}
	$('#crf_number_for_copy').text(aof_to_copy);
	$('#copy_to_clip_img').show();
	var formId = $('#formId_forCopy').val();
	if(formId != ''){
		$('#crf_number_for_copy').attr('title',formId);
	}else{
		$('#crf_number_for_copy').attr('title','');
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

function trackingcrf(crf_number){
    $('#crf_tracking_no').val(crf_number);
    $('#amend_tracking_search').click();
}

function crfcustomerdropdownCallBackFunction(response,object){
    if(response.status == 'success'){
        var customerCrfList =  response.data[0];
        $('#customerName').find('option').remove();
        $('#customerName').append('<option value="">Select Customer Name</option>');
        for(const [key,value] of Object.entries(customerCrfList)){
            $('#customerName').append('<option value='+key+'>'+value+'</option>');
        }
    }
}