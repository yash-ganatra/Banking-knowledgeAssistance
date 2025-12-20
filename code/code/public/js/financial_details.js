$(document).ready(function(){
    var addInitialFundingObj = $('#addInitialFundingForm').validate({// initialize the plugin
        rules: {
            cheque_image: {
                required: function(element){
                    return $("#cheque_image").find("img").length == 0;
                },
            },
            initial_funding_type: {
                required: true,
            },
            initial_funding_date: {
                required: true,
            },
            //  funding_source: {
            //     required: true,
            // },
            others_type: {
                required: true,
              },
            // amount: {
            //     required: function(element){
            //         return $(".others_type").val() != "zero";
            //     },
            //     required: true,
            //     normalizer: function( value ) {
            //     return $.trim( value );
            //   },
            // },
            reference:{
                required: true,
                maxlength:22,
                normalizer: function( value ) {
                return $.trim( value );
              },
            },
            bank_name: {
                required: true,
            },
            ifsc_code: {
                required: true,
                ifsccode: true,
                normalizer: function( value ) {
                return $.trim( value );
              },
            },
            account_number: {
                required: true,
                normalizer: function( value ) {
                return $.trim( value );
              },
            },
            account_name: {
                required: true,
                normalizer: function( value ) {
                return $.trim( value );
              },
            },
            relationship: {
                required: function(){
                    return (($("input[name='self_thirdparty']:checked").val() == "thirdparty")
                    && $("#relationship").val() == "");
                },
            },
            maturity: {
                required: true,
            },
            emd_name: {
                required: true,
                normalizer: function( value ) {
                return $.trim( value );
              },
            },
            years: {
                required: true,
                normalizer: function( value ) {
                return $.trim( value );
              },
            },
            months: {
                required: true,
                normalizer: function( value ) {
                return $.trim( value );
              },
            },
            days: {
                required: true,
                normalizer: function( value ) {
                return $.trim( value );
              },
            },
            td_amount: {
                required: true,
                normalizer: function( value ) {
                return $.trim( value );
              },
            },
            tenure_amount: {
                required: true,
                normalizer: function( value ) {
                return $.trim( value );
              },
            },
            tenure_year: {
                required: true,
                normalizer: function( value ) {
                return $.trim( value );
              },
            },
            tenure_month: {
                required: true,
                normalizer: function( value ) {
                return $.trim( value );
              },
            },
            frequency: {
                required: true,
                normalizer: function( value ) {
                return $.trim( value );
              },
            },
        },
        messages: {
            cheque_image: {
                required: "Please Upload Cheque"
            },
            initial_funding_type: {
                required: "Select Initial Funding Type"
            },
            initial_funding_date: {
                required: "Select Initial Funding Date"
            },
            // funding_source: {
            //     required: "Please Enter Funding Source"
            // },
            others_type: {
                required: "Please Selcet Any One Option"
            },
            // amount: {
            //     required: "Please Enter Amount"
            // },
            reference: {
                required: "Please Enter Number",
                maxlength:"Please Enter Valid Number"
            },
            bank_name: {
                required: "Please Select Bank"
            },
            ifsc_code: {
                required: "Enter IFSC Code",
                ifsccode: "Enter Valid IFSC Code"
            },
            account_number: {
                required: "Enter Account Number"
            },
            account_name: {
                required: "Enter Account Name"
            },
            relationship: {
                required: "Select Relationship"
            },
            maturity: {
                required: "Select Maturity Instructions"
            },
            emd_name: {
                required: "Please Enter 3rd Party Name"
            },
            // years: {
            //     required: "Enter Years"
            // },
            // months: {
            //     required: "Enter Months"
            // },
            // days: {
            //     required: "Enter Days"
            // },
            td_amount: {
                required: "Enter Deposit Amount"
            },
            tenure_amount: {
                required: "Enter Deposit Amount"
            },
            tenure_year: {
                required: "Enter Years"
            },
            tenure_month: {
                required: "Enter Months"
            },
            frequency: {
                required: "Please Select Frequency Type"
            },
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
    jQuery.validator.addMethod("ifsccode", function(value, element)
    {
        return this.optional(element) || /^([A-Za-z]{4})+([A-Za-z-0-9]{7})$/.test(value);
    }, "Please enter a valid IFSC Code");



     _availblefundingUi = $('.visibility_check').length;
     _funding_form_check = [];
     form_check_funding_var();

    $("body").on("click",".nexttabtermdeposit",function(){
        if (!addInitialFundingObj.form()) { // Not Valid
            return false;
        }else{

        }

        if(_is_review !=1){

        $("#maturity").val('1').trigger('change');

        }
        _funding_form_check[idSequence-1]['initial_funding']= true;

        // if($('input[name="auto_renew"]:checked').val() == 'Y')
        // {
        //     $("#maturity option[value='3']").wrap('<span class="title" style="display: none;" />');
        //     if($("#maturity option[value='1']").parent().find('span').length == 0)
        //     {
        //         $("#maturity option[value='1']").unwrap();
        //         $("#maturity option[value='2']").unwrap();
        //     }
        //     $("#maturity").val(1).trigger('change');
        // }


        //=========================For Normal ETB TD amount validation for Acocunt number with Balnace
        if ($("input[name='initial_funding_type']:checked").val() == 3  && _role == '2') {
            var availableamoutfordirect = $('#direct_account_number').find(":selected").text();
            if(typeof availableamoutfordirect != 'undefined'){

                availableamoutfordirect =  availableamoutfordirect.replace($('#direct_account_number').val(),'').replace('- (Rs. ','').replace(/[ )]/g,'');
                if(parseInt($('.direct_amount').val()) > availableamoutfordirect ){
                      $.growl({message: "Amount selected is more than avaliable Balance!"},{type: "warning"});
                      return false;
                }

            }else{
                $.growl({message: "Unable to retrive Balance Amount"},{type: "warning"});
                return false;
            }
        }

        //========================================End================================================
        
        var availableamoutfordirect = $('#avaliable_balance').val();
        if(parseInt($('.direct_amount').val()) > availableamoutfordirect ){
                  $.growl({message: "Amount selected is more than avaliable Balance!"},{type: "warning"});
                  return false;
        }





        var interest_payout = $(".interest_payout").val();

        $("#maturity_ifsc_code").val($("#ifsc_code").val()).attr('readonly',true);
        $('#maturity_ifsc_code').prop('disabled',true);  
        $('#maturity_account_number').prop('disabled',true);
        $('#maturity_account_name').prop('disabled',true);      
        
        
        if($("input[name='initial_funding_type']:checked").val() == 3)
        {
            $("#maturity_bank_name").val('29').trigger('change');
            // $("#maturity_account_number").val($("#direct_account_number").find("option:selected").text()).attr('readonly',true);
            if( _ifsc_cc_enable != null  && (_ifsc_cc_enable.etb_cc) == 'CC'){
                var accountNumber = $("#direct_account_number").val().substr(0,3);
                var ifscNo = 'DCBL0000'+accountNumber;
                $("#maturity_ifsc_code").val(ifscNo).attr('readonly',true);
                $('#maturity_ifsc_code').prop('disabled',true);  
            }else{
                var accountNumber = $("#direct_account_number").val().substr(0,3);
                var ifscNo = 'DCBL0000'+accountNumber;
            $("#maturity_ifsc_code").val(ifscNo).attr('readonly',true);
                $('#maturity_ifsc_code').prop('disabled',true);  
            }
            $("#maturity_account_number").val($("#direct_account_number").val()).attr('readonly',true);
            $("#maturity_account_name").val($("#customer_name").val()).attr('readonly',true);
        }else if($("input[name='maturity_flag']:checked").val() == 1 || _maturity_flag == 1 || _maturity_flag == 0){
            $("#maturity_bank_name").val($("#bank_name").val()).trigger('change');
            $("#maturity_account_number").val($("#account_number").val()).attr('readonly',true);
            $("#maturity_account_name").val($("#account_name").val()).attr('readonly',true);
        }
        $("#td_amount").val($("#amount").val());
        $("#tenure_amount").val($("#amount").val());
        $('a[data-id="'+$(this).attr('tab')+'"]').removeClass('text-muted-lnavs').unbind("click", false);
        $('a[data-id="'+$(this).attr('tab')+'"]').click();

        if($('#deposit_type').attr('data-id') != '2'){
        addSelect2('interest_payout','Maturity',true);
        }
        addSelect2('maturity_bank_name','Maturity',true);
        return false;
    });

    $(".firsttab").click(function() {
        $('#saveandcontinue').addClass('display-none');
    });

    $(".secondTab").click(function() {
        $('#saveandcontinue').removeClass('display-none');
    });

    $("body").on("change","#bank_name",function(){
        var bankObject = [];
        bankObject.data = {};
        bankObject.url =  '/bank/getifsccode';
        bankObject.data['id'] = $(this).val();
        bankObject.data['functionName'] = 'BankCodeCallBack';

        crudAjaxCall(bankObject);
        return false;
    });

    $("body").on("change","#maturity_bank_name",function(){
        var bankObject = [];
        bankObject.data = {};
        bankObject.url =  '/bank/getifsccode';
        bankObject.data['id'] = $(this).val();
        bankObject.data['functionName'] = 'MaturityBankCodeCallBack';

        crudAjaxCall(bankObject);
        return false;
    });

    $("body").on("click",".financialinfo",function(){
        if (!addInitialFundingObj.form()) { // Not Valid
            return false;
        }else{

        }
        
        if(!checkImagesAreOk('initial_funding', 1)){
            return false;
        };

        for(var chk=1; chk<=(_availblefundingUi); chk++){
             if ((_accountType == 3) && (_accountType == 4)) {
               
                 if(!form_check_funding(chk)){
                     return false;
                 }
             }
        }

        var initial_funding_type = $('input[name="initial_funding_type"]:checked').val();
               
        //Validation for ip_requirement_normal
        var ipRequirementNormal = _globalSchemeDetails.min_ip_amount
       
        var amountVal = parseInt($('#amount').val());
        if(typeof(_globalSchemeDetails)!="undefined" && _globalSchemeDetails != ""){
            if ((_accountType == 1) || (_accountType == 5) || (_accountType == 2)) {
           
                 if(amountVal < ipRequirementNormal){
                    $.growl({message: "Amount selected is less than IP amount requirement(Min IP amount Rs."+ipRequirementNormal+" )"},{type: "warning"});
                     return false;
                 }            
             
                 }
            }

        // Validations of TD Amount and Tenure!
		if(typeof(_globalSchemeDetails)!="undefined" && _globalSchemeDetails != ""){
			// TD only or Combo
			if(_accountType == 3 || _accountType == 4){
				if(!validateTenure(_globalSchemeDetails['validation_days'],_globalSchemeDetails['min'],_globalSchemeDetails['max'])){
					return false;
				}
                if(_globalSchemeDetails['td_rd'] == 'TD'){
        				if(!validateTDamount($("#td_amount").val(),_globalSchemeDetails['min_amount'],_globalSchemeDetails['max_amount'])){
        					return false;
        				}
                 }else{
                    if(!validateTDamount($("#tenure_amount").val(),_globalSchemeDetails['min_amount'],_globalSchemeDetails['max_amount'])){
                            return false;
                        }
                 }
				if(!validateBulkRetail(_globalSchemeDetails['bulk_retail'])){
					return false;
				}
			}
		}

        // if($('#maturity_ifsc_code').val().length != 11 || $('#maturity_ifsc_code').val().slice(0,4) != 'DCBL'  || !(/^([0-9]{7})$/.test($('#maturity_ifsc_code').val().substr(4)))){
            
        //     $.growl({message: "Enter valid IFSC code"},{type: "warning"});
        //     return false;
        // }

       


		if(typeof _min_ip_reqmt != "undefined"){
			var total_reqmt = 0;
			var td_val = parseInt($("#td_amount").val());
			td_val = isNaN(td_val) ? 0 : td_val;
			if(typeof _min_ip_reqmt['SA'] != "undefined"){
				var sa_req = parseInt(_min_ip_reqmt['SA']);
			}else{
				var sa_req = 0;
			}
			if(typeof _min_ip_reqmt['TD'] != "undefined"){
				var td_req = parseInt(_min_ip_reqmt['TD']);
			}else{
				var td_req = 0;
			}
			td_req = td_req > td_val ? td_req : td_val;

			if(amountVal < (sa_req + td_req)){
				$.growl({message: "Initial funding less than total account requirements!"},{type: "warning"});
				return false;
			}
		}

        if(_ifsc_cc_enable != null && _ifsc_cc_enable != ''){
            if((_ifsc_cc_enable.etb_cc) == 'CC'){
                if($("#maturity_ifsc_code").val().length != 11 || $("#maturity_ifsc_code").val().slice(0,4) != 'DCBL' || !(/^([0-9]{7})$/.test($("#maturity_ifsc_code").val().substr(4)))){
                    $.growl({message: "Enter valid IFSC code"},{type: "warning"});
                    return false;
                }
            }
        }

        var financialinfo = [];
        
        financialinfo.data = {};
        //source of fund
        financialinfo.data['financialDetails'] = {};
        financialinfo.data['financialDetails']['images'] = {};
        financialinfo['url'] = '/bank/savefinancialinfo';
        $(".AddFinancialinfoField").each(function() {
            if($(this).attr("type") == 'checkbox'){
                if ($(this).prop('checked')==true)
                {
                    financialinfo.data['financialDetails'][$(this).attr("name")]= 'zero';
                }else{
                    financialinfo.data['financialDetails'][$(this).attr("name")] = '';
                    if (financialinfo.data['financialDetails']['initial_funding_type'] == 1) {
                       financialinfo.data['financialDetails']['others_type'] = 'CHECK';
                    }else if(financialinfo.data['financialDetails']['initial_funding_type'] == 2){
                       financialinfo.data['financialDetails']['others_type'] = 'NEFT_RTGS';
                    }else{
                       financialinfo.data['financialDetails']['others_type'] = '';
                    }

                }
            }else if($(this).attr('type') == 'radio'){
                financialinfo.data['financialDetails'][$(this).attr('name')] = $('input[name="'+$(this).attr('name')+'"]:checked').val();
            }else if($(this).val() != ''){

                // if(($(this).attr('name') == "account_number") && ($(this).attr('type') != "text"))
                // {
                //     financialinfo.data['financialDetails'][$(this).attr('name')] = $("#direct_account_number").find("option:selected").text();
                // }else{
                // }
                financialinfo.data['financialDetails'][$(this).attr('name')] = $(this).val();
            }


        });
        if ($("input[name='initial_funding_type']:checked").val() == 3 && _role == '2') {
            var availableamoutfordirect = $('#direct_account_number').find(":selected").text();
            if(typeof availableamoutfordirect != 'undefined'){
                availableamoutfordirect =  availableamoutfordirect.replace($('#direct_account_number').val(),'').replace('- (Rs. ','').replace(/[ )]/g,'');
                financialinfo.data['financialDetails']['total_savings_funds'] = availableamoutfordirect;
                financialinfo.data['financialDetails']['min_td_amount'] = _min_ip_reqmt.TD;
            }
        }


        $(".uploaded_image").each(function(){
            if($(this).attr("src") != ''){
                var image = $(this).attr("src").split('/');
                financialinfo.data['financialDetails'][$(this).attr('name')] = image[image.length-1];
                financialinfo.data['financialDetails']['images'][$(this).attr('name')] = image[image.length-1];
            }
        });

        if ($("input[name='initial_funding_type']:checked").val() == 5 && financialinfo.data['financialDetails']['others_type'] == '') {
			financialinfo.data['financialDetails']['others_type'] = 'fundingSource';
        }
        
        var checkFinancialField = checkFinancialFields(financialinfo.data['financialDetails'],_accountType);
        if (typeof(checkFinancialField.NotFound) != 'undefined') {
            $.growl({message: "Failed! Enable to fetch field : "+checkFinancialField.NotFound},{type: "warning"});
            return false;
        }
        
        financialinfo.data['financialDetails'] = checkFinancialField;
        financialinfo.data['formId'] = $(this).attr("id");
        financialinfo.data['functionName'] = 'SaveFinancialDetailsCallBack';

        disableSaveAndContinue(this);

        crudAjaxCall(financialinfo);
        return false;
    });

    
    $("body").on("change","input[name^='auto_renew']",function(){
        if($(this).val() == 'Y'){
            $("#maturity option[value='3']").wrap('<span class="title" style="display: none;" />');
            if($("#maturity option[value='1']").parent().find('span').length == 0)
            {
                $("#maturity option[value='1']").unwrap();
                $("#maturity option[value='2']").unwrap();
            }
            $("#maturity").val(1).trigger('change');
        }else{
            $("#maturity").val('').trigger('change');
            $("#maturity option[value='1']").wrap('<span class="title" style="display: none;" />');
            $("#maturity option[value='2']").wrap('<span class="title" style="display: none;" />');
            if($("#maturity option[value='3']").parent().find('span').length == 0)
            {
                $("#maturity option[value='3']").unwrap();
            }
            $("#maturity").val('3').trigger('change');
        }
        return false;
    });

    $("body").on("change","input[name='emd']",function(){
        if($(this).val() == 1){
            $('#emdname').removeClass('display-none');
        }else{
            $('#emdname').addClass('display-none');
            $("#emd_name").val('');
        }
        return false;
    });

    $("body").on("click","#credit_flag",function(){
        $("#maturity_account_details").addClass('display-none');
        $("#maturity_flag").prop( "checked", false );
        $("#edit_flag").prop( "checked", false );
    });

    $("body").on("click","#edit_flag",function(){
        $("#maturity_flag").prop( "checked", false );
    
        $("#credit_flag").prop( "checked", false );

        if($("#credit_flag").is(":visible")){
            $("#maturity_account_details").removeClass('display-none');
            $("#credit_flag").prop( "checked", false );
        }

        if($(this).prop('checked') == true)
        {
            addSelect2('maturity_bank_name','Maturity',false);
            $("#maturity_ifsc_code").attr('readonly',false);
            $("#maturity_account_number").attr('readonly',false);
            $("#maturity_account_name").attr('readonly',false);
            $("#reason_for_Account_change_div").removeClass("display-none");
            $("#cancel_cheque_image_div").removeClass("display-none");
        }else if(_is_review != 1){
            $("#maturity_bank_name").val($("#bank_name").val()).trigger('change');
            $("#maturity_ifsc_code").val($("#ifsc_code").val()).attr('readonly',true);
            $("#maturity_account_number").val($("#account_number").val()).attr('readonly',true);
            $("#maturity_account_name").val($("#account_name").val()).attr('readonly',true);
            $("#reason_for_Account_change_div").addClass("display-none");
            $("#cancel_cheque_image_div").addClass("display-none");
            addSelect2('maturity_bank_name','Maturity',true);
        }

    });

    $("body").on("click","#maturity_flag",function(){
        if($("#credit_flag").is(":visible")){
            $("#maturity_account_details").removeClass('display-none');
            $("#credit_flag").prop( "checked", false );
            $("#edit_flag").prop( "checked", false );
        }
        if($(this).prop('checked') == false)
        {
            addSelect2('maturity_bank_name','Maturity',false);
            $("#maturity_ifsc_code").attr('readonly',false);
            $("#maturity_account_number").attr('readonly',false);
            $("#maturity_account_name").attr('readonly',false);
            $("#reason_for_Account_change_div").removeClass("display-none");
            $("#cancel_cheque_image_div").removeClass("display-none");
        }
        else{
            $("#maturity_bank_name").val($("#bank_name").val()).trigger('change');
            $("#maturity_ifsc_code").val($("#ifsc_code").val()).attr('readonly',true);
            $("#maturity_account_number").val($("#account_number").val()).attr('readonly',true);
            $("#maturity_account_name").val($("#account_name").val()).attr('readonly',true);
            addSelect2('maturity_bank_name','Maturity',true);
            $("#reason_for_Account_change_div").addClass("display-none");
            $("#cancel_cheque_image_div").addClass("display-none");
        }
    });

    $("body").on("click","input[name='initial_funding_type']",function(){
        if($(this).val() == 1 || $(this).val() == 2){
          $('.others_type').prop('checked', false);
        }

        if($(this).val() == 1)
        {
            $("#date_label").html('Cheque Date');
            $("#reference_label").html('Cheque Number');
            $("#others_div").addClass("display-none");
            $("#others_div").siblings().removeClass('display-none');
            $("#etb_others_div").addClass("display-none");
            $("#upload-cheque-div").removeClass("display-none");
            $("#others_radio_div").addClass("display-none");
            $("#amount").prop('disabled',false);
            $("#reference").attr("minlength", 1).attr("maxlength", 6);

        }else if($(this).val() == 5){
            $("#others_div").removeClass("display-none");
            $("#others_div").siblings().addClass('display-none');
            $("#amount_div").removeClass("display-none");
            $("#upload-cheque-div").addClass("display-none");
            $("#others_radio_div").removeClass("display-none");
                $("#funding_source").prop('disabled',true);
                $("#amount").prop('disabled',true);
            // if($('input[name="others_type"]:checked').val() == "zero"){
            //     $("#funding_source").prop('disabled',true);
            //     $("#amount").prop('disabled',true);
            // }else{
            //     $("#funding_source").prop('disabled',false);
            //     $("#amount").prop('disabled',false);
            // }
        }else if($(this).val() == 3){
            $("#etb_others_div").removeClass("display-none");
            $("#etb_others_div").siblings().addClass('display-none');
            $("#amount_div").removeClass('display-none');
            $("#upload-cheque-div").addClass("display-none");
        }else{
            $("#others_div").addClass("display-none");
            $("#others_div").siblings().removeClass('display-none');
            $("#etb_others_div").addClass("display-none");
            $("#upload-cheque-div").addClass("display-none");
            $("#others_radio_div").addClass("display-none");
            if($(this).val() != 5){
                $("#date_label").html('Transaction Date');
                $("#reference_label").html('UTR Number');
                $("#reference").attr("minlength", 1).attr("maxlength", 22);
                $("#amount").prop('disabled',false);
            }
        }
        $('#reference').keypress(function(e) {
            if ($("input[name='initial_funding_type']:checked").val() == 1) {
                var letters=/^[0-9]/gi;
            }else if($("input[name='initial_funding_type']:checked").val() == 2){
                var letters=/^[A-Z0-9]/gi;
            }
            if(!(e.key).match(letters)) e.preventDefault();
        });

        $(".AddFinancialinfoField").each(function() {
            if($(this).attr("type") != "radio")
            {
                if(($(this).attr("name") == "years") || ($(this).attr("name") == "months") || ($(this).attr("name") == "days"))
                {
                    $("#years").val(0);
                    $("#months").val(0);
                    $("#days").val(0);
                }else if($(this).attr("name") == "bank_name"){
                    $("#bank_name").val('').trigger('change');
                }else if($(this).attr("name") != "interest_payout"){
                    $(this).val('');
                }
            }

            if(_globalSchemeDetails['min'] == _globalSchemeDetails['max'] ){
                    if(_globalSchemeDetails['validation_days'] == 'Days'){
                      $("#days").val(_globalSchemeDetails['max']);
                    }else{
                      $("#months").val(_globalSchemeDetails['min']);
                    }
                }

        });
    });

    $('#reference').keypress(function(e) {
        if ($("input[name='initial_funding_type']:checked").val() == 1) {
            var letters=/^[0-9]/gi;
        }else if($("input[name='initial_funding_type']:checked").val() == 2){
            var letters=/^[A-Z0-9]/gi;
        }
        if(!(e.key).match(letters)) e.preventDefault();
    });

    $("body").on("click","input[name='self_thirdparty']",function(){
        if($(this).val() == 'thirdparty')
        {
            addSelect2('relationship','Relationship',false);
        }else{
            $(".relationship").val('').trigger('change');
            addSelect2('relationship','Relationship',true);
        }
    });

    $("body").on("click","input[name='others_type']",function(){
        if($(this).is(":checked"))
        {
            $("#amount").val('');
            $("#funding_source").val('');
            $("#funding_source").prop('disabled', true);
            $("#amount").prop('disabled', true);
        }else{
            $("#funding_source").prop('disabled', true);
            $("#amount").prop('disabled', true);
        }
    });

    /*$("body").on("change","#direct_account_number",function(){
        var accountNumberObject = [];
        accountNumberObject.data = {};
        accountNumberObject.url =  '/bank/getaccountdetails';
        accountNumberObject.data['acccountId'] = $(this).val();
        accountNumberObject.data['accountNumber'] = $(this).find("option:selected").text();
        accountNumberObject.data['functionName'] = 'accountDetailsCallBack';

        crudAjaxCall(accountNumberObject);
        return false;
    });*/


});

function checkFinancialFields(fundingDetails, accountType) {
        switch (fundingDetails.initial_funding_type) {
                case '1': //cheque
                    var requiredFields = ['initial_funding_type','images','cheque_image','initial_funding_date','reference','bank_name','ifsc_code','account_number','account_name','amount','self_thirdparty'];

                    if (accountType == '3' || accountType == '4') { //TD & combo
                        var requiredFields = ['initial_funding_type','images','initial_funding_date','cheque_image','reference','bank_name','ifsc_code','account_number','account_name','amount','self_thirdparty','years','days','months','td_amount','auto_renew','interest_payout','emd','maturity','maturity_flag'];
       
       					switch(fundingDetails.maturity_flag){
                            case '0':

                            break;
                            case '1':
                                requiredFields.push('maturity_bank_name','maturity_ifsc_code','maturity_account_number','maturity_account_name');
                            break;
                            case '2':
                                requiredFields.push('maturity_bank_name','maturity_ifsc_code','maturity_account_number','maturity_account_name','reason_for_Account_change','cancel_cheque_image','images');
                            break;
                            case '3':
                                requiredFields.push('maturity_bank_name','maturity_ifsc_code','maturity_account_number','maturity_account_name');
                            break;
                            default:
                    			$.growl({message: "maturity flag not recognised"},{type: "warning"});
                    			return false;
                            break;
                        }
                        
                    }

                    if (typeof(fundingDetails.self_thirdparty) != 'undefined' && fundingDetails.self_thirdparty == 'thirdparty') {
                        requiredFields.push('relationship');
                    }

                    break;
                case '2': //NEFT/RTGS
                    var requiredFields = ['initial_funding_type','initial_funding_date','reference','bank_name','ifsc_code','account_number','account_name','amount','self_thirdparty'];

                    if (accountType == '3' || accountType == '4') { //TD & combo
                        var requiredFields = ['initial_funding_type','images','initial_funding_date','reference','bank_name','ifsc_code','account_number','account_name','amount','self_thirdparty','years','days','months','td_amount','auto_renew','interest_payout','emd','maturity','maturity_flag'];
       
                        switch(fundingDetails.maturity_flag){
                            case '0':

                            break;
                            case '1':
                                requiredFields.push('maturity_bank_name','maturity_ifsc_code','maturity_account_number','maturity_account_name');
                            break;
                            case '2':
                                requiredFields.push('maturity_bank_name','maturity_ifsc_code','maturity_account_number','maturity_account_name','reason_for_Account_change','cancel_cheque_image');
                            break;
                            case '3':
                                requiredFields.push('maturity_bank_name','maturity_ifsc_code','maturity_account_number','maturity_account_name');
                            break;
                            default:
                                $.growl({message: "maturity flag not recognised"},{type: "warning"});
                                return false;
                            break;
                        }
                    }

                    if (typeof(fundingDetails.self_thirdparty) != 'undefined' && fundingDetails.self_thirdparty == 'thirdparty') {
                        requiredFields.push('relationship');
                    }

                    break;
                case '5': //OTHERS
                    var requiredFields = ['initial_funding_type','others_type'];

                    if (typeof(fundingDetails.others_type) != 'undefined' && fundingDetails.others_type != 'zero') {
                        var requiredFields = ['initial_funding_type','funding_source','others_type','amount'];
                    }

                    break;
                case '3': //CALL CENTER
                    var requiredFields = ['initial_funding_type','maturity_flag','account_number','amount','years','days','months','td_amount','auto_renew','interest_payout','emd','maturity','maturity_bank_name','maturity_ifsc_code','maturity_account_number','maturity_account_name'];

                    if (_role == 2) {
                        requiredFields.push('total_savings_funds','min_td_amount');
                    }
                    
                    if (typeof(fundingDetails.maturity_flag) != 'undefined' && fundingDetails.maturity_flag == '2') {
                        requiredFields.push('reason_for_Account_change','cancel_cheque_image','images');
                    }

                    break;
                default:
                    $.growl({message: "funding type not recognised"},{type: "warning"});
                    break;
            }

            var fundingDetailsFields = {};
            requiredFields.forEach((field) => {
                var fieldName = eval("fundingDetails."+field);
                if (typeof(fieldName) == 'undefined') {
                    fundingDetailsFields['NotFound'] = field;
                }else{
                    fundingDetailsFields[field] = fieldName;
                }
            });
            // console.log(fundingDetailsFields);
            // return false;

            return fundingDetailsFields;
    }
/*function accountDetailsCallBackFunction(response,object)
{
    console.log(response);
    console.log(object);
    return false;
}*/
