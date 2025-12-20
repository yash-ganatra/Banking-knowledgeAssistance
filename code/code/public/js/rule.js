
_globalCurrentUi = '';
_globalCurrentApplicant = '';

let _globalTimer = setInterval(function(){
    var availbleUi = $('.visibility_check');
    _globalCurrentUi = '';
    
    if($('#update_api_queue_div').hasClass('display')){
        if($('#apiqueueaofNumber').val().length >= 9){
            $('#update_api_queue_div').removeClass('display-none');
        }else{
            if(!($('#update_api_queue_div').hasClass())){
                $('#update_api_queue_div').addClass('display-none');
            }
        }
    }

    if($('#update_amendapi_queue_div').hasClass('display')){
        if($('#amendapiqueuecrfNumber').val().length >= 12){
            $('#update_amendapi_queue_div').removeClass('display-none');
        }else{
            if(!($('#update_amendapi_queue_div').hasClass())){
                $('#update_amendapi_queue_div').addClass('display-none');
            }
        }
    }

    for (var i = 0; i < availbleUi.length ; i++) {
         var avail_id = availbleUi[i].id.split("-")[1];
             if ($("#visibility_check-"+avail_id).is(":visible")) {
                _globalCurrentUi = "basic_account-"+avail_id;
                _globalCurrentApplicant = avail_id;      
             }
         }
    }, 500);


Array.prototype.unique = function() {
  return this.filter(function (value, index, self) { 
    return self.indexOf(value) === index;
  });
}

// function getModeofOperations(no_of_account_holders,dobs)
// {
//     var applicantdobs = dobs.split(',');
//     var minor = false;
//     var values = [];
//     for(i=0;i<=applicantdobs.length;i++)
//     {
//         var date_string = moment(applicantdobs[i], "DD.MM.YYYY").format("YYYY-MM-DD");
//         selectedDate = new Date(date_string);
//         today = new Date();
//         years = new Date(today - selectedDate)/(24 * 60 * 60 * 1000 * 365.25 );
//         if(Math.floor(years) < 18){
//             minor = true;
//             break;
//         }
//     }
//     if(no_of_account_holders < 2)
//     {
//         //1 is self
//         values.push(1);
//     }
//     if(minor)
//     {
//         //5 is gaurdian
//         values.push(5);   
//     }
//     //More than 1 account holders
//     if(no_of_account_holders > 1)
//     {
//         //1 is self
//         values.push(2,3,4,5,6,21);
//     }     
//     return values;
// }

function form_check_basic_var(){

    var accountHolders =  $("#qty_input").val();
    for(var c=1; c<=accountHolders; c++){
       var createObj =false;
        if (typeof _basic_form_check[c-1]  == 'undefined'){
                createObj = true;
        }else{
            if(typeof _basic_form_check[c-1]['basic_account-'+c]== 'undefined'){
                createObj = true;
            }
        }
        if(createObj){
            idSequence = c;
            var i = "basic_account-"+ idSequence;
            var obj = {};
            obj[i] = false;    
            _basic_form_check.push(obj);
             
        }

}


/*    setTimeout(function(){
        if (_is_progress == "1") {
            addAccountObj.form();
        }
    }, 2000);*/


}


function form_check_ovd_var(){
    var accountHolders =  parseInt(_availbleOvdUi/4);
    for(var c=1; c<=accountHolders; c++){
       var createObj =false;
        if (typeof _ovd_form_check[c-1]  == 'undefined'){
                createObj = true;
        }else{
            if(typeof _ovd_form_check[c-1]['ovd_account-'+c]== 'undefined'){
                createObj = true;
            }
        }
        if(createObj){
            idSequence = c;
            var obj = { ['ovd_id-'+ idSequence]: false,['ovd_permanant-'+ idSequence]: false,['ovd_communication-'+ idSequence]: false};
            _ovd_form_check.push(obj);
          

        }

}
        var photographsignature = {['photographsignature']: false};
        _ovd_form_check.push(photographsignature);
}

