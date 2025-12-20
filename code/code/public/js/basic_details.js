_stop_double_click = false;
$(document).ready(function(){
    var addAccountObj = $('#addAccountForm').validate({     
        // ignore: ":hidden",    // initialize the plugin
        rules: {
            account_type: {
                required: true,
            },
            pf_type: {
                required: true,
            },
            pancard_no: {
                required: function(element){
                    return (($('input[name=pf_type-'+element.id.split('-')[1]+']:checked').val() == "pancard")
                        && ($("#applicantId-"+element.id.split('-')[1]).attr('customertype') != "etb"))
                },
                pan: true,
            },
            mobile_number: {
                required: function(element){
                    return $("#applicantId-"+element.id.split('-')[1]).attr('customertype') != "etb";
                },
                mobile: true,
                required :true,

            },
            email:{
                required: function(element){
                    return ($("#email-"+element.id.split('-')[1]).val() == 0);
                },
                normalizer: function( value ) {
                return $.trim( value );
              },
            },
            marital_status:{
                required: function(element){
                    return $("#applicantId-"+element.id.split('-')[1]).attr('customertype') != "etb";
                },
                
            },
            dob: {
                required: true
            },
            empno: {
                required: true
            }, 
             scheme_code: {
                required: true
            },
            td_scheme_code: {
                required: true
            },
            panImage: {
                required: function(element){
                    return (($("#pf_type_card-"+element.id.split('-')[1]).find("img").length == 0)
                        && ($("#applicantId-"+element.id.split('-')[1]).attr('customertype') != "etb"));
                },
            },
            pan_osv_check: {
                required: true
            },
            // label_code: {
            //     required: function(element){
            //         return $("#scheme_code").val() == 9;
            //     },
            // },
            elite_account_number: {
                required: function(element){
                    return ($("#elite_account_number-"+element.id.split('-')[1]).val() == 0);
                },
            },
            label_code: {
                required: function(element){
                    return ($("#label_code-"+element.id.split('-')[1]).val() == 0);
                },
        },
           
        },
        messages: {
            account_type: {
                required: "Select Account Type"
            },            
            pf_type: {
                required: "Select PF type"
            },
            pancard_no: {
                required: "Please Enter PAN Card No",
                pan:"Please Enter Correct Format"
            },
            mobile_number: {
                required: "Please Enter Mobile Number",
                mobile: "Please Enter Correct Mobile Number"
            },
            marital_status: {
                required: "Please Select Marital Status"
            },
            dob: {
                required: "Please Enter DOB"
            },
            email: {
                  required: "Please Enter E-mail ID"
            },
            empno: {
                required: "Please Enter Employee Number"
            },
            scheme_code: {
                required: "Please Select Scheme"
            },
            td_scheme_code: {
                required: "Please Select Scheme"
            },
            panImage: {
                required: "Please Upload Image"
            },
            pan_osv_check: {
                required: "Please check field"
            },
            label_code: {
                required: "Please Enter Label Code"
            },
            elite_account_number: {
                required: "Please Enter DCB Elite Account Number"
            }
        },
        errorPlacement: function( error, element ) {
            if($(element).attr('type') == 'text'){
                error.insertAfter( element );
            }else if($(element).attr('type') == 'radio'){
                error.insertAfter( element.parent().parent() );
            }else if($(element).attr('type') == 'hidden'){
                error.insertAfter( element );
            }else{
                error.insertAfter( element.parent() );
            }
        },
    });
    jQuery.validator.addMethod("pan", function(value, element)
    {
        return this.optional(element) || /^[A-Z]{5}\d{4}[A-Z]{1}$/.test(value);
    }, "Please enter a valid PAN");

        jQuery.validator.addMethod("email", function(value, element)
    {
         return this.optional(element) ||   /^([a-zA-Z0-9\+_\-]+)(\.[a-zA-Z0-9\+_\-]+)*@([a-zA-Z0-9\-]+\.)+[a-zA-Z]{2,6}$/.test(value);
    }, "Please enter a valid Email"); 

        jQuery.validator.addMethod("mobile", function(value, element)
    {
        return this.optional(element) || /^^[6-9]\d{9}$/.test(value);
    }, "Please enter a valid Mobile Number");

    

    $("body").on("change",'#account_type',function(){
		
		// Clear localStorage tab.html() data from previous UIs, if any
		localStorage.removeItem('tab1'); localStorage.removeItem('tab2'); 
		localStorage.removeItem('tab3'); localStorage.removeItem('tab4');
		
        $("#customer_account_type-1 option[value='3']").prop('disabled',false);
        $("#customer_account_type-1").select2();
        if($(this).val() == 4)
        {
            $("#td_scheme_code_div").removeClass('display-none');
        }else{
            $("#td_scheme_code_div").addClass('display-none');
        }

        if(($(this).val() == '5'))
        {
            // $($('.existing_cust')[0]).html('&nbsp');
            $('#etb_button-1').css('visibility','hidden');

        }

        if($(this).val() =='2'){
            ($("#plus-btn").attr('disabled','disabled'));
            ($("#minus-btn").attr('disabled','disabled'));
            $("#currentAccountProInd").removeClass('display-none');
            $("#current_prop_indi").removeClass('display-none');

        }else{
            $("#currentAccountProInd").addClass('display-none');
            $("#current_prop_indi").addClass('display-none');

        }

        var const_ =  $("#constitution").val();
        var tdschemeObject = [];
        tdschemeObject.data = {};
        tdschemeObject.url =  '/bank/getschemecodebyaccounttype';
        tdschemeObject.data['account_type'] = $(this).val();
        tdschemeObject.data['constitution'] = const_;
        tdschemeObject.data['functionName'] = 'SchemeCodebyAccountTypeCallBackFunction';
        
        crudAjaxCall(tdschemeObject);
        return false;
        });



	_basic_form_check = [];
	form_check_basic_var();

        $("body").on("click","#plus-btn",function(){
        if($("#account_type").val() == 5 && $("#qty_input").val() == 2){
            $.growl({message: "Only Two Applicants Allowerd for this account type"},{type: "warning"});
            ($("#plus-btn").attr('disabled','disabled'))
        }

        form_check_basic_var();
        if($("#qty_input").val() >= 2){
            $("#mode_of_operation option[value='1']").wrap('<span class="mode_of_operation" style="display: none;" />');
        }else{
            $("#mode_of_operation option[value='1']").unwrap();
        }
       
            addApplicant($("#qty_input").val());

		setTimeout(function(){
			registerDOBevents();
		},2000);
        return false;
    });   


    $("body").on("click","#minus-btn",function(){

        if($("#account_type").val() == 5 && $("#qty_input").val() <= 2){
        $("#plus-btn").removeAttr('disabled');
        }

        $(".tabList li")[0].click();

        var accountHolders =  $("#qty_input").val();
         _basic_form_check.splice(accountHolders);
                    
        if($("#qty_input").val() >= 2){
            $("#mode_of_operation option[value='1']").wrap('<span class="mode_of_operation" style="display: none;" />');
        }else{
            $("#mode_of_operation option[value='1']").unwrap();
        }        

		var applicantId = parseInt($("#qty_input").val()) + parseInt(1);
		$(".tabList li:last-child").remove();
		$("#tab"+applicantId).remove();
		$("#applicant"+applicantId).remove();
		$('#tab1').addClass('active').click();
		$("#nextapplicant-"+$("#qty_input").val()).addClass('saveAccountDetails').html("Save and Continue");      
        
        setTimeout(function(){
            registerDOBevents();
        },2000);
        if(huf_no_scheme_req.indexOf(_globalSchemeDetails.scheme_code) != -1) $("#currentAccountProInd").addClass("display-none");
        return false;
    }); 





    $("body").on("change","#scheme_code",function(){
        var accountHolders =  $("#qty_input").val();

        if(($("#scheme_code").val() == null) || ($("#scheme_code").val() == '') ){
            $("#PAN_F60_Tabs").addClass('pan60active');
            $("#pan60active_blur").addClass('pan60active_blur');
            $('#account_type').prop('disabled',true);
            disableAddingApplicant();
            //  $('#minus-btn').removeClass('minusplus_button_disable');
            // $('#plus-btn').removeClass('minusplus_button_disable');

            return false;
        }


        


        if(($("#scheme_code").val() != null) && ($("#scheme_code").val() != '')){
            $('#account_type').prop('disabled',true);
            enableAddingApplicant();
            // $('#minus-btn').addClass('minusplus_button_disable');
            // $('#plus-btn').addClass('minusplus_button_disable');
            
        }

        if($("#scheme_code").val() != '3') {
            $('[id^=empno]').val('');
        }

        // If select schemecode SB106 To select by default one applicant  
        if ($("#scheme_code").val() == '3' && $('#account_type').val()=='5') {  
            disableAddingApplicant();
        }

		var selectedSchemeCode = $(this).find("option:selected").text();
		if(selectedSchemeCode == "") return;
		
        var schemeCodeObject = [];
        schemeCodeObject.data = {};
        schemeCodeObject.url =  '/bank/getschemedata';
        schemeCodeObject.data['id'] = $(this).val();
        schemeCodeObject.data['formId'] = $('#formId').val();
        if($('#account_type').val() == 4)
        {
            schemeCodeObject.data['account_type'] = 1;
        }else{
            schemeCodeObject.data['account_type'] = $('#account_type').val();
        }
        schemeCodeObject.data['scheme_code'] = $(this).find("option:selected").text();
        schemeCodeObject.data['functionName'] = 'SchemeDataCallBack';

        crudAjaxCall(schemeCodeObject);
        if($("#tabs-nav li").length==1){
            huf_form_e_d();
        }else if($("#constitution").val()=="NON_IND_HUF"){
            $(".form60").hide();
        }
        return false;
    });
    
        
    $("body").on("change","#td_scheme_code",function(){		
		// Return if there is no actual scheme code selected
        if(($("#td_scheme_code").val() == null) || ($("#td_scheme_code").val() == '') ){
            $("#PAN_F60_Tabs").addClass('pan60active');
            $("#pan60active_blur").addClass('pan60active_blur');
            disableAddingApplicant();
            return false;
        }

        if(($("#td_scheme_code").val() != null) && ($("#td_scheme_code").val() != '') ){
            enableAddingApplicant();
        }

        
		var selectedSchemeCode = $(this).find("option:selected").text();
		if(selectedSchemeCode == "") return;  
		
        // TD SCHEME CODE 3
        var schemeCodeObject = [];
        schemeCodeObject.data = {};
        schemeCodeObject.url =  '/bank/getschemedata';
        schemeCodeObject.data['id'] = $(this).val();
        schemeCodeObject.data['formId'] = $('#formId').val();
        schemeCodeObject.data['account_type'] = $('#account_type').val();  
        schemeCodeObject.data['scheme_code'] = $(this).find("option:selected").text();
        schemeCodeObject.data['functionName'] = 'SchemeDataCallBack';
 
        crudAjaxCall(schemeCodeObject);
        return false;
    });

    $("body").on("click",".pf_type",function(){
        var id = $(this).attr('id').split('-')[1];
        
        if($('input[id=pf_type-'+id+']:checked').val() == "form60"){		
			// Check if PAN Mandatory
			var currPFselection = $('input[id=pf_type-'+id+']:checked').val();
			if(!CheckRule_SchemePAN(_globalTDSchemeDetails, currPFselection)){
				$.growl({message: "PAN card mandatory for the selected scheme code"},{type: "warning"});
				return false;
			}else{

			}
			
            $("#pancard_no-"+id).val('').trigger('change');
            $("#pancardnoDiv_"+id).addClass('display-none');
            $("#pf_type_proof-"+id).find('label').html('Upload Form60');
            $("#pf_type_proof-"+id).find('button').html('<i class="fa fa-plus-circle"></i>Add Form60');
        }else{
            $("#pancardnoDiv_"+id).removeClass('display-none');
            $("#pf_type_proof-"+id).find('label').html('Upload PAN');
            $("#pf_type_proof-"+id).find('button').html('<i class="fa fa-plus-circle"></i>Add PAN');
        }

        $(".AddPanDetailsField").each(function(){
            var currId = $(this).attr('id').split('-')[1];
            if (currId == id) {
                if($(this).attr("name") == "marital_status")
                {
                    $(this).val('').trigger('change');
                }

                if($(this).attr("type") == "text")
                {
                    $(this).val('');
                }            
            }
        })

        $("#document_preview_pf_type_card-"+id).cropper("destroy");
        if($("#pf_type_card-"+id).find("img").length != 0){
            $("#pf_type_card-"+id).find("img").attr("src",'');
            $("#pf_type_card-"+id+"_div").remove();
            $("#pf_type_card-"+id).find('#pf_type_div').addClass('display-none');
            $("#pf_type_card-"+id).find('.add-document-btn').removeClass('display-none');
        }
    });

    $("body").on("change","select[name='customer_account_type']",function(){
        if($(this).val() == 3)
        {
            $("#empnoDiv-"+$(this).attr("id").split("-")[1]).removeClass('display-none');
        }else{
            $("#empnoDiv-"+$(this).attr("id").split("-")[1]).addClass('display-none');            
        }
        return false;
    });

  //   $("body").on("click","#checkCustomer",function(){

		// if(($("#customer_id").val() != '') && ($("#customer_id").val().length != 9)){
		// 	$.growl({message: "Customer ID should be 9 digits"},{type: "warning"});
  //           return false;
		// }
		// if(($("#etb_pancard_no").val() != '') && ($("#etb_pancard_no").val().length != 10)){
		// 	$.growl({message: "Invalid PAN"},{type: "warning"});
  //           return false;
		// }						
		
  //       if(($("#customer_id").val() == '') && ($("#etb_pancard_no").val() == '')){
  //           $.growl({message: "Please Enter Customer ID/PAN No."},{type: "warning"});
  //           return false;
  //       }else{
		// 	$.growl({message: "Searching based on Customer ID.."},{type: "warning",delay:4000});			
		// }

  //       $('.br_submit_loader').removeClass('display-none-existing-customer-loader');
  //       var accountObject = [];
  //       accountObject.data = {};
  //       accountObject.url =  '/bank/saveetbaccount';
  //       accountObject.data['applicantId'] = $(this).attr('applicantId');
  //       accountObject.data['customer_id'] = $("#customer_id").val();
  //       accountObject.data['pancard_no'] = $("#etb_pancard_no").val();
  //       accountObject.data['account_type'] = $("#account_type").val();
  //       accountObject.data['scheme_code'] = $("#scheme_code").val();
  //       accountObject.data['account_level_type'] = $("#account_level_type").val();
  //       accountObject.data['no_of_account_holders'] = $("#qty_input").val();
  //       accountObject.data['functionName'] = 'SaveETBAccountCallBack';
		// accountObject.data['etb'] = 'Y'; 
        
  //       disableWorkAreaEtbCustomer();
  //       crudAjaxCall(accountObject);
  //       return false;
  //   });


  $("body").on("click",".saveetbcc",function(){
        
        var id = $(this).attr('id');
        var row_id = id.replace('saveetbcc-','');
        var account_no = $('#actno-'+row_id).text();
        var balance = $('#bal-'+row_id).text();
        var globalcustomerDetails = $('#custdetails-'+row_id).text()
        var mode_of_operation = $('#modeofoperation-'+row_id).text()

       
       
        var accountObject = [];
        accountObject.data = {};
        accountObject.url =  '/bank/saveetbcc';
        accountObject.data['applicantId'] = '0';
        accountObject.data['customer_id'] = $("#customer_id").val();
        accountObject.data['account_no'] = account_no;
        accountObject.data['balance'] = balance;
        accountObject.data['cust_details'] = globalcustomerDetails;
        accountObject.data['mode_of_operation'] = mode_of_operation;
        accountObject.data['account_type'] = $("#account_type").val();
        accountObject.data['scheme_code'] = $("#scheme_code").val();
        accountObject.data['account_level_type'] = $("#account_level_type").val();
        accountObject.data['no_of_account_holders'] = $("#qty_input").val();
        accountObject.data['functionName'] = 'SaveETBccCallBack';
        accountObject.data['etb'] = 'Y'; 
        
        disableWorkAreaEtbCustomer();
        crudAjaxCall(accountObject);
        return false;
    });





    $("body").on("click","#checkCustomer",function(){

        var accountObject = [];
        accountObject.data = {};
        accountObject.url =  '/bank/checkEtbCustomerType';
        accountObject.data['applicantId'] = $(this).attr('applicantId');
        accountObject.data['scheme_code'] = $("#scheme_code").val();
        accountObject.data['customer_id'] = $("#customer_id").val();
        accountObject.data['pancard_no'] = $("#etb_pancard_no").val();
        accountObject.data['account_type'] = $("#account_type").val();
        accountObject.data['account_level_type'] = $("#account_level_type").val();
        accountObject.data['no_of_account_holders'] = $("#qty_input").val();
        accountObject.data['functionName'] = 'SaveETBAccountCallBack';
        accountObject.data['etb'] = 'Y'; 
        accountObject.data['constitution'] = $("#constitution").val() ?? "";
        
        disableWorkAreaEtbCustomer();
        custAmendRequestPendingPopUp($("#customer_id").val());
        crudAjaxCall(accountObject);
        if($("#tabs-nav li").length==1){
            huf_form_e_d();
        }
        return false;
    });

    $("body").on("click",".customer_modal",function(){
		
		var tabName = 'tab'+$(this).attr("data-id");
		if(localStorage.getItem(tabName)==null){
			localStorage.setItem('tab'+$(this).attr("data-id"),$('#tab'+$(this).attr("data-id")).html());
		}	
		
        $("#checkCustomer").attr('applicantId',$(this).attr("data-id"));        
		
		if(global_growl != '') enableWorkAreaEtbCustomer();		// Ensure previous modal is clean
		
		$($(this).data('target')).modal('show'); 		//$($(this).data('target')).modal('toggle');
        return false;
    });
	
    if($("#formId").val() != ''){
        disableSchemCodeChange();
        disableAddingApplicant();
    }
	
	$("body").on("click",".nextapplicant",function(){  
        if (!addAccountObj.form()) { // Not Valid
            return false;
        }else{
 
        }
        disableAddingApplicant();
        disableSchemCodeChange();
        // ETB_NTB_validation($('#qty_input').val());

        if (isPanExists == 1){
            $.growl({message:"Customer (PAN Number) exists in Finacle"},{type: "warning"});
            return false;
        }

        if($('#account_type').val() == '5')
        {
            // $('.existing_cust').html('&nbsp');
            // $('#etb_button-1').css('display','none');
            $('#etb_button-1').css('visibility','hidden');

            
        }



        if($('#qty_input').val() > _globalTDSchemeDetails.joint_applicant_related){
                 
          $.growl({message: "Number of Applicant(s) more than permissible limit ("+_globalTDSchemeDetails.joint_applicant_related+")"},{type: "warning",delay:2000,allow_dismiss:false});
            return false;

        }

        var curr = $(this);
            var idSequence = 1;
            if(curr[0].id != null){
                idSequence = curr[0].id.split('-')[1];
            }    

        var dob_date_string = moment($('#dob-'+idSequence).val(), "DD-MM-YYYY").format("YYYY-MM-DD");    
        if(!$(this).hasClass("is_huf")){
            if(!checkschemeForAgeok(idSequence,dob_date_string)){
                return false;
            }  
        } 
       
        _basic_form_check[idSequence-1]['basic_account-'+idSequence]= true;   

        for(var c=1; c<=$('#qty_input').val(); c++){
            if($('#nextapplicant-'+c).is(':visible')){
                idSequence = c+1;
                setTimeout(function(){   
                    if($('#dob-'+idSequence).val() != ''){                  
                     $('#dob-'+idSequence).trigger('change');
                    }
               },2000);
            }
        }
         

        $('a[data-id="'+$(this).attr('id')+'"]').removeClass('text-muted-lnavs').unbind("click", false);
        $('a[data-id="'+$(this).attr('id')+'"]').click();
        _globalPanOkToContinue = true;
        return false;
    });
		
    $("body").on("click",".saveAccountDetails",function(){
        
        if(_stop_double_click == true){
            return false;
        }else{
            _stop_double_click = true;
            $('.saveAccountDetails').prop('disabled',true);
            setTimeout(function(){
                _stop_double_click = false;
                $('.saveAccountDetails').prop('disabled',false);
            },8000);
        }

        if (!addAccountObj.form()) { // Not Valid
            return false;
        } else {

        }

        // ETB_NTB_validation($('#qty_input').val());
         
        var curr = $(this);
		var idSequence = 1;
		if(curr[0].id != null){
			idSequence = curr[0].id.split('-')[1];
		}                           

         var pf_type = $('input[name=pf_type-'+this.id.split('-')[1]+']:checked').val();
         var accountType = $('#account_type').val();
       
         //18_03_2024 pan logic is remove
        // if(_is_review != 1 && accountType != 5){
        //     if ((!_globalPanOkToContinue) && (pf_type == 'pancard') && $("#pancard_no-"+idSequence).val() != '') {
		// 		$.growl({message: "PAN Number(s) not validated. Please recheck."},{type: "warning",delay:2000,allow_dismiss:false});
		// 		var elem = "panIsValid_"+idSequence;
		// 		document.getElementById(elem).focus();				
		// 		document.getElementById(elem).style.transitionDuration  = "3s";
		// 		randomRotate = Math.floor(Math.random() * 300)+ 45;
		// 		document.getElementById(elem).style.transform = "rotate("+randomRotate+"deg)";
        //         return false;
        //     }
        // }
		
        $('.process-step').css('pointer-events', '')

        var accountHolders =  $("#qty_input").val();
        //check if previous screens are ok
        for(var chk=1; chk<=(accountHolders-1); chk++){
            if (accountHolders == 1) { //not require to check for single applicant
                break;
            }
            if(!form_check_basic(chk)){
             return false;
            }

        }

        for(var applicantId=1; applicantId<=(accountHolders); applicantId++){
            if(!checkImagesAreOk('basic_details', applicantId)){
                return false;
            };
        }

        var is_etb = $("#applicantId-"+1).attr('customertype');
        var accountType = $("#account_type").val();
        if((accountType == 4 || accountType == 5) && (is_etb == "etb" )){
            $.growl({message: "selected account type restricted for ETB"},{type: "warning"});
            return false;
        }

    
        var dob_date_string = moment($('#dob-'+idSequence).val(), "DD-MM-YYYY").format("YYYY-MM-DD");
        
        if(!(_nonInd=="NON_IND_HUF" && idSequence=="2")){
        if(!checkschemeForAgeok(idSequence,dob_date_string)){
            return false;
        }
        }
        var date_string = moment($('#dob-1').val(), "DD-MM-YYYY").format("YYYY-MM-DD");  
        selectedDate = new Date(date_string);
        today = new Date();
        accountHolderAge= new Date(today - selectedDate)/(24 * 60 * 60 * 1000 * 365.25 );
        accountHolderAgess = [];
        for(var c=1; c<=accountHolders; c++){
             idSequence = c;
             var date_string = moment($('#dob-'+idSequence).val(), "DD-MM-YYYY").format("YYYY-MM-DD");  
             selectedDate = new Date(date_string);
             today = new Date();
              accountHolderAge= new Date(today - selectedDate)/(24 * 60 * 60 * 1000 * 365.25 );       
             //alert(accountHolderAge);
             accountHolderAgess.push(accountHolderAge);

        }

        var panNumbers = [];
        for (var i = 1; i <= accountHolders; i++) {
            idSequence = i;
            if ($('#pf_type-' + idSequence).is(":checked")) {
                var panNumber = $("#pancard_no-"+idSequence).val()
                panNumbers.push(panNumber)
            }
        }

        if (isPanDuplicate(panNumbers)) {
            $.growl({message:"Validation Failed: Same PAN card used across applicants"},{type: "warning"});
            return false;
        }

        if(_globalSchemeDetails.scheme_code == 'SB151' && idSequence != 2){
            $.growl({message:"Scheme validation failed. only two applicants allow."},{type: "warning"});
            return false;
        }

        if (_globalSchemeDetails.scheme_code != "SB110" && _globalSchemeDetails.scheme_code !='SB151' && _nonInd != "NON_IND_HUF") {
        
            if(_globalSchemeDetails.account_type == 1 && _globalSchemeDetails.allow_14yrs == 'Y'){
                var i = accountHolderAgess.length-1;
                if(Math.floor(accountHolderAgess[i]) < 14){
                    $.growl({message:"Minor applicant(s) not permitted"},{type: "warning"});
                    return false;
                }else{

                }
            }else{
                var i = accountHolderAgess.length-1;
                if(Math.floor(accountHolderAgess[i]) < 18){
                  $.growl({message:"Minor applicant(s) not permitted"},{type: "warning"});
                    return false;
            }else{
            
        }
           }
        }

        if(_nonInd == "NON_IND_HUF"){
            if(accountHolderAgess[0] < 18){
                $.growl({message:"Minor karta/maneger not permitted"},{type: "warning"});
                return false;
            }
        }
            // if(accountHolderAgess.every(checkMinor)) {
            //       $.growl({message:"Minor applicant(s) not permitted"},{type: "warning"});
            //         return false;
            // }else{
            // }       
        // }

        
		var pf_type = $('input[name=pf_type-'+this.id.split('-')[1]+']:checked').val();	
        var accountType= $("#account_type").val();													 
        //if(pan_check == "Strict" && accountType != 5)
        if(pan_check == "Strict")
        {
            // if (pf_type == "pancard" && panIsvalid == 0){
              //  $.growl({message:"NSDL: Invalid PAN Number"},{type: "warning"});
            //     return false;
            // } 
			
            if (isPanExists == 1){
                $.growl({message:"Customer (PAN Number) exists in Finacle"},{type: "warning"});
                return false;
            }
        }
				
		//Check Age Rules
		// console.log("Age Ruled in Save");
        /*var applicant = $(this).attr("id").split('-')[1];
		if(applicant == "1" && !CheckAgeRule('dob-'+applicant)){
			$.growl({message: "Scheme validation failed. Please check Age criteria"},{type: "warning"});
			return false;
		}*/
		
		
		// Scheme Code 4 => SSB Junior Super Saver
        // Discussion with Mani: allowing < 14 as solo applicant for SSB would fail at custID creation as guardian details would be required!
		/*
        if(($('#account_type').val() == 1 && $("#scheme_code").val()==4) && !checkForSuperSaver()){ 
			$.growl({message: "Scheme validation failed. Please check criteria"},{type: "warning"});
			return false;
		}*/
        
		// Check for Unique PAN number, except for SSB Junior Saver
		/*if($('#account_type').val() == 1 && $("#scheme_code").val()==4){
			// Ignore Savings SSB
		}else{
			if(!checkUniquePAN()){
				$.growl({message: "Scheme validation failed. Please check criteria"},{type: "warning"});
				return false;
			}
		}*/

        /*if($("#qty_input").val() > 1 && !checkUniqueIDvalues()){
            $.growl({message: "Scheme validation failed. Please check criteria"},{type: "warning"});
            return false;           
        }*/
        
        

        var name = '';
        var no = '';
        var panDetailsObject = [];
        panDetailsObject.data = {};
        panDetailsObject.data['AccountDetails'] = {};
        panDetailsObject.data['PanDetails'] = {};
        panDetailsObject.data['huf_details'] = {};
        panDetailsObject['url'] = '/bank/saveaccountdetails';

        if(accountType == 2){
            panDetailsObject.data['AccountDetails']['flow_tag_1'] = $('#current_prop_indi option:selected').val();
        }
        
        $(".AddAccountDetailsField").each(function() {
            if($(this).val() != ''){
                panDetailsObject.data['AccountDetails'][$(this).attr('name')] = $(this).val();
            }
        });
		
        
		// Deligth Savings ~ Savings
        var delightKitId = $('#delight-kit-id').text();

		if(panDetailsObject.data['AccountDetails']['account_type'] == '5' || delightKitId != ''){ 
			panDetailsObject.data['AccountDetails']['account_type'] = '1';
			panDetailsObject.data['AccountDetails']['delight_scheme'] = '5';
		}else{
			panDetailsObject.data['AccountDetails']['delight_scheme'] = '';  
		}

       
        $(".AccountForm").each(function(){
            var accountId = $(this).attr("id");
            panDetailsObject.data['PanDetails'][accountId] = {};
            if($("#applicantId-"+accountId).val() != ''){
                panDetailsObject.data['PanDetails'][accountId]['is_update'] = true;
                panDetailsObject.data['PanDetails'][accountId]['applicantId'] = $("#applicantId-"+accountId).val();
            }
            $(".AddPanDetailsField").each(function() {
                if(accountId == $(this).attr('id').split('-')[1]){
                    name = $(this).attr('id').split('-')[0];
                    if($(this).attr('type')=='radio')
                    {
                        panDetailsObject.data['PanDetails'][accountId][name] =
                        $('input[id='+$(this).attr('id')+']:checked').val();
                    }else{
                        if(name == "pancard_no"){
                            panDetailsObject.data['PanDetails'][accountId][name] = rsenc($("#"+$(this).attr("id")).inputmask().val(), $('meta[name="cookie"]').attr('content').split('.')[2]);
                        }else if(name == "mobile_number"){
                            panDetailsObject.data['PanDetails'][accountId][name] = rsenc($("#"+$(this).attr("id")).inputmask().val(), $('meta[name="cookie"]').attr('content').split('.')[2]);
                        }else if(name == "dob"){
                            panDetailsObject.data['PanDetails'][accountId][name] = rsenc($(this).val(), $('meta[name="cookie"]').attr('content').split('.')[2]);
                        }else if(name == "email"){
                            panDetailsObject.data['PanDetails'][accountId][name] = rsenc($(this).val(), $('meta[name="cookie"]').attr('content').split('.')[2]);
                        }else{
                            panDetailsObject.data['PanDetails'][accountId][name] = $(this).val();
                        }
                    }
                }
            });
            $(".uploaded_image").each(function(){
                if($(this).attr("src") != ''){
                    if(accountId == $(this).attr('name').split('-')[1]){
                        var image = $(this).attr("src").split('/');
                        name = $(this).attr('name').split('-')[0];
                        panDetailsObject.data['PanDetails'][accountId][name] = image[image.length-1];
                    }
                }
            });
          
           
            
        });

        $(".AddHufDetailsField").each(function(){
                name = $(this).attr('id').split('-')[0];
                panDetailsObject.data['AccountDetails'][name] = $(this).val();
        
        });
        
        if($("#formId").val() != ''){
            panDetailsObject.data['formId'] = $("#formId").val();
        }

       
        panDetailsObject.data['functionName'] = 'SaveAccountDetailsCallBack';


        disableSaveAndContinue(this);

        crudAjaxCall(panDetailsObject);
        return false;
    });

    //$("body").on("focusout","input[id^='pancard_no']",function(){
		
	// Define Pan trigger for 1st applicant	

    $("body").on("keyup",".pan",function(){
       var applicantId = $(this).attr('id').split('-')[1];
	   definePANcheckTrigger(applicantId);
    });
	
	$('.panIsValid').on("click",function(){
		if(typeof $(this).attr('id') == 'undefined') return;
		var applicantId = $(this).attr('id').split('_')[1];
		if(applicantId == '') return;	
		var customerType = $("#applicantId-"+applicantId).attr('customertype');
        var accountType= $("#account_type").val();
		// if(customerType == 'etb') {
        //     panIsValid(applicantId, 'ETB');
        // }else {
        //     panIsValid(applicantId, 'NTB');
        // }
	});


}); // End of $(document).ready()


