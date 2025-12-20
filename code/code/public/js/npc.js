var newArray = [];
$(document).ready(function(){
	//commentsField only allow 250 characters

	$('.commentsField').keyup(function(){
		 currVal = $(this).val();
		 if (currVal.length > 250 ) {
			$.growl({message:'Maximum 250 Characters Allowed'},{type: "warning"});
			$(this).val(currVal.substr(0,250));
		 }
	});

    setTimeout(function(){
        $('.inReview').each(function(){
            $(this).parent().parent().hide();
        });

    },1000);

    

    $("body").on("click",".npcReview",function(){

        var alreadyreviewObject = [];
        alreadyreviewObject.data = {};
        alreadyreviewObject.url =  '/npc/alreadyreview';
        alreadyreviewObject.data['form_id'] = $(this).attr('id');

        alreadyreviewObject.data['functionName'] = 'AlreadyreviewCallBack';

        crudAjaxCall(alreadyreviewObject);


        //redirectUrl($(this).attr("id"),'/npc/review');
        return false;
    });

    $("body").on("click",".okToReview",function(){
        var formID =  $('#reviewModalFormId').val();
        redirectUrl(formID,'/npc/review');
        return false;
    });

    $("body").on("change",".reviewComments",function(){
        if(this.checked){
            if($(this).parent().parent().next().next().html() != '')
            {
                $(this).parent().parent().next().next().html('');
            }
            $(this).parent().parent().next().addClass('display-none');
            if($(this).parent().parent().parent().next().find('.saveField')){
                $(this).parent().parent().parent().next().addClass('display-none');
            }
        }else{
            $(this).parent().parent().next().removeClass('display-none');
            if($(this).parent().parent().parent().next().find('.saveField')){
                $(this).parent().parent().parent().next().removeClass('display-none');
            }
        }
        var id = $(this).closest('#verify-checkbox').attr('id');
        $('#'+$(this).closest('#verify-checkbox').attr('id')).find('input:checkbox').each(function() {
            if($("input[name='"+$(this).attr("name")+"']").is(":checked") == false){
                valid = false;
                return false;
            }else{
                valid = true;
            }
        });
        var checkid = $(this).attr('id').split('-');
        var fieldtype = checkid[0];
        var applicant = checkid[1];
 
        if(_sourceofapp == 'DSA'){
                if((valid)){          
            $(".clear").removeClass('display-none');
            $(".discrepent").addClass('display-none');
        }else{
                    var checkallow = 0;
                    $('.reviewComments').each(function(){
                        var fieldcheckid = $(this).attr('id');
                        var fieldcheck = fieldcheckid.split('-')[0];
                        if(fieldcheck != 'pf_type_image_toggle'){
                            if($('#'+fieldcheckid).prop('checked') == false){
                                checkallow++;
                            }
                        }
                    }); 
                    if(checkallow == 0){
                        $(".clear").removeClass('display-none');
                        $(".discrepent").addClass('display-none');
                    }else{
            $(".clear").addClass('display-none');
            $(".discrepent").removeClass('display-none');
        }
                }
        }else{

            if((valid)){          
                $(".clear").removeClass('display-none');
                $(".discrepent").addClass('display-none');
            }else{
           
                $(".clear").addClass('display-none');
                $(".discrepent").removeClass('display-none');
            }
        }

        return false;

    });

    

    $(".entityreview").on("click",function(){

        var name = $(this).attr("name");
        
        if($('#'+name+'_img_pdf').html() == undefined){
        if($("#document_preview_"+name+'_img').attr('src') != undefined){
            var image = $("#document_preview_"+name+'_img').attr('src').split('/').pop();
        }else{
            if($("."+name+'_img').attr('src') != undefined){
                var image = $("."+name+'_img').attr('src').split('/').pop();
            }else{
                $.growl({message: "Please Add Image"},{type: "warning"});
                return false;
            }
        }
        }else{
            var image = $('#'+name+'_img_pdf').html().toLowerCase();
        }


        if(this.checked){
            if($(this).parent().parent().next().next().html() != '')
            {
                $(this).parent().parent().next().next().html('');
            }
            $(this).parent().parent().next().addClass('display-none');
        }else{
            $(this).parent().parent().next().removeClass('display-none');
        }
        var id = $(this).closest('#verify-checkbox').attr('id');
        $('#'+$(this).closest('#verify-checkbox').attr('id')).find('input:checkbox').each(function() {
            if($("input[name='"+$(this).attr("name")+"']").is(":checked") == false){
                valid = false;
                return false;
            }else{
                valid = true;
            }
        });
        if((valid)){
            $(".clear").removeClass('display-none');
            $(".discrepent").addClass('display-none');
        }else{
            $(".clear").addClass('display-none');
            $(".discrepent").removeClass('display-none');
        }
        
        if($(this).prop("checked") == true){
            var is_active = 1;
        }else{
            var is_active = 0;
        }


        
        var saveEntityReview = [];
        saveEntityReview.data = {};
        saveEntityReview.url =  '/npc/entityreview';
        saveEntityReview.data['form_id'] = $("#formId").val();
        saveEntityReview.data['blade_id'] = $(this).attr("name");
        saveEntityReview.data['clearance_image'] = image;
        saveEntityReview.data['clearance_id'] = $(this).attr("data-id");
        saveEntityReview.data['is_active'] = is_active;
        saveEntityReview.data['functionName'] = 'EntityReviewCallBack';


        crudAjaxCall(saveEntityReview);
        return false;
    });


    $(".saveComments").on("click",function(){

         if($(this).prev().val().trim() == ''){

            $.growl({message: "Please enter comments"},{type: "warning"});
            return false;
        }
        
        var regexMatch= $(this).prev().val().match(/[^a-zA-Z0-9 .,%?\/\\()_\n\r]/gi);
        if (regexMatch != null && regexMatch.length > 0) {
            $.growl({message: "Error! Invalid comment (Special characters are not allowed)"},{type: "warning"});
            return false;
        }
        var clearance_id = $(this).prev().attr("id");
        

        var saveCommentsObject = [];
        saveCommentsObject.data = {};
        saveCommentsObject.url =  '/npc/savecomments';
        saveCommentsObject.data['form_id'] = $("#formId").val();
        saveCommentsObject.data['column_name'] = $(this).prev().attr("id");
        saveCommentsObject.data['comments'] = $(this).prev().val();
        saveCommentsObject.data['section'] = $(this).closest('.card').attr('id');
        if(typeof($(this).parent().attr("id"))!="undefined"){
        if(clearance_id.substr(-10) == '_clearance'){
            saveCommentsObject.data['clearance_active'] = 0;
        }
            saveCommentsObject.data['reviewId'] = $(this).parent().attr("id");
        }
        saveCommentsObject.data['functionName'] = 'SaveCommentsCallBack';

        crudAjaxCall(saveCommentsObject);
        return false;
    }); 

    $("body").on("click",".editComments",function(){
		existingValue = $(this).parent().text();		
		existingValue = existingValue.slice(0,-4);
		existingId = $(this).attr("id");
		fieldName = $(this).attr("data-field");
		
        $(this).parent().parent().prev().removeClass('display-none').attr('id',existingId);
        $(this).parent().addClass('display-none');
        if($(this).parent().parent().parent().next().hasClass('eloneedit') == true){
            $(this).parent().parent().parent().next().removeClass('display-none')
        }

		if(fieldName != '' && existingValue != ''){
			$('#'+fieldName).val(existingValue);
		}		
        return false;
    });

    $("body").on("click",".reject",function(){
        var saveRejectedCommentsObject = [];
        saveRejectedCommentsObject.data = {};
        saveRejectedCommentsObject.url =  '/npc/savecomments';
        saveRejectedCommentsObject.data['form_id'] = $("#formId").val();
        saveRejectedCommentsObject.data['column_name'] = $(this).parent().parent().parent().find('.rejectedComments').attr("id");
        saveRejectedCommentsObject.data['comments'] = $(this).parent().parent().parent().find('.rejectedComments').val();
        if(typeof($(this).parent().parent().prev().prev().attr("id"))!="undefined"){
            saveRejectedCommentsObject.data['reviewId'] = $(this).parent().parent().prev().prev().attr("id");
        }
        saveRejectedCommentsObject.data['functionName'] = 'SaveRejectedCommentsCallBack';

        crudAjaxCall(saveRejectedCommentsObject);
        return false;
    });

    $("body").on("click",".editRejectedComments",function(){
        $(this).parent().prev().removeClass('display-none').attr('id',$(this).attr("id"));
        $(this).parent().addClass('display-none');
        return false;
    });

    $('.customer_type').on('change', function() {
        getUserDataApplications();
    });

    $("body").on("click",".submit_to_bank",function(){
        var is_empty = false;
        var is_hold_reject = false;

        if($(this).attr('id') == 'hold' || $(this).attr('id') == 'reject'){
            var is_hold_reject = true;
            if($(this).attr('id') == 'hold'){
                var hold_reject_id = 'hold_comment';
                var hold_comment_display = 'Hold Comment';
            }else{
                var hold_reject_id = 'reject_comment';
                var hold_comment_display = 'Reject Comment';
            }
        }
        // $(".comments-blck").find('input').each(function(){
        if(!is_hold_reject){
            $(".commentsField").each(function(){
                if(!$(this).is(":hidden")){
                    if($(this).val() == '')
                    {
                        var name = $(this).attr('id').split('_')[0];
                        is_empty = true;
                        return false;
                    }
                }
            });

            if((is_empty) && ($(this).hasClass("noValidation") == false))
            {
                $.growl({message: "Please add comments"},{type: "warning"});
                return false;
            }

            var allow_forward = true;
            // $('.comments-blck').each(function(index){
            $(".commentsField").each(function(){
                if($(this).is(":visible")){
                    if($(this).val() == '' || $(this).parent().css('display') != 'none'){
                        allow_forward = false;
                        return false;
                    }
                }
            });

            if((!allow_forward) && ($(this).hasClass("noValidation") == false))
            {
                $.growl({message: "Please save Comments"},{type: "warning"});
                return false;
            }
            if(typeof($(this).attr('id')) == "undefined")
            {
                $.growl({message: "Please update responses"},{type: "warning"});
                return false;
            }
        }else{
            if($('#'+hold_reject_id).val() == ''){
                $.growl({message: "Please Add "+hold_comment_display},{type: "warning"});
                return false;
            }
        }

				

        var currRole = $('#roleId').val();
		if(currRole == 'L3'){
			var L3_comments = true;
			$(".commentsField:visible").each(function(){ if($(this).val().trim() == '') L3_comments=false; });
			if(!L3_comments){
				$.growl({message: "Please update responses"},{type: "warning"});
				return false;
			}  
		}
		document.getElementById("approved").disabled=true;
		
		
		var all_clear = true;
        $(".comments-blck").find('input').each(function(){ if($(this).val() != '') all_clear=false; });

        var descCount = 0;
        $(".comments-blck").find('input').each(function(){ if($(this).val() != '') descCount++; });

		var _endTime = new Date();
		if(typeof(_startTime) != 'undefined'){
			var timeTaken = (_endTime.getTime() - _startTime.getTime()) / 1000;
		}else{
			var timeTaken = -1;
		}

       if($('#nri_date').text() != ''){

            var valueDate = '053';
            var creationDate = '';
            var reviewDate =  '';
            var customerDate = $('#nri_date').text();

        }else{
           var valueDate =  $('input[name="value_date"]:checked').val();
           var creationDate = $('#creation_date').attr('created_at'); 
           var reviewDate = $('#review_date').attr('review_date'); 
           var customerDate = '';
       }

		$('.br_submit_loader').removeClass('display-none-npc-loader');
        $('.br_submit_loader').removeClass('display-none-npc2-loader');

        $("#NpcModal").modal('show');
        var submitToBankObject = [];
        submitToBankObject.data = {};
        submitToBankObject.url =  '/npc/submittobank';
        submitToBankObject.data['form_id'] = $("#formId").val();
        submitToBankObject.data['holdcomment'] = $("#hold_comment").val();
        submitToBankObject.data['rejectcomment'] = $("#reject_comment").val();
        submitToBankObject.data['status'] = $(this).attr('id');
		submitToBankObject.data['fromRole'] = $('#fromRole').val();
		submitToBankObject.data['descCount'] = descCount; 
		submitToBankObject.data['timeTaken'] = timeTaken; 
        submitToBankObject.data['value_date'] = valueDate;
        submitToBankObject.data['creation_date'] = creationDate; 
        submitToBankObject.data['review_date'] = reviewDate; 
        submitToBankObject.data['customer_date'] = customerDate; 

		
        if(all_clear){
             submitToBankObject.data['functionName'] = 'SubmitToFinacleCallBack';
        }else{
            submitToBankObject.data['functionName'] = 'SubmitToBankCallBack';
        }
        disableWorkAreaL1Customer();

        crudAjaxCall(submitToBankObject);
        return false;
    });

    $("body").on("click","#account_details_search",function(){
        var accountObject = [];
        accountObject.data = {};
        accountObject.url =  '/bank/accountdetails';
        accountObject.data['aof_tracking_no'] = $("#aof_tracking_no").val();
        if($("#customerName").val() != "")
        {
            accountObject.data['customerName'] = $("#customerName").val();
            accountObject.data['aof_tracking_no'] = $("#customerName").val();
        }
        accountObject.data['functionName'] = 'AccountDetailsCallBack';

        crudAjaxCall(accountObject);
        return false;
    });


  //------------------------------Function will remove after check 19-FEB-2021------------------//

  
    // $("body").on("change","#customerName",function(){
    //     if($(this).val() != '')
    //     {
    //         $("#aof_tracking_no").val('');
    //         return false;
    //     }
    // });


      //------------------------------Function will remove after check 19-FEB-2021------------------//

    // $("body").on("focusout","#aof_tracking_no",function(){
    //     if($(this).val() != '')
    //     {
    //         $("#customerName").val('').trigger('change');
    //         return false;
    //     }
    // });

    $("body").on("click","#create_customer_id",function(){
        $('.privilegeaccessRedirect_background ').removeClass('display-none');
        var formId = $(this).attr('formId');
        disablePrivilageAccessButton(this);
        createCustomerId(formId,'privilege');
    });

    $("body").on("click","#check_funding_status",function(){
        $('.privilegeaccessRedirect_background ').removeClass('display-none');
        var formId = $(this).attr('formId');
        disablePrivilageAccessButton(this);
        checkFundingStatus(formId,'privilege');
    });

    $("body").on("click","#create_account_no",function(){
        $('.privilegeaccessRedirect_background ').removeClass('display-none');
        var formId = $(this).attr('formId');
        disablePrivilageAccessButton(this);
        createAccountNumber(formId,'privilege');
    });

    $("body").on("click","#fund_transfer",function(){
        $('.privilegeaccessRedirect_background ').removeClass('display-none');
        var formId = $(this).attr('formId');
        disablePrivilageAccessButton(this);
        fundTransfer(formId,'privilege');
    });

    $("body").on("click","#check_tdaccount_created",function(){
        $('.privilegeaccessRedirect_background ').removeClass('display-none');
        var formId = $(this).attr('formId');
        disablePrivilageAccessButton(this);
        checkTDaccountCreated(formId,'privilege');
    });

    $("body").on("click","#query_generate_id",function(){
        $('.privilegeaccessRedirect_background ').removeClass('display-none');
        //var accountId = $(this).attr('accountId');
        var formId = $(this).attr('formId');
        disablePrivilageAccessButton(this);
        generateQueryId(formId);
    });

    $("body").on("click","#check_dedupe_status",function(){
        $('.privilegeaccessRedirect_background ').removeClass('display-none');
        var formId = $(this).attr('formId');
        disablePrivilageAccessButton(this);
        checkDedupeStatus(formId);
    });			

    $("body").on("click","#mark_form_for_qc",function(){
        $('.privilegeaccessRedirect_background ').removeClass('display-none');
        var formId = $(this).attr('formId');
        disablePrivilageAccessButton(this);
        markFormForQC(formId);
    });

     $("body").on("click",'.linktext',function(){
        var title = $(this).attr('title');
        $.growl({message: title},{type: "success"});

    });

    $("body").on("click",".oaoReview",function(){
        var param = $(this).attr("id");
        
        redirectUrl(param,'/npc/oaoReview');
        return false;
    });


    $("body").on("click",".fundReceived",function(){

        var fundReceivedObject = [];
        fundReceivedObject.data = {};
        fundReceivedObject.url =  '/npc/fundReceived';
        fundReceivedObject.data['oao_id'] = $(this).attr('id');

        fundReceivedObject.data['functionName'] = 'fundReceivedCallBack';

        crudAjaxCall(fundReceivedObject);
        return false;
    });

    $("body").on("click",".vkycDone",function(){

        var vkycDoneObject = [];
        vkycDoneObject.data = {};
        vkycDoneObject.url =  '/npc/vkycDone';
        vkycDoneObject.data['oao_id'] = $(this).attr('id');

        vkycDoneObject.data['functionName'] = 'vkycDoneCallBack';

        crudAjaxCall(vkycDoneObject);
        return false;
    });

    $("body").on("click",".updateDsaField",function(){
        var field = $(this).attr('id').split('-')[0];
        var formId = $(this).attr('id').split('-')[1];
        var value = $('#'+field+'-value').val();
        var comment = $('#'+field+'-comment').val();

        if (value == '' || comment == '') {
            $.growl({message: 'Please enter value and comment as mandatory fields'}, {type: 'warning'});
            return false;
        }

        var oaoUpdateObject = [];
        oaoUpdateObject.data = {};
        oaoUpdateObject.url =  '/npc/updateOaoDetails';
        oaoUpdateObject.data['form_id'] = formId;
        oaoUpdateObject.data['field'] = field;
        oaoUpdateObject.data['value'] = value;
        oaoUpdateObject.data['comment'] = comment;

        oaoUpdateObject.data['functionName'] = 'updateOaoDetailsCallBack';

        crudAjaxCall(oaoUpdateObject);
        return false;
    });


    $('#checkimgmasking').on('click',function(){
        var formId = $('#formId').val();
        var obj = [];
        obj.data ={};
        obj.url = '/npc/checkimagemasking';
        obj.data['form_id'] = formId;
        obj.data['functionName'] = 'checkimagemaskingCallBack';
        crudAjaxCall(obj);
        return false;
    });


   
    // Set NPC Dashboard for tat view
    // First Time
    
    setTimeout(function(){
        updateNpcTatDashboard();
        //$('.npcReview').css('padding-right', '10px');
    }, 2000);

    // Now every 10sec
    setInterval(() => {        
        updateNpcTatDashboard();
        //$('.npcReview').css('padding-right', '10px');
    }, 6000);



});