function form_check_risk_var(){

    var accountHolders =  _availbleRiskUi;
    for(var c=1; c<=accountHolders; c++){
       var createObj =false;
        if (typeof _riskClassification_form_check[c-1]  == 'undefined'){
                createObj = true;
        }else{
            if(typeof _riskClassification_form_check[c-1]['riskClassification_account-'+c]== 'undefined'){
                createObj = true;
            }
        }
        if(createObj){
            idSequence = c;
            var i = "riskClassification_account-"+ idSequence;
            var obj = {};
            obj[i] = false;    
            _riskClassification_form_check.push(obj);
        }
    }
}
function form_check_funding_var(){
    var accountHolders =  _availblefundingUi;
    for(var c=1; c<=accountHolders; c++){
       var createObj =false;
        if (typeof _funding_form_check[c-1]  == 'undefined'){
                createObj = true;
        }else{
            if(typeof _funding_form_check[c-1]['initial_funding']== 'undefined'){
                createObj = true;
            }
        }
        if(createObj){
            idSequence = c;
            var i = "initial_funding";
            var obj = {};
            obj[i] = false;    
            _funding_form_check.push(obj);
             
        }
}
}
function form_check_declaration_var(){
    var accountHolders =  _availbleDeclarationUi;
    for(var c=1; c<=accountHolders; c++){
       var createObj =false;
        if (typeof _declaration_form_check[c-1]  == 'undefined'){
                createObj = true;
        }else{
            if(typeof _declaration_form_check[c-1]['declaration']== 'undefined'){
                createObj = true;
            }
        }
        if(createObj){
            idSequence = c;
            var i = "declaration";
            var obj = {};
            obj[i] = false;    
            _declaration_form_check.push(obj);
             
        }
}
}

function form_check_basic(chk){

     if (_basic_form_check[chk-1]['basic_account-'+chk]!=true) {
              $.growl({message: "Applicant "+chk+ " details not captured completely"},{type: "warning"});
              return false;
       }else return true;
}
function form_check_ovd(chk){

        for (var key in _ovd_form_check[chk-1]) {
                if (_ovd_form_check[chk-1][key] === false) {
                $.growl({message: "Applicant "+chk+ " details not captured completely"},{type: "warning"});
                return false;
                break;
                }
        }
        return true;
}
function form_check_risk(chk){

     if (_riskClassification_form_check[chk-1]['riskClassification_account-'+chk]!=true) {
              $.growl({message: "Applicant "+chk+ " details not captured completely"},{type: "warning"});
              return false;
       }else return true;
}
function form_check_funding(chk){

     if (_funding_form_check[chk-1]['initial_funding']!=true) {
              $.growl({message: "Applicant Initial funding details not captured completely"},{type: "warning"});
              return false;
       }else return true;
}
function form_check_declaration(chk){

     if (_declaration_form_check[chk-1]['declaration']!=true) {
              $.growl({message: "Applicant declaration details not captured completely"},{type: "warning"});
              return false;
       }else return true;
}


function validateTDamount(td_amount,min,max)
{
    // if((td_amount < min) || (td_amount > max))
    if((td_amount < parseInt(min) ) || (td_amount > parseInt(max) ))
    {
        $.growl({message: "For the chosen Scheme Code TD Amount should be between Rs. "+min+" and "+max + ' '},{type: "warning"});
        return false;
    }
    return true;
}

