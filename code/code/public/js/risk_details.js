$(document).ready(function(){
    var addRiskClassificationObj = $('#addRsikClassificationForm').validate({
        // ignore: "",  // initialize the plugin
        rules: {
            annual_turnover: {
                required: true,
            },
            basis_categorisation: {
                required: true,
            },
            categorisation_others_comments:{
                required: function(element){
                    return $('input[name="basis_categorisation"]:checked').val() == 6;
                }
            },
            occupation: {
                // required: true,
                required: function(element){
                    return $("#applicantId-"+element.id.split('-')[1]).attr('customertype') != "etb";
                },
            },
            politically_exposed_person_status: {
                required: true,
            },
            country_name: {
                required: true,
            },
            country_of_birth: {
                required: true,
            },
            citizenship: {
                required: true,
            },
            residence: {
                required: true,
            },
            inward_outward: {
                required: true,
            },
            education: {
                // required: true,
                required: function(element){
                    return $("#applicantId-"+element.id.split('-')[1]).attr('customertype') != "etb";
                },
            },
            customer_type: {
                // required: true,
                required: function(element){
                    return $("#applicantId-"+element.id.split('-')[1]).attr('customertype') != "etb";
                },
            },
            gross_income: {
                // required: true,
                required: function(element){
                    return $("#applicantId-"+element.id.split('-')[1]).attr('customertype') != "etb";
                },
            },
            networth: {
                // required: true,
                required: function(element){
                    return $("#applicantId-"+element.id.split('-')[1]).attr('customertype') != "etb";
                },
            },
            place_of_birth: {
                // required: true,
                required: function(element){
                    return $("#applicantId-"+element.id.split('-')[1]).attr('customertype') != "etb";
                },
                normalizer: function( value ) {
                return $.trim( value );
              },
            },
            expected_transactions: {
                required: true,
            },
            source_of_funds: {
                required: true,
            },
            tin: {
                required: true,
            },
            approximate_value: {
                required: true,
            },
            other_occupation: {
                required: true,
                normalizer: function( value ) {
                return $.trim( value );
              },
            },
            source_others_comments: {
                required: function(element){
                    return $('input[name="source_of_funds"]:checked').val() == 5;
                }
            },
        },
        messages: {
            annual_turnover: {
                required: "Select Annual Turnover"
            },
            basis_categorisation: {
                required: "Select Basis of Categorisation"
            },
            categorisation_others_comments:{
                required: "Please Enter Other Categorisation"
            },
            politically_exposed_person_status: {
                required: "Enter Politically Exposed Person Staus"
            },
            country_name: {
                required: "Select Current Residency"
            },
            country_of_birth: {
                required: "Select Country of Birth"
            },
            citizenship: {
                required: "Select Citizenship"
            },
            residence: {
                required: "Select Residence for Tax Purpose"
            },
            occupation: {
                required: "Select Occupation"
            },
            inward_outward: {
                required: "Select Foreign Inward / Outward Remittence Expected"
            },
            education: {
                required: "Select Education"
            },
            customer_type: {
                required: "Select Customer Type"
            },
            gross_income: {
                required: "Select Gross Income"
            },
            networth: {
                required: "Select Networth"
            },
            place_of_birth: {
                required: "Enter Place of Birth"
            },
            expected_transactions: {
                required: "Select Excepted Transactions"
            },
            source_of_funds: {
                required: "Select Source of Funds"
            },
            tin: {
                required: "Please select TIN Number"
            },
            approximate_value: {
                required: "Please select Approximate Value"
            },
            source_others_comments: {
                required: "Please specify other source of funds"
            },
            other_occupation: {
                required: "Please Enter Other Occupation"
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

    _availbleRiskUi = $('.visibility_check').length;
    _riskClassification_form_check = [];
    form_check_risk_var();
    

    $("body").on("change","input[name^='us_person']",function(){
        var applicantId = $(this).attr("id").split('-')[1];
        if($(this).val() == 1){
            $('#tinDiv-'+applicantId).show();
        }else{
            $('#tinDiv-'+applicantId).hide();
        }
        return false;
    });
    
    $("body").on("click",".nextriskclassifiaction",function(){
        if (!addRiskClassificationObj.form()) { // Not Valid
            return false;
        }else{

        }
            var curr = $(this);
            var idSequence = 1;
            if(curr[0].id != null){
                idSequence = curr[0].id.split('-')[1];
            }    
        _riskClassification_form_check[idSequence-1]['riskClassification_account-'+idSequence]= true;   
        

        // if(_is_etb != 'ETB'){

        // var getLenght = '';
        // if(_is_etb != 'ETB'){
        //  $.each(_checkNtbEtb,function(key,value){
            //  getLenght = key;
       
        //  for(let j=1;j>=getLenght;j++){
         
         
            // if(value.is_new_customer != "0"){

            for (var i =1; i<=_no_of_account_holders.length;  i++) {
                    if(_checkNtbEtb[i].is_new_customer != "0"){

                if (_globalCountryRisk[i] == 'H') {
                    $.growl({message: 'Account opening is prohibited for restricted countries.'},{type: "warning"});
                    return false;
                }
                    } 
            }
        //  }
        // });
        //  }
            
        //console.log($(this).attr('id'));
        // $('a[data-id="'+$(this).attr('tab')+'"]').removeClass('text-muted-lnavs').unbind("click", false);
        // $('a[data-id="'+$(this).attr('tab')+'"]').click();
        $('a[data-id="'+$(this).attr('id')+'"]').removeClass('text-muted-lnavs').unbind("click", false);
        $('a[data-id="'+$(this).attr('id')+'"]').click();
        return false;
    })

    $("body").on("click",".riskClassification",function(){
        if (!addRiskClassificationObj.form()) { // Not Valid
            return false;
        }else{

        }

        for(var chk=1; chk<=(_availbleRiskUi-1); chk++){
                if (_availbleRiskUi == 1) { //not require to check for single applicant
                    break;
                }
               if(!form_check_risk(chk)){
                return false;
               }

        }

        // var getLenght = '';
        // if(_is_etb != 'ETB'){
        //  $.each(_checkNtbEtb,function(key,value){
            //  getLenght = key;
       
        //  for(let j=1;j>=getLenght;j++){

            // if(value.is_new_customer != "0"){
        // if(_is_etb != 'ETB'){

            for (var i =1; i<=_no_of_account_holders.length;  i++) {
                    if(_checkNtbEtb[i].is_new_customer != "0"){

                if (_globalCountryRisk[i] == 'H') {
                    $.growl({message: 'Account opening is prohibited for restricted countries (Applicant '+i+').'},{type: "warning"});
                    return false;
                }
            }
        }
        //  }
        //  }
        // });
  
        var riskClassificationObject = [];
        riskClassificationObject.data = {};
        //risk details
        riskClassificationObject.data['riskclassificationDetails'] = {};
        riskClassificationObject['url'] = '/bank/saveriskdetails';

        var allOk = true;
        // var checkCustType = '';
        $(".RiskClassificationForm").each(function(){
            var accountId = $(this).attr("id");
            var etbntbTypechk = $(this).attr("is_new_custtype");

            riskClassificationObject.data['riskclassificationDetails'][accountId] = {};
            if($("#applicantId-"+accountId).val() != ''){
                riskClassificationObject.data['is_update'] = true;
                riskClassificationObject.data['riskclassificationDetails'][accountId]['applicantId'] = $("#applicantId-"+accountId).val();
            }
            $(".RiskClassificationField").each(function(){
                if(accountId == $(this).attr('id').split('-')[1]){
                    name = $(this).attr('id').split('-')[0];
                    if($(this).attr('type')=='radio')
                    {
                        riskClassificationObject.data['riskclassificationDetails'][accountId][name] =
                        $('input[name='+$(this).attr('name')+']:checked').val();
                    }else{
                        riskClassificationObject.data['riskclassificationDetails'][accountId][name] = $(this).val();
                    }
                }
            });
            riskClassificationObject.data['riskclassificationDetails'][accountId]['annual_turnover'] = $('#annual_turnover').val();
            riskClassificationObject.data['riskclassificationDetails'][accountId]['source_of_funds'] = $('#source_of_funds').val();
            riskClassificationObject.data['riskclassificationDetails'][accountId]['expected_transactions'] = $('#expected_transactions').val();
            riskClassificationObject.data['riskclassificationDetails'][accountId]['inward_outward'] =  $('input[name = "inward_outward" ]:checked').val();
            riskClassificationObject.data['riskclassificationDetails'][accountId]['approximate_value'] = $('#approximate_value').val();
            riskClassificationObject.data['riskclassificationDetails'][accountId]['is_new_customer'] = etbntbTypechk;

            if(jQuery.inArray( "5", $('#source_of_funds').val() ) != '-1'){
                riskClassificationObject.data['riskclassificationDetails'][accountId]['source_others_comments'] = $('#source_others_comments').val();
            }

        // var getLenght = '';
           // if(_is_etb != 'ETB'){
        
            // $.each(_checkNtbEtb,function(key,value){
                // getLenght = key;
            
            // for(let i=1;i>=getLenght;i++){
               
                if(_checkNtbEtb[accountId].is_new_customer != "0"){  
                    lmh_ratings = ['Low', 'Medium', 'High'];
                console.dir(riskClassificationObject.data['riskclassificationDetails'][accountId]);
                if(lmh_ratings.indexOf(riskClassificationObject.data['riskclassificationDetails'][accountId]['risk_classification_rating']) > -1){
                    
                }else{
                    $.growl({message: 'Invalid risk classification for an applicant!'},{type: "warning"});
                    allOk = false;
                    return false;           
                }
            }
            // }
            // });
        });

        riskClassificationObject.data['formId'] = $(this).attr("id");
        riskClassificationObject.data['functionName'] = 'SaveRiskClassificationObjectDetailsCallBack';

        disableSaveAndContinue(this);           
        setTimeout(function(){
            if(allOk){
                crudAjaxCall(riskClassificationObject);         
            }
        }, 1000);
        return false;
    });

    // $("body").on("change","select[id^='customer_type'],select[id^='country_name'],select[id^='occupation'],select[id^='country_of_birth'],select[id^='citizenship'],select[id^='residence']",function(){
    //     var applicantId = $(this).attr("id").split('-')[1];
    //     //calculateRiskClassficationRating(applicantId); 


    // });


    
    $("body").on("change","select[id^='customer_type'],select[id^='occupation'],select[id^='country_name'],select[id^='country_of_birth'],select[id^='citizenship'],select[id^='residence']",function(){
        var applicantId = $(this).attr("id").split('-')[1];
        _selectedID = $(this).attr("id").split('-')[0];
        calculateRiskClassficationRating(applicantId);        
	});
    
    $('input[id^=pep-]').on('click',function(){
        var applicantId = $(this).attr("id").split('-')[1];
        _selectedID = $(this).attr("id").split('-')[0];
        calculateRiskClassficationRating(applicantId);        
    });

    $("body").on("change","select[id^='basis_categorisation']",function(){
        var applicantId = $(this).attr("id").split('-')[1];
        if($(this).val() == 6){
            $("#categorisation_others_comments-"+applicantId).show();
        }else{
            $("#categorisation_others_comments-"+applicantId).hide();
        }
        return false;
    });

    $("body").on("change","select[id^='occupation']",function(){
        var applicantId = $(this).attr("id").split('-')[1];
        return updateDescBasedOnOccupation($(this).val(),applicantId);
    });

    $("body").on("change","input[id^='inward_outward']",function(){
        if($(this).val() == 1){ 
            $("#approximatevalue").show();
        }
        else{
            $("#approximatevalue").hide();
        }
        return false;
    });

});

function calculateRiskClassficationRating(applicantId)
{
    if(($("#customer_type-"+applicantId).val() == '') || ($("#occupation-"+applicantId).val() == ''))
    {
        return false;
    }

    if($("#occupation-"+applicantId).val() == 15){
        $('[data-attr = '+"pepno-"+applicantId+']').removeAttr('checked');
        $('#pep-'+applicantId).prop('checked',true);
    }else{
        $('#pep-'+applicantId).removeAttr('checked')
        $('[data-attr = '+"pepno-"+applicantId+']').prop('checked',true)
    }
    
    var riskObject = [];
    riskObject.data = {};
    riskObject.url =  '/bank/riskclassificationrating';
    riskObject.data['applicantId'] = applicantId;
    riskObject.data['customer_risk_type'] = $("#customer_type-"+applicantId).val();
    riskObject.data['occupation'] = $("#occupation-"+applicantId).val();
    riskObject.data['citizenship'] = $("#citizenship-"+applicantId).val();
    riskObject.data['country_risk'] = $("#country_name-"+applicantId).val();
    riskObject.data['country_of_birth'] = $("#country_of_birth-"+applicantId).val();
    riskObject.data['country'] = $('#h_per_country-'+applicantId).val();
    riskObject.data['residence'] = $('#residence-'+applicantId).val();
	riskObject.data['pep'] = $("#pep-"+applicantId+':checked').val();
    riskObject.data['functionName'] = 'RiskClassficationRatingCallBack';
	
    crudAjaxCall(riskObject);
    return false;
}

function getCategorisation(applicantId)
{
    var riskObject = [];
    riskObject.data = {};
    riskObject.url =  '/bank/categorisation';
    riskObject.data['applicantId'] = applicantId;
    riskObject.data['occupation'] = $("#occupation-"+applicantId).val();
    riskObject.data['country'] = $("#country_name-"+applicantId).val();
    riskObject.data['citizenship'] = $("#citizenship-"+applicantId).val();
    riskObject.data['country_of_birth'] = $("#country_of_birth-"+applicantId).val();
    riskObject.data['occupation_name'] = $("#occupation-"+applicantId).find("option:selected").text();
	riskObject.data['pep'] = $("#pep-"+applicantId+':checked').val();
    riskObject.data['functionName'] = 'CategorisationCallBack';

    crudAjaxCall(riskObject);
    return false;
}