function checkimagemaskingCallBackFunction(response){
    if(response['status'] == "success"){        
        $.growl({message: response['msg']},{type: response['status']});
    }else{        
        $.growl({message: response['msg']},{type: "warning"});
    }
    return true;
}

function submitL3Update(updateType, serial){
    var note = ''; 
    var imageName = '';

    if (updateType == 'note') {
        note = $('#note_decription-'+serial).val();
		if(note.trim() == ''){
			$.growl({message: 'Please update the Notes section before submitting'}, {type: 'warning'});
			return false;
		}
    }else{ 
        imageName = $("#level3tempimage_note_card-"+serial).attr('src');
		if(typeof(imageName) == 'undefined' || imageName.trim() == ''){  
			$.growl({message: 'Please update image section before submitting'}, {type: 'warning'});
			return false;
		}
        imageName = imageName.split('/');
        imageName = imageName[imageName.length - 1];
    }

    var uploadImageObject = [];
    uploadImageObject.data = {};
    uploadImageObject.url =  '/bank/level3update';
    uploadImageObject.data['image'] = imageName;
    uploadImageObject.data['formId'] = $('#formId').val(); 
    uploadImageObject.data['updateType'] = updateType;
    uploadImageObject.data['note'] = note;
    uploadImageObject.data['serial'] = serial;
    uploadImageObject.data['functionName'] = 'L3UpdateCallback';
    //console.log(uploadImageObject);
    crudAjaxCall(uploadImageObject);
    return false;
}