function definePANcheckTrigger(applicantId){ 
        var panstr = $("#pancard_no-"+applicantId).val().trim();
        if (panstr.length < 10) {
            return false;
        }
        
        if($("#pancard_no-"+applicantId).val() == '')
        {
            $.growl({message: "Please Enter PAN No"},{type: "warning"});
            return false;
        }
        var accountType= $("#account_type").val();
     
        //18_03_2024_pan logic remove
            // panIsValid(applicantId);
       
         
		// If ETB do not check if it exists in Finacle!
		var customerType = $("#applicantId-"+applicantId).attr('customertype');
       
        // if(customerType !="etb" && accountType != 5){
        if(customerType !="etb"){
			panExists(applicantId);
		}
}

function addApplicant(no_of_applicants)
{
    var addApplicantObject = [];
    addApplicantObject.data = {};
    addApplicantObject.url =  '/bank/addapplicant';
    addApplicantObject.data['no_of_applicants'] = no_of_applicants;
    addApplicantObject.data['functionName'] = 'AddApplicantCallBack';

    crudAjaxCall(addApplicantObject);
    return false;
}

function savePanDetails(panDetails)
{
    var panObject = [];
    panObject.data = {};
    panObject.url =  '/bank/savepandetails';
    panObject.data['panDetails'] = panDetails;
    panObject.data.panDetails.panNo = rsenc(panObject.data.panDetails.panNo, $('meta[name="cookie"]').attr('content').split('.')[2]);
    panObject.data['functionName'] = 'savePanDetailsCallBack';

    crudAjaxCall(panObject);
    return false;
}

