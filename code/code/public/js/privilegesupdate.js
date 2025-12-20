$(document).ready(function(){

  $("body").on("click","#account_details_update_search",function(){
        var accountObject = [];
        accountObject.data = {};
        accountObject.url =  '/bank/privilegeupdateaccountdetails';
        accountObject.data['aof_tracking_no'] = $("#aof_tracking_no").val();
        if($("#customerName").val() != "")
        {
            accountObject.data['customerName'] = $("#customerName").val();
            accountObject.data['aof_tracking_no'] = $("#customerName").val();
        }
        accountObject.data['functionName'] = 'PrivilegeUpdateAccountDetailsCallBack';

        crudAjaxCall(accountObject);
        return false;
    });

    $("body").on("click","#query_generate_id",function(){
        $('.privilegeaccessRedirect_background ').removeClass('display-none');
        //var accountId = $(this).attr('accountId');
        var formId = $(this).attr('formId');
        disablePrivilageAccessButton(this);
        generateQueryId(formId);
    });


    

  $("body").on("click","#check_dedupe_status",function(){
        var validation = true;
        var formId = $(this).attr('formId');
        var accountObject = [];
        accountObject.data = {};
        accountObject.data['DdupeDetails'] = {};
        accountObject.data['dedupe_comment'] = {};
        accountObject.url =  '/bank/updatededupestatus';
        accountObject.data['type'] = 'privilegeupdate';
        accountObject.data['formId'] = formId;
        $(".update_dedupestatus").each(function() {
            if($(this).val() != ''){
                accountObject.data['DdupeDetails'][$(this).attr('name')] = $(this).val();
            }else{
                $.growl({message:"Please enter Dedupe Status"},{type: "warning"});
                validation = false;
            }
        });

        $(".dedupe_comment").each(function() {
            if($(this).val() != ''){
                accountObject.data['dedupe_comment'][$(this).attr('name')] = $(this).val();
            }else{
                $.growl({message:"Please enter Dedupe Comment"},{type: "warning"});
                validation = false;

            }
        });
        if (!validation) {
            return false;
        }
        accountObject.data['functionName'] = 'UpdateDedupeStatusCallBack';

        crudAjaxCall(accountObject);
        return false;
    });


  $("body").on("click","#update_customer_id",function(){
        var validation = true;
        var formId = $(this).attr('formId');
        var accountObject = [];
        accountObject.data = {};
        accountObject.data['CustomerId'] = {};
        accountObject.data['customer_id_comment'] = {};
        accountObject.url =  '/bank/updatecustomerid';
        accountObject.data['formId'] = formId;
        accountObject.data['type'] = 'privilegeupdate';

        $(".customer_id").each(function() {
            var applicantId = $(this).attr('id').split('-')[1];
            if($(this).val() != ''){
                accountObject.data['CustomerId'][$(this).attr('name')] = $(this).val();
            }else{
                $.growl({message:"Please enter Customer ID for applicant-"+applicantId},{type: "warning"});
                validation = false;
                
            }
        });
        $(".customer_id_comment").each(function(){
            var applicantId = $(this).attr('id').split('-')[1];
            if($(this).val() != ''){
                accountObject.data['customer_id_comment'][$(this).attr('name')] = $(this).val();
            }else{
                $.growl({message:"Please enter Customer ID Comment for applicant-"+applicantId},{type: "warning"});
                validation = false;
            }
        });
        if (!validation) {
            return false;
        }
        accountObject.data['functionName'] = 'UpdateCustomerIdCallBack';

        crudAjaxCall(accountObject);
        return false;
    });

  $("body").on("click","#kyc_update",function(){

        var datavalue = $(this).attr('data-value');

        if(datavalue == 'api'){
            var commentId = 'kyc_update_comment_api';
            var flagId  = 'kyc_update_api';
            var method = 'api';
        }else{
            var commentId = 'kyc_update_comment_manual';
            var flagId  = 'kyc_update_manual';
            var method = 'manual';
        }



        var validation = true;
        var formId = $(this).attr('formId');
        var accountObject = [];
        accountObject.data = {};
        accountObject.data['KycUpdate'] = {};
        accountObject.data['kyc_update_comment'] = {};
        accountObject.url =  '/bank/updatekyc';
        accountObject.data['formId'] = formId;
        accountObject.data['method'] = method;
        accountObject.data['type'] = 'privilegeupdate';

        $("."+flagId).each(function() {
            var applicantId = $(this).attr('id').split('-')[1];
            if($(this).val() != ''){
                accountObject.data['KycUpdate'][$(this).attr('name')] = $(this).val();
            }else{
                $.growl({message:"Please enter Kyc Update for applicant-"+applicantId},{type: "warning"});
                validation = false;
                
            }
        });
        $("."+commentId).each(function(){
            var applicantId = $(this).attr('id').split('-')[1];
            if($(this).val() != ''){
                accountObject.data['kyc_update_comment'][$(this).attr('name')] = $(this).val();
            }else{
                $.growl({message:"Please enter Kyc Update Comment for applicant-"+applicantId},{type: "warning"});
                validation = false;
            }
        });
        if (!validation) {
            return false;
        }
        accountObject.data['functionName'] = 'UpdateKycCallBack';

        crudAjaxCall(accountObject);
        return false;
    });


    $("body").on("click","#internet_bank",function(){

        var datavalue = $(this).attr('data-value');

        if(datavalue == 'api'){
            var commentId = 'internet_bank_comment_api';
            var flagId  = 'internet_bank_api';
            var method = 'api';
        }



        var validation = true;
        var formId = $(this).attr('formId');
        var accountObject = [];
        accountObject.data = {};
        accountObject.data['internet_bank_value'] = {};
        accountObject.data['internet_bank_comment'] = {};
        accountObject.url =  '/bank/updateinternetbank';
        accountObject.data['formId'] = formId;
        accountObject.data['method'] = method;
        accountObject.data['type'] = 'privilegeupdate';

        $("."+flagId).each(function() {
            var applicantId = $(this).attr('id').split('-')[1];
            if($(this).val() != ''){
                accountObject.data['internet_bank_value'][$(this).attr('name')] = $(this).val();
            }else{
                $.growl({message:"Please enter Internet Bank Update for applicant-"+applicantId},{type: "warning"});
                validation = false;
                
            }
        });
        $("."+commentId).each(function(){
            var applicantId = $(this).attr('id').split('-')[1];
            if($(this).val() != ''){
                accountObject.data['internet_bank_comment'][$(this).attr('name')] = $(this).val();
            }else{
                $.growl({message:"Please enter Internet Bank Update Comment for applicant-"+applicantId},{type: "warning"});
                validation = false;
            }
        });
        if (!validation) {
            return false;
        }
        accountObject.data['functionName'] = 'UpdateInternetBankCallBack';

        crudAjaxCall(accountObject);
        return false;
    });


  $("body").on("click","#update_funding_status",function(){

        if($('#update_funding').val() == ""){
            $.growl({message:"Please enter Funding Status"},{type: "warning"});
            return false;
       }

       if($('#funding_comment').val() == ""){
            $.growl({message:"Please enter Funding Comment"},{type: "warning"});
            return false;
       }

        var formId = $(this).attr('formId');
        var accountObject = [];
        accountObject.data = {};
        accountObject.url =  '/bank/updatefundinstatus';
        accountObject.data['formId'] = formId;
        accountObject.data['update_funding'] = $("#update_funding").val();
        accountObject.data['type'] = 'privilegeupdate';
        accountObject.data['funding_comment'] =$('#funding_comment').val();
        accountObject.data['functionName'] = 'UpdateFundinStatusCallBack';

        crudAjaxCall(accountObject);
        return false;
    });

  $("body").on("click","#update_account_no",function(){

       if($('#sa_update_accountno').val() == ""){
            $.growl({message:"Please enter Savings Account number"},{type: "warning"});
            return false;
       } 
        

       if($('#td_update_accountno').val() == ""){
            $.growl({message:"Please enter TD Acoount number"},{type: "warning"});
            return false;
       }



       if($('#account_id_comment').val() == ""){
            $.growl({message:"Please enter Account ID Comment"},{type: "warning"});
            return false;
       }



        var formId = $(this).attr('formId');
        var accounttype = $(this).attr('accounttype');

        var SAaccountNo = $("#sa_update_accountno").val();

        var TDaccountNo = $("#td_update_accountno").val();

        var accountObject = [];
        accountObject.data = {};
        accountObject.url =  '/bank/updateaccountno';
        accountObject.data['formId'] = formId;
        accountObject.data['accounttype'] = accounttype;
        accountObject.data['sa_account_no'] = SAaccountNo;
        accountObject.data['td_account_no'] = TDaccountNo;
        accountObject.data['type'] = 'privilegeupdate';
        accountObject.data['account_id_comment'] = $('#account_id_comment').val();
        accountObject.data['functionName'] = 'UpdateAccountNoCallBack';

        crudAjaxCall(accountObject);
        return false;
    });

    $("body").on("click","#update_ftr_fund_transfer",function(){
        
        if($('#ftr_fundtransfer').val() == ""){
            $.growl({message:"Please enter FTR Status"},{type: "warning"});
            return false;
       }

       if($('#ftr_status_comment').val() == ""){
            $.growl({message:"Please enter Acoount ID Comment"},{type: "warning"});
            return false;
       }

        var formId = $(this).attr('formId');
        var accounttype = $(this).attr('accounttype');

        var accountObject = [];
        accountObject.data = {};
        accountObject.url =  '/bank/updateftrfundtransfer';
        accountObject.data['formId'] = formId;
        accountObject.data['ftr_fundtransfer'] = $("#ftr_fundtransfer").val();
        accountObject.data['type'] = 'privilegeupdate';
        accountObject.data['ftr_status_comment'] = $('#ftr_status_comment').val();
        accountObject.data['functionName'] = 'UpdateFundTransferCallBack';

        crudAjaxCall(accountObject);
        return false;
    });

     $("body").on("click","#update_signature_flag",function(){
        
        var datavalue = $(this).attr('data-value');

        if(datavalue == 'manual'){
            var commentId = 'signature_flag_comment_manual';
            var flagId  = 'signature_flag_manual';
            var method = 'manual';
        }else{
            var commentId = 'signature_flag_comment';
            var method = 'api';
            var flagId  = 'signature_flag';
        }

        if($('#'+flagId).val() == ""){
            $.growl({message:"Please enter Signature Status"},{type: "warning"});
            return false;
       }

       if($('#'+commentId).val() == ""){
            $.growl({message:"Please enter Signature Flag Comment"},{type: "warning"});
            return false;
       }

        var formId = $(this).attr('formId');

        var accountObject = [];
        accountObject.data = {};
        accountObject.url =  '/bank/updatesignaturestatus';
        accountObject.data['formId'] = formId;
        accountObject.data['signature_flag'] = $('#'+flagId).val();
        accountObject.data['type'] = 'privilegeupdate';
        accountObject.data['method'] = method;
        accountObject.data['signature_flag_comment'] = $('#'+commentId).val();
        accountObject.data['functionName'] = 'UpdateSignatureFlagCallBack';

        crudAjaxCall(accountObject);
        return false;
    });

    $("body").on("click","#update_card_flag",function(){
        
        if($('#card_flag').val() == ""){
            $.growl({message:"Please enter Card Status"},{type: "warning"});
            return false;
       }

       if($('#card_flag_comment').val() == ""){
            $.growl({message:"Please enter Card Flag Comment"},{type: "warning"});
            return false;
       }

        var formId = $(this).attr('formId');

        var accountObject = [];
        accountObject.data = {};
        accountObject.url =  '/bank/updatecardflagstatus';
        accountObject.data['formId'] = formId;
        accountObject.data['card_flag'] = $("#card_flag").val();
        accountObject.data['type'] = 'privilegeupdate';
        accountObject.data['card_flag_comment'] = $('#card_flag_comment').val();
        accountObject.data['functionName'] = 'UpdateCardFlagCallBack';

        crudAjaxCall(accountObject);
        return false;
    });


    $("body").on("click","#update_next_role",function(){

        if($("#updatenextrole option:selected").val() == ""){
            $.growl({message:"Please Select Next Role"},{type: "warning"});
            return false;
       }

       if($('#next_role_comment').val() == ""){
            $.growl({message:"Please enter Next Role Comment"},{type: "warning"});
            return false;
       }

        var formId = $(this).attr('formId');
        
        var accountObject = [];
        accountObject.data = {};
        accountObject.url =  '/bank/updatenextrole';
        accountObject.data['formId'] = formId;
        accountObject.data['next_role'] = $("#updatenextrole option:selected").val()
        accountObject.data['type'] = 'privilegeupdate';
        accountObject.data['next_role_comment'] = $('#next_role_comment').val();
        accountObject.data['functionName'] = 'UpdateNextRoleCallBack';

        crudAjaxCall(accountObject);
        return false;
    });

    $("body").on("click","#abort_form",function(){

        if($("#update_abort_form").val() == ""){
            $.growl({message:"Please enter form status"},{type: "warning"});
            return false;
       }

       if($('#form_abort_comment').val() == ""){
            $.growl({message:"Please enter form Abort comment"},{type: "warning"});
            return false;
       }

        var formId = $(this).attr('formId');
        
        var accountObject = [];
        accountObject.data = {};
        accountObject.url =  '/bank/abortform';
        accountObject.data['formId'] = formId;
        accountObject.data['abort'] = $("#update_abort_form").val()
        accountObject.data['type'] = 'privilegeupdate';
        accountObject.data['form_abort_comment'] = $('#form_abort_comment').val();
        accountObject.data['functionName'] = 'FormAbortCallBack';

        crudAjaxCall(accountObject);
        return false;
    });
  


	
});