function L3UpdateCallbackFunction(response,object)  
{    
    if(response['status'] == "success"){        
        $.growl({message: response['msg']},{type: response['status']});
    }else{        
        $.growl({message: response['msg']},{type: "warning"});
    }
    return true;
}

function getUserDataApplications()
{
    var tableRemainingHeight = $(".header-navbar").height()+$(".accountsgrid").height()/*+$(".filtergrid").height()*/+260;

    var sentDateRange = $('#sentDate').val();
    var sentDates = sentDateRange.split(" to ");

    var tableObject = [];
    tableObject.data = {};
    tableObject.data['AOF_NUMBER'] = $("#aofNumber").val();  
    tableObject.data['customer'] = $("#customerName").val();
    tableObject.data['customer_type'] = $('#customerType').val();
    tableObject.data['tabType'] = globalchecktable;  
    tableObject.data['aof_tabType'] = globalchecktable;
    tableObject.data['startDate'] = sentDates[0];
    tableObject.data['endDate'] = sentDates[1];    
    tableObject.data['table'] = "userApplicationsTable";
    tableObject.url =  '/npc/userapplications';
    

    datatableAjaxCall(tableObject,tableRemainingHeight, 0,"asc");
    return false;
}

function getnewtabDataApplications(tabType)
{   
    var tableRemainingHeight = $(".header-navbar").height()+$(".accountsgrid").height()/*+$(".filtergrid").height()*/+260;
    var tabId = $(this).attr('id');
    var sentDateRange = $('#sentDate').val();
  
    var sentDates = sentDateRange.split(" to ");
    
    var tableObject = [];
    tableObject.data = {};
    tableObject.data['AOF_NUMBER'] = $("#aofNumber").val();  
    tableObject.data['customer'] = $("#customerName").val();
    tableObject.data['customer_type'] = $('#customerType').val();
    tableObject.data['startDate'] = sentDates[0];
    tableObject.data['endDate'] = sentDates[1];
   
    tableObject.data['tabType'] = tabType;
    tableObject.data['aof_tabType'] = globalchecktable;
    tableObject.data['table'] = "userApplicationsTable";
    tableObject.url =  '/npc/userapplications';
    

    datatableAjaxCall(tableObject,tableRemainingHeight, 0,"asc");
    return false;
}