function isMinor(dob){
	var yearsDiff = moment().diff(dob, 'years');
	if(yearsDiff < 18){
       
        return true;
    }else{

		return false;
    }
}


function panIsValid(applicantId,etb_ntb = 'NTB')
{
    var panValidObject = [];
    panValidObject.data = {};
    panValidObject.url =  '/bank/panisvalid';
    panValidObject.data['applicantId'] = applicantId;
    panValidObject.data['etb_ntb'] = etb_ntb;
    panValidObject.data['dob'] = $('#dob-'+applicantId).val();
    panValidObject.data['customer_full_name'] = $('#customer_full_name-'+applicantId).val();
    panValidObject.data['pancard_no'] = rsenc($("#pancard_no-"+applicantId).val(), $('meta[name="cookie"]').attr('content').split('.')[2]);
    panValidObject.data['scheme_code'] = $("#scheme_code option:selected").text();
    panValidObject.data['account_type'] = $("#account_type").val();
    panValidObject.data['functionName'] = 'PanIsValidCallBack';

    crudAjaxCall(panValidObject);
    return false;
}

function panExists(applicantId)
{
    var panExistsObject = [];
    panExistsObject.data = {};
    panExistsObject.url =  '/bank/panexists';
    panExistsObject.data['applicantId'] = applicantId;
    panExistsObject.data['pancard_no'] = rsenc($("#pancard_no-"+applicantId).val(), $('meta[name="cookie"]').attr('content').split('.')[2]);
    panExistsObject.data['functionName'] = 'panExistsCallBack';

    crudAjaxCall(panExistsObject);
    return false;
}