function PrivilegeUpdateAccountDetailsCallBackFunction(response,object)
{   
    $('.privilegeaccessRedirect_background ').addClass('display-none');
    $("#account_details_update").html(response);
	setTimeout(function(){
		setClipboardCode();
	}, 500);

    //dynamic create step button for l3 update 
    var i = 1;
       $('.timeline-badge').each(function(){
        $(this).addClass('step-'+i)
        i++;
       });
  
    return false;
}



function UpdateDedupeStatusCallBackFunction(response,object){
    if(response['status'] == "success"){
        

        $.growl({message: response['msg']},{type: response['status']});
       

            if(object.data['type'] == 'privilegeupdate')
            { 
                setTimeout(function(){
                   privilegeaccessRedirect();
                },1000);
            }
        
    }else{
        
        $.growl({message: response['msg']},{type: "warning"});
        
         if(object.data['type'] == 'privilegeupdate')
            { 
                setTimeout(function(){
                   privilegeaccessRedirect();
                },1000);
            }
    }
    return false;
}


function UpdateCustomerIdCallBackFunction(response,object){
    if(response['status'] == "success"){
        

        $.growl({message: response['msg']},{type: response['status']});
       

            if(object.data['type'] == 'privilegeupdate')
            { 
                setTimeout(function(){
                   privilegeaccessRedirect();
                },1000);
            }
        
    }else{
        
        $.growl({message: response['msg']},{type: "warning"});
        
         if(object.data['type'] == 'privilegeupdate')
            { 
                setTimeout(function(){
                   privilegeaccessRedirect();
                },1000);
            }
    }
    return false;
}


