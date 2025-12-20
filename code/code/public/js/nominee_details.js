$(document).ready(function(){
    

    var addNomineeDetailsObj = $('#addNomineeDetailsForm-1').validate({
        // ignore: "",  // initialize the plugin
        rules: {
            nominee_name: {
                required: function(element){
                    return $("#nominee_exists-"+element.id.split('-')[1]).prop('checked') == true;
                },
                normalizer: function( value ) {
                return $.trim( value );
              },
            },
            nominee_address_line1: {
                required: function(element){
                    return $("#nominee_exists-"+element.id.split('-')[1]).prop('checked') == true;
                },
                normalizer: function( value ) {
                return $.trim( value );
              },
            },
            nominee_address_line2: {
                required: function(element){
                    return $("#nominee_exists-"+element.id.split('-')[1]).prop('checked') == true;
                },
                normalizer: function( value ) {
                return $.trim( value );
              },
            },
             nominee_country: {
                required: function(element){
                    return $("#nominee_exists-"+element.id.split('-')[1]).prop('checked') == true;
                }
            },
            nominee_state: {
                required: function(element){
                    return $("#nominee_exists-"+element.id.split('-')[1]).prop('checked') == true;
                }
            },
            nominee_city: {
                required: function(element){
                    return $("#nominee_exists-"+element.id.split('-')[1]).prop('checked') == true;
                }
            },
             nominee_pincode: {
                required: function(element){
                    return $("#nominee_exists-"+element.id.split('-')[1]).prop('checked') == true;
                },
                normalizer: function( value ) {
                return $.trim( value );
              },
            },
            relatinship_applicant: {
                required: function(element){
                    return $("#nominee_exists-"+element.id.split('-')[1]).prop('checked') == true;
                }
            },
            nominee_dob: {
                required: function(element){
                    return $("#nominee_exists-"+element.id.split('-')[1]).prop('checked') == true;
                }
            },
            guardian_name: {
                required: function(element){
                    return (($("#nominee_exists-"+element.id.split('-')[1]).prop('checked') == true) && ($("#nominee_age-"+element.id.split('-')[1]).val()  < 18));
                },
                normalizer: function( value ) {
                return $.trim( value );
              },
            },
            guardian_address_line1: {
                required: function(element){
                    return (($("#nominee_exists-"+element.id.split('-')[1]).prop('checked') == true) && ($("#nominee_age-"+element.id.split('-')[1]).val()  < 18));
                },
                normalizer: function( value ) {
                return $.trim( value );
              },
            },
            guardian_address_line2: {
                required: function(element){
                    return (($("#nominee_exists-"+element.id.split('-')[1]).prop('checked') == true) && ($("#nominee_age-"+element.id.split('-')[1]).val()  < 18));
                },
                normalizer: function( value ) {
                return $.trim( value );
              },
            },
            relatinship_applicant_guardian: {
                required: function(element){
                    return (($("#nominee_exists-"+element.id.split('-')[1]).prop('checked') == true) && ($("#nominee_age-"+element.id.split('-')[1]).val()  < 18));
                }
            },
            guardian_country: {
                required: function(element){
                    return (($("#nominee_exists-"+element.id.split('-')[1]).prop('checked') == true) && ($("#nominee_age-"+element.id.split('-')[1]).val()  < 18));
                }
            },
            guardian_state: {
                required: function(element){
                    return (($("#nominee_exists-"+element.id.split('-')[1]).prop('checked') == true) && ($("#nominee_age-"+element.id.split('-')[1]).val()  < 18));
                }
            },
            guardian_city: {
                required: function(element){
                    return (($("#nominee_exists-"+element.id.split('-')[1]).prop('checked') == true) && ($("#nominee_age-"+element.id.split('-')[1]).val()  < 18));
                }
            },
            guardian_pincode: {
                required: function(element){
                    return (($("#nominee_exists-"+element.id.split('-')[1]).prop('checked') == true) && ($("#nominee_age-"+element.id.split('-')[1]).val()  < 18));
                }
            },
            name_as_per_passbook_1: {
                required: function(element){
                    return $("#nominee_exists-"+element.id.split('-')[1]).prop('checked') == true;
                }
            },
            name_as_per_passbook_2: {
                required: function(element){
                    return $("#nominee_exists-"+element.id.split('-')[1]).prop('checked') == true;
                }
            },
            witness_signature: {
                required: function(element){
                    return (($("#nominee_exists-"+element.id.split('-')[1]).prop('checked') == true) && ($("#witness1_signature-"+element.id.split('-')[1]).find("img").length == 0));
                }
            },
        },
        messages: {
            nominee_name: {
                required: "Enter Nominee Name"
            },
            nominee_address_line1: {
                required: "Please Enter Address"
            },
             nominee_address_line2: {
                required: "Please Enter Address"
            },
            nominee_country: {
                required: "Please State Country"
            },
            nominee_state: {
                required: "Please Enter State"
            },
            nominee_city: {
                required: "Please Enter City"
            },
            nominee_pincode: {
                required: "Please Enter Pincode"
            },
            relatinship_applicant: {
                required: "Select Relationship with Nominee"
            },
            nominee_dob: {
                required: "Select Nominee Date of Birth"
            },
            guardian_name: {
                required: "Enter Guardian Name"
            },
            relatinship_applicant_guardian: {
                required: "Select Relationship with Applicant"
            },
            guardian_address_line1: {
                required: "Please Enter Address"
            },
             guardian_address_line2: {
                required: "Please Enter Address"
            },
            guardian_country: {
                required: "Please Select Country"
            },
            guardian_state: {
                required: "Please Enter State"
            },
             guardian_city: {
                required: "Please Enter City"
            },
             guardian_pincode: {
                required: "Please Enter Pincode"
            },
            name_as_per_passbook: {
                required: "Select Name as per Passbook"
            },
            witness_signature: {
                required: "Upload Witness Signature"
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




var tdNomineeDetailsObj = $('#addNomineeDetailsForm-2').validate({
        // ignore: "",  // initialize the plugin
        rules: {
            nominee_name: {
                required: function(element){
                    return $("#nominee_exists-"+element.id.split('-')[1]).prop('checked') == true;
                }
            },
            nominee_address_line1: {
                required: function(element){
                    return $("#nominee_exists-"+element.id.split('-')[1]).prop('checked') == true;
                }
            },
            nominee_address_line2: {
                required: function(element){
                    return $("#nominee_exists-"+element.id.split('-')[1]).prop('checked') == true;
                }
            },
            nominee_country: {
                required: function(element){
                    return $("#nominee_exists-"+element.id.split('-')[1]).prop('checked') == true;
                }
            },
            nominee_state: {
                required: function(element){
                    return $("#nominee_exists-"+element.id.split('-')[1]).prop('checked') == true;
                }
            },
            nominee_city: {
                required: function(element){
                    return $("#nominee_exists-"+element.id.split('-')[1]).prop('checked') == true;
                }
            },
             nominee_pincode: {
                required: function(element){
                    return $("#nominee_exists-"+element.id.split('-')[1]).prop('checked') == true;
                }
            },
            relatinship_applicant: {
                required: function(element){
                    return $("#nominee_exists-"+element.id.split('-')[1]).prop('checked') == true;
                }
            },
            nominee_dob: {
                required: function(element){
                    return $("#nominee_exists-"+element.id.split('-')[1]).prop('checked') == true;
                }
            },
            guardian_name: {
                required: function(element){
                    return (($("#nominee_exists-"+element.id.split('-')[1]).prop('checked') == true) && ($("#nominee_age-"+element.id.split('-')[1]).val()  < 18));
                }
            },
            guardian_address_line1: {
                required: function(element){
                    return (($("#nominee_exists-"+element.id.split('-')[1]).prop('checked') == true) && ($("#nominee_age-"+element.id.split('-')[1]).val()  < 18));
                }
            },
            guardian_address_line2: {
                required: function(element){
                    return (($("#nominee_exists-"+element.id.split('-')[1]).prop('checked') == true) && ($("#nominee_age-"+element.id.split('-')[1]).val()  < 18));
                }
            },
            relatinship_applicant_guardian: {
                required: function(element){
                    return (($("#nominee_exists-"+element.id.split('-')[1]).prop('checked') == true) && ($("#nominee_age-"+element.id.split('-')[1]).val()  < 18));
                }
            },
            guardian_country: {
                required: function(element){
                    return (($("#nominee_exists-"+element.id.split('-')[1]).prop('checked') == true) && ($("#nominee_age-"+element.id.split('-')[1]).val()  < 18));
                }
            },
            guardian_state: {
                required: function(element){
                    return (($("#nominee_exists-"+element.id.split('-')[1]).prop('checked') == true) && ($("#nominee_age-"+element.id.split('-')[1]).val()  < 18));
                }
            },
            guardian_city: {
                required: function(element){
                    return (($("#nominee_exists-"+element.id.split('-')[1]).prop('checked') == true) && ($("#nominee_age-"+element.id.split('-')[1]).val()  < 18));
                }
            },
            guardian_pincode: {
                required: function(element){
                    return (($("#nominee_exists-"+element.id.split('-')[1]).prop('checked') == true) && ($("#nominee_age-"+element.id.split('-')[1]).val()  < 18));
                }
            },
            name_as_per_passbook_1: {
                required: function(element){
                    return $("#nominee_exists-"+element.id.split('-')[1]).prop('checked') == true;
                }
            },
            name_as_per_passbook_2: {
                required: function(element){
                    return $("#nominee_exists-"+element.id.split('-')[1]).prop('checked') == true;
                }
            },
            witness_signature: {
                required: function(element){
                    return (($("#nominee_exists-"+element.id.split('-')[1]).prop('checked') == true) && ($("#witness1_signature-"+element.id.split('-')[1]).find("img").length == 0));
                }
            },
        },
        messages: {
            nominee_name: {
                required: "Enter Nominee Name"
            },
            nominee_address_line1: {
                required: "Please Enter Address"
            },
            nominee_country: {
                required: "Please Select Country"
            },
             nominee_address_line2: {
                required: "Please Enter Address"
            },
            nominee_state: {
                required: "Please Enter State"
            },
            nominee_city: {
                required: "Please Enter City"
            },
            nominee_pincode: {
                required: "Please Enter Pincode"
            },
            relatinship_applicant: {
                required: "Select Relationship with Nominee"
            },
            nominee_dob: {
                required: "Select Nominee Date of Birth"
            },
            guardian_name: {
                required: "Enter Guardian Name"
            },
            relatinship_applicant_guardian: {
                required: "Select Relationship with Applicant"
            },
            guardian_address_line1: {
                required: "Please Enter Address"
            },
             guardian_address_line2: {
                required: "Please Enter Address"
            },
            guardian_country: {
                required: "Please Select Country"
            },
            guardian_state: {
                required: "Please Enter State"
            },
             guardian_city: {
                required: "Please Enter City"
            },
             guardian_pincode: {
                required: "Please Enter Pincode"
            },
            name_as_per_passbook: {
                required: "Select Name as per Passbook"
            },
            witness_signature: {
                required: "Upload Witness Signature"
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

    $("body").on("click","#same_nominee",function(){
        if ($(this).prop("checked") == true)
        {
            $("#nominee_name-2").val($("#nominee_name-1").val()).attr("disabled");
            $("#nominee_address_line1-2").val($("#nominee_address_line1-1").val()).attr("readonly");
            $("#nominee_address_line2-2").val($("#nominee_address_line2-1").val()).attr("readonly");
            $("#nominee_country-2").val($("#nominee_country-1").val()).attr("readonly");
            $("#nominee_state-2").val($("#nominee_state-1").val()).attr("readonly");
            $("#nominee_city-2").val($("#nominee_city-1").val()).attr("readonly");
            $("#nominee_pincode-2").val($("#nominee_pincode-1").val()).attr("readonly");
            $("#relatinship_applicant-2").val($("#relatinship_applicant-1").val()).trigger('change').attr("readonly");
            $("#nominee_dob-2").val($("#nominee_dob-1").val()).attr("readonly");
            $("#nominee_age-2").val($("#nominee_age-1").val()).attr("readonly");
            $("#guardian_name-2").val($("#guardian_name-1").val()).attr("readonly");
            $("#guardian_address-2").val($("#guardian_address-1").val()).attr("readonly");

            if($("#nominee_age-2").val() < 18 )
            {    
                $(".minor_guardian-2").removeClass('display-none');
                $("#guardian_name-2").val($("#guardian_name-1").val()).attr("readonly");
                $("#relatinship_applicant_guardian-2").val($("#relatinship_applicant_guardian-1").val()).trigger('change').attr("readonly");
                $("#guardian_address_line1-2").val($("#guardian_address_line1-1").val()).attr("readonly");
                $("#guardian_address_line2-2").val($("#guardian_address_line2-1").val()).attr("readonly");
                $("#guardian_pincode-2").val($("#guardian_pincode-1").val()).attr("readonly");
                $("#guardian_city-2").val($("#guardian_city-1").val()).attr("readonly");
                $("#guardian_state-2").val($("#guardian_state-1").val()).attr("readonly");
                $("#guardian_country-2").val($("#guardian_country-1").val()).trigger('change').attr("readonly");
            }else{
                $(".minor_guardian-2").addClass('display-none');                
            }
        } 
    });

    $("body").on("click",".nomineeDetails",function(){
        if (!addNomineeDetailsObj.form()) { // Not Valid
            return false;
        }
        if(_accountType == 4){
            if(!tdNomineeDetailsObj.form()){
                    return false;
                }
        }

        if(!checkImagesAreOk('nominee_details', 1)){
            return false;
        };
    
        if(_globalSchemeDetails.nomination_related == 'Mandatory'){
            if($('#nominee_exists-1').prop('checked') != true) { 
                $.growl({message: "Nominee is mandatory for the selected scheme code"},{type: "warning"});
                return false;
           }
        }

        var nomineeDetailsObject = [];
        nomineeDetailsObject.data = {};
        nomineeDetailsObject['url'] = '/bank/savenomineedetails';
        $(".nomineeDetailsForm").each(function() {
            var accountId = $(this).attr("id");
            nomineeDetailsObject.data[accountId] = {};
            nomineeDetailsObject.data[accountId]['witnessSignatures']= {};

            if($("#applicantId-"+accountId).val() != ''){
                nomineeDetailsObject.data['is_update'] = true;
                nomineeDetailsObject.data[accountId]['applicantId'] = $("#applicantId-"+accountId).val();
            }

			firstNom = $('#nominee_exists-1').prop('checked');
			secondNom = $('#nominee_exists-2').prop('checked');
			
			if(typeof(firstNom) != 'undefined' && !firstNom){				
				removeFirstNom = true;				
			}else removeFirstNom = false;
			
			if(typeof(secondNom) != 'undefined' && !secondNom){				
				removeSecondNom = true;				
			}else removeSecondNom = false;				

            $(".NomineeDetailsField").each(function() {
                if(accountId == $(this).attr('id').split('-')[1]){
                    name = $(this).attr('id').split('-')[0];
                    if(name == 'nominee_exists'){
                        if ($(this).prop('checked')==true)
                        {
                            nomineeDetailsObject.data[accountId][name]= "yes";
                        }else{
                            nomineeDetailsObject.data[accountId][name] = "no";
                        }
                    }else{
                        if($(this).attr('type') == 'radio')
                        {
                            nomineeDetailsObject.data[accountId][name] = $('input[id="'+$(this).attr('id')+'"]:checked').val();
                        }else{
                            nomineeDetailsObject.data[accountId][name] = $(this).val();
							if(accountId == 1 && removeFirstNom) nomineeDetailsObject.data[accountId][name] = '';
							if(accountId == 2 && removeSecondNom) nomineeDetailsObject.data[accountId][name] = ''; 
                        }
                    }
                } 
            });

            $(".uploaded_image").each(function(){
                if($(this).attr("src") != ''){
                    if(accountId == $(this).attr('name').split('-')[1]){
                        name = $(this).attr('name').split('-')[0];
                        var image = $(this).attr("src").split('/');
                        nomineeDetailsObject.data[accountId][name] = image[image.length-1];
                        nomineeDetailsObject.data[accountId]['witnessSignatures'][name] = image[image.length-1];
                    }                    
                }
            });
        });

        nomineeDetailsObject.data['formId'] = $(this).attr("id");
        nomineeDetailsObject.data['functionName'] = 'SaveNomineeDetailsCallBack';
        
        disableSaveAndContinue(this);
        
        crudAjaxCall(nomineeDetailsObject);
        return false;
    });

    $("body").on("click",".address_type",function(){
        $('.nominee_Details').prop('checked',false);
        var addressObject = [];
        addressObject.data = {};
        addressObject.url =  '/bank/getaddress';
        addressObject.data['formId'] = $("#formId").val();
        addressObject.data['applicantId'] = $(this).attr('id').split('-')[1];
        addressObject.data['address_type'] = $("input[name='address_type']:checked").val();
        addressObject.data['functionName'] = 'AddressCallBack';

        crudAjaxCall(addressObject);
        // return false;
    });

    //suman

    $("body").on("click",".nominee_Details",function(){
        $('.address_type').prop('checked',false);
        var addressObject = [];
        addressObject.data = {};
        addressObject.url =  '/bank/getnomineedetailsbyAccid';
        addressObject.data['formId'] = $("#formId").val();
        addressObject.data['applicantId'] = $(this).attr('id').split('-')[1];
        addressObject.data['nominee_Details'] = $("input[name='nominee_Details']:checked").val();
        addressObject.data['functionName'] = 'NomineeDetailsByAccIdCallBack';
        
        crudAjaxCall(addressObject);
        // return false;
    });


    // function disableNomineeDetails(){
    //     if ($("input[name='nominee_Details']:checked").val() == "savingnomineeDetails") {
    //         $("#nominee_name-"+object.data['applicantId']).prop('disabled',true);
    //         $("#relatinship_applicant-"+object.data['applicantId']).prop('disabled',true);
    //         $("#nominee_address_line1-"+object.data['applicantId']).prop('disabled',true);
    //         $("#nominee_address_line2-"+object.data['applicantId']).prop('disabled',true);
    //         $("#nominee_pincode-"+object.data['applicantId']).prop('disabled',true);
    //         $("#nominee_city-"+object.data['applicantId']).prop('disabled',true);
    //         $("#nominee_state-"+object.data['applicantId']).prop('disabled',true);
    //         $("#nominee_dob-"+object.data['applicantId']).prop('disabled',true);
    //         $("#nominee_age-"+object.data['applicantId']).prop('disabled',true);
    //         $("#nominee_country-"+object.data['applicantId']).prop('disabled',true);
    //         if($("#nominee_age-"+object.data['applicantId']).val()  < 18){
    //             $("#guardian_name-"+object.data['applicantId']).prop('disabled',true);
    //             $("#guardian_address_line1-"+object.data['applicantId']).prop('disabled',true);
    //             $("#guardian_address_line2-"+object.data['applicantId']).prop('disabled',true);
    //             $("#guardian_pincode-"+object.data['applicantId']).prop('disabled',true);
    //         }

    //     }
    //     if($("input[name='address_type']:checked").val() == "permanent" || $("input[name='address_type']:checked").val() == "communication"){
    //         $("#nominee_name-"+object.data['applicantId']).prop('disabled',false);
    //         $("#relatinship_applicant-"+object.data['applicantId']).prop('disabled',false);
    //         $("#nominee_dob-"+object.data['applicantId']).prop('disabled',false);
    //         $("#nominee_age-"+object.data['applicantId']).prop('disabled',false);
    //     }
    // }


    

    $("body").on("click",".same_as_nominee_address",function(){
        var applicantId = $(this).attr("id").split('-')[1];
        //console.log(applicantId);

        if ($(this).prop("checked") == true)
        {
            
            $("#guardian_address_line1-"+applicantId).val($("#nominee_address_line1-"+applicantId).val());
            $("#guardian_address_line2-"+applicantId).val($("#nominee_address_line2-"+applicantId).val());
            $("#guardian_country-"+applicantId).val($("#nominee_country-"+applicantId).val()).trigger('change');
            $("#guardian_state-"+applicantId).val($("#nominee_state-"+applicantId).val());
            $("#guardian_city-"+applicantId).val($("#nominee_city-"+applicantId).val());
            $("#guardian_pincode-"+applicantId).val($("#nominee_pincode-"+applicantId).val());
            
        } 
        
    });

    $("body").on("keyup",".guardian_pincode",function(){
        if($(this).val().length >= 6){
            var pincodeObject = [];
            pincodeObject.data = {};
            pincodeObject.url =  '/bank/getaddressdatabypincode';
            pincodeObject.data['id'] = $(this).attr('id');
            pincodeObject.data['pincode'] = $(this).val();
            pincodeObject.data['functionName'] = 'GuardianAddressDataCallBack';
        
            crudAjaxCall(pincodeObject);
            return false;
       }
    });

    $("body").on("focusout",".guardian_name",function(){
      
        var applicantId = $(this).attr("id").split('-')[1];

        var  nomineeName =  $("#nominee_name-"+applicantId).val() ;
        var guardianName =  $("#guardian_name-"+applicantId).val() ;
        console.log(nomineeName);
        console.log(guardianName);

        if(nomineeName == guardianName){
         $.confirm({
            title:"confirmation",
            content: 'Please confirm Guardian Name same as Nominee Name',
            confirmButton: "Ok",
            cancelButton: "Cancel",
            text:"This is very dangerous, you shouldn't do it! Are you really really sure?",
            confirm: function(button) {
               return true;
            },
            cancel: function(button) {
                $("#guardian_name-"+applicantId).val('') ;
                return true;
            }
            
        });
     }else{
        return true;
     }
        
    
    });
});