function panExistsCallBackFunction(response,object)
{
    isPanExists = 0;
    if((response['status'] == "success") || (response['status'] == "warning")){
        if(response.data != '')
        {
            isPanExists = 1;
        }
         $.growl({message: response['msg']},{type: response['status'],allow_dismiss:false});
    }else{
        $.growl({message: response['msg']},{type: "warning"});
    }        
    return false;
}

var enableWorkAreaEtbCustomer = function(){ 
    $('#customer_modal').css('pointer-events', 'auto');
    $('#customer_modal').unbind("keydown"); 
    $('.modal-dialog').css('opacity', '1');
    $('.modal-dialog').css('pointer-events', 'auto');
    $('.modal-dialog').unbind("keydown");
    $('.modal-backdrop.show').css('opacity', '0') ;
	$('.br_submit_loader').addClass('display-none-existing-customer-loader');
    $('#customer_id').val('');
    $('.close').click(); 
	if(global_growl != '') global_growl.close();
}

var disableWorkAreaEtbCustomer = function(){ 
    global_growl = $.growl({message:'Please wait while we process your request...<i class="fa fa-2x fa-spinner fa-spin"></i>'},{type: "warning",delay:89500,allow_dismiss:false});
    $('#customer_modal').css('pointer-events', 'none');
    $('#customer_modal').keydown(function(event) { 
        return false;
    });
    // $('.modal-dialog').css('opacity', '0.8');
    // $('.modal-dialog').css('pointer-events', 'none');
    $('.modal-backdrop.show').css('opacity', '0.8'); 
    setTimeout(function(){
        $('.br_submit_loader').addClass('display-none-existing-customer-loader');
        enableWorkAreaEtbCustomer();
        if(customerType=="etb"){
            return false;
        }		
        $.growl({message:'Taking longer time than usual, please check with admin team!'},{type: "warning"});
    }, 90000); 
    
}