function getOaoDataApplications(url,table,height)
{
    var tableRemainingHeight = height;


    var tableObject = [];
    tableObject.data = {};
    tableObject.data['AOF_NUMBER'] = $("#aofNumber").val();
    tableObject.data['MOBILE_NUMBER'] = $("#mobileNumber").val();
    tableObject.data['customer'] = $("#customerName").val();
    tableObject.data['customer_type'] = $('#customerType').val();
    tableObject.data['table'] = table;
    tableObject.url =  url;

    datatableAjaxCall(tableObject,tableRemainingHeight, 0,"desc");
    return false;
}

function createCustomerId(formId,type='')
{
    var customerIdObject = [];
    customerIdObject.data = {};
    customerIdObject.url =  '/npc/createcustomerid';
    customerIdObject.data['form_id'] = formId;
    customerIdObject.data['type'] = type;
    customerIdObject.data['functionName'] = 'createCustomerIdCallBack';

    crudAjaxCall(customerIdObject);
    return false;
}

function createCustomerIdCallBackFunction(response,object)
{
    var baseUrl = $('meta[name="base_url"]').attr('content');
    var redirectUrl = '/npc/dashboard';
    // if(object.data['type'] == 'privilege')
    // {
    //     redirectUrl = '/bank/privilegeaccess';
    // }  
    if(response['status'] == "success"){
		if(typeof response['data'][0] != "undefined"){
			customerIDSuccess(response['data'][0]);
		}else{
			customerIDSuccess('<br><Check Finacle');
		}

        $.growl({message: response['msg']},{type: response['status']});
        if(object.data['type'] != 'privilege')
        {
            checkFundingStatus(object.data['form_id']);
        }else{

            if(object.data['type'] == 'privilege')
            { 
                setTimeout(function(){
                   privilegeaccessRedirect();
                },1000);
            }else{

                 setTimeout(function(){
                    window.location = baseUrl+redirectUrl;
                },3000);
            }
        }
    }else{
        enablePrivilageAccessButton($('#create_customer_id'));
        $.growl({message: response['msg']},{type: "warning"});
        $('.privilegeaccessRedirect_background ').addClass('display-none');
        // setTimeout(function(){
        //     window.location = baseUrl+redirectUrl;
        // }, 1000);
         if(object.data['type'] == 'privilege')
            { 
                setTimeout(function(){
                   privilegeaccessRedirect();
                },1000);
            }else{

                 setTimeout(function(){
                    window.location = baseUrl+redirectUrl;
                },3000);
            }
    }
    return false;
}