function UpdateKycCallBackFunction(response,object){
    if(response['status'] == "success"){
        

        $.growl({message: response['msg']},{type: response['status']});
       

            if(object.data['type'] == 'privilegeupdate')
            { 
                setTimeout(function(){
                   privilegeaccessRedirect();
                },1000);
            }
        
    }else{
        
        $.growl({message: response['msg']},{type: "warning"});
        
         if(object.data['type'] == 'privilegeupdate')
            { 
                setTimeout(function(){
                   privilegeaccessRedirect();
                },1000);
            }
    }
    return false;
}

function UpdateInternetBankCallBackFunction(response,object){
    if(response['status'] == "success"){
        

        $.growl({message: response['msg']},{type: response['status']});
       

            if(object.data['type'] == 'privilegeupdate')
            { 
                setTimeout(function(){
                   privilegeaccessRedirect();
                },1000);
            }
        
    }else{
        
        $.growl({message: response['msg']},{type: "warning"});
        
         if(object.data['type'] == 'privilegeupdate')
            { 
                setTimeout(function(){
                   privilegeaccessRedirect();
                },1000);
            }
    }
    return false;
}



function UpdateFundinStatusCallBackFunction(response,object){
    if(response['status'] == "success"){
        

        $.growl({message: response['msg']},{type: response['status']});
       

            if(object.data['type'] == 'privilegeupdate')
            { 
                setTimeout(function(){
                   privilegeaccessRedirect();
                },1000);
            }
        
    }else{
        
        $.growl({message: response['msg']},{type: "warning"});
        
         if(object.data['type'] == 'privilegeupdate')
            { 
                setTimeout(function(){
                   privilegeaccessRedirect();
                },1000);
            }
    }
    return false;
}