function getSchemeDetails(accountType)
{
    var schemeCodeObject = [];
    schemeCodeObject.data = {};
    schemeCodeObject.url =  '/bank/getschemedata';
    if($('#scheme_code').val() != '')
    {
        schemeCodeObject.data['id'] = $('#scheme_code').val();
        schemeCodeObject.data['scheme_code'] = $('#scheme_code').find("option:selected").text();
    }
    if($('#td_scheme_code').val() != '')
    {
        schemeCodeObject.data['id'] = $('#td_scheme_code').val();
        schemeCodeObject.data['td_scheme_code'] = $('#td_scheme_code').find("option:selected").text();
    }
    schemeCodeObject.data['account_type'] = accountType;
    schemeCodeObject.data['functionName'] = 'SchemeDetailsCallBack';

    crudAjaxCall(schemeCodeObject);
    return false;
}

function SchemeDetailsCallBackFunction(response,object)
{
    var accounttype = object.data['account_type'];
    if(response.data['accountType'] == 'SA'){
        _globalSchemeDetails = response.data['schemeData']; 
        if(accounttype != 4) _globalTDSchemeDetails = '';
    }else{
        _globalTDSchemeDetails = response.data['schemeData']; 
        if(accounttype != 4) _globalSchemeDetails = '';
    }

    // If TD schemecode is for staff Only one applicant permissible
    if(_globalTDSchemeDetails.staff_customer != null){
        if(accounttype == 3 && _globalTDSchemeDetails.staff_customer.toLowerCase() =='staff' && $('#qty_input').val()>1 ){              
            $.growl({message: "Validation Failed! For TD Staff schemes only one applicant permissible. Please retry!"},{type: "warning"});
            $('#qty_input').val(1); 
            setTimeout(function(){location.reload(true);},1500);
        }
    }

    // If Savings schemecode is for staff Only one applicant permissible
    if(_globalSchemeDetails.staff_customer != null){
        if(accounttype == 1 && _globalSchemeDetails.staff_customer.toLowerCase() =='staff' && $('#qty_input').val()>1 ){                
            $.growl({message: "Validation Failed! For Staff schemes only one applicant permissible. Please retry!"},{type: "warning"});
            $('#qty_input').val(1); 
            setTimeout(function(){location.reload(true);},1500);
        }
    }
    return false;
}


