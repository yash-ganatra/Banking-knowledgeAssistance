function  mobile(id){
	var value = $('#input_'+id).val();
	if(value.length != 10){
		return false;
	}

	if(parseInt(value.substr(0,1)) > 5){
		return true;
	}else{
		return false;
	}
}

function pincode(id){
	
	var pinValue = $('#input_'+id).val()
	if(pinValue == ''){
		return false;
	}

	var obj = [];
	obj.data = {};
	obj.url = '/bank/getdetailspincodeselected';
	obj.data['pincodeData'] = pinValue;
	obj.data['id'] = 'input_'+id;
	obj.data['ui_id'] = id;
	obj.data['functionName'] = 'fetcPincodeDataCallBack';
	crudAjaxCall(obj);
	// return true;
}

function  checkMinorandMajor(id) {
	var nominee_dob = $('#input_'+id).val();

	if(nominee_dob == ''){
		return false;
	}
	
	//-----------------mm-dd-yyyy get format--------------------\\

	var nominee_dob = nominee_dob.split('-');
	var getdate = nominee_dob[1]+'-'+nominee_dob[0]+'-'+nominee_dob[2];
	
	//-----------------end format ---------------------\\

	var newDate = new Date();
	var birthDate =  new Date(getdate);
	
	var getCurryear =  newDate.getUTCFullYear();
	var getDobyear =  birthDate.getUTCFullYear();

	var yearDiff = getCurryear - getDobyear;
	
	if(yearDiff < 18){
		 $('.gaurdian').removeAttr('style');
	}else{
		 $('.gaurdian').css('display','none');
	}

	return true;


}

function kyc(id) {
	var kycvalue = $('#input_'+id).val();

	if($('#proof_of_identity').val() == '')
	{
		$.growl({message:'Please select Proof of ID'},{type:'warning'});
		return false;
	}


	var currDate = new Date();
	var number = '';
	var expiryDate = '';
	var issuesDate = '';
		number = $('#number_of_indentity').val();

		if($('#proof_of_identity').val() == 2 || $('#proof_of_identity').val() == 3){
		
			expiryDate = $('#dateProofId').val();
			issuesDate = $('#issuedateProofId').val();
			if(expiryDate == ''){
				$.growl({message:'Please enter expiry date.'},{type:'warning'});
				return false;
			}

			if(issuesDate == ''){
				$.growl({message:'Please enter issues date.'},{type:'warning'});
				return false;
			}
			
			var getDate =  expiryDate.split("-")[0];
			var getMonth =  expiryDate.split("-")[1]-1;
			var getYear =  expiryDate.split("-")[2];

			var getexpDate =  new Date(getYear,getMonth,getDate);
			
			if(currDate > getexpDate){
				$.growl({message:'Please enter current date to next date.'},{type:'warning'});
				return false;
			}
		}
	

	if(number == ''){
		$.growl({message:'Please enter selected Proof of ID number'},{type:'warning'});
		return false;
	}
	if($('#proof_of_identity').val() == 1){
		var checkStatus =  checkIDproofNumber();
		if(!checkStatus){
			return false;
		}
	}
	return true;

}

function dropdown(id){
	var dropDownId = $('#input_'+id).val();

	if(dropDownId == ''){
		$.growl({message:'Please select field !!'},{type:'warning'});
			return false;
	}

	if(dropDownId == 'SINGL'){
		
		if($('#amendRow_52:visible').val() == ''){
			$.growl({message:'Selecting Single not permitted if spouse name change is also selected !!'},{type:'warning'});
			return false;
		}
	}

	return true;
}

function titledropdown(id){
	var dropDownId = $('#input_'+id).val();

	if(dropDownId == ''){
		$.growl({message:'Please select field !!'},{type:'warning'});
			return false;
	}

	return true;
}

function genderdropdown(id){
	var genderdropdown = $('#input_'+id).val();

	if(genderdropdown == ''){
		$.growl({message:'Please select field !!'},{type:'warning'});
			return false;
	}
	$('.titledropdown').val('');
	$('.titledropdown').removeAttr('disabled');
	
	titledrop = $('.titledropdown')[0]
	titledropid = $(titledrop).attr('id')
	titleoptions = $('#'+titledropid+' > option')
	titleoptions.each(function(id,valueline){

	    optionval = $(valueline).val();
	    if(typeof(optionval) != 'undefined' && optionval != ''){
		    allowedTitle = _title[optionval];
		    // console.log(allowedTitle);
		    if(allowedTitle != null){

			    if(allowedTitle.search(genderdropdown) >= 0){
			    	$(valueline).attr('disabled', false);
			    	$(valueline).removeAttr('hidden');
			    }else{
					$(valueline).attr('disabled', true);
					$(valueline).attr('hidden', true);
			    }
		    }
	    }
	});

	return true
}