function checkFundingStatus(formId,type='')
{
    var fundingObject = [];
    fundingObject.data = {};
    fundingObject.url =  '/npc/checkfundingstatus';
    fundingObject.data['form_id'] = formId;
    fundingObject.data['type'] = type;
    fundingObject.data['functionName'] = 'checkFundingStatusCallBack';

    crudAjaxCall(fundingObject);
    return false;
}

function checkFundingStatusCallBackFunction(response,object)
{
    var baseUrl = $('meta[name="base_url"]').attr('content');
    var redirectUrl = '/npc/dashboard';
    // if(object.data['type'] == 'privilege')
    // {
    //     redirectUrl = '/bank/privilegeaccess';
    // }
    if(response['status'] == "success"){
        fundindClearedSuccess();
        $.growl({message: response['msg']},{type: response['status']});

        if(object.data['type'] != 'privilege')
        {
            createAccountNumber(object.data['form_id']);
        }else{
            setTimeout(function(){
                   privilegeaccessRedirect();
            },1000);
        }
    }else{
        enablePrivilageAccessButton($('#check_funding_status'));

        $.growl({message: response['msg']},{type: "warning"});
        $('.privilegeaccessRedirect_background ').addClass('display-none');
        // setTimeout(function(){
        //     window.location = baseUrl+redirectUrl;
        // }, 1000);
         if(object.data['type'] == 'privilege')
            { 
                setTimeout(function(){
                   privilegeaccessRedirect();
                },1000);
            }else{

                 setTimeout(function(){
                    window.location = baseUrl+redirectUrl;
                },3000);
            }
        return false;
    }
}