function setUIForBasicDetailsAlreadyFilled(){
    if(_accountType == 3){
        var TD_AGE;
            if (_globalTDSchemeDetails.age == null) {
               TD_AGE = 'Any age permissible';
            }else if (_globalTDSchemeDetails.age == 'Senior') {
               TD_AGE = 'Senior';
            }
                 
            var description = _globalTDSchemeDetails.scheme_code + '\n' + '- VALIDATION DAYS : ' + _globalTDSchemeDetails.validation_days 
                            + '\n' +'- MIN : ' + _globalTDSchemeDetails.min + '\n' + '- MAX : ' + _globalTDSchemeDetails.max 
                            + '\n' + '- PAYOUT : ' + _globalTDSchemeDetails.payout + '\n' + '- MIN AGE : ' + _globalTDSchemeDetails.min_age
                            + '\n' + '- MAX AGE : ' + _globalTDSchemeDetails.max_age 
                            + '\n' + '- CALLABLE : ' + _globalTDSchemeDetails.callable_noncallable;
        $("#scheme_code_description").attr('aria-label',description);
        }else{
            if(_globalSchemeDetails.scheme_code != "undefined")
            {                
                var description = _globalSchemeDetails.scheme_code + '\n' + '- AQB : ' + _globalSchemeDetails.aqb 
                                + '\n' + '- WITHDRAW LIMIT : ' + _globalSchemeDetails.withdraw_limit + '\n' + '- SPENDING LIMIT : ' + _globalSchemeDetails.spending_limit
                                + '\n' + '- FREE LEAVES : ' + _globalSchemeDetails.free_leaves
                                + '\n' + '- MINIMUM SWEEPS AMOUNT : ' + _globalSchemeDetails.sweeps_parameter;
                $("#scheme_code_description").attr('aria-label',description);
            }else{
                $("#scheme_code_description").attr('aria-label',"Please select scheme code");
            }
        }

        if(_globalSchemeDetails.staff_customer.toLowerCase() =='staff'){
                    $('.form60').addClass('display-none');
                    $(".customer_account_type").val('3').prop('disabled',true).trigger('change');
                    $('.empno').removeClass('display-none');
                    addSelect2('customer_account_type','customer account type',true);

                }else{
                    $('.form60').removeClass('display-none');   
                    $('.empno').addClass('display-none');
                    addSelect2('customer_account_type','customer account type',false);
                }
       
        if(_globalSchemeDetails.staff_customer == "Non Staff"){

            $("#customer_account_type-1 option[value='3']").prop('disabled',true);
            $("#customer_account_type-1").select2();
        }else{
            $("#customer_account_type-1 option[value='3']").prop('disabled',false);
            $("#customer_account_type-1").select2();
            // $("#customer_account_type-1 option[value='3']").unwrap();
        }

        if(_globalSchemeDetails.scheme_code == "SB118")
        {
            $(".labelCodeDiv").removeClass('display-none');
        }else{
            $(".labelCodeDiv").addClass('display-none');                                       
        }

        if(_globalSchemeDetails.scheme_code == "SB124" || _globalSchemeDetails.scheme_code == "CA224")
            {
                $(".eliteAccountNumberDiv").removeClass('display-none');
            }else{
                $(".eliteAccountNumberDiv").addClass('display-none');                    
            }

        if(_accountType == '2'){
            $("#currentAccountProInd").removeClass('display-none');
            $("#current_prop_indi").removeClass('display-none');

            if(_globalSchemeDetails.staff_customer == "Non Staff"){

                $("#customer_account_type-1 option[value='3']").prop('disabled',true);
                $("#customer_account_type-1").select2();
        }else{
                $("#customer_account_type-1 option[value='3']").prop('disabled',false);
                $("#customer_account_type-1").select2();
            }

        }else{
            $("#currentAccountProInd").addClass('display-none');
            $("#current_prop_indi").addClass('display-none');

        }
        if(huf_no_scheme_req.indexOf(_globalSchemeDetails.scheme_code) != -1) $("#currentAccountProInd").addClass("display-none");
        
}