function address(id){
	var addressId = $('#input_'+id).val();

	if(addressId.trim() == ""){

		$.growl({message:'Please Enter Address'},{type:'warning'});
		return false;
	}

	if(addressId.length <= 45){
		return true;
	}else{
		$.growl({message:'Validation failed address field !!'},{type:'warning'});
		return false;
	}
}

function name(id){

	var nameId = $('#input_'+id).val();
	var getID = id.split('_')[0];
	var msg = '';

	switch(getID){
		case '52':
			msg = 'Please Enter Spouse Name';
		break;

		case '51':
			msg = 'Please Enter Father Name';
		break;

		case '16':
			msg = 'Please Enter Mother Maiden Name';
		break;

		case '13':
			msg = 'Please Enter Correct FATCA Place of Birth';
		break;

		default :
			msg = 'Please Enter Name';
		break;
	}

	console.log(id);

	if(nameId.trim() == ""){

		$.growl({message:msg},{type:'warning'});
		return false;
	}

	if(nameId.length <= 100){
		return true;
	}else{
		$.growl({message:'Validation failed name field !!'},{type:'warning'});
		return false;
	}
	
}

function aadhar(id){
	var aadharId =  $('#input_'+id).val();
  	var currAadhaarNumber = aadharId.replace(/\D/g, '');       

    //console.log(currAadhaarNumber);
    if(typeof currAadhaarNumber == "undefined" || currAadhaarNumber == '' || currAadhaarNumber.length != 12 || !window['validateAadhaarNumberLocally'].apply(window,[currAadhaarNumber])){
            $.growl({message: "Invalid Aadhaar Number"},{type: "warning"});
            return false;
    }
    return true;
}

function emailcheck(id,status=''){
	var emailId = $('#input_'+id).val();
	
	if(emailId == ''){
		$.growl({message: "Please enter email-Id"},{type: "warning"});
		return false;
	}

	if(!(/(^[a-zA-Z0-9\+_\.-]+)(\.[a-zA-z0-9\+_\-])*@([a-zA-Z0-9\-]+\.)+[a-zA-Z]{2,6}$/.test(emailId))){
		$.growl({message: "Please enter email-Id"},{type: "warning"});
		return false;
	}
	var obj = [];
	obj.data = {};
	obj.url = '/bank/checkvaliddomain';
	obj.data['email_Id'] = emailId;
	obj.data['ui_id'] = id;
	obj.data['functionName'] = 'checkValidDomainCallBack';
	

	crudAjaxCall(obj);
	// return true;

}

function pan(id){
	var panNumber = $('#input_'+id).val();
	var panOldNumber =  $('#old_value_'+id).text()

	if(panNumber == panOldNumber){
		$.growl({message: "Please do not select same Pan Number"},{type: "warning"});
		return false;
	}

	if(panNumber == ''){
		$.growl({message: "Please enter Pan Number"},{type: "warning"});
		return false;
	}

	var obj = [];
	obj.data = {};
	obj.url = '/bank/amendpanisvalid';
	obj.data['pancard_no'] = panNumber;
	obj.data['ui_id'] = id;
	obj.data['functionName'] = 'PanIsAmendValidCallBack';
	crudAjaxCall(obj);
	// return true;
}


function checkCustId(id){
	var custId = $('#input_'+id).val();
	var checkAmendId = id.split('_')[0];
	var checkExist = '';
	if(checkAmendId == 50){
		checkExist = 'Y';
	}

	if(custId.length != 9){
		$.growl({message: "Please enter valid customerId"},{type: "warning"});
		return false;
	}

	var obj = [];
	obj.data = {};
	obj.url = '/bank/checkvalidcustid';
	obj.data['customer_id'] = custId;
	obj.data['ui_id'] = id;
	obj.data['checkExist'] = checkExist;
	obj.data['functionName'] = 'custIdValidCallBack';
	crudAjaxCall(obj);
	// return true;

}

function updateSaveGenCrfButton(id,status){
	if(status){
		$('#input_'+id).prop('disabled',true);
			$('#save_'+id).hide();
			$('#edit_'+id).show();
	}else{
		$('#input_'+id).prop('disabled',false);
			$('#save_'+id).show();
			$('#edit_'+id).hide();
	}
	if($('.save_field:visible').length==0 ){
			$('#saveAmendData').removeClass('disabled');
	}else{
		$('#saveAmendData').addClass('disabled');
	}
}


function checkValidDomainCallBackFunction(response,object){
    if(response['status'] == 'success'){
        $.growl({message:response['msg']},{type:response['status']});
        updateSaveGenCrfButton(object.data.ui_id,true);
       
    }else{
        $.growl({message:response['msg']},{type:'warning'});
        $('#saveAmendData').prop('disabled',true);
        updateSaveGenCrfButton(object.data.ui_id,false);
        return false;
    }
}