function validateTenure(type,min,max)
{  
    // if(_globalSchemeDetails['td_rd'] == 'TD'){
        var years = parseInt($("#years").val());
        var months = parseInt($("#months").val());  
        var days = parseInt($("#days").val());
        
    // }else{
    //     var years = parseInt($("#tenure_year").val());
    //     var months = parseInt($("#tenure_month").val());  
    //     var days = 0;
    // }
	
    var depositPeriod = 0;
	years = isNaN(years) ? 0 : years;
	months = isNaN(months) ? 0 : months; 
	days = isNaN(days) ? 0 : days;

    if((years > 0 || months > 0) && days > 30){
        $.growl({message: "Days cannot be more than 30, if year or month is selected"},{type: "warning"});
        return false;
    }
	
    if(type == 'Days')
    {
        depositPeriod = parseInt(parseInt(years) * 365) + parseInt(parseInt(months) * 30) + parseInt(days);
    }else if(type == "Months"){
        depositPeriod = parseInt(parseInt(years) * 12) + parseInt(months) + (parseInt(days) / 30);
    }else{
        return false;
    }
    if((depositPeriod < min) || (depositPeriod > max))
    {
		var msg = "For the chosen Scheme Code tenure should be";  
		if(min != 0) msg += " minimum "+min;
		if(min != 0 && max != 0) msg += " and ";
		if(max != 0) msg += " maximum "+max + " "+ type+"(s)";
        $.growl({message: msg},{type: "warning"});
        return false;
    }
    return true;
}

function validateBulkRetail(bulkRetailType)
{
    depositAmount = $("#td_amount").val();
    tenureAmount = $("#tenure_amount").val();
    fundingAmount = $("#amount").val();
    if(parseInt(depositAmount) > parseInt(fundingAmount))
    {
        $.growl({message: "Deposit Amount can not be more than "+fundingAmount},{type: "warning"});
        return false;
    }
    if(parseInt(tenureAmount) > parseInt(fundingAmount))
    {
        $.growl({message: "Deposit Amount can not be more than "+fundingAmount},{type: "warning"});
        return false;
    }
    if(bulkRetailType == "Bulk")
    {
        if(depositAmount < 30000000)
        {
            $.growl({message: "For the Chosen Scheme Code Amount should not be less than 2Cr."},{type: "warning"});
            return false;
        }
    }else if(bulkRetailType == "Retail"){
        if(depositAmount > 30000000)
        {
            $.growl({message: "For the Chosen Scheme Code Amount should not be more than 2Cr."},{type: "warning"});
            return false;
        }
    }
    return true;
}

// function validateSourceofFunds(source_of_funds)
// {
//     if(jQuery.inArray( "2",source_of_funds) != "-1"){
//         $(".occupation option[value='1']").prop('disabled',true);
//      }else{
//         $(".occupation option[value='1']").prop('disabled',false);
//     }     
//     addSelect2('occupation','Occupation',false);
//     return false;
// }

function updateDescBasedOnOccupation(occupation,applicantId)
{
    if(occupation == 28 || occupation == 6 || occupation == 14 || occupation == 23){
        $("#other_occupation_div-"+applicantId).show();
    }else{
        $("#other_occupation_div-"+applicantId).hide();
    }
    return false;
}

function CheckAgeRule(date_elem)
{
	var dob_date_string = moment($('#'+date_elem).val(), "DD.MM.YYYY").format("YYYY-MM-DD");    
		
	var _TD = false;
	var _SAVINGS = false;
	if(_globalTDSchemeDetails != '') _TD = true;
	if(_globalSchemeDetails != '') _SAVINGS = true;
	
	if((_TD && _SAVINGS) || _TD)
		_schemeDetails = _globalTDSchemeDetails;
	else
		_schemeDetails = _globalSchemeDetails;
		
	return CheckRule_SchemeAge(_schemeDetails, dob_date_string);
}


function getAge(dob)
{
    //var yearsDiff = moment().diff(dob, 'years');
    var Diff = moment().diff(dob);
    var Dur = moment.duration(Diff);
    var yearsDiff = Dur._data.years;

    if((Dur._data.months > 0) || (Dur._data.days > 0)){
          yearsDiff += 0.1;
    }

    return yearsDiff;

}