function checkMinor(Age){
    return Math.floor(Age) < 18;
}

function createEtbClearButton(id){  
    var $input = $('<button type="button" class="btn bg-transparent btn-outline-grey waves-effect clearETB" data-id="'+id+'" style="margin-left:10px; color:#58dbc1;">Clear</button>');
    $input.prependTo($('.customer_modal[data-id="'+id+'"]').parent());
	
	$('.clearETB').on("click",function(){
		var id = $(this).attr("data-id");
		if(typeof id == 'undefined' || id == '') return;
		var tabName = 'tab'+id;
		if(localStorage.getItem(tabName)!=null){
				$('#'+tabName).html(localStorage.getItem(tabName));
		}
		$('.clearETB[data-id="'+id+'"]').remove();
	});
}

function disableAddingApplicant(){
    $('#minus-btn').addClass('minusplus_button_disable');
    $('#plus-btn').addClass('minusplus_button_disable');    
    document.getElementById('account_type').disabled = true;
    document.getElementById('account_type').parentNode.style.pointerEvents = 'none';
    
}

function enableAddingApplicant(){
    $('#minus-btn').removeClass('minusplus_button_disable');
    $('#plus-btn').removeClass('minusplus_button_disable');     
}

function disableSchemCodeChange(){
    $('#scheme_code').prop('disabled',true);
    document.getElementById('scheme_code').disabled = true;
    document.getElementById('scheme_code').parentNode.style.pointerEvents = 'none';
}