function createAccountNumber(formId,type='')
{
    var accountObject = [];
    accountObject.data = {};
    accountObject.url =  '/npc/createaccountnumber';
    accountObject.data['form_id'] = formId;
    accountObject.data['type'] = type;
    accountObject.data['functionName'] = 'createAccountNumberCallBack';

    crudAjaxCall(accountObject);
    return false;
}

function createAccountNumberCallBackFunction(response,object)
{
    var baseUrl = $('meta[name="base_url"]').attr('content');
    var redirectUrl = '/npc/dashboard';
    // if(object.data['type'] == 'privilege')
    // {
    //     redirectUrl = '/bank/privilegeaccess';
    // }
    $.growl({message: response['msg']},{type: response['status']});
    
    if(response['status'] == "success"){
		if(typeof response['data'][0] != "undefined"){
            //For savings Account
			creatingAccountNumberSuccess(response['data'][0]);

            if(object.data['type'] != 'privilege')
            {
                fundTransfer(object.data['form_id']);
            }else{
                if(object.data['type'] == 'privilege')
               { 
                         setTimeout(function(){
                       privilegeaccessRedirect();
                    }, 1000);
                }else{
                 setTimeout(function(){
                    window.location = baseUrl+redirectUrl;
                }, 3000);

                }
            }

		}else{
			creatingAccountNumberSuccess('Request Submitted Check Finacle');
            if(object.data['type'] == 'privilege')
            { 
                     setTimeout(function(){
                   privilegeaccessRedirect();
                }, 1000);
            }else{
             setTimeout(function(){
                window.location = baseUrl+redirectUrl;
             }, 3000);

            }
		}

        
    }else{
        enablePrivilageAccessButton($('#create_account_no'));

        $.growl({message: response['msg']},{type: "warning"});
        $('.privilegeaccessRedirect_background ').addClass('display-none');
        // setTimeout(function(){
        //     window.location = baseUrl+redirectUrl;
        // }, 1000);
         if(object.data['type'] == 'privilege')
           { 
                     setTimeout(function(){
                   privilegeaccessRedirect();
                }, 1000);
            }else{
             setTimeout(function(){
                window.location = baseUrl+redirectUrl;
            }, 3000);

            }
        return false;
    }
}

function fundTransfer(formId,type='')
{
    var fundObject = [];
    fundObject.data = {};
    fundObject.url =  '/npc/fundtransfer';
    fundObject.data['form_id'] = formId;
    fundObject.data['type'] = type;
    fundObject.data['functionName'] = 'fundTransferCallBack';

    crudAjaxCall(fundObject);
    return false;
}

function fundTransferCallBackFunction(response,object)
{
    var baseUrl = $('meta[name="base_url"]').attr('content');
    var redirectUrl = '/npc/dashboard';
    if(object.data['type'] == 'privilege')
    {
        redirectUrl = '/bank/privilegeaccess';
    }
    if(response['status'] == "success"){
        markFormForQC(response.data['form_Id']);
        fundtransferSuccess(baseUrl+redirectUrl);
        $.growl({message: response['msg']},{type: response['status']});
    }else{
        enablePrivilageAccessButton($('#fund_transfer'));

        $.growl({message: response['msg']},{type: "warning"});
	
		//if(typeof response.data.Body.transferResponse != "undefined")
		//$.growl({message: JSON.stringify(response.data.Body.transferResponse)},{type: "warning",delay:60000,allow_dismiss:true}); 
	
        $('.privilegeaccessRedirect_background ').addClass('display-none');
    }
    setTimeout(function(){
           privilegeaccessRedirect();
        }, 2000);
    return false;
}

function checkTDaccountCreated(formId,type='')
{
    var fundObject = [];
    fundObject.data = {};
    fundObject.url =  '/npc/checktdaccountcreated';
    fundObject.data['form_id'] = formId;
    fundObject.data['type'] = type;
    fundObject.data['functionName'] = 'checkTDaccountCreatedCallBack';

    crudAjaxCall(fundObject);
    return false;
}

function checkTDaccountCreatedCallBackFunction(response,object)
{
    var baseUrl = $('meta[name="base_url"]').attr('content');
    var redirectUrl = '/npc/dashboard';
    if(object.data['type'] == 'privilege')
    {
        redirectUrl = '/bank/privilegeaccess';
    }
    if(response['status'] == "success"){
        $.growl({message: response['msg']},{type: response['status']});
    }else{
        enablePrivilageAccessButton($('#check_tdaccount_created'));

        $.growl({message: response['msg']},{type: "warning"});
        $('.privilegeaccessRedirect_background ').addClass('display-none');
    }
    setTimeout(function(){
           privilegeaccessRedirect();
        }, 1000);
    return false;
}

function AccountDetailsCallBackFunction(response,object)
{   
    $('.privilegeaccessRedirect_background ').addClass('display-none');
    $("#account_details").html(response);
	setTimeout(function(){
			setClipboardCode();
		}, 500);
    return false;
}

function generateQueryId(formId)
{
    var fundObject = [];
    fundObject.data = {};
    fundObject.url =  '/npc/generatequeryid';
    fundObject.data['formId'] = formId;
    fundObject.data['functionName'] = 'generateQueryIdCallBack';

    crudAjaxCall(fundObject);
    return false;
}