function CheckRule_SchemeAge(schemeObject, dob)
{
	//var yearsDiff = moment().diff(dob, 'years');
    var Diff = moment().diff(dob);
    var Dur = moment.duration(Diff);
    var yearsDiff = Dur._data.years;

    if((Dur._data.months > 0) || (Dur._data.days > 0)){
          yearsDiff += 0.1;
    }

    if(schemeObject.max_age == 0){
        if(yearsDiff >= schemeObject.min_age)
        {
            return true;
        }else{
            return false;
        }
    }else if(schemeObject.min_age == 0){
        if(yearsDiff <= schemeObject.max_age)
        {
            return true;
        }else{
            return false;
        }
    }else{
        if(yearsDiff >= schemeObject.min_age && yearsDiff <= schemeObject.max_age)
            return true;
        else
            return false;
    }
}


function CheckRule_SchemePAN(schemeObject, currPFselection)
{
	if(schemeObject.pan_mandatory == 'Y' && currPFselection != "pancard")
		return false;	
	else
		return true;	 	
}

  //------------------------------Function will remove after check 19-FEB-2021------------------//
// function isMinor(dob){
// 	// expects inputs as var dob_date_string = moment(this.value, "DD.MM.YYYY").format("YYYY-MM-DD"); 
// 	var yearsDiff = moment().diff(dob, 'years');
// 	if(yearsDiff < 18)
// 		return true;
// 	else
// 		return false;
// }

function checkForSuperSaver()
{	
	var totalApplicants = $("#qty_input").val()
	
	if(totalApplicants == 1 || totalApplicants > 2){
		$.growl({message: "2 applicants expected for SSB Junior Saver Account"},{type: "warning"});
        return false;
	}
	
	var otherApplicantAge = moment().diff(moment($("#dob-2").val(),"DD-MM-YYYY"), 'years');
	if(otherApplicantAge < 18){
		$.growl({message: "Other applicant can not be minor for SSB Junior Saver Account"},{type: "warning"});
		return false;
	}
	
	if($("#pancard_no-1").val() != $("#pancard_no-2").val()){
		$.growl({message: "PAN card details needs to be same for both applicants for SSB Junior Saver Account"},{type: "warning"});
		return false;		
	}	
	return true;	
}

function checkUniquePAN()
{ 
	var totalApplicants = $("#qty_input").val()
	
	if(totalApplicants > 1){
		panArray = [];
		for (var i=1; i<=totalApplicants; i++) panArray.push($('#pancard_no-'+i).val());		
		if(panArray.length != panArray.unique().length){
			$.growl({message: "Unique PAN Numbers not evident."},{type: "warning"});
			return false;
		}	
	}else return true;	
}

function checkUniqueIDvalues()
{
	return true;	
}

function checkAgeForJointTD(dob){
    if(_nonInd != "NON_IND_HUF" && _schemeDetails.scheme_code != 'SB151'){
     if(getAge(dob) >= 18){

        return true;
     }else{

        $.growl({message: "Scheme validation failed. Joint Applicant can not be Minor!"},{type: "warning"});
        return false;

     }
    }else{
        return true;
    }
     // return getAge(dob) >= 18;
}