function UpdateAccountNoCallBackFunction(response,object){
    if(response['status'] == "success"){
        

        $.growl({message: response['msg']},{type: response['status']});
       

            if(object.data['type'] == 'privilegeupdate')
            { 
                setTimeout(function(){
                   privilegeaccessRedirect();
                },1000);
            }
        
    }else{
        
        $.growl({message: response['msg']},{type: "warning"});
        
         if(object.data['type'] == 'privilegeupdate')
            { 
                setTimeout(function(){
                   privilegeaccessRedirect();
                },1000);
            }
    }
    return false;
}


function UpdateFundTransferCallBackFunction(response,object){
    if(response['status'] == "success"){
        

        $.growl({message: response['msg']},{type: response['status']});
       

            if(object.data['type'] == 'privilegeupdate')
            { 
                setTimeout(function(){
                   privilegeaccessRedirect();
                },1000);
            }
        
    }else{
        
        $.growl({message: response['msg']},{type: "warning"});
        
         if(object.data['type'] == 'privilegeupdate')
            { 
                setTimeout(function(){
                   privilegeaccessRedirect();
                },1000);
            }
    }
    return false;
}

function UpdateSignatureFlagCallBackFunction(response,object){
    if(response['status'] == "success"){
        

        $.growl({message: response['msg']},{type: response['status']});
       

            if(object.data['type'] == 'privilegeupdate')
            { 
                setTimeout(function(){
                   privilegeaccessRedirect();
                },1000);
            }
        
    }else{
        
        $.growl({message: response['msg']},{type: "warning"});
        
         if(object.data['type'] == 'privilegeupdate')
            { 
                setTimeout(function(){
                   privilegeaccessRedirect();
                },1000);
            }
    }
    return false;
}