function generateQueryIdCallBackFunction(response,object)
{
    //var baseUrl = $('meta[name="base_url"]').attr('content');
    //console.log(response['data'][0]);

    // if(typeof response['data'] != "undefined"){
    //     for(var k=0; k<response['data'].length; k++){
    //         if(response['data'][k] != ''){
    //             $('#qid_ok_'+(k+1)).removeClass('display-none');
    //             $('#qid_notok_'+(k+1)).addClass('display-none');
    //             console.log($('#qid_ok_'+(k+1)));
    //         }else{
    //             $('#qid_ok_'+(k+1)).addClass('display-none');
    //             $('#qid_notok_'+(k+1)).removeClass('display-none');
    //         }
    //     }
    // }

    if(response['status'] == "success"){
        
        $.growl({message: response['msg']},{type: response['status']});
        //$('.privilegeaccessRedirect_background ').addClass('display-none');
        //var qId = response
    }else{
        enablePrivilageAccessButton($('#query_generate_id'));

        $.growl({message: response['msg']},{type: "warning"});
        $('.privilegeaccessRedirect_background ').addClass('display-none');
    }
       //privilegeaccessRedirect();
    setTimeout(function(){
       privilegeaccessRedirect();
    }, 1000);
    return false;
}

function checkDedupeStatus(formId)
{
    var ddObject = [];
    ddObject.data = {};
    ddObject.url =  '/npc/checkdedupestatusall'; 
    ddObject.data['formId'] = formId; 
    ddObject.data['functionName'] = 'checkDedupeStatusCallBack';

    crudAjaxCall(ddObject);
    return false;
}

function checkDedupeStatusCallBackFunction(response,object)
{
    //var baseUrl = $('meta[name="base_url"]').attr('content');
    if(response['status'] == "success"){
        $.growl({message: response['msg']},{type: response['status']});
    }else{
        enablePrivilageAccessButton($('#check_dedupe_status'));
        
        $.growl({message: response['msg']},{type: "warning"});
        $('.privilegeaccessRedirect_background ').addClass('display-none');
    }
    // setTimeout(function(){
    //     window.location = baseUrl+'/bank/privilegeaccess';
    // }, 1000);
     setTimeout(function(){
       privilegeaccessRedirect();
    }, 1000);
    return false;
}

function markFormForQC(formId) {
    var markObject = [];
    markObject.data = {};
    markObject.url =  '/npc/markFormForQC'; 
    markObject.data['formId'] = formId; 
    markObject.data['functionName'] = 'markFormForQCCallBack';

    crudAjaxCall(markObject);
    return false;
}

function markFormForQCCallBackFunction(response,object)
{
    //var baseUrl = $('meta[name="base_url"]').attr('content');
    if(response['status'] == "success"){
        $.growl({message: response['msg']},{type: response['status']});
    }else{
        enablePrivilageAccessButton($('#check_dedupe_status'));
        
        $.growl({message: response['msg']},{type: "warning"});
        $('.privilegeaccessRedirect_background ').addClass('display-none');
    }
    // setTimeout(function(){
    //     window.location = baseUrl+'/bank/privilegeaccess';
    // }, 1000);
     setTimeout(function(){
       privilegeaccessRedirect();
    }, 1000);
    return false;
}

var enableWorkAreaL1Customer = function(){
   $('.br_submit_loader').addClass('display-none-npc-loader');
   $('#NpcModal').unbind("keydown");
   $('#NpcModal').css('pointer-events', 'auto');
   $('.modal-dialog').unbind("keydown");
}

var disableWorkAreaL1Customer = function(){
    $('#processing_message').text('Please wait while we are processing your request...').css('color','black');
    $('#NpcModal').css('pointer-events', 'none');
    $('#NpcModal').keydown(function(event) {
        return false;
    });
    $('.modal-dialog').css('opacity', '0.8');
    $('.modal-dialog').css('pointer-events', 'none');
    $('.modal-backdrop.show').css('opacity', '0.9');
    setTimeout(function(){
        $('#processing_message').text('Taking longer time than usual, please check with admin team!').css('color','red');
        enableWorkAreaL1Customer();
    }, 180000);
}

var enableWorkAreaL2Customer = function(redirURL){
	$('#NpcModal2').on( "click", "keypress", "keydown", function() {
		window.location = redirURL;
	});
    $('#NpcModal2').unbind("keydown");
    $('#NpcModal2').css('pointer-events', 'auto');
}

var disableWorkAreal2Customer = function(){
    $('.modal-backdrop.show').css('opacity', '0.9')
    $('#NpcModal2').css('pointer-events', 'none');
    $('#NpcModal2').keydown(function(event) {
        return false;
    });
    $('.modal-dialog').css('opacity', '0.8');
    $('.modal-dialog').css('pointer-events', 'none');
    
    if((typeof(_delightSavings) != 'undefined') && (_delightSavings)){
      $('.progress_text1').html('Updating <br>Customer ID');
    }else{
      $('.progress_text1').html('Creating <br>Customer ID');

    }
	$('.progress_text2').html('Funding');
	$('.progress_text3').html('Account ID');
	$('.progress_text4').html('FTR');

    setTimeout(function(){
        $('#processing_message_l2').text('L2 Taking longer time than usual, please check with admin team!').css('color','red');
        enableWorkAreaL2Customer();
    }, 9000000000);
}

function customerIDSuccess(custID)
{
    $('#circle_success1').removeClass('display-none');
    $('#check1').removeClass('display-none');
    $('#npc_stop_loader1').removeClass('l2_progress_loader__element1');
    $('#npc_stop_loader1').addClass('l2_success_loader');
    $('#npc_stop_loader2').addClass('l2_progress_loader__element1');
    $('.progress_text1').html('Customer ID<br><br>'+custID);
$('.progress_text2').html('Checking for<br>clear funds..');
}