var checkschemeForAgeok = function(idSequence,dob_date_string){

    var ageOk = false;
    var _TD = false;
    var _SAVINGS = false;
    if(_globalTDSchemeDetails != '') _TD = true;
    if(_globalSchemeDetails != '') _SAVINGS = true;
    
    if((idSequence > 1) && _TD){
        
        return checkAgeForJointTD(dob_date_string);
        
    } 

    if((_TD && _SAVINGS) || _TD)
        _schemeDetails = _globalTDSchemeDetails;
    else
        _schemeDetails = _globalSchemeDetails;

    // Check rules only for the first applicant
    if(!CheckRule_SchemeAge(_schemeDetails, dob_date_string)){
        
        if((_schemeDetails.scheme_code == 'SB110' || _schemeDetails.scheme_code == 'SB151') && (idSequence > 1 )){

            ageOk = true;

        }
        // else if((_schemeDetails.scheme_code == 'SB150' ) && (idSequence == 1 )){

        //     ageOk = true;

        // } 
        
        else{
            if(_schemeDetails.min_age == 0) 
            {   
                $.growl({message: "Scheme validation failed. Permissible age upto "+_schemeDetails.max_age+" years!"},{type: "warning"});
            }else if(_schemeDetails.max_age == 0){
                $.growl({message: "Scheme validation failed. Permissible age from "+_schemeDetails.min_age+" years!"},{type: "warning"});
            }else{
                $.growl({message: "Scheme validation failed. Permissible age "+_schemeDetails.min_age+" upto "+_schemeDetails.max_age+" years!"},{type: "warning"});
            }  
        }                  
    }else{
        ageOk = true;
    }
            
    // If Minor select Single as default value
        if(isMinor(dob_date_string)){       
            $("#marital_status-"+idSequence).val('2').attr('disabled', 'disabled');
            $("#marital_status-"+idSequence).val('3').attr('disabled', 'disabled');
            $('#marital_status-'+idSequence).val('1').trigger('change');  
            $("#customer_account_type-"+idSequence+" option[value='2']").prop('disabled',true);
            $("#customer_account_type-"+idSequence).select2();
            if (idSequence == 1 && $('#account_type').val() == '5') {
                ageOk = false;
                $.growl({message: "Primary Minor applicant not permitted"},{type: "warning"});
            }
        }else
        {
            $("#marital_status-"+idSequence).removeAttr('disabled');
            $("#customer_account_type-"+idSequence+" option[value='2']").prop('disabled',false);
             $("#customer_account_type-"+idSequence).select2();
        }
    return ageOk;    
}

function isPanDuplicate(panNumbers){
    if (panNumbers.length > 1) {
        const uniquePan = Array.from(new Set(panNumbers));
        return uniquePan.length != panNumbers.length;
    }else{
        return false;
    }
}

function isIdentityProofDuplicate(ovdNumbers){
    if (ovdNumbers.length > 1) {
        const uniqueIdProof = Array.from(new Set(ovdNumbers));
        return uniqueIdProof.length != ovdNumbers.length;
    }else{
        return false;
    }
}

function isGenderTitleBlank(applicantId) {
    const gender = $("#gender-"+applicantId).val();
    const title = $("#title-"+applicantId).val();
    if (gender == '' || title == '') {
        return true;
    }else{
        return false;
    }
}