function UpdateCardFlagCallBackFunction(response,object){
    if(response['status'] == "success"){
        

        $.growl({message: response['msg']},{type: response['status']});
       

            if(object.data['type'] == 'privilegeupdate')
            { 
                setTimeout(function(){
                   privilegeaccessRedirect();
                },1000);
            }
        
    }else{
        
        $.growl({message: response['msg']},{type: "warning"});
        
         if(object.data['type'] == 'privilegeupdate')
            { 
                setTimeout(function(){
                   privilegeaccessRedirect();
                },1000);
            }
    }
    return false;
}


function UpdateNextRoleCallBackFunction(response,object){
    if(response['status'] == "success"){
        

        $.growl({message: response['msg']},{type: response['status']});
       

            if(object.data['type'] == 'privilegeupdate')
            { 
                setTimeout(function(){
                   privilegeaccessRedirect();
                },1000);
            }
        
    }else{
        
        $.growl({message: response['msg']},{type: "warning"});
        
         if(object.data['type'] == 'privilegeupdate')
            { 
                setTimeout(function(){
                   privilegeaccessRedirect();
                },1000);
            }
    }
    return false;
}

function FormAbortCallBackFunction(response,object){
    if(response['status'] == "success"){
        $.growl({message: response['msg']},{type: response['status']});
            if(object.data['type'] == 'privilegeupdate')
            { 
                setTimeout(function(){
                   privilegeaccessRedirect();
                },1000);
            }
        
    }else{
        $.growl({message: response['msg']},{type: "warning"});
         if(object.data['type'] == 'privilegeupdate')
            { 
                setTimeout(function(){
                   privilegeaccessRedirect();
                },1000);
            }
    }
    return false;
}

function privilegeaccessRedirect()
{   
    // $("#account_details").load(location.href + " #account_details > *");
    setTimeout(function(){
      $('#account_details_update_search').trigger('click');
    }, 1000); 
}

 $("body").on("click","#reject_button",function(){
    if($("#reject_comment").val() == ""){
      $.growl({message:"Please enter Reject Comment"},{type: "warning"});
      return false;
    }

    var formId = $(this).attr('formId');
        
        var accountObject = [];
        accountObject.data = {};
        accountObject.url =  '/bank/markformreject';
        accountObject.data['formId'] = formId;
        accountObject.data['reject_comment'] = $("#reject_comment").val()
        accountObject.data['type'] = 'privilegeupdate';
        accountObject.data['functionName'] = 'RejectFormCallback';

        crudAjaxCall(accountObject);
        return false;
});

 function RejectFormCallbackFunction(response,object){
    if(response['status'] == "success"){
        $.growl({message: response['msg']},{type: response['status']});
       

            if(object.data['type'] == 'privilegeupdate')
            { 
                setTimeout(function(){
                   privilegeaccessRedirect();
                },1000);
            }
        
    }else{
        
        $.growl({message: response['msg']},{type: "warning"});
        
         if(object.data['type'] == 'privilegeupdate')
            { 
                setTimeout(function(){
                   privilegeaccessRedirect();
                },1000);
            }
    }
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