// function CheckEmptySchemeCode(){
        

//         if($("#scheme_code").val() == ""){
//               $("#PAN_F60_Tabs").addClass('pan60active');
//               $.growl({message: "Please select scheme code"},{type: "warning"});
//               return false;
//             }
//             else{
//                 $("#PAN_F60_Tabs").removeClass('pan60active');
                         
//             }
// }
function ETB_NTB_validation(applicants) {
    if($("#applicantId-1").val() == ''){
        for (var i = 2; i <= applicants; i++) {
            $("#etb_button-"+i).attr('disabled', 'disabled');
            if ($("#applicantId-"+i).val() != '') {
                $.growl({message: "Please select applicant-"+i+" as NTB only"},{type: "warning"});
                return false;
            }
        }
    }else if($("#applicantId-1").val() != ''){
        for (var i = 2; i <= applicants; i++) {
            $("#etb_button-"+i).removeAttr("disabled");
            if ($("#applicantId-"+i).val() == '') {
                $.growl({message: "Please select applicant-"+i+" as ETB only"},{type: "warning"});
                return false;
            }
        }

    }

}


function blankBasicDetails(accountHolders) {
    for (var applicantId = 1; applicantId <= accountHolders; applicantId++) {
        $('#pancard_no-'+applicantId).val('');
        $('#dob-'+applicantId).val('');
        $('#customer_full_name-'+applicantId).val('');
        $('#mobile_number-'+applicantId).val('');
        $('#email-'+applicantId).val('');

    }
}

function huf_form_e_d() {
    let account_type = $("#account_type").val() ?? "0";
    let input = $("#constitution");
    // if(huf_no_scheme_req.indexOf(_globalSchemeDetails.scheme_code) != -1) return;
    
    if (input.val() == "NON_IND_HUF") {
        let i = 2; 
        $("#qty_input").val("2");
        while ($('#applicant' + i).length == 1) {  
            $('#applicant' + i).remove();  
            $('#tab' + i).remove();  
            i++;  
        } 
        $('#plus-btn').removeClass('minusplus_button_disable');
        $("#plus-btn").prop('disabled', false);
        $("#minus-btn").prop('disabled', false);
        $(".npof-ach").hide();
        $("#plus-btn").click();
        $("#plus-btn").prop('disabled', true);
        $("#minus-btn").prop('disabled', true);
        $("#hub_reletionship").show();
        $("#primary_karta_mag_text").text("Karta/Manager");
        $('#account_level_type').closest('.details-custcol-row').hide();
        $('.form60').addClass('display-none');

    } else {
      
        $("#plus-btn").prop('disabled', false);
        $("#minus-btn").prop('disabled', false);
        $("#hub_reletionship").hide();
        $("#primary_karta_mag_text").text("Primary Account Holder");
        $('#account_level_type').closest('.details-custcol-row').show();  
        $('.form60').removeClass('display-none');
    }
    $("#huf_reletionship-1").remove();
        
}


function check_pan_in_huf() {
    
    let allInput = $("input.pan");
    let a_types = [1, 2, 3];
    let account_type = $("#account_type").val() ?? "0";
    account_type *= 1;
    if (a_types.indexOf(account_type) > -1) {
        let input = $("#constitution");
        allInput.each(function (e) {
            let value = $(this).val();
            if (input.val() == "NON_IND_HUF" && $(this).hasClass("pan_huf")) {
                let v = value[3] ?? "";
                if (v.toLowerCase() != "h" && value.length > 3) {
                    $.growl({ message: "Invalid HUF Pan!" }, { type: "warning" });
                    $(this).val(value.slice(0, 3));
                    $(this).inputmask("aaaa-a-9999-a", {
                        "placeholder": "XXXX-X-9999-X",
                        autoUnmask: true,
                        onpaste:false,
                    });
                }
            }
            else if (input.val() == "NON_IND_HUF" && $(this).hasClass("pan")) {
                let pv = value[3] ?? "";
                if (pv.toLowerCase() != "p" && value.length > 3) {
                    $.growl({ message: "Invalid Pan!" }, { type: "warning" });
                    $(this).val(value.slice(0, 3));
                    $(this).inputmask("aaaa-a-9999-a", {
                        "placeholder": "XXXX-X-9999-X",
                        autoUnmask: true,
                        onpaste: false,
                    });
                }
            }
        })
    }
}
$(document).on("input change focus keyup", ".pan", function () {
    check_pan_in_huf()
})
$(document).ready( function(){
    let isExecuted = false;
    if ($("#tabs-nav li").length == 1 && !isExecuted) {
        huf_form_e_d(); 
        isExecuted = true;
    }
});

$("body").on("change", "#huf_signatory_relation-2", function() {
    if ($(this).val() == "Manager") {
        $.growl({ message: "If selecting Manager as Signatory Relationship then Karta/Manager will be permissible only for Female!" }, { type: "warning" });
    }
});
// end huf