function checkImagesAreOk(section, applicantId) {
    if (section != '' && applicantId!= '') {
        var imageOk = true;
        switch (section) {
            case 'basic_details':

                // for(var applicantId = 1; applicantId<=accountHolders; applicantId++){
                    var image = $('#pf_type_card-'+applicantId).find('.uploaded_image').attr('src');
                    var customerType = $("#applicantId-"+applicantId).attr('customertype');
                    if (typeof(image) == 'undefined' && customerType != "etb") {
                        $.growl({message: "Please upload mandatory images for applicant "+applicantId},{type: "warning"});
                        imageOk = false;
                    }
                // }
                break;

            case 'ovd_details':
                
                    var customerType = $("#applicantId-"+applicantId).attr('customertype');
                    
                    var identityImage = $('#id_proof_image_front-'+applicantId).find('.uploaded_image').attr('src');
                    var addressImage  = $('#add_proof_image_front-'+applicantId).find('.uploaded_image').attr('src');
                    var commuImage    = $('#current_add_proof_image-'+applicantId).find('.uploaded_image').attr('src');
                    var customer_photo  = $('#customer_photo').find('.uploaded_image').attr('src');

                    var ImageNames = ['identity proof','address proof'];
                    var ovdImages = [identityImage,addressImage];
                   
                    if(_accounDetails.constitution != 'NON_IND_HUF'){
                    
                    if($('#proof_of_identity-'+applicantId).val()!=9){ //for ekyc skip identity and address proof image
                        for(i=0;i<ovdImages.length;i++){
                            if (typeof(ovdImages[i]) == 'undefined' && customerType != "etb") {
                                $.growl({message: "Please upload mandatory "+ImageNames[i]+" image for applicant "+applicantId},{type: "warning"});
                                imageOk = false;
                            }
                        }
                    }

                    if (typeof(commuImage) == 'undefined' && !$('#address_flag-'+applicantId).is(':checked') && customerType != "etb") {
                        $.growl({message: "Please upload mandatory communication address proof image for applicant "+applicantId},{type: "warning"});
                        imageOk = false;
                    }
                }

                if(_accounDetails.constitution == 'NON_IND_HUF'){
                    if($('#proof_of_identity-'+applicantId).val()!=9){ //for ekyc skip identity and address proof image
                        for(i=0;i<ovdImages.length;i++){
                            if (typeof(ovdImages[i]) == 'undefined' && customerType != "etb") {
                                $.growl({message: "Please upload mandatory "+ImageNames[i]+" image for applicant"+applicantId},{type: "warning"});
                                imageOk = false;
                            }
                        }
                    }

                    if(applicantId == 1){

                        if (typeof(commuImage) == 'undefined' && !$('#address_flag-'+applicantId).is(':checked') && customerType != "etb") {
                            $.growl({message: "Please upload mandatory communication address proof image for applicant "+applicantId},{type: "warning"});
                            imageOk = false;
                        }
                    }

                    if(applicantId == 2 && $('#address_per_flag-' + applicantId).is(':checked') == false){
                        if ((typeof(addressImage) == 'undefined') && customerType != "etb") {
                            imageOk = false;
                        }
                    }
                }
                
                if (typeof(customer_photo) == 'undefined') {
                    $.growl({message: "Please upload mandatory CUBE AOF image"},{type: "warning"});
                    imageOk = false;
                }
                break;

            case 'initial_funding':

                if ($('input[name="initial_funding_type"]:checked').val() == '1') {
                    var image = $('#cheque_image').find('.uploaded_image').attr('src');
                    if (typeof(image) == 'undefined') {
                        $.growl({message: "Please upload mandatory cheque image"},{type: "warning"});
                        imageOk = false;
                    }
                }
                break;

            case 'nominee_details':
            
                var witnessDeclaration = $('#witness1_signature-1').find('.uploaded_image').attr('src');
                if ($('#witness1_signature-1').is(':visible') && typeof(witnessDeclaration) == 'undefined') {
                    $.growl({message: "Please upload mandatory witness Declaration image"},{type: "warning"});
                    imageOk = false;
                }
                break;

            default:
                $.growl({message: "default"},{type: "warning"});
                imageOk = false;
                break;
        }
    }else{
        $.growl({message: "Incomplete image details"},{type: "warning"});
        imageOk = false;
    }

    return imageOk;
}

function sortDashboardByRole(dashboardUrl){
     
    switch(dashboardUrl){

        case '/bank/userapplications':
            sortindex = 4;
        break;

        case '/npc/userapplications':
            if(_role == 5 || _role == 6){
                sortindex = 0;
            }else{
                sortindex = 0; // account_details update
            }
        break;

        case '/inward/batchapplications':
            sortindex = 4;
        break;

        case '/delightadmin/kitcountapprovaltable':
            sortindex = 4;
        break;

        case '/callcenter/callcenteruserapplications':
            sortindex = 4;
        break;

        case '/archival/userapplications':
            sortindex = 6;
        break;

        default:
            sortindex = 0;
        break;
        }
            
    return sortindex;
}

function addressFieldValidation(element) {
   return element.value.replace(/[^a-z0-9 (),@./#&-]/gi, '').replace(/(\..*)\./g, '$1');
}

function checkValidCountry(countryId) {
    if (countryId != '1') {
        return false;
    }
    return true;
}