function fundindClearedSuccess()
{
    $('#circle_success2').removeClass('display-none');
    $('#check2').removeClass('display-none');
    $('#npc_stop_loader2').removeClass('l2_progress_loader__element1');
    $('#npc_stop_loader2').addClass('l2_success_loader');
    $('#npc_stop_loader3').addClass('l2_progress_loader__element1');
    $('.progress_text2').html('Funding Clear');
    // if((_delightSavings) && (typeof(_delightSavings) != 'undefined')){
    if((typeof(_delightSavings) != 'undefined') && (_delightSavings)){

        $('.progress_text3').html('Updating <br>Account ID');
    }else{
        $('.progress_text3').html('Creating <br>Account ID');
    }
}

function creatingAccountNumberSuccess(AcctNo)
{
    $('#circle_success3').removeClass('display-none');
    $('#check3').removeClass('display-none');
    $('#npc_stop_loader3').removeClass('l2_progress_loader__element1');
    $('#npc_stop_loader3').addClass('l2_success_loader');
    $('#npc_stop_loader4').addClass('l2_progress_loader__element1');
    // 16 MARCH 2022 UI issue Solve 
    if(typeof(AcctNo["ACCOUNT_TYPE"]) != "undefined"){
        if(AcctNo["ACCOUNT_TYPE"] == "2"){

        $('.progress_text3').html('Savings : '+AcctNo["SA"]+'<br>Current : '+AcctNo["CA"]);
  

        }else if(AcctNo["ACCOUNT_TYPE"] == "1"){

        $('.progress_text3').html('Account No.<br><br>'+AcctNo["SA"]);

        }else if(AcctNo["ACCOUNT_TYPE"] == "4"){
            $('.progress_text3').html('Savings : <br><br>'+AcctNo["SA"]+'<br>Treding : '+AcctNo["TD"]);    
        }else{
            $('.progress_text3').html('Account No.<br><br>'+AcctNo);
        }
    }else{

        $('.progress_text3').html('Account No.<br><br>'+AcctNo);
    }
    $('.progress_text4').html('Initiating<br>Fund Transfer..');
}

function fundtransferSuccess(redirURL)
{
    $('#circle_success4').removeClass('display-none');
    $('#check4').removeClass('display-none');
    $('#npc_stop_loader4').removeClass('l2_progress_loader__element1');
    $('#npc_stop_loader4').addClass('l2_success_loader');
    $('.progress_text4').html('FTR Successful');
    $('#npc_stop_loader5').addClass('l2_success_loader');
    $('.progress_text5').html('<a id="redirToDashLink" href="'+redirURL+'">Click To Continue</a>');
    $('#redirToDashLink').css('pointer-events', 'auto');
}

function privilegeaccessRedirect()
{   
    // $("#account_details").load(location.href + " #account_details > *");
    setTimeout(function(){
      $('#account_details_search').trigger('click');
    }, 1000); 
}

function showHideSections(customerType){
    if (customerType == 'ETB') {
        setTimeout(function(){
            $('#photograph-tab').show();
        },2000);
    }
}


function updateNpcTatDashboard(){
    for(let element=0;element<$('.aof_update_dt').length ;element++)
        {
            var curreentelement=$('.aof_update_dt')[element];
            var innerdate=$('.aof_update_dt')[element].innerHTML;
            // var getminutes=datetime.getMinutes();
            //var dates=moment(innerdate, 'DD-MM-YYYY HH:mm')
            var minDiff = moment().diff(moment(innerdate, 'DD-MM-YYYY HH:mm'), 'minutes')
            //check and test 17 feb 2022 updated by 29-06-2022
            if(minDiff <= 30)
            {             
             $(curreentelement).parent().parent().find('.tat_indicator').addClass("date_green");    

            }else if(minDiff >= 30 && minDiff <= 60 ){
             $(curreentelement).parent().parent().find('.tat_indicator').removeClass("date_white");             
                
              $(curreentelement).parent().parent().find('.tat_indicator').addClass("date_orange");

            }else{
              $(curreentelement).parent().parent().find('.tat_indicator').addClass("date_red");
            }
        }

}

function updateOaoDetailsCallBackFunction(response,object)
{
    var baseUrl = $('meta[name="base_url"]').attr('content');
    if(response['status'] == "success"){
        $.growl({message: response['msg']},{type: response['status']});
    }else{
        $.growl({message: response['msg']},{type: "warning"});
    }
    
    setTimeout(function(){
        window.location.reload();
    },1000);
    return false;
}

function fundReceivedCallBackFunction(response,object)
{
    var baseUrl = $('meta[name="base_url"]').attr('content');
    if(response['status'] == "success"){
        $.growl({message: response['msg']},{type: response['status']});
    }else{
        $.growl({message: response['msg']},{type: "warning"});
    }

    setTimeout(function(){
        window.location = baseUrl+'/npc/oaodashboard';
    },1000);
    return false;
}

function vkycDoneCallBackFunction(response,object)
{
    var baseUrl = $('meta[name="base_url"]').attr('content');
    if(response['status'] == "success"){
        $.growl({message: response['msg']},{type: response['status']});
    }else{
        $.growl({message: response['msg']},{type: "warning"});
    }

    setTimeout(function(){
        window.location = baseUrl+'/npc/oaodashboard';
    },1000);
    return false;
}