function PanIsAmendValidCallBackFunction(response,object){
        
    if(response['status'] == 'success'){
        $.growl({message:response['msg']},{type:response['status']});
        updateSaveGenCrfButton(object.data.ui_id,true);

    }else{
        $.growl({message:response['msg']},{type:'warning'});
        updateSaveGenCrfButton(object.data.ui_id,false);
    }
 }


 function custIdValidCallBackFunction(response,object){
 	if(response['status'] == 'success'){
            $.growl({message:response['msg']},{type:response['status']});
            updateSaveGenCrfButton(object.data.ui_id,true);
            
        }else{
            $.growl({message:response['msg']},{type:'warning'});
            updateSaveGenCrfButton(object.data.ui_id,false);
    }
 }

//  function checkAccountId(id){

//  	var custId = $('#input_'+id).val();

// 	// if(custId.length == 12){
// 	// 	return true;
// 	// }else{
// 	// 	$.growl({message: "Please enter valid Account Number"},{type: "warning"});
// 	// 	return false;
// 	// }

// 	// var obj = [];
// 	// obj.data = {};
// 	// obj.url = '/bank/checkvalidcustid';
// 	// obj.data['customer_id'] = custId;
// 	// obj.data['ui_id'] = id;
// 	// obj.data['functionName'] = 'custIdValidCallBack';
// 	// crudAjaxCall(obj);
// 	return true;

//  }

 function checkDelFlag(id){
 	var checkDel = $('#input_'+id).val();
 	if(checkDel == ''){
 		$.growl({message: "Please Enter Data."},{type: "warning"});
		return false;
 	}
 	if(checkDel != 'Y'){
 		$.growl({message: "Please Enter Valid Flag."},{type: "warning"});
		return false;
 	}
 	return true;
 }

function chkrestricted(id){
	var countryId = $('#input_'+id).val();
	if(countryId == ''){
		$.growl({message:'Please Select Country.'},{type: "warning"});
		return false;
	}

	var obj = [];
	obj.data = {};
	obj.url = '/bank/chkrestcountry';
	obj.data['county_id'] = countryId;
	obj.data['ui_id'] = id;
	obj.data['functionName'] = 'countryRestrictedCallBack';
	crudAjaxCall(obj);
}


function countryRestrictedCallBackFunction(response,object){
	if(response['status'] == 'success'){
        $.growl({message:response['msg']},{type:response['status']});
        updateSaveGenCrfButton(object.data.ui_id,true);
            
    }else{
        $.growl({message:response['msg']},{type:'warning'});
        updateSaveGenCrfButton(object.data.ui_id,false);
    }
}
 // function accountActiveFlag(id){
 // 	var checkDel = $('#input_'+id).val();
 // 	if(checkDel == ''){
 // 		$.growl({message: "Please Enter Data."},{type: "warning"});
	// 	return false;
 // 	}
 // 	if(checkDel != 'A'){
 // 		$.growl({message: "Please Enter Valid Flag."},{type: "warning"});
	// 	return false;
 // 	}
 // 	return true;
 // }


 function dateofBirth(id){
	// var checDate = $('#input_'+id).val();
	var checkDate = moment($('#input_'+id).val(), "DD-MM-YYYY").format("YYYY-MM-DD");    
	var dobDate = new Date(checkDate);
	var currDate = new Date();

	if(currDate <= dobDate){
 		$.growl({message: "Do not select future date."},{type: "warning"});
		return false;
	}
	return true;
 }


 function custActFlagChk(id){
	var custFlag = $("#input_"+id).val();
	
	if(custFlag == 'N'){
		return true;
	}else{
		$.growl({message: "Please Enter Valid Flag"},{type: "warning"});
		return false;
	}
 }

function checkValidAccount(id){

	var acctNo = $("#input_"+id).val();
	
	if(acctNo.length < 14){
		$.growl({message: "Please Enter Valid Account Number."},{type: "warning"});
		return false;
	}

	var obj = [];
	obj.data = {};
	obj.url = '/amend/checkvalidacctno';
	obj.data['ui_id'] = id;
	obj.data['functionName'] = 'checkValidAccountNo';
	obj.data['account_no'] = acctNo;
	crudAjaxCall(obj);

}


function checkValidAccountNoBackFunction(response,object){

	if(response['status'] == 'success'){
		$.growl({message:response['msg']},{type:response['status']});
		updateSaveGenCrfButton(object.data.ui_id,true);
		
	}else{
		$.growl({message:response['msg']},{type:'warning'});
		updateSaveGenCrfButton(object.data.ui_id,false);
	}	
}

