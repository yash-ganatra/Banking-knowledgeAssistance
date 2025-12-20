$(document).ready(function(){  

    hasValidImageSrc = function ($container) {
        return $container.find('img').filter(function() {
          return $(this).attr('src')?.trim() !== '';
        }).length > 0;
      }

    addOvdDocumentObj = $('#addOvdDocumentForm').validate({
        // ignore: "",    // initialize the plugin
        rules: {
            proof_of_identity: {
                required: function(element){
                    return $("#applicantId-"+element.id.split('-')[1]).attr('customertype') != "etb";
                },
            },
            id_proof_card_number: {
                // required: true,
                required: function(element){
                    return $("#applicantId-"+element.id.split('-')[1]).attr('customertype') != "etb";
                },
            },            
            gender: {
                required: true,
            },
            short_name: {
                required: true,
                normalizer: function( value ) {
                return $.trim( value );
              },
            },
            title: {
                // required: true
                required: function(element){
                    return $("#applicantId-"+element.id.split('-')[1]).attr('customertype') != "etb";
                },
            },
            first_name: {
                required: function (element){
                    if(_accounDetails.constitution == 'NON_IND_HUF' && element.id=="first_name-2"){
                        return true;
                    }else{
                        return false;
                    }
                },
                normalizer: function( value ) {
                return $.trim( value );
              },
            },

            last_name: {
                required: true,
                normalizer: function( value ) {
                return $.trim( value );
              },
            },
            mothers_maiden_name: {
                // required: true
                required: function(element){
                    return $("#applicantId-"+element.id.split('-')[1]).attr('customertype') != "etb";
                },
                normalizer: function( value ) {
                return $.trim( value );
              },
            },
            mother_full_name: {
                // required: true
                required: function(element){
                    return $("#applicantId-"+element.id.split('-')[1]).attr('customertype') != "etb";
                },
                normalizer: function( value ) {
                return $.trim( value );
              },
            },
            father_spouse: {
                required: true,
                normalizer: function( value ) {
                return $.trim( value );
              },
                /*required: function(element){
                    return $("#applicantId-"+element.id.split('-')[1]).attr('customertype') != "etb";
                },*/
            },
            father_name: {
                // required: true
                required: function(element){
                    return $("#applicantId-"+element.id.split('-')[1]).attr('customertype') != "etb";
                },
                normalizer: function( value ) {
                return $.trim( value );
              },
            },
            id_proof_osv_check: {
                // required: true,
                /*required: function(element){
                    return $("#applicantId-"+element.id.split('-')[1]).attr('customertype') != "etb";
                },*/
                required: function(element){
                    return $("#id_proof_osv_check-"+element.id.split('-')[1]).prop('checked') == false;
                }
            },
            id_proof_image_front: {
                required: function(element){                    
                    // CG - to fix the OVD image validation issue  -- 150425
                    //return (($(element).val()==="") &&
                    return ( 
                            (!hasValidImageSrc($("#id_proof_image_front-"+element.id.split('-')[1]))) &&  
                        ($("#proof_of_identity-"+element.id.split('-')[1]).val() != 9) && 
                            ($("#applicantId-"+element.id.split('-')[1]).attr('customertype') != "etb")
                        );
                },
            },
            proof_of_address: {
                // required: true
                required: function(element){
                    return $("#applicantId-"+element.id.split('-')[1]).attr('customertype') != "etb";
                },
            },
            add_proof_card_number: {
                // required: true
                required: function(element){
                    return $("#applicantId-"+element.id.split('-')[1]).attr('customertype') != "etb";
                },
            },
            add_proof_image_front: {
                required: function(element){
                    // return (($("#add_proof_image_front-"+element.id.split('-')[1]).find("img").attr("src") == '') && 
                    //     //Proof of identity 9 is for E-KYC proof_of_address
                    //     ($("#proof_of_identity-"+element.id.split('-')[1]).val() != 9 && $("#proof_of_address-"+element.id.split('-')[1]).val() != 9) && 
                    //     ($("#applicantId-"+element.id.split('-')[1]).attr('customertype') != "etb"));                    
                    return ( 
                        (!hasValidImageSrc($("#add_proof_image_front-"+element.id.split('-')[1]))) &&  
                        ($("#proof_of_identity-"+element.id.split('-')[1]).val() != 9) && ($("#proof_of_address-"+element.id.split('-')[1]).val() != 9)&& 
                        ($("#applicantId-"+element.id.split('-')[1]).attr('customertype') != "etb")
                    );
                },
            },
            passport_driving_expire: {
                 required: true
             },
            passport_driving_expire_permanent: {
                required: true
            },

            id_psprt_dri_issue:{
                required: true
            },
            add_psprt_dri_issue:{
                required: true
            },
            per_address_line1: {
                // required: true
                required: function(element){
                    return $("#applicantId-"+element.id.split('-')[1]).attr('customertype') != "etb";
                },
                normalizer: function( value ) {
                return $.trim( value );
              },
            },
            per_landmark: {
                // required: true
                required: function(element){
                    return ($("#applicantId-"+element.id.split('-')[1]).attr('customertype') != "etb" && 
                        $("#per_landmark-"+element.id.split('-')[1]).val().length <= 45);
                },
                maxlength:45,
                normalizer: function( value ) {
                return $.trim( value );
              },
            },
            per_address_line2: {
                // required: true
                required: function(element){
                    return $("#applicantId-"+element.id.split('-')[1]).attr('customertype') != "etb";
                },
                normalizer: function( value ) {
                return $.trim( value );
              },
            },
            per_country: {
                required: true
            },
            per_state: {
                // required: true
                required: function(element){
                    return $("#applicantId-"+element.id.split('-')[1]).attr('customertype') != "etb";
                },
            },
            per_city: {
                // required: true
                required: function(element){
                    return $("#applicantId-"+element.id.split('-')[1]).attr('customertype') != "etb";
                },
            },
            per_pincode: {
                // required: true,
                required: function(element){
                    return $("#applicantId-"+element.id.split('-')[1]).attr('customertype') != "etb";
                },
                maxlength:6,
            },
            add_proof_osv_check: {
                required: true,
            },
            entity_add_proof_osv_check: {
                required: true,
            },
            proof_of_current_address: {
                required: function(element){
                    return (($("#address_flag-"+element.id.split('-')[1]).prop('checked') == false) && 
                        ($("#applicantId-"+element.id.split('-')[1]).attr('customertype') != "etb"));
                }
            },
            current_add_proof_card_number: {
                required: function(element){
                    return (($("#address_flag-"+element.id.split('-')[1]).prop('checked') == false) && 
                        ($("#applicantId-"+element.id.split('-')[1]).attr('customertype') != "etb"));
                }
            },
            current_add_proof_image:{
                required: function(element){
                    return (($("#current_add_proof_image-"+element.id.split('-')[1]).find("img").length == 0) && 
                        //Proof of identity 9 is for E-KYC
                        ($("#proof_of_identity-"+element.id.split('-')[1]).val() != 9) && 
                        ($("#applicantId-"+element.id.split('-')[1]).attr('customertype') != "etb"));
                }
            },
            current_address_line1: {
                required: function(element){
                    return (($("#address_flag-"+element.id.split('-')[1]).prop('checked') == false) && ($("#applicantId-"+element.id.split('-')[1]).attr('customertype') != "etb"));
                },
                normalizer: function( value ) {
                return $.trim( value );
              },
            },
            current_address_line2: {
                required: function(element){
                    return (($("#address_flag-"+element.id.split('-')[1]).prop('checked') == false) && ($("#applicantId-"+element.id.split('-')[1]).attr('customertype') != "etb"));
                }
            },
            current_landmark: {
                required: function(element){

                    return (($("#address_flag-"+element.id.split('-')[1]).prop('checked') == false) && 
                        ($("#applicantId-"+element.id.split('-')[1]).attr('customertype') != "etb"));
                },
                normalizer: function( value ) {
                return $.trim( value );
              },
            },
            current_country: {
                required: function(element){
                    return (($("#address_flag-"+element.id.split('-')[1]).prop('checked') == false) && ($("#applicantId-"+element.id.split('-')[1]).attr('customertype') != "etb"));
                }
            },
            current_state: {
                required: function(element){
                    return (($("#address_flag-"+element.id.split('-')[1]).prop('checked') == false) && ($("#applicantId-"+element.id.split('-')[1]).attr('customertype') != "etb"));
                }
            },
            current_city: {
                required: function(element){
                    return (($("#address_flag-"+element.id.split('-')[1]).prop('checked') == false) && ($("#applicantId-"+element.id.split('-')[1]).attr('customertype') != "etb"));
                }
            },
            current_pincode: {
                required: function(element){
                    return (($("#address_flag-"+element.id.split('-')[1]).prop('checked') == false) && ($("#applicantId-"+element.id.split('-')[1]).attr('customertype') != "etb"));
                },
                maxlength:6,
            },
            entity_pincode: {
                required: true,
            },
            entity_landmark: {
                required: true,
            },
            entity_state: {
                required: true,
            },
            entity_city: {
                required: true,
            },
            entity_country: {
                required: true,
            },
            entity_address_line1: {
                required: true,
            },
            entity_address_line2: {
                required: true,
            },
            entity_name: {
                required: true,
            },
            entity_add_proof_image: {
                required: function(element){
                    return ($("#entity_add_proof_image").find("img").length == 0);
                },
                
            },
            proof_of_entity_address:{
                required: true,
            },
            entity_add_proof_card_number:{
                required: true,
            },
            cur_add_proof_osv_check: {
                required: true,
            },
            customer_image_osv_check: {
                required: true,
            },
            customer_signature_osv_check: {
                required: true,
            },
            customer_photo: {
                required: function(element){
                    if($('#callCenterFlow').val() == 1){   
                        return false;
                    }else{
                      return $("#customer_photo").find("img").length == 0;
                    }
                }
            },
            mode_of_operation: {
                required: function(element){
                    if($('#callCenterFlow').val() == 1){   
                        return false;
                    }else{
                      return true;
                    }
                }
               
            },
            signature_type: {
                required: function(element){
                    if($('#callCenterFlow').val() == 1){   
                        return false;
                    }else{
                      return true;
                    }
                }
               
            },
            entity_mobile_number:{
                required: true,
            },
			entity_email_id:{
                required: true,
                },
            coparcenar_name: {
                required: function (element){
                    if(_accounDetails.constitution == 'NON_IND_HUF'){
                        return true;
                    }else{
                        return false;
                    }
                },
            },
            huf_relation: {
                required: function (element){
                    if(_accounDetails.constitution == 'NON_IND_HUF'){
                        return true;
                    }else{
                        return false;
                    }
                },
            },
            coparcener_type: {
                required: function (element){
                    if(_accounDetails.constitution == 'NON_IND_HUF'){
                        return true;
                    }else{
                        return false;
                    }
                },
            },
            dob: {
                required: function (element){
                    if(_accounDetails.constitution == 'NON_IND_HUF'){
                        return true;
                    }else{
                        return false;
                    }
                },
            },
		}, 
		  messages: {
            dob : {
                required: "Enter date of birth"
            },
            huf_relation : {
                required: "Select relation"
            },
            coparcener_type : {
                required: "Select type"
            },
            coparcenar_name : {
                required: "Enter Name"
            },
            proof_of_identity: {
                required: "Select Proof of identity"
            },
            id_proof_card_number: {
                required: "Please Enter Valid Number"
            },
            gender: {
                required: "Select Gender"
            },
            title: {
                required: "Select Title"
            },
            first_name: {
                required: "Please Enter Name"
            },
            last_name: {
                required: "Please Enter Last Name"
            },
            short_name: {
                required: "Please Enter Short Name"
            },
            mothers_maiden_name: {
                required: "Please Enter Mother's Maiden Name"
            },
            mother_full_name: {
                required: "Please Enter Mother's Full Name"
            },
            father_spouse: {
                required: "Please Select Father/Spouse Name"
            },
            father_name: {
                required: "Please Enter Father/Spouse Name"
            },
            id_proof_osv_check: {
                required: "Kindly confirm having sighted and verified original OVD document"
            },
            id_proof_image_front: {
                required: "Please Upload Id Proof"
            },
            proof_of_address: {
                required: "Please Select Proof of Address"
            },
            add_proof_card_number: {
                required: "Please Enter Valid Number"
            },
            add_proof_image_front: {
                required: "Please Upload Image"
            },
             passport_driving_expire: {
                required: "Please Select Expiry Date"
            },
             passport_driving_expire_permanent: {
                required: "Please Select Expiry Date"
            },
            id_psprt_dri_issue:{
             required: "Please Select issue Date"
            },
            add_psprt_dri_issue:{
             required: "Please Select issue Date"
            },
            per_address_line1: {
                required: "Please Enter Address Line1"
            },
              per_landmark: {
                required: "Please Enter Landmark"
            },
            per_address_line2: {
                required: "Please Enter Address Line1"
            },
            per_country: {
                required: "Please Enter Country"
            },
            per_state: {
                required: "Please Enter State"
            },
            per_city: {
                required: "Please Enter City"
            },
            per_pincode: {
                required: "Please Enter Pincode",
                maxlength:"Please Enter Correct Pincode"
            },
            add_proof_osv_check: {
                required: "Kindly confirm having sighted and verified original OVD document"
            },
            proof_of_current_address: {
                required: "Please Enter Proof of Current Address"
            },
            current_add_proof_card_number: {
                required: "Please Enter Valid Number"
            },
            current_add_proof_image: {
                required: "Upload Current Address Proof"
            },
            current_address_line1: {
                required: "Please Enter Current Address Line1"
            },
            current_country: {
                required: "Please Enter Current Country"
            },
            current_address_line2: {
                required: "Please Enter Current Address Line2"
            },
              current_landmark: {
                required: "Please Enter Landmark"
            },
            current_state: {
                required: "Please Select Current State"
            },
            current_city: {
                required: "Please Select Current City"
            },
            current_pincode: {
                required: "Please Enter Current Pincode",                
                maxlength:"Please Enter Correct Pincode"
            },

            entity_add_proof_image: {
                required: "Please Upload Entity Address Proof Image",
            },
            proof_of_entity_address:{
                required: "Please Select Proof of Entity Address",
            },
            entity_add_proof_card_number:{
                required: "Please Enter Entity Address Proof Card Number",
            },
            entity_name: {
                required: "Please Enter Entity Name",
            },
            entity_address_line1: {
                required: "Please Enter Entity Address Line 1",
            },
            entity_address_line2: {
                required: "Please Enter Address Line 2",
            },
            entity_country: {
                required: "Please Select Entity Country",
            },
            entity_pincode:{
                required: "Please Enter Entity Pincode"
            },
            entity_state: {
                required: "Please Select Entity State",
            },
            entity_city: {
                required: "Please Select Entity City",
            },
            entity_landmark:{
                required: "Please Enter Entity Landmark"
            },
            cur_add_proof_osv_check: {
                required: "Kindly confirm having sighted and verified original OVD document",
            },
            entity_add_proof_osv_check:{
                required: "Kindly confirm having sighted and verified original OVD document",
            },
            customer_image_osv_check: {
                required: "Kindly confirm having sighted and verified original OVD document",
            },
            customer_signature_osv_check: {
                required: "Kindly confirm having sighted and verified original OVD document",
            },
            customer_photo: {
                required: "Please Upload Customer Photo and Signature"
            },
            mode_of_operation: {
                required: "Select Mode of Operation"
            },
            signature_type: {
                required: "Select Signature Type"
            },
            entity_mobile_number:{
                required: "Please Enter Mobile Number",
                mobile: "Please Enter Correct Mobile Number"
            },
            entity_email_id:{
                 required: "Please Enter E-mail Id",
                 email: "Please Enter Correct E-mail Id"
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

    jQuery.validator.addMethod("mobile", function(value, element)
    {
        return this.optional(element) || /^^[6-9]\d{9}$/.test(value);
    }, "Please enter a valid mobile number");

    jQuery.validator.addMethod("entity_email_id", function(value,element)
    {
            return this.optional(element) || /(^[a-zA-Z0-9\+_.\-]+)(\.[a-zA-z0-9\+_.\-])*@([a-zA-Z0-9\-]+\.)+[a-zA-Z]{2,6}$/.test(value);
    }, "Please enter a valid email Id");

    jQuery.validator.addMethod("gst", function(value,element){
        if($('#proof_of_entity_address-2').val() == '5'){
            return this.optional(element) || /[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{2}[0-9A-Z]{1}$/.test(value);
        }
        return true;
    }, "Please enter a valid GST Number");


    
    jQuery.validator.addMethod("genderValidation", function(value, element) {
        var gender = $('input[name="gender"]:checked').val();
        if((gender == "male") && (value == 1)){
            return true;
        }else if((gender == "female") && (value == 2)){
            return true;
        }else if((gender == "female") && (value == 3)){
            return true;
        }else{
           return false; 
        }
        return false;
    }, "Gender is not matched with title");


     _availbleOvdUi = $('.visibility_check').length;
     _ovd_form_check = [];
     form_check_ovd_var();
     
    $("body").on("change",".current_country,.per_country",function(){
        if($(this).val() != 1)
        {
            $("#"+$(this).attr('id').split('_')[0]+"_pincode-"+$(this).attr('id').split('-')[1]).val('');
            $("#"+$(this).attr('id').split('_')[0]+"_state-"+$(this).attr('id').split('-')[1]).val('');
            $("#"+$(this).attr('id').split('_')[0]+"_city-"+$(this).attr('id').split('-')[1]).val('');
        }
        return false;
    });

    $("body").on("change","input[id^='address_per_flag']",function(){
        if($(this).hasClass("address_per_flag_huf")){
            copy_kara_ovd_add($(this));
            return;
        }
    });

    function copy_kara_ovd_add(inp) {

        if(inp.prop("checked") == true){
            $("#address_per_line1-"+2).find('p').html($("#per_address_line1-"+1).val());
            $("#address_per_line2-"+2).find('p').html($("#per_address_line2-"+1).val());
            $("#per_a_landmark-"+2).find('p').html($("#per_landmark-"+1).val());
            $("#per_a_pincode-"+2).find('p').html($("#per_pincode-"+1).val());
            $("#per_a_country-"+2).find('p').html($("#per_country-"+1+" option:selected").text());
            $("#per_a_state-"+2).find('p').html($("#per_state-"+1).val());
            $("#per_a_city-"+2).find('p').html($("#per_city-"+1).val());
            $("#per_address_line1-"+2).val($("#per_address_line1-"+1).val());
            $("#per_address_line2-"+2).val($("#per_address_line2-"+1).val());
            $("#per_landmark-"+2).val($("#per_landmark-"+1).val());
            $("#per_pincode-"+2).val($("#per_pincode-"+1).val());
            $("#per_country-"+2).val($("#per_country-"+1).val());
            $("#per_state-"+2).val($("#per_state-"+1).val());
            $("#per_city-"+2).val($("#per_city-"+1).val());
            $("#per_landmark-"+2).val($("#per_landmark-"+1).val());
            $("#proof_of_address-"+2).val($("#proof_of_address-"+1).val());
            $("#add_proof_card_number-"+2).val($("#add_proof_card_number-"+1).val());
            $("#passport_driving_expire_permanent-"+2).val($("#passport_driving_expire_permanent-"+1).val());
            $("#add_psprt_dri_issue-"+2).val($("#add_psprt_dri_issue-"+1).val());

        $("#address_per_line1-"+2+",#address_per_line2-"+2+",#per_a_landmark-"+2+",#per_a_country-"+2+",#per_a_state-"+2+",#per_a_city-"+2+",#per_a_pincode-"+2).removeClass('display-none');
        $("#per_address_line1-"+2+",#per_address_line2-"+2+",#per_landmark-"+2+",#per_country-"+2+",#per_state-"+2+",#per_city-"+2+",#per_pincode-"+2+","+
                "#per_address_proof_number-"+2+",#proof_of_address-"+2).parent().addClass('display-none');
        $("#upload_per_address_proof-"+2).addClass('display-none');
        $(".per_address_proof_huf").addClass('display-none');
    }else{
        $("#per_address_line1-"+2+",#per_landmark-"+2+",#per_address_line2-"+2+",#per_country-"+2+",#per_state-"+2+",#per_city-"+2+",#per_pincode-"+2+","+
            "#per_address_proof_number-"+2+",#proof_of_address-"+2).parent().removeClass('display-none');
        $("#address_per_line1-"+2+",#address_per_line2-"+2+",#per_a_landmark-"+2+",#per_a_country-"+2+",#per_a_state-"+2+",#per_a_city-"+2+",#per_a_pincode-"+2).addClass('display-none');
        $("#upload_per_address_proof-"+2).removeClass('display-none');
        $(".per_address_proof_huf").removeClass('display-none');
    }
}
 
    function copy_form_kara(inp){
        if(inp.prop("checked") == true){
                $("#address_line1-"+2).find('p').html($("#current_address_line1-"+1).val());
                $("#address_line2-"+2).find('p').html($("#current_address_line2-"+1).val());
                $("#landmark-"+2).find('p').html($("#current_landmark-"+1).val());
                $("#pincode-"+2).find('p').html($("#current_pincode-"+1).val());
                $("#country-"+2).find('p').html($("#current_country-"+1+" option:selected").text());
                $("#state-"+2).find('p').html($("#current_state-"+1).val());
                $("#city-"+2).find('p').html($("#current_city-"+1).val());
                $("#landmark-"+2).find('p').html($("#current_landmark-"+1).val());

                $("#current_address_line1-"+2).val($("#current_address_line1-"+1).val());
                $("#current_address_line2-"+2).val($("#current_address_line2-"+1).val());
                $("#current_landmark-"+2).val($("#current_landmark-"+1).val());
                $("#current_pincode-"+2).val($("#current_pincode-"+1).val());
                $("#current_country-"+2).val($("#current_country-"+1).val());
                $("#current_state-"+2).val($("#current_state-"+1).val());
                $("#current_city-"+2).val($("#current_city-"+1).val());
                $("#current_landmark-"+2).val($("#current_landmark-"+1).val());
            
            $("#address_line1-"+2+",#address_line2-"+2+",#landmark-"+2+",#country-"+2+",#state-"+2+",#city-"+2+",#pincode-"+2).removeClass('display-none');
            $("#current_address_line1-"+2+",#current_address_line2-"+2+",#current_landmark-"+2+",#current_country-"+2+",#current_state-"+2+",#current_city-"+2+",#current_pincode-"+2+","+
                    "#cur_address_proof_number-"+2+",#cur_address_proof-"+2).parent().addClass('display-none');
            $("#upload_cur_address_proof-"+2).addClass('display-none');
        }else{
            $("#current_address_line1-"+2+",#current_landmark-"+2+",#current_address_line2-"+2+",#current_country-"+2+",#current_state-"+2+",#current_city-"+2+",#current_pincode-"+2+","+
                "#cur_address_proof_number-"+2+",#cur_address_proof-"+2).parent().removeClass('display-none');
            $("#address_line1-"+2+",#address_line2-"+2+",#landmark-"+2+",#country-"+2+",#state-"+2+",#city-"+2+",#pincode-"+2).addClass('display-none');
            $("#upload_cur_address_proof-"+2).removeClass('display-none');
        }
        
    }

    $("body").on("change","input[id^='address_flag']",function(){
        if($(this).hasClass("huf_input_same_add")){
            copy_form_kara($(this));
            return;
        }
        var applicantId = $(this).attr('id').split('-')[1];
        if($("#address_flag-"+applicantId).prop('checked') == true){
            $("#address_line1-"+applicantId).find('p').html($("#per_address_line1-"+applicantId).val());
            $("#address_line2-"+applicantId).find('p').html($("#per_address_line2-"+applicantId).val());
            $("#landmark-"+applicantId).find('p').html($("#per_landmark-"+applicantId).val());
            $("#pincode-"+applicantId).find('p').html($("#per_pincode-"+applicantId).val());
            $("#country-"+applicantId).find('p').html($("#per_country-"+applicantId+" option:selected").text());
            $("#state-"+applicantId).find('p').html($("#per_state-"+applicantId).val());
            $("#city-"+applicantId).find('p').html($("#per_city-"+applicantId).val());
            $("#landmark-"+applicantId).find('p').html($("#per_landmark-"+applicantId).val());
            $("#current_address_line1-"+applicantId).val($("#per_address_line1-"+applicantId).val());
            $("#current_address_line2-"+applicantId).val($("#per_address_line2-"+applicantId).val());
            $("#current_landmark-"+applicantId).val($("#per_landmark-"+applicantId).val());
            $("#current_pincode-"+applicantId).val($("#per_pincode-"+applicantId).val());
            $("#current_country-"+applicantId).val($("#per_country-"+applicantId).val());
            // $("#current_country-"+applicantId).val($("#per_country-"+applicantId+" option:selected").text());
            $("#current_state-"+applicantId).val($("#per_state-"+applicantId).val());
            $("#current_city-"+applicantId).val($("#per_city-"+applicantId).val());
            $("#current_landmark-"+applicantId).val($("#per_landmark-"+applicantId).val());
            $("#address_line1-"+applicantId+",#address_line2-"+applicantId+",#landmark-"+applicantId+",#country-"+applicantId+",#state-"+applicantId+",#city-"+applicantId+",#pincode-"+applicantId).removeClass('display-none');
            $("#current_address_line1-"+applicantId+",#current_address_line2-"+applicantId+",#current_landmark-"+applicantId+",#current_country-"+applicantId+",#current_state-"+applicantId+",#current_city-"+applicantId+",#current_pincode-"+applicantId+","+
                    "#cur_address_proof_number-"+applicantId+",#cur_address_proof-"+applicantId).parent().addClass('display-none');
            $("#upload_cur_address_proof-"+applicantId).addClass('display-none');
            // $("#upload_cur_address_proof-"+applicantId).css('opacity', '0.3');
            // $("#upload_cur_address_proof-"+applicantId).css('pointer-events', 'none');
            // $("#proof_of_current_address-"+applicantId).prop('disabled',true);
            // $("#current_add_proof_card_number-"+applicantId).prop('disabled',true);
        }else{
            $("#current_address_line1-"+applicantId+",#current_landmark-"+applicantId+",#current_address_line2-"+applicantId+",#current_country-"+applicantId+",#current_state-"+applicantId+",#current_city-"+applicantId+",#current_pincode-"+applicantId+","+
                "#cur_address_proof_number-"+applicantId+",#cur_address_proof-"+applicantId).parent().removeClass('display-none');
            $("#address_line1-"+applicantId+",#address_line2-"+applicantId+",#landmark-"+applicantId+",#country-"+applicantId+",#state-"+applicantId+",#city-"+applicantId+",#pincode-"+applicantId).addClass('display-none');
            $("#upload_cur_address_proof-"+applicantId).removeClass('display-none');
            // $("#upload_cur_address_proof-"+applicantId).css('opacity', '');
            // $("#upload_cur_address_proof-"+applicantId).css('pointer-events', '');
            // $("#proof_of_current_address-"+applicantId).prop('disabled',false);
            // $("#current_add_proof_card_number-"+applicantId).prop('disabled',false);
        }
    });

    
    $('#address_per_flag-2').change(function() {

        var id = $(this).attr('id');
        var applicantId = id.split('-')[1];

        let isChecked = $(this).prop('checked');
        $('#address_flag-2').click();
        let namecom = '<span class="lbl padding-8">Same as Karta Communication Address</span>';
        $('#name-container').html(namecom);
    });


    $("#ovdnext-2").click(function () {
      
        let isAddressChecked = $("#address_per_flag-2").prop('checked');

        if (!isAddressChecked) {
            if ($(this).hasClass("is_huf")) {
                copy_form_normal_ovd(2);
                let namecom = '<span class="lbl padding-8">Same as Registered Address</span>';
                $('#name-container').html(namecom);
            }
        }
    });
    
    function copy_form_normal_ovd(applicantId){

                $("#address_line1-"+applicantId).find('p').html($("#per_address_line1-"+applicantId).val());
                $("#address_line2-"+applicantId).find('p').html($("#per_address_line2-"+applicantId).val());
                $("#landmark-"+applicantId).find('p').html($("#per_landmark-"+applicantId).val());
                $("#pincode-"+applicantId).find('p').html($("#per_pincode-"+applicantId).val());
                $("#country-"+applicantId).find('p').html($("#per_country-"+applicantId+" option:selected").text());
                $("#state-"+applicantId).find('p').html($("#per_state-"+applicantId).val());
                $("#city-"+applicantId).find('p').html($("#per_city-"+applicantId).val());
                $("#landmark-"+applicantId).find('p').html($("#per_landmark-"+applicantId).val());
                $("#current_address_line1-"+applicantId).val($("#per_address_line1-"+applicantId).val());
                $("#current_address_line2-"+applicantId).val($("#per_address_line2-"+applicantId).val());
                $("#current_landmark-"+applicantId).val($("#per_landmark-"+applicantId).val());
                $("#current_pincode-"+applicantId).val($("#per_pincode-"+applicantId).val());
                $("#current_country-"+applicantId).val($("#per_country-"+applicantId).val());
                // $("#current_country-"+applicantId).val($("#per_country-"+applicantId+" option:selected").text());
                $("#current_state-"+applicantId).val($("#per_state-"+applicantId).val());
                $("#current_city-"+applicantId).val($("#per_city-"+applicantId).val());
                $("#current_landmark-"+applicantId).val($("#per_landmark-"+applicantId).val());
                $("#address_line1-"+applicantId+",#address_line2-"+applicantId+",#landmark-"+applicantId+",#country-"+applicantId+",#state-"+applicantId+",#city-"+applicantId+",#pincode-"+applicantId).removeClass('display-none');
                $("#current_address_line1-"+applicantId+",#current_address_line2-"+applicantId+",#current_landmark-"+applicantId+",#current_country-"+applicantId+",#current_state-"+applicantId+",#current_city-"+applicantId+",#current_pincode-"+applicantId+","+
                        "#cur_address_proof_number-"+applicantId+",#cur_address_proof-"+applicantId).parent().addClass('display-none');
                $("#upload_cur_address_proof-"+applicantId).addClass('display-none');
        }
    
      // Add ID Proof Number vertification, if any
    $('input[name="id_proof_card_number"]').focusout(function(){checkIDproofNumber()});
    $('input[name="add_proof_card_number"]').focusout(function(){checkADDproofNumber()});

    $("body").on("click",".documents",function(){
        if (!addOvdDocumentObj.form()) { // Not Valid
            return false;
        }else{

        }
        $('.nav-tabs').find('a').removeClass('active').attr("aria-expanded",false);
        $('.documentstab').removeClass('active');
        $(this).addClass("active");
        $(this).attr("aria-expanded",true);
        $("#"+$(this).data("id")).addClass('active');
        return false;
    });


    $("body").on("click",".identity",function(){
			
		 // Avoid going to next tab if ID proof not valid.
		 if (checkIDproofNumber() == false) {
			return false;
		 }else{
		 
		 }

        if (!addOvdDocumentObj.form()) { // Not Valid
            return false;
        }else{

        }
      
        _ovd_form_check[_globalCurrentApplicant-1]['ovd_id-'+_globalCurrentApplicant] = true;   
       
        if(_accounDetails.constitution != 'NON_IND_HUF'){ 

        // $('a[data-id="'+$(this).attr('tab')+'"]').removeClass('text-muted-lnavs').unbind("click", false);        
        // 22May23 - For BS5 - commented below line
        //$('a[data-id="'+$(this).attr('tab')+'"]').click();
        // $('[href=#'+$(this).attr('tab')+']').tab('show');
        var accountId = $(this).attr("data-id").split('-')[1];  
       
        if($("#applicantId-"+accountId).attr('customertype') !='etb'){
        var tab= $(this).attr('tab');    
        var proof_of_identity=$('#proof_of_identity-'+accountId).val();        
        var proof_card_number=rsenc($('#id_proof_card_number-'+accountId).inputmask().val(), $('meta[name="cookie"]').attr('content').split('.')[2]);
        
        var identityDetailsObject = [];
        identityDetailsObject.data = {};

        if(proof_of_identity == 9){			
            if(typeof $("#id_proof_card_number-"+accountId).attr('data-ekyc-aadharrefno')!='undefined'){
                identityDetailsObject.data['id_proof_aadhaar_ref_number'] = $("#id_proof_card_number-"+accountId).attr('data-ekyc-aadharrefno'); 
                // ovdDetailsObject.data[accountId]['add_proof_aadhaar_ref_number'] = $("#id_proof_card_number-"+accountId).attr('data-ekyc-aadharrefno');  
            }   
        }	
        identityDetailsObject.data['proofOfIdentity'] = proof_of_identity;
        identityDetailsObject.data['tab'] =tab;       
        identityDetailsObject.data['applicantID'] =accountId;
        identityDetailsObject.data['proofcardNumber'] =proof_card_number;
        identityDetailsObject['url'] = '/etbntb_check_via_idovd';
        identityDetailsObject.data['functionName'] = 'identityDetailsCallBack';

        crudAjaxCall(identityDetailsObject);
        }
        else{
        $('a[data-id="'+$(this).attr('tab')+'"]').removeClass('text-muted-lnavs').unbind("click", false);        
        // 22May23 - For BS5 - commented below line
        // $('a[data-id="'+$(this).attr('tab')+'"]').click();   
        $('[href="#' + $(this).attr('tab') + '"]').tab('show');
    
        }
        }
        else{
        $('a[data-id="'+$(this).attr('tab')+'"]').removeClass('text-muted-lnavs').unbind("click", false);       
        $('[href="#' + $(this).attr('tab') + '"]').tab('show');

        }
       

        return false;
    });
    $("body").on("click",".huf_identity-2",function(){

        if(_accounDetails.constitution == 'NON_IND_HUF'){
       
            if ($('#address_per_flag-'+_globalCurrentApplicant).prop('checked') == true) {
            if (_global_is_review == 1) {
                 $('#address_per_flag-'+_globalCurrentApplicant).removeAttr('disabled');
            }
            $('#address_per_flag-'+_globalCurrentApplicant).click();   
            $('#address_per_flag-'+_globalCurrentApplicant).click();
            if (_global_is_review == 1) {
               // alert('help');
                $('#address_per_flag-'+_globalCurrentApplicant).attr("disabled", true);
            }
       }
    }
    });
    
    $("body").on("click",".asperovd",function(){
        
        var countcheck = 0;
        var id = $(this).attr("id").split('-')[1]; 
        var addressline1 = $('#per_address_line1-'+id).val().trim();
        var addressline2 = $('#per_address_line2-'+id).val().trim();
        var addressline3 = $('#per_landmark-'+id).val().trim();
        
        var addressline1LengthWithoutSpaces = addressline1.replace(/\s/g, '').length;
        var addressline2LengthWithoutSpaces = addressline2.replace(/\s/g, '').length;
        var addressline3LengthWithoutSpaces = addressline3.replace(/\s/g, '').length;

        var totalCount = addressline1LengthWithoutSpaces + addressline2LengthWithoutSpaces + addressline3LengthWithoutSpaces;

        if(_accounDetails.constitution == 'NON_IND_HUF'){
            
           if(_customerDetails[id].is_new_customer == '1'){
            
            if((totalCount) <= 18) 
            {
                $(".error-message").css("display", "");        
                    countcheck++;
            }else{        
                    $(".error-message").css("display", "none");  
                }
        
            if(countcheck>0){
                return false;
            }
        }
    }
         // Avoid going to next tab if ID proof not valid.
         if (checkIDproofNumber() == false) {
            return false;
         }else{
         
         }

        if (!addOvdDocumentObj.form()) { // Not Valid
            return false;
        }else{

        }    
      

        _ovd_form_check[_globalCurrentApplicant-1]['ovd_permanant-'+_globalCurrentApplicant] = true; 

        var validCountry = checkValidCountry($('#per_country-'+_globalCurrentApplicant).val());
        if (!validCountry) {
            $.growl({message:"Selected country not permitted"},{type: "warning"});
            return false;
        }
    
        if ($('#address_flag-'+_globalCurrentApplicant).prop('checked') == true) {
             if (_global_is_review == 1) {
                  $('#address_flag-'+_globalCurrentApplicant).removeAttr('disabled');
             }
             $('#address_flag-'+_globalCurrentApplicant).click();   
             $('#address_flag-'+_globalCurrentApplicant).click();
             if (_global_is_review == 1) {
                 $('#address_flag-'+_globalCurrentApplicant).attr("disabled", true);
             }
        }else{

        }

        
        /*checkbox disabled*/        
        var id = $(this).attr("data-seq");  
        var addressline1 = $('#per_address_line1-'+id).val().trim();
        var addressline2 = $('#per_address_line2-'+id).val().trim();
        var addressline3 = $('#per_landmark-'+id).val().trim();

        var addressline1LengthWithoutSpaces = addressline1.replace(/\s/g, '').length;
        var addressline2LengthWithoutSpaces = addressline2.replace(/\s/g, '').length;
        var addressline3LengthWithoutSpaces = addressline3.replace(/\s/g, '').length;
        var totalCount = addressline1LengthWithoutSpaces + addressline2LengthWithoutSpaces + addressline3LengthWithoutSpaces;
        
        if(totalCount <= 18) 
        {
            $(".ekyccount-"+id).css("display", "none");        
        }else{        
            $(".ekyccount-"+id).css("display", "block");    
        }

        if(_accounDetails.constitution != 'NON_IND_HUF'){ 

        if($("#applicantId-"+id).attr('customertype') !='etb'){

        var tab= $(this).attr('tab');      
        var accountId = $(this).attr("id").split('-')[1];
        var proof_of_identity=$('#proof_of_address-'+accountId).val();
        var proof_card_number=rsenc($('#add_proof_card_number-'+accountId).inputmask().val(), $('meta[name="cookie"]').attr('content').split('.')[2]);
              
        var identityDetailsObject = [];
        identityDetailsObject.data = {};        
        identityDetailsObject.data['proofOfAddress'] = proof_of_identity;

        if($('#proof_of_identity-'+accountId).val() == 9){			
            if(typeof $("#id_proof_card_number-"+accountId).attr('data-ekyc-aadharrefno')!='undefined'){               
                identityDetailsObject.data['add_proof_aadhaar_ref_number'] = $("#id_proof_card_number-"+accountId).attr('data-ekyc-aadharrefno'); 
                identityDetailsObject.data['proofOfAddress']= $('#proof_of_identity-'+accountId).val();
            }   
        }	
       
        identityDetailsObject.data['tab'] =tab;
        identityDetailsObject.data['applicantID'] =accountId;        
        identityDetailsObject.data['addproofcardNumber'] =proof_card_number;
        identityDetailsObject['url'] = '/etbntb_check_via_idovd';
        identityDetailsObject.data['functionName'] = 'identityDetailsCallBack';
        crudAjaxCall(identityDetailsObject);
        }
        else{

        $('a[data-id="'+$(this).attr('tab')+'"]').removeClass('text-muted-lnavs').unbind("click", false);
        // 22May23 - For BS5 - commented below line
        //$('a[data-id="'+$(this).attr('tab')+'"]').click();
        $('[href="#' + $(this).attr('tab') + '"]').tab('show');
  
        }

    }
    else{
        $('a[data-id="'+$(this).attr('tab')+'"]').removeClass('text-muted-lnavs').unbind("click", false);
        // 22May23 - For BS5 - commented below line
        //$('a[data-id="'+$(this).attr('tab')+'"]').click();
        $('[href="#' + $(this).attr('tab') + '"]').tab('show');
    }
        return false;
    });


    $("body").on("click",".check-ovd-applicant",function(){
            var countcheck = 0;
            var id = $(this).attr("id").split('-')[1]; 
            var addressline1 = $('#current_address_line1-'+id).val().trim();
            var addressline2 = $('#current_address_line2-'+id).val().trim();
            var addressline3 = $('#current_landmark-'+id).val().trim();
            
            var addressline1LengthWithoutSpaces = addressline1.replace(/\s/g, '').length;
            var addressline2LengthWithoutSpaces = addressline2.replace(/\s/g, '').length;
            var addressline3LengthWithoutSpaces = addressline3.replace(/\s/g, '').length;

            var totalCount = addressline1LengthWithoutSpaces + addressline2LengthWithoutSpaces + addressline3LengthWithoutSpaces;

             if(_customerDetails[id].is_new_customer == '1'){
                
            if((totalCount) <= 18) 
            {
                $(".error-message").css("display", "");        
                countcheck++;
            }else{        
                $(".error-message").css("display", "none");  
            }
             }
    
        if(countcheck>0){
            return false;
        }
        

        if(!addOvdDocumentObj.form()) { // Not Valid
            return false;
        }else{

        }
        _ovd_form_check[_globalCurrentApplicant-1]['ovd_communication-'+_globalCurrentApplicant] = true;   

        // $('a[data-id="'+$(this).attr('id')+'"]').removeClass('text-muted-lnavs').unbind("click", false);
        // $('a[data-id="'+$(this).attr('id')+'"]').click();
        

        if(_global_is_review != 1){
        var applicantId = $(this).attr("id").split('-')[1];
         applicantId++;
        if($('#gender-'+applicantId).is(':visible')){
             
               var oldtitleValue = $('#title-'+applicantId).val();
                setTimeout(function(){                     
                     $('#gender-'+applicantId).trigger('change');
                     $('#title-'+applicantId).val(oldtitleValue).trigger('change');
               },1000);
            }
        }
        if(_accounDetails.constitution != 'NON_IND_HUF'){ 
        var accountId = $(this).attr("data-id").split('-')[1];      
        if($("#applicantId-"+accountId).attr('customertype') !='etb'){
        var tab= $(this).attr('id');    
        var proof_of_identity=$('#proof_of_identity-'+accountId).val();        
        var proof_card_number=rsenc($('#id_proof_card_number-'+accountId).inputmask().val(), $('meta[name="cookie"]').attr('content').split('.')[2]);
        var proof_of_address=$('#proof_of_address-'+accountId).val();
        var addressproof_card_number=rsenc($('#add_proof_card_number-'+accountId).inputmask().val(), $('meta[name="cookie"]').attr('content').split('.')[2]);
        
        var ovdDetailsObject = [];
        ovdDetailsObject.data = {};

        if(proof_of_identity == 9){			
            if(typeof $("#id_proof_card_number-"+accountId).attr('data-ekyc-aadharrefno')!='undefined'){
                ovdDetailsObject.data['id_proof_aadhaar_ref_number'] = $("#id_proof_card_number-"+accountId).attr('data-ekyc-aadharrefno');  
                ovdDetailsObject.data['add_proof_aadhaar_ref_number'] = $("#id_proof_card_number-"+accountId).attr('data-ekyc-aadharrefno');  
                ovdDetailsObject.data['proofOfAddress']= $('#proof_of_identity-'+accountId).val();  
            }   
        }	
        ovdDetailsObject.data['proofOfIdentity'] = proof_of_identity;
        ovdDetailsObject.data['proofOfAddress'] = proof_of_address;
        ovdDetailsObject.data['tab'] =tab;       
        ovdDetailsObject.data['applicantID'] =accountId;
        ovdDetailsObject.data['proofcardNumber'] =proof_card_number;
        ovdDetailsObject.data['addproofcardNumber'] =addressproof_card_number;
        ovdDetailsObject['url'] = '/etbntb_check_via_idovd';
        ovdDetailsObject.data['functionName'] = 'ovdETBNTBDetailsCallBack';
        crudAjaxCall(ovdDetailsObject);

        }
        else{
        $('a[data-id="'+$(this).attr('id')+'"]').removeClass('text-muted-lnavs').unbind("click", false);
        $('a[data-id="'+$(this).attr('id')+'"]').click();
        
        }
     }
     else{
        $('a[data-id="'+$(this).attr('id')+'"]').removeClass('text-muted-lnavs').unbind("click", false);
        $('a[data-id="'+$(this).attr('id')+'"]').click();
     }

        return false;
        
    });

    $("body").on("click",".check-all-ovd-applicant",function(){
        var countCheck = 0;
        
        $(".OvdDocumentForm").each(function(){
            var id = $(this).attr("id");     
        var addressline1 = $('#current_address_line1-'+id).val().trim();
        var addressline2 = $('#current_address_line2-'+id).val().trim();
            var addressline3 = $('#current_landmark-'+id).val().trim();
        
        var addressline1LengthWithoutSpaces = addressline1.replace(/\s/g, '').length;
        var addressline2LengthWithoutSpaces = addressline2.replace(/\s/g, '').length;
            var addressline3LengthWithoutSpaces = addressline3.replace(/\s/g, '').length;
                
            var totalCount = addressline1LengthWithoutSpaces + addressline2LengthWithoutSpaces + addressline3LengthWithoutSpaces;
           if(_customerDetails[id].is_new_customer == '1'){
        if ((totalCount) <= 18) 
        {
            $(".error-message").css("display", "");        
                countCheck++;
        } 
        else{        
            $(".error-message").css("display", "none");  
        }
           }
        });

        if(countCheck >0){
            return false;
        }

        if (!addOvdDocumentObj.form()) { // Not Valid
           
            return false;
        }else{

        }

        _ovd_form_check[_globalCurrentApplicant-1]['ovd_communication-'+_globalCurrentApplicant] = true;   

        var validCountry = checkValidCountry($('#current_country-'+_globalCurrentApplicant).val());
        if (!validCountry) {
            $.growl({message:"Selected country not permitted"},{type: "warning"});
            return false;
        }

        var accountHolders =  parseInt(_availbleOvdUi/4);
       
        for(var chk=1; chk<=accountHolders; chk++){
            if(!$(this).hasClass("is_huf")){
               if(!form_check_ovd(chk)){
                return false;
               }else{

               }
        }
        }

               $('#declarationsForm .nav-item').css('pointer-events', 'auto');
        // $('a[data-id="'+$(this).attr('tab')+'"]').removeClass('text-muted-lnavs').unbind("click", false);
        // $('a[data-id="'+$(this).attr('tab')+'"]').click();
        if(_accounDetails.constitution != 'NON_IND_HUF'){ 
        var accountId = $(this).attr("data-id").split('-')[1];      
        if($("#applicantId-"+accountId).attr('customertype') !='etb'){
        var tab= $(this).attr('tab');    
        var proof_of_identity=$('#proof_of_identity-'+accountId).val();        
        var proof_card_number=rsenc($('#id_proof_card_number-'+accountId).inputmask().val(), $('meta[name="cookie"]').attr('content').split('.')[2]);
        var proof_of_address=$('#proof_of_address-'+accountId).val();
        var addressproof_card_number=rsenc($('#add_proof_card_number-'+accountId).inputmask().val(), $('meta[name="cookie"]').attr('content').split('.')[2]);
        
        var ovdDetailsObject = [];
        ovdDetailsObject.data = {};

        if(proof_of_identity == 9){			
            if(typeof $("#id_proof_card_number-"+accountId).attr('data-ekyc-aadharrefno')!='undefined'){
                ovdDetailsObject.data['id_proof_aadhaar_ref_number'] = $("#id_proof_card_number-"+accountId).attr('data-ekyc-aadharrefno');  
                ovdDetailsObject.data['add_proof_aadhaar_ref_number'] = $("#id_proof_card_number-"+accountId).attr('data-ekyc-aadharrefno');  
                ovdDetailsObject.data['proofOfAddress']= $('#proof_of_identity-'+accountId).val();  
            }   
        }	
        ovdDetailsObject.data['proofOfIdentity'] = proof_of_identity;
        ovdDetailsObject.data['proofOfAddress'] = proof_of_address;
        ovdDetailsObject.data['tab'] =tab;       
        ovdDetailsObject.data['applicantID'] =accountId;
        ovdDetailsObject.data['proofcardNumber'] =proof_card_number;
        ovdDetailsObject.data['addproofcardNumber'] =addressproof_card_number;
        ovdDetailsObject['url'] = '/etbntb_check_via_idovd';
        ovdDetailsObject.data['functionName'] = 'ovdETBNTBDetailsCallBack';
        crudAjaxCall(ovdDetailsObject);

        }
        else{
        $('a[data-id="'+$(this).attr('tab')+'"]').removeClass('text-muted-lnavs').unbind("click", false);
        $('a[data-id="'+$(this).attr('tab')+'"]').click();        
        }
    }
    else{
        $('a[data-id="'+$(this).attr('tab')+'"]').removeClass('text-muted-lnavs').unbind("click", false);
        $('a[data-id="'+$(this).attr('tab')+'"]').click();    
    }


        return true;    
    });
    $('body').on('click','.checkEtbNtbidovd',function(){

        var accountHolders = parseInt(_availbleOvdUi/4);        
        
        for (var i = 1; i <= accountHolders; i++) {
           idSequence = i;
           
        var proofOfIdentity= $('#proof_of_identity-'+idSequence).val();          
        
                
        }       
      
        var idOvdDetailsObject = [];
        idOvdDetailsObject.data = {};
        idOvdDetailsObject.data['proofOfIdentity'] = proofOfIdentity;
        idOvdDetailsObject['url'] = '/etbntb_check_via_idovd';
            
        crudAjaxCall(idOvdDetailsObject);
    
        return false;
            
    });


    $("body").on("click",".saveOvdDetails",function(){
				
        if (!addOvdDocumentObj.form()) { // Not Valid
            return false;
        }else{

        }
        var nomatchkyccount = 0;
        var ovdNumbers = [];
        var accountHolders = parseInt(_availbleOvdUi/4);

         for (var i = 1; i <= accountHolders; i++) {
            idSequence = i;
            var customerType = $("#applicantId-"+idSequence).attr('customertype');
            if(!checkImagesAreOk('ovd_details', idSequence)){
                return false;
            };
            var idProof = $("#id_proof_card_number-"+idSequence).val();			            
			if (idProof != '') {
                ovdNumbers.push(idProof);
            }
			
			if(($('#proof_of_identity-'+idSequence).val()==9) && ((typeof $("#id_proof_card_number-"+idSequence).attr('data-ekyc-aadharrefno')=='undefined') || ($("#id_proof_card_number-"+idSequence).attr('data-ekyc-aadharrefno')==''))){
				$.growl({message:"eKYC details not validated for applicant "+idSequence+". Please submit eKYC reference and try again."},{type: "warning"});
				return false;
			}	
            
            var checkekycBoth = 'N';

            if($('#proof_of_identity-'+idSequence).val() == 9 || $('#proof_of_address-'+idSequence).val() == 9){
                checkekycBoth = 'Y';
            }

			if((checkekycBoth != 'Y') && ($("#proof_of_current_address-"+idSequence).val() == 29)){
                $.growl({message:"Only eKyc applicant are permitted Others as current address proof."},{type: "warning"});
                return false;
            }	

            if (isGenderTitleBlank(idSequence)) {
                $.growl({message:"Error! Unable to find Gender/ Title for applicant "+idSequence},{type: "warning"});
                return false;
            }
        }

        var entityaddressImage  = $('#entity_add_proof_image').find('.uploaded_image').attr('src');
        var imageOk_ = true;
        if(_accounDetails.account_type == '2' && _accounDetails.flow_tag_1 == 'PROP'){
            if (typeof(entityaddressImage) == 'undefined' && customerType != "etb") {
                $.growl({message: "Please upload mandatory Entity address proof image for applicant"},{type: "warning"});
                imageOk_ = false;
            }
        }

        if(customerType != 'etb'){
            
            if(isIdentityProofDuplicate(ovdNumbers)){
                 $.growl({message:"Validation Failed: Same Identity or Address Proof card used across applicants"},{type: "warning"});
                return false;
            }
        }

        var ovdDetailsObject = [];
        ovdDetailsObject.data = {};
        ovdDetailsObject.data['OVDS'] = {};

        ovdDetailsObject['url'] = '/bank/saveovddetails';

         $('#declarationsForm .nav-item').css('pointer-events', 'auto');

        $(".OvdDocumentForm").each(function(){
            var accountId = $(this).attr("id");
            ovdDetailsObject.data[accountId] = {};
            ovdDetailsObject.data[accountId]['OVDS']= {};
            ovdDetailsObject.data['Entity']= {};
            ovdDetailsObject.data['HUF']= {};
            ovdDetailsObject.data['HUF_COP']= {};

            if($(".AddHufCoparcenarNameField").length!=0 &&_accounDetails.constitution == 'NON_IND_HUF' && accountId == 2){
                let co_name_arr = [];
                $(".AddHufCoparcenarNameField").each(function(){
                    let id = $(this).attr("id");
                    let id_arr = id.split("-");
                    let id_num = id_arr[0].replace("coparcenar_name_field","");
                    
                    let name = $(this).val();
                    let dob = $(`#coparcenar_dob_field${id_num}-${accountId}`).val();
                    let rel = $(`#coparcenar_rel_field${id_num}-${accountId}`).val();
                    let cop_type = $(`#coparcenar_type_field${id_num}-${accountId}`).val();
                    let cop_id = $(`#huf_co_name_id${id_num}-${accountId}`).val();
                    if(cop_id == "" || cop_id == undefined){
                        co_name_arr.push({"name":name,"cop_type":cop_type,"rel":rel,"dob":dob});
                    }else{
                        co_name_arr.push({"name":name,"cop_type":cop_type,"rel":rel,"dob":dob,"cop_id":cop_id});
                    }
                });
                ovdDetailsObject.data['HUF_COP']["huf_coparcenar"] = co_name_arr;
            }
            if($(".AddNonIndHUFDetailsField").length!=0 &&_accounDetails.constitution == 'NON_IND_HUF' && accountId == 2){
                ovdDetailsObject.data['HUF_COP']["huf_num_of_coparcenars"] = $(".AddNonIndHUFDetailsField").val(); 
            }

            if($("#applicantId-"+accountId).val() != ''){
                ovdDetailsObject.data['is_update'] = true;
                ovdDetailsObject.data[accountId]['applicantId'] = $("#applicantId-"+accountId).val();
            }else{
                $.growl({message: "Error! failed to access applicant Id"},{type: "warning"});         
                return false;
            }
			
            
            $(".AddOvdDetailsField").each(function() {
                if(accountId == $(this).attr('id').split('-')[1]){
                    name = $(this).attr('id').split('-')[0];
                    if(name == 'address_flag'){
                        if ($(this).prop('checked')==true)
                        {
                            ovdDetailsObject.data[accountId][name]= 1;
                        }else{
                            ovdDetailsObject.data[accountId][name] = 0;
                        }
                    }else if(name == 'address_per_flag'){
                        if ($(this).prop('checked')==true)
                        {
                            ovdDetailsObject.data[accountId][name]= 1;
                        }else{
                            ovdDetailsObject.data[accountId][name] = 0;
                        }
                    }else if(name == 'aadhar_link'){
                        if ($(this).prop('checked')==true)
                        {
                            ovdDetailsObject.data[accountId][name]= 1;
                        }else{
                            ovdDetailsObject.data[accountId][name] = 0;
                        }
                    }else if(name == 'aadhar_link_permanent'){
                        if ($(this).prop('checked')==true)
                        {
                            ovdDetailsObject.data[accountId][name]= 1;
                        }else{
                            ovdDetailsObject.data[accountId][name] = 0;
                        }
                    }
                    else{
                        if(/^[a-zA-Z0-9 ]*$/.test($(this).val()) == false){
                            ovdDetailsObject.data[accountId][name] = $("#"+$(this).attr("id")).inputmask('unmaskedvalue');
                        }else{
                            if(name != 'title'){
                            ovdDetailsObject.data[accountId][name] = $(this).val();
                            }else{
                                ovdDetailsObject.data[accountId][name] = $('#'+name+'-'+accountId+' option:selected').val();
                            }   
                        }
                    }
                }
				
				// for eKYC - get the AadharVaultRef no pushed
				if(name == 'id_proof_card_number' && $('#proof_of_identity-'+accountId).val()==9){			
					if(typeof $("#id_proof_card_number-"+accountId).attr('data-ekyc-aadharrefno')!='undefined'){
						ovdDetailsObject.data[accountId]['id_proof_aadhaar_ref_number'] = $("#id_proof_card_number-"+accountId).attr('data-ekyc-aadharrefno'); 
						ovdDetailsObject.data[accountId]['add_proof_aadhaar_ref_number'] = $("#id_proof_card_number-"+accountId).attr('data-ekyc-aadharrefno');  
					}
                
				}			
                if(name == 'add_proof_card_number' && $('#proof_of_address-'+accountId).val()==9){	
                    if(typeof $("#add_proof_card_number-"+accountId).attr('data-ekyc-aadharrefno')!='undefined'){
                        ovdDetailsObject.data[accountId]['add_proof_aadhaar_ref_number'] = $("#add_proof_card_number-"+accountId).attr('data-ekyc-aadharrefno');  
                    }
                }
                // 22MAY23 - encode/enc card nos.
                if(name == 'id_proof_card_number' || name == 'add_proof_card_number'){								
                    if($('#proof_of_identity-'+accountId).val() == 9){
                        if($('#id_proof_card_number-'+accountId).val() != $('#id_proof_card_number-'+accountId).attr('data-ekyc')){
                            nomatchkyccount++;
                        }
                        if($('#id_proof_card_number-'+accountId).attr('data-hash') != ''){
                            ovdDetailsObject.data[accountId]['datahash'] = $('#id_proof_card_number-'+accountId).attr('data-hash');
                        }
                    }						
                    if($(this).val()!=''){
                        ovdDetailsObject.data[accountId][name] = rsenc($(this).val(), $('meta[name="cookie"]').attr('content').split('.')[2]);
                    }
				}

                if(name == 'add_proof_card_number'){								
                    if($('#proof_of_address-'+accountId).val() == 9){
                        if($('#add_proof_card_number-'+accountId).val() != $('#add_proof_card_number-'+accountId).attr('data-ekyc')){
                
                            nomatchkyccount++;
                        }
                        if($('#add_proof_card_number-'+accountId).attr('data-hash') != ''){
                            ovdDetailsObject.data[accountId]['datahash'] = $('#add_proof_card_number-'+accountId).attr('data-hash');
                        }
                    }						
                    if($(this).val()!=''){
                        ovdDetailsObject.data[accountId][name] = rsenc($(this).val(), $('meta[name="cookie"]').attr('content').split('.')[2]);
                    }
				}
                
                if(name == 'per_address_line1' || name == 'per_address_line2' || name == 'per_country' || name == 'per_pincode' || name == 'per_state' || name == 'per_city' || name == 'per_landmark'){								
                    if($(this).val()!=''){
                        ovdDetailsObject.data[accountId][name] = rsenc($(this).val(), $('meta[name="cookie"]').attr('content').split('.')[2]);
                    }
				}
                
                    
                
                
                if(name == 'current_address_line1' || name == 'current_address_line2' || name == 'current_country' || name == 'current_pincode' || name == 'current_state' || name == 'current_city' || name == 'current_landmark'){								
                    if($('#'+name+'-'+accountId).val() !=''){
                        ovdDetailsObject.data[accountId][name] = rsenc($('#'+name+'-'+accountId).val(), $('meta[name="cookie"]').attr('content').split('.')[2]);
                    }
				}
                
                ovdDetailsObject.data[accountId]['father_spouse'] = $('#father_spouse-'+accountId+':checked').val();

                if (name == 'proof_of_identity' && $('#proof_of_identity-'+accountId).val() != 2 && $('#proof_of_identity-'+accountId).val() != 3) {
                    ovdDetailsObject.data[accountId]['passport_driving_expire'] = '';
                } 

                if (name == 'proof_of_address' && $('#proof_of_address-'+accountId).val() != 2 && $('#proof_of_address-'+accountId).val() != 3) {
                    ovdDetailsObject.data[accountId]['passport_driving_expire_permanent'] = '';
                }   
                if (name == 'proof_of_identity' && $('#proof_of_identity-'+accountId).val() != 2 && $('#proof_of_identity-'+accountId).val() != 3) {
                    ovdDetailsObject.data[accountId]['id_psprt_dri_issue'] = '';
                } 

                if (name == 'proof_of_address' && $('#proof_of_address-'+accountId).val() != 2 && $('#proof_of_address-'+accountId).val() != 3) {
                    ovdDetailsObject.data[accountId]['add_psprt_dri_issue'] = '';
                }   
            });

			
			
            $(".EntItyDetailsField").each(function() {
                name = $(this).attr('id').split('-')[0];
                if(name == 'address_flag'){
                    if ($(this).prop('checked')==true)
                    {
                        ovdDetailsObject.data['Entity'][name]= 1;
                    }else{
                        ovdDetailsObject.data['Entity'][name] = 0;
                    }
                }else if(name == 'aadhar_link'){
                    if ($(this).prop('checked')==true)
                    {
                        ovdDetailsObject.data['Entity'][name]= 1;
                    }else{
                        ovdDetailsObject.data['Entity'][name] = 0;
                    }
                }else if(name == 'aadhar_link_permanent'){
                    if ($(this).prop('checked')==true)
                    {
                        ovdDetailsObject.data['Entity'][name]= 1;
                    }else{
                        ovdDetailsObject.data[name] = 0;
                    }
                }
                else{
                    if(/^[a-zA-Z0-9 ]*$/.test($(this).val()) == false){
                        ovdDetailsObject.data['Entity'][name] = $("#"+$(this).attr("id")).inputmask('unmaskedvalue');
                    }else{
                        ovdDetailsObject.data['Entity'][name] = $(this).val();
                    }
                }
            });

			
            $(".EntItyDetailsField_Huf").each(function() {
                let name = $(this).attr('id').split('-')[0];
                console.log(name);
                if(name == 'address_flag'){
                    if ($(this).prop('checked')==true)
                    {
                        ovdDetailsObject.data['HUF'][name]= 1;
                    }else{
                        ovdDetailsObject.data['HUF'][name] = 0;
                    }
                }else if(name == 'aadhar_link'){
                    if ($(this).prop('checked')==true)
                    {
                        ovdDetailsObject.data['HUF'][name]= 1;
                    }else{
                        ovdDetailsObject.data['HUF'][name] = 0;
                    }
                }else if(name == 'aadhar_link_permanent'){
                    if ($(this).prop('checked')==true)
                    {
                        ovdDetailsObject.data['HUF'][name]= 1;
                    }else{
                        ovdDetailsObject.data[name] = 0;
                    }
                }
                else{
                    if(/^[a-zA-Z0-9 ]*$/.test($(this).val()) == false){
                        ovdDetailsObject.data['HUF'][name] = $("#"+$(this).attr("id")).inputmask('unmaskedvalue');
                    }else{
                        ovdDetailsObject.data['HUF'][name] = $(this).val() ?? "";
                    }
                }
            });
            
            
                $(".uploaded_image").each(function(){
                    if($(this).attr("src") != ''){
                        var image = $(this).attr("src").split('/');
                        if(typeof($(this).attr('name').split('-')[1]) != "undefined")
                        {
                            if(accountId == $(this).attr('name').split('-')[1]){
                                name = $(this).attr('name').split('-')[0];
                                if((typeof(ovdDetailsObject.data[accountId][name]) != 'string'))
                                {
                                    ovdDetailsObject.data[accountId][name] = image[image.length-1];

                                    ovdDetailsObject.data[accountId]['OVDS'][name] = image[image.length-1];     
                                }else{
                                    ovdDetailsObject.data[accountId][name] = ovdDetailsObject.data[accountId][name]+','+image[image.length-1];
                                    ovdDetailsObject.data[accountId]['OVDS'][name] = ovdDetailsObject.data[accountId]['OVDS'][name]+','+image[image.length-1]; 
                                }
                            }    
                        }else{
                            name = $(this).attr('name');
                            if(name.substr(0,7)=='entity_'){
                                ovdDetailsObject.data['Entity'][name] = image[image.length-1];
                        }else if(name.substr(0,7)=='huf_ent'){
                            ovdDetailsObject.data['HUF'][name] = image[image.length-1];
                            }else{
                                ovdDetailsObject.data['OVDS'][name] = image[image.length-1];
                            }
                        }                    
                    }
                });

            if ($('#proof_of_identity-'+accountId).val()==9) {
                ovdDetailsObject.data[accountId]['id_proof_image'] = '';
                ovdDetailsObject.data[accountId]['add_proof_image'] = '';
                
            }
        });

        // ovdDetailsObject.data['signature_type'] = $('input[name="signature_type"]:checked').val();
        ovdDetailsObject.data['signature_type'] = $('#signature_type').val();
        ovdDetailsObject.data['mode_of_operation'] = $("#mode_of_operation").val();
        ovdDetailsObject.data['formId'] = $(this).attr("id");
        var accountHolders = [];
        $('.accountHolder:checked').each(function(i){
            accountHolders[i] = $(this).val();
        });
        ovdDetailsObject.data['account_holders'] = accountHolders;
        ovdDetailsObject.data['functionName'] = 'SaveOvdDetailsCallBack';

        /*console.log(ovdDetailsObject);
        return false;*/
        disableSaveAndContinue(this);
        
        if(nomatchkyccount >0){
            $.growl({message: "EKYC Number Update. Kindly Retry EKYC retrieval !"},{type: "warning"});     
            var saveAndContinueBtn = $('.saveOvdDetails');
            enableSaveAndContinue(saveAndContinueBtn);   
            return false;
        }else{
        crudAjaxCall(ovdDetailsObject);
        }
        return false;
    });

   //  $("body").on("change",".id_proof_list,.per_address_proof_list,.cur_address_proof_list",function(){
   //      var type = $(this).attr("proof_type");  
   //      var idProof = $(this).find("option:selected").text();
   //      var applicantId = $(this).attr('id').split('-')[1];
   //      var id = type + '_' + idProof.toLowerCase().replace(/ /g,"_")+'-'+applicantId;

   //      enableovdfields(applicantId);
   //      /*if((type == "per_address") && ( $(this).find('option:selected').val() == $("#proof_of_identity-"+applicantId).val() )){
   //          if($('#add_proof_image_front-'+applicantId+'_div').length>0 || $('#add_proof_image_back-'+applicantId+'_div').length>0){            
   //              $.growl({message: "Existing image found. Please delete previous image for any update!"},{type: "warning"});         
   //              return;
   //          }
   //      }*/

   //      $("#"+type+"_proof_number-"+applicantId).find('input').attr('placeholder','Enter '+idProof+' Number');
   //      $("#upload_"+type+"_proof-"+applicantId).find('label.uploadLabel').html('Upload '+idProof);
   //      $("#upload_"+type+"_proof-"+applicantId).closest('span').html('');

   //      if(type == "cur_address")
   //      {
   //          $("#upload_"+type+"_proof-"+applicantId).find('button').html('<i class="fa fa-plus-circle"></i>Add '+idProof);
   //      }else{
   //          $("#upload_"+type+"_proof-"+applicantId).find('button.front').html('Add '+idProof+' Front Side');
   //          $("#upload_"+type+"_proof-"+applicantId).find('button.back').html('Add '+idProof+' Back Side');
   //          $("#upload_"+type+"_proof-"+applicantId).find('button.upload_front_side').html('<i class="fa fa-plus-circle"></i>Add '+idProof+' Front Side');
   //          $("#upload_"+type+"_proof-"+applicantId).find('button.upload_back_side').html('<i class="fa fa-plus-circle"></i>Add '+idProof+' Back Side');
   //      }

   //      $("#upload_"+type+"_proof-"+applicantId).find('button').attr("id",'upload_'+id);
   //      $("#upload_"+id).attr("data-document",idProof);

   //      if($(this).find('option:selected').val() == '1') {  
   //          $("#"+type+"_proof_number-"+applicantId).find('input').inputmask("9999-9999-9999",{ "clearIncomplete": true });
   //          $("#id_proof_card_number-"+applicantId).addClass('eye-masking').attr("type", "password");
   //          $("#id_proof_card_number-"+applicantId).attr("minlength", 1).attr("maxlength", 20);
   //          $(".field_icon").removeClass('display-none');
   //          $("#add_proof_card_number-"+applicantId).addClass('eye-mask').attr("type", "password");
   //          $(".field").removeClass('display-none');
   //          if(type == "per_address")
   //          {
   //              $("#add_proof_osv_check-"+applicantId).parent().parent().removeClass('display-none');
   //              $("#add_proof_osv_check-"+applicantId).prop('checked', true);
   //          }
   //      }else if($(this).find('option:selected').val() != '1'){
   //          $("#id_proof_card_number-"+applicantId).removeClass('eye-masking').attr("type", "text");   
   //          $(".field_icon").addClass('display-none'); 
   //          $("#add_proof_card_number-"+applicantId).removeClass('eye-mask').attr("type", "text");
   //          $(".field").addClass('display-none');
   //          $("#"+type+"_proof_number-"+applicantId).find('input').inputmask('remove');
   //      }

   //      if((type == "per_address") && ( $(this).find('option:selected').val() == $("#proof_of_identity-"+applicantId).val() ))
   //      {
   //          $("#upload_per_add_proof_frontdiv-"+applicantId).removeClass('display-none');
   //          $("#upload_per_add_proof_backdiv-"+applicantId).removeClass('display-none');
   //          $("#add_proof_image_front-"+applicantId).find('div.add-document-btn').addClass('display-none');
   //          $("#add_proof_image_back-"+applicantId).find('div.add-document-btn').addClass('display-none');
   //          $("#document_preview_add_proof_image_front-"+applicantId).attr("src",'');
   //          $("#document_preview_add_proof_image_back-"+applicantId).attr("src",'');
			
            
   //          //$("#document_preview_add_proof_image_front-"+applicantId).attr("src",$("#document_preview_id_proof_image_front-"+applicantId).attr("src"));
			// var frontSrc = $('#document_preview_id_proof_image_front-'+applicantId).attr('src');							
			// if(typeof frontSrc != "undefined" && frontSrc.length != 0){				
			// 	linkupload(applicantId, frontSrc.split('/').pop(), 'front');
			// }
			
			// //$("#document_preview_add_proof_image_back-"+applicantId).attr("src",$("#document_preview_id_proof_image_back-"+applicantId).attr("src"));
			// var backSrc = $('#document_preview_id_proof_image_back-'+applicantId).attr('src');					
			// if(typeof backSrc != "undefined" && backSrc.length != 0){
			// 	linkupload(applicantId, backSrc.split('/').pop(), 'back');  
			// }  
			
   //          $("#add_proof_card_number-"+applicantId).val($("#id_proof_card_number-"+applicantId).val());
   //          if(jQuery.inArray( $(this).find('option:selected').val(), [2,3])){
   //              $("#passport_driving_expire_permanent-"+applicantId).val($("#passport_driving_expire-"+applicantId).val());
   //          }
   //          $(".documentstabproof").click(function () {
   //              $("#add_proof_card_number-"+applicantId).val($("#id_proof_card_number-"+applicantId).val())   
   //          });
   //          $("#add_proof_osv_check-"+applicantId).prop('checked', true);
   //          return false;
   //      }else{
   //          if(type == "per_address")
   //          {
   //              $("#document_preview_add_proof_image_front-"+applicantId).attr("src",'');
   //              $("#document_preview_add_proof_image_back-"+applicantId).attr("src",'');
   //          }            
   //      }

   //      if((type == "id") || (type == "per_address"))
   //      {
   //          if(type == "per_address")
   //          {
   //              type = "add";
   //          }
   //          if($("#"+type+"_proof_image_front-"+applicantId).find("img").length != 0){
   //              $("#"+type+"_proof_image_front-"+applicantId).find("img").parent().addClass('display-none');
   //              $("#"+type+"_proof_image_front-"+applicantId).find('.add-document-btn').removeClass('display-none');
   //          }
   //          if($("#"+type+"_proof_image_back-"+applicantId).find("img").length != 0){
   //              $("#"+type+"_proof_image_back-"+applicantId).find("img").parent().addClass('display-none');
   //              $("#"+type+"_proof_image_back-"+applicantId).find('.add-document-btn').removeClass('display-none');
   //          }
   //          $("#document_preview_"+type+"_proof_image_front-"+applicantId).attr("src",'');
   //          $("#document_preview_"+type+"_proof_image_back-"+applicantId).attr("src",'');
   //      }else if(type == "cur_address"){
   //          if($("#current_add_proof_image-"+applicantId).find("img").length != 0){
   //              $("#current_add_proof_image-"+applicantId).find("img").parent().addClass('display-none');
   //              $("#current_add_proof_image-"+applicantId).find('.add-document-btn').removeClass('display-none');
   //          }
   //          $("#document_preview_cur_add_proof-"+applicantId).attr("src",'');
   //      }

   //      if($(this).find('option:selected').val() == 9)
   //      {
   //          $("#ekycDiv-"+applicantId).removeClass('display-none');
   //      }else{
   //          $("#ekycDiv-"+applicantId).addClass('display-none');
   //      }
   //  });


/*==========================ID Proof Address ==========================*/


$("body").on("change",".id_proof_list",function(){
         var type = $(this).attr("proof_type");  
        var idProof = $(this).find("option:selected").text();
        var applicantId = $(this).attr('id').split('-')[1];
        var id = type + '_' + idProof.toLowerCase().replace(/ /g,"_")+'-'+applicantId;

        // CG - to fix OVD image validation issue - 150425
        // if working we need to check for other image and scenarios
        $('[name="id_proof_image_front"]').val('')
        $('.deleteImage:visible').click();
        //$('[name="id_proof_image_front"]').parent().find('img').remove();

        enableovdfields(applicantId);
        /*if((type == "per_address") && ( $(this).find('option:selected').val() == $("#proof_of_identity-"+applicantId).val() )){
            if($('#add_proof_image_front-'+applicantId+'_div').length>0 || $('#add_proof_image_back-'+applicantId+'_div').length>0){            
                $.growl({message: "Existing image found. Please delete previous image for any update!"},{type: "warning"});         
                return;
            }
        }*/

        $("#"+type+"_proof_number-"+applicantId).find('input').attr('placeholder','Enter '+idProof+' Number');
        $("#upload_"+type+"_proof-"+applicantId).find('label.uploadLabel').html('Upload '+idProof);
        $("#upload_"+type+"_proof-"+applicantId).closest('span').html('');

        if(type == "cur_address")
        {
            $("#upload_"+type+"_proof-"+applicantId).find('button').html('<i class="fa fa-plus-circle"></i>Add '+idProof);
        }else{
            $("#upload_"+type+"_proof-"+applicantId).find('button.front').html('Add '+idProof+' Front Side');
            $("#upload_"+type+"_proof-"+applicantId).find('button.back').html('Add '+idProof+' Back Side');
            $("#upload_"+type+"_proof-"+applicantId).find('button.upload_front_side').html('<i class="fa fa-plus-circle"></i>Add '+idProof+' Front Side');
            $("#upload_"+type+"_proof-"+applicantId).find('button.upload_back_side').html('<i class="fa fa-plus-circle"></i>Add '+idProof+' Back Side');
        }

        $("#upload_"+type+"_proof-"+applicantId).find('button').attr("id",'upload_'+id);
        $("#upload_"+id).attr("data-document",idProof);

        if($(this).find('option:selected').val() == '1') {  
            $("#"+type+"_proof_number-"+applicantId).find('input').inputmask("9999-9999-9999",{ "clearIncomplete": true });
            $("#id_proof_card_number-"+applicantId).addClass('eye-masking').attr("type", "password");
            $("#id_proof_card_number-"+applicantId).attr("minlength", 1).attr("maxlength", 20);
            $(".field_icon").removeClass('display-none');
            $("#add_proof_card_number-"+applicantId).addClass('eye-mask').attr("type", "password");
            $(".field").removeClass('display-none');
            if(type == "per_address")
            {
                $("#add_proof_osv_check-"+applicantId).parent().parent().removeClass('display-none');
                $("#add_proof_osv_check-"+applicantId).prop('checked', true);
            }
        }else if($(this).find('option:selected').val() != '1'){
            $("#id_proof_card_number-"+applicantId).removeClass('eye-masking').attr("type", "text");   
            $(".field_icon").addClass('display-none'); 
            $("#add_proof_card_number-"+applicantId).removeClass('eye-mask').attr("type", "text");
            $(".field").addClass('display-none');
            $("#"+type+"_proof_number-"+applicantId).find('input').inputmask('remove');
        }

        if(type == "id")
        {
            
            if($("#"+type+"_proof_image_front-"+applicantId).find("img").length != 0){
                $("#"+type+"_proof_image_front-"+applicantId).find("img").parent().addClass('display-none');
                $("#"+type+"_proof_image_front-"+applicantId).find('.add-document-btn').removeClass('display-none');
            }
            if($("#"+type+"_proof_image_back-"+applicantId).find("img").length != 0){
                $("#"+type+"_proof_image_back-"+applicantId).find("img").parent().addClass('display-none');
                $("#"+type+"_proof_image_back-"+applicantId).find('.add-document-btn').removeClass('display-none');
            }
            $("#document_preview_"+type+"_proof_image_front-"+applicantId).attr("src",'');
            $("#document_preview_"+type+"_proof_image_back-"+applicantId).attr("src",'');
        }

        if($(this).find('option:selected').val() == 9)
        {
            $("#ekycDiv-"+applicantId).removeClass('display-none');
            disableEnableIdentityAndAdreessImgBox('disable',applicantId);
            $("#upload_"+id).prop('disabled',true);
        }else{
            $("#ekycDiv-"+applicantId).addClass('display-none');
            disableEnableIdentityAndAdreessImgBox('enable',applicantId);
            $("#upload_"+id).prop('disabled',false);
        }

        $('#id_proof_image_front-'+applicantId+'_div').remove();
        // $('#idProofImageFront-'+applicantId).remove();  
    });


/*==========================Address Proof ==========================*/ 

$("body").on("change",".per_address_proof_list",function(){
             var type = $(this).attr("proof_type");  
        var idProof = $(this).find("option:selected").text();
        var applicantId = $(this).attr('id').split('-')[1];
        var id = type + '_' + idProof.toLowerCase().replace(/ /g,"_")+'-'+applicantId;
        $('.ekyc_field-'+applicantId).prop('disabled',false);
        $("#per_country-"+applicantId).prop('disabled',false);
        $("#per_country-"+applicantId).removeClass('disabled');
        $("#per_country-"+applicantId).select2('');

        $('[name="add_proof_image_front"]').val('')
        // $('.deleteImage:visible').click();
        // enableovdfields(applicantId);
        /*if((type == "per_address") && ( $(this).find('option:selected').val() == $("#proof_of_identity-"+applicantId).val() )){
            if($('#add_proof_image_front-'+applicantId+'_div').length>0 || $('#add_proof_image_back-'+applicantId+'_div').length>0){            
                $.growl({message: "Existing image found. Please delete previous image for any update!"},{type: "warning"});         
                return;
            }
        }*/

        if($(this).find('option:selected').val() == 9)
        {
            $(".perm_ekyc_field-"+applicantId).addClass('disabled')
            $(".perm_ekyc_field-"+applicantId).prop('readonly',true)

        }else{
            $(".perm_ekyc_field-"+applicantId).removeClass('disabled')
            $(".perm_ekyc_field-"+applicantId).prop('readonly',false);
        }

        $("#"+type+"_proof_number-"+applicantId).find('input').attr('placeholder','Enter '+idProof+' Number');
        $("#upload_"+type+"_proof-"+applicantId).find('label.uploadLabel').html('Upload '+idProof);
        $("#upload_"+type+"_proof-"+applicantId).closest('span').html('');

        if(type == "cur_address")
        {
            $("#upload_"+type+"_proof-"+applicantId).find('button').html('<i class="fa fa-plus-circle"></i>Add '+idProof);
        }else{
            $("#upload_"+type+"_proof-"+applicantId).find('button.front').html('Add '+idProof+' Front Side');
            $("#upload_"+type+"_proof-"+applicantId).find('button.back').html('Add '+idProof+' Back Side');
            $("#upload_"+type+"_proof-"+applicantId).find('button.upload_front_side').html('<i class="fa fa-plus-circle"></i>Add '+idProof+' Front Side');
            $("#upload_"+type+"_proof-"+applicantId).find('button.upload_back_side').html('<i class="fa fa-plus-circle"></i>Add '+idProof+' Back Side');
        }

        $("#upload_"+type+"_proof-"+applicantId).find('button').attr("id",'upload_'+id);
        $("#upload_"+id).attr("data-document",idProof);

        if($(this).find('option:selected').val() == '1') {  
            $("#"+type+"_proof_number-"+applicantId).find('input').inputmask("9999-9999-9999",{ "clearIncomplete": true });
            $("#id_proof_card_number-"+applicantId).addClass('eye-masking').attr("type", "password");
            $("#id_proof_card_number-"+applicantId).attr("minlength", 1).attr("maxlength", 20);
            $(".field_icon").removeClass('display-none');
            $("#add_proof_card_number-"+applicantId).addClass('eye-mask').attr("type", "password");
            $(".field").removeClass('display-none');
            if(type == "per_address")
            {
                $("#add_proof_osv_check-"+applicantId).parent().parent().removeClass('display-none');
                $("#add_proof_osv_check-"+applicantId).prop('checked', true);
            }
        }else if($(this).find('option:selected').val() != '1'){
            $("#id_proof_card_number-"+applicantId).removeClass('eye-masking').attr("type", "text");   
            $(".field_icon").addClass('display-none'); 
            $("#add_proof_card_number-"+applicantId).removeClass('eye-mask').attr("type", "text");
            $(".field").addClass('display-none');
            $("#"+type+"_proof_number-"+applicantId).find('input').inputmask('remove');
        }

        if((type == "per_address") && ( $(this).find('option:selected').val() == $("#proof_of_identity-"+applicantId).val() ) && _global_is_review != 1)
        {
            $("#upload_per_add_proof_frontdiv-"+applicantId).removeClass('display-none');
            $("#upload_per_add_proof_backdiv-"+applicantId).removeClass('display-none');
            $("#add_proof_image_front-"+applicantId).find('div.add-document-btn').addClass('display-none');
            $("#add_proof_image_back-"+applicantId).find('div.add-document-btn').addClass('display-none');
            $("#document_preview_add_proof_image_front-"+applicantId).attr("src",'');
            $("#document_preview_add_proof_image_back-"+applicantId).attr("src",'');
            
            
            //$("#document_preview_add_proof_image_front-"+applicantId).attr("src",$("#document_preview_id_proof_image_front-"+applicantId).attr("src"));
            var frontSrc = $('#document_preview_id_proof_image_front-'+applicantId).attr('src');                         
            if(typeof frontSrc != "undefined" && frontSrc.length != 0){              
             linkupload(applicantId, frontSrc.split('/').pop(), 'front');
            }
            
            //$("#document_preview_add_proof_image_back-"+applicantId).attr("src",$("#document_preview_id_proof_image_back-"+applicantId).attr("src"));
            var backSrc = $('#document_preview_id_proof_image_back-'+applicantId).attr('src');                   
            if(typeof backSrc != "undefined" && backSrc.length != 0){
             linkupload(applicantId, backSrc.split('/').pop(), 'back');  
            }  
            
            $("#add_proof_card_number-"+applicantId).val($("#id_proof_card_number-"+applicantId).val());
            if(jQuery.inArray( $(this).find('option:selected').val(), [2,3])){
                $("#passport_driving_expire_permanent-"+applicantId).val($("#passport_driving_expire-"+applicantId).val());
                $("#add_psprt_dri_issue-"+applicantId).val($("#id_psprt_dri_issue-"+applicantId).val());
            }
            $(".documentstabproof").click(function () {
                $("#add_proof_card_number-"+applicantId).val($("#id_proof_card_number-"+applicantId).val())   
            });
            $("#add_proof_osv_check-"+applicantId).prop('checked', true);
            return false;
        }else{
            if(type == "per_address")
            {
                $("#document_preview_add_proof_image_front-"+applicantId).attr("src",'');
                $("#document_preview_add_proof_image_back-"+applicantId).attr("src",''); 
                if($(this).find('option:selected').val() == $("#proof_of_identity-"+applicantId).val()){
                    $("#add_proof_card_number-"+applicantId).val($("#id_proof_card_number-"+applicantId).val());
                    if(jQuery.inArray( $(this).find('option:selected').val(), [2,3])){
                        $("#passport_driving_expire_permanent-"+applicantId).val($("#passport_driving_expire-"+applicantId).val());
                        $("#add_psprt_dri_issue-"+applicantId).val($("#id_psprt_dri_issue-"+applicantId).val());
                    }
                    $(".documentstabproof").click(function () {
                        $("#add_proof_card_number-"+applicantId).val($("#id_proof_card_number-"+applicantId).val())   
                    });
                }else{
                    $("#add_proof_card_number-"+applicantId).val('');
                }
            }            
        }





        if((type == "id") || (type == "per_address"))
        {
            if(type == "per_address")
            {
                type = "add";
            }
            if($("#"+type+"_proof_image_front-"+applicantId).find("img").length != 0){
                $("#"+type+"_proof_image_front-"+applicantId).find("img").parent().addClass('display-none');
                $("#"+type+"_proof_image_front-"+applicantId).find('.add-document-btn').removeClass('display-none');
            }
            if($("#"+type+"_proof_image_back-"+applicantId).find("img").length != 0){
                $("#"+type+"_proof_image_back-"+applicantId).find("img").parent().addClass('display-none');
                $("#"+type+"_proof_image_back-"+applicantId).find('.add-document-btn').removeClass('display-none');
            }
            $("#document_preview_"+type+"_proof_image_front-"+applicantId).attr("src",'');
            $("#document_preview_"+type+"_proof_image_back-"+applicantId).attr("src",'');
        }
    });



/*==========================Current Address Proof ==========================*/ 

$("body").on("change",".cur_address_proof_list",function(){
        var type = $(this).attr("proof_type");  
        var idProof = $(this).find("option:selected").text();
        var applicantId = $(this).attr('id').split('-')[1];
        var id = type + '_' + idProof.toLowerCase().replace(/ /g,"_")+'-'+applicantId;
        
        
         if(type == "cur_address"){

          
            $("#upload_"+type+"_proof-"+applicantId).find('button').html('<i class="fa fa-plus-circle"></i>Add '+idProof);

            $("#upload_"+type+"_proof-"+applicantId).find('button').attr("id",'upload_'+id);
            $("#upload_"+id).attr("data-document",idProof);


            if($("#current_add_proof_image-"+applicantId).find("img").length != 0){
                $("#current_add_proof_image-"+applicantId).find("img").parent().addClass('display-none');
                $("#current_add_proof_image-"+applicantId).find('.add-document-btn').removeClass('display-none');
            }
            $("#document_preview_cur_add_proof-"+applicantId).attr("src",'');
            $("#upload_"+id).prop('disabled',false);
            $('#cur_add_proof_osv_check-'+applicantId).prop('disabled',false);
        }

       
    });

    // =========================Entity Address Proof===========================
    $("body").on("change",".entity_address_proof_list",function(){
        var type = $(this).attr("proof_type");  
        var idProof = $(this).find("option:selected").text();
		idProof = idProof.substr(0,20);
        var applicantId = $(this).attr('id').split('-')[1];
        var id = type + '_' + idProof.toLowerCase().replace(/ /g,"_")+'-'+applicantId;  
        id = id.substring(0, id.indexOf('('));
    
    
        if(type == "entity_address"){
            $("#upload_"+type+"_proof").find('button').html('<i class="fa fa-plus-circle"></i>Add '+idProof);

            $("#upload_"+type+"_proof").find('button').attr("id",'upload_'+id);
            $("#upload_"+id).attr("data-document",idProof);
            if($("#entity_add_proof_image").find("img").length != 0){
                $("#entity_add_proof_image").find("img").parent().addClass('display-none');
                $("#entity_add_proof_image").find('.add-document-btn').removeClass('display-none');
            }
             $("#document_preview_entity_add_proof").attr("src",'');
        }
    });



    if($( "#perAddTab,#curAddTab,#idProofNext" ).hasClass('text-muted-lnavs')){
        $("#perAddTab,#curAddTab,#idProofNext").bind('click', false);
        buttonStatus('idProof');
        buttonStatus('perAddProof');    
    }

    $("body").on("change","select[id^='proof_of_identity']",function(){
        
        var applicantId = $(this).attr("id").split('-')[1];
        $('#id_proof_card_number-'+applicantId).val('');

        if (_global_is_review != 1) {
            if ($(this).val() == 9) { //EKYC
                // $(".ekyc_field-"+applicantId).trigger('change').prop("disabled", true);
                // $('#proof_of_address-'+applicantId).val('').trigger('change').prop("disabled", true);
                $('#id_proof_card_number-'+applicantId).val('');
                $('#add_proof_card_number-'+applicantId).val('');
                $("#passport_driving_expire_permanent-"+applicantId).val('');
            }else{
                $('#add_proof_osv_check-'+applicantId).parent().removeClass('display-none');
                $('#id_proof_osv_check-'+applicantId).parent().removeClass('display-none');
                $('#add_proof_osv_check-'+applicantId).prop("disabled",false);
                $('#id_proof_osv_check-'+applicantId).prop("disabled",false);
                $(".ekyc_field-"+applicantId).trigger('change').prop("disabled", false);
                // $('#proof_of_address-'+applicantId).val('').trigger('change').prop("disabled", false);
                $('#gender-'+applicantId).prop("disabled", false);
                $('#title-'+applicantId).prop("disabled", false);
            }

            if ( $('#address_flag-'+applicantId).is(':checked')) {
                $('#address_flag-'+applicantId).click();
            }
        }
        if (_global_is_review == 1) {
            if ($(this).val() != 9) { //EKYC REVIEW
                // $('#proof_of_address-'+applicantId).val('').trigger('change').prop("disabled", false);
            }
        }
        
        if(applicantId == 1)
        {
            if($(this).val() != 1){
                $("#aadhar_link-"+applicantId).hide();
            }else{
                $("#aadhar_link-"+applicantId).show();
            }
        }

        if($(this).val() == 2 || $(this).val() == 3){
            var attr = $("#passport_driving_expire-"+applicantId).attr('readonly');
            if(typeof(attr) != "undefined")
            {
                $("#passport_driving_expire-"+applicantId).attr('readonly',false);
            }
            $("#passport_driving-"+applicantId).show();
            $("#passport_driving_issue-"+applicantId).show();
        }else{
            $("#passport_driving-"+applicantId).hide();
            $("#passport_driving_issue-"+applicantId).hide();
        }
        return false;
    });

    $("body").on("change","select[id^='proof_of_address']",function(){
        var applicantId = $(this).attr("id").split('-')[1];
        if(applicantId == 1){
            if($(this).val() == 1){
                $("#aadhar_link_permanent-"+applicantId).show();
            }else{
                $("#aadhar_link_permanent-"+applicantId).hide();
            }
        }
        if($(this).val() == 2 || $(this).val() == 3){
            $("#passport_driving_permanent-"+applicantId).show();
            $("#passport_driving_issue_permanent-"+applicantId).show();

        }else{
            $("#passport_driving_permanent-"+applicantId).hide();
            $("#passport_driving_issue_permanent-"+applicantId).hide();
        }        
        return false;
    });    

    $('.per_address_proof').on('change',function(){
        var nameoftype = $(this).attr('name');
        
           
        var applicantId = $(this).attr("id").split('-')[1];
           if($(this).val() == 9){
            $("#ekyc_permanent-"+applicantId).show();
        }else{
            $("#ekyc_permanent-"+applicantId).hide();
            $('.perm_ekyc_field-'+applicantId).prop('disabled',false);
            $('.perm_ekyc_field-'+applicantId).val('');
        } 
     
    });

    $("body").on("keyup",".first_name,.last_name",function(){
        var applicantId = $(this).attr('id').split('-')[1];
        var first_name = $('input[id=first_name-'+applicantId+']').val();
        var last_name = $('input[id=last_name-'+applicantId+']').val();
        if((first_name + ' ' + last_name).length <= 24){
            var short_name = first_name + ' ' + last_name;
        }else{
            var short_name = last_name.substr(0,24);
        }
        
        $('input[id="short_name-'+applicantId+'"]').val(short_name);
        return false;
    });
    
    // new code title
    $("select[id^='gender']").each(function () {
        var applicantId = $(this).attr('id').split('-')[1];
        updateTitleOptions(applicantId);
    });

    $("body").on("change", "select[id^='gender']", function () {
        var applicantId = $(this).attr('id').split('-')[1];
        $("#title-" + applicantId).val('').trigger('change');
        updateTitleOptions(applicantId);
    });
    
    function updateTitleOptions(applicantId) {
        var selectedGender = $("#gender-" + applicantId).val();
        var selectedMaritalStatus = $("#applicant_ms-" + applicantId).val();
        var selectedDob = $("#applicant_dob-" + applicantId).val();
        selectedMaritalStatus = (selectedMaritalStatus == 1) ? 'S' : (selectedMaritalStatus == 2 ? 'M' : 'O');
                var Diff = moment().diff(selectedDob);
                var Dur = moment.duration(Diff);
                var yearsDiff = Dur._data.years;
    
        if ((Dur._data.months > 0) || (Dur._data.days > 0)) {
            yearsDiff += 0.1; 
                }
        var selectedTitle = $("#title-" + applicantId).val();
                
        $('#title-' + applicantId).prop("disabled", true);
    
        $("#title-" + applicantId).val('').trigger('change');
    
        for (let i = 0; i < $('#title-' + applicantId)[0].options.length; i++) {
            let tmpid = $('#title-' + applicantId)[0].options[i].value;
            let match = false;
    
            for (let j = 0; j < _applicantTitles.length; j++) {
                let t = _applicantTitles[j];
                if (t.gender && t.marital_status) {
                    if (t.id == tmpid &&
                        t.gender.includes(selectedGender) &&
                        t.marital_status.includes(selectedMaritalStatus) &&
                        t.max_age >= yearsDiff &&
                        t.min_age <= yearsDiff) {
                        match = true;
                        break;
                    }
                }
            }
                  
            if (match) {
                $("#title-" + applicantId + " option[value='" + tmpid + "']").removeAttr('disabled');
            } else {
                $("#title-" + applicantId + " option[value='" + tmpid + "']").attr('disabled', 'disabled');
            }
        }
                      
        if (yearsDiff < 18) {
            $("#title-" + applicantId + " option").attr('disabled', 'disabled');
                  
            if (selectedGender == 'F') {
                $("#title-" + applicantId + " option[value='3']").removeAttr('disabled');  // Example: Miss
            } else if (selectedGender == 'M') {
                $("#title-" + applicantId + " option[value='11']").removeAttr('disabled');  // Example: Master
                }
            }
    
        $('#title-' + applicantId).prop("disabled", false);
        $("#title-" + applicantId).select2({ placeholder: "Select Title", allowClear: true });
        
        $("#title-" + applicantId).val(selectedTitle).trigger('change');
        
            }
             
    $("body").on('click',"button[id^='submit_ekyc']",function(){
        var applicantId = $(this).attr('id').split('-')[1];
        $('#id_proof_card_number-'+applicantId).attr('data-ekyc-aadharRefNo', '');
        if($("#id_proof_card_number-"+applicantId).val() == '')
        {
            $.growl({message: "Please Enter E-KYC Reference No."},{type: "warning"});         
            return false;
        }
        $(this).html('Wait...');
        var ekycObject = [];
        ekycObject.data = {};
        ekycObject.url =  '/bank/ekycdetails';
        ekycObject.data['id'] = applicantId;
        ekycObject.data['kyc_reference_no'] = $("#id_proof_card_number-"+applicantId).val();
        ekycObject.data['functionName'] = 'EKYCDetailsCallBack';
    
        crudAjaxCall(ekycObject);
        return false;
    });
});

function buttonStatus(className) {
    $("."+className+" input[type='text'],input[type='radio'],input[type='hidden'],select").on("keyup click change", function(){
        var valid = '';
        $('.'+className).find('input:text ,input:radio ,input:hidden ,select').each(function() {
            if(($(this).val() == "") || 
                (($(this).attr('type') == "radio") 
                    && ($("input[name='"+$(this).attr("name")+"']").is(":checked") == false)) || 
                (($(this).attr('type') == "hidden") && ($(this).val()==''))){
                valid = false;
                return false;
            }else{
                valid = true;
            }
        });
        if(valid){
            $("."+className).next().find(".nexttab").removeClass('btn-disabled').addClass('btn-primary').unbind("click", false);
        }else{
            $("."+className).next().find(".nexttab").addClass('btn-disabled').bind("click", false);
        }
    });
    return false;
}

function EKYCDetailsCallBackFunction(response,object)
{
    if(response['status'] == "success"){
            
		if(typeof response.data != 'undefined' && typeof response.data['ekyc_reference_no'] != 'undefined'){			
			$('#id_proof_card_number-'+object.data['id']).attr('data-ekyc-aadharRefNo', response.data['ekyc_reference_no']);
			$('#id_proof_card_number-'+object.data['id']).attr('data-ekyc', response.data['ekyc_no']);
            $('#id_proof_card_number-'+object.data['id']).attr('data-hash', response.datahash);

		}else{
			$('#id_proof_card_number-'+object.data['id']).attr('data-ekyc-aadharRefNo', '');
            $('#id_proof_card_number-'+object.data['id']).attr('data-ekyc','');
            $('#id_proof_card_number-'+object.data['id']).attr('data-hash','');
			$.growl({message: 'Aadhar vault reference number not available!'},{type: "warning"});
			return false;
		}
        // $('#proof_of_address-'+object.data['id']).val('').trigger('change').prop("disabled", true);       
        
        $('#add_proof_osv_check-'+object.data['id']).parent().addClass('display-none');
        $('#id_proof_osv_check-'+object.data['id']).parent().addClass('display-none');
        disableEnableIdentityAndAdreessImgBox('disable',object.data['id']);
	        // $('#current_add_proof_image-'+object.data['id']).css('opacity', '0.3');
        // $('#current_add_proof_image-'+object.data['id']).css('pointer-events', 'none');
        $('#first_name-'+object.data['id']).val(response.data['first_name']);
        $('#middle_name-'+object.data['id']).val(response.data['middle_name']);
        $('#last_name-'+object.data['id']).val(response.data['last_name']);
        $('#gender-'+object.data['id']).val(response.data['gender']).trigger('change');

        if (typeof(response.data['title']) != 'undefined' && response.data['title'] != '') {
            $('#title-'+object.data['id']).val(response.data['title']).trigger('change');
        }else{
            $('#title-'+object.data['id']).val('').trigger('change');
            $.growl({message: 'Error! Unable to find gender/ title.'},{type: "warning"});
            return false;
        }        

		var line3 = response.data['landmark'];		
        $('#per_address_line1-'+object.data['id']).val(response.data['address_line_1']);
        $('#per_address_line2-'+object.data['id']).val(response.data['address_line_2']);
        $('#per_pincode-'+object.data['id']).val(response.data['pincode']);
        $('#per_state-'+object.data['id']).val(response.data['state']);
        $('#per_city-'+object.data['id']).val(response.data['city']);
		$('#per_landmark-'+object.data['id']).val(line3.substr(0, 44));
        $(".ekyc_field-"+object.data['id']).attr('disabled',true);
        $('#per_city-'+object.data['id']).val(response.data['city']);
        $('#title-'+object.data['id']).val(response.data['title']).trigger('change');
        $('#per_country-'+object.data['id']).val(response.data['country']);
        $('#per_country-'+object.data['id']).select2();
        // addSelect2('gender','Gender',true);
        // addSelect2('title','Title',true);
        $('#per_pincode-'+object.data['id']).keyup();
        $.growl({message: response['msg']},{type: response['status']});
        
        if(response.datacount <= 18)
        {               
            $(".ekyccount-"+object.data['id']).addClass('display-none');
        }
        else
        {
            $(".ekyccount-"+object.data['id']).removeClass('display-none');
        }

    }else{
        $.growl({message: response['msg']},{type: "warning"});
    }
    $("#submit_ekyc-"+object.data['id']).html('E-KYC');
    // console.log(response.data['userDetails']);
    // if (_global_is_review != 1) {
    // $('#proof_of_address-'+object.data['id']).val('').trigger('change');
    $('#add_proof_card_number-'+object.data['id']).val('');
    $("#passport_driving_expire_permanent-"+object.data['id']).val('');
    // addSelect2('per_country','Country',true);
    // }

    return false;
}

function checkIDproofNumber(){
    
    // Invoked From:    $('input[name="id_proof_card_number"]').focusout(function(){checkIDproofNumber()})

    var currIDtype = $('select[name="proof_of_identity"]').val();
    if(currIDtype == 1) {    // Aadhaar
        var currAadhaarNumber = '';
        currAadhaarNumber = $('input[name="id_proof_card_number"]').val();
        currAadhaarNumber = currAadhaarNumber.replace(/\D/g, '');       

        //console.log(currAadhaarNumber);
        if(typeof currAadhaarNumber == "undefined" || currAadhaarNumber == '' || currAadhaarNumber.length != 12 || !validateAadhaarNumberLocally(currAadhaarNumber)){
                $.growl({message: "Invalid Aadhaar Number"},{type: "warning"});
                return false;
        }
        return true;
    }
}


function checkADDproofNumber(){
    
    // Invoked From:    $('input[name="id_proof_card_number"]').focusout(function(){checkIDproofNumber()})

    var currADDtype = $('select[name="proof_of_address"]').val();
    if(currADDtype == 1) {    // Aadhaar
        var currAadhaarNumber = '';
        currAadhaarNumber = $('input[name="add_proof_card_number"]').val();
        currAadhaarNumber = currAadhaarNumber.replace(/\D/g, '');       

        //console.log(currAadhaarNumber);
        if(typeof currAadhaarNumber == "undefined" || currAadhaarNumber == '' || currAadhaarNumber.length != 12 || !validateAadhaarNumberLocally(currAadhaarNumber)){
                $.growl({message: "Invalid Aadhaar Number"},{type: "warning"});
                return false;
        }
        return true;
    }
}

function validateAadhaarNumberLocally(aadharNumber){
        
    //console.log(aadharNumber.length,aadharNumber);
    const d = [
      [0, 1, 2, 3, 4, 5, 6, 7, 8, 9],
      [1, 2, 3, 4, 0, 6, 7, 8, 9, 5], 
      [2, 3, 4, 0, 1, 7, 8, 9, 5, 6], 
      [3, 4, 0, 1, 2, 8, 9, 5, 6, 7], 
      [4, 0, 1, 2, 3, 9, 5, 6, 7, 8], 
      [5, 9, 8, 7, 6, 0, 4, 3, 2, 1], 
      [6, 5, 9, 8, 7, 1, 0, 4, 3, 2], 
      [7, 6, 5, 9, 8, 2, 1, 0, 4, 3], 
      [8, 7, 6, 5, 9, 3, 2, 1, 0, 4], 
      [9, 8, 7, 6, 5, 4, 3, 2, 1, 0]
    ]

    const p = [
      [0, 1, 2, 3, 4, 5, 6, 7, 8, 9], 
      [1, 5, 7, 6, 2, 8, 3, 0, 9, 4], 
      [5, 8, 0, 3, 7, 9, 6, 1, 4, 2], 
      [8, 9, 1, 6, 0, 4, 3, 5, 2, 7], 
      [9, 4, 5, 3, 1, 2, 6, 8, 7, 0], 
      [4, 2, 8, 6, 5, 7, 3, 9, 0, 1], 
      [2, 7, 9, 3, 8, 0, 6, 4, 1, 5], 
      [7, 0, 4, 6, 9, 1, 3, 2, 5, 8]
    ]

  let c = 0
  let invertedArray = aadharNumber.split('').map(Number).reverse()

  invertedArray.forEach((val, i) => {
      c = d[c][p[(i % 8)][val]]
  })

  return (c === 0)
}

var linkupload = function(applicantId, srcFilename, frontback){
	if(typeof applicantId == "undefined" || typeof srcFilename == "undefined" || typeof frontback == "undefined"){
		console.log('Error in linkupload!');
		return false;
	}
	$(".saveDocument").addClass("btn-lblue").removeClass("btn-primary").attr("disabled",true);
	var linkImageObject = [];
	linkImageObject.data = {};
	linkImageObject.url =  '/bank/linkupload';
	linkImageObject.data['srcfile'] = srcFilename;
	linkImageObject.data['image_type'] = 'add_proof_image_'+frontback+'-'+applicantId;
	linkImageObject.data['name'] = 'add_proof_image-'+applicantId;
	linkImageObject.data['upload_type'] = 'link';  
	linkImageObject.data['functionName'] = 'ImageCallBack';  
    linkImageObject.data['declarations'] = $("#declarationsForm").attr('id');
         

	crudAjaxCall(linkImageObject);
	return false;
};

function enableovdfields(applicantId){

        // $(".ekyc_field").attr('disabled',true);
        addSelect2('gender','Gender',false);
        addSelect2('title','Title',false);
        $(".osv-done-blck").removeClass('display-none');
        $('#id_proof_image_front-'+applicantId).css('opacity', 'unset');
        $('#id_proof_image_front-'+applicantId).css('pointer-events', '');
        $('#id_proof_image_back-'+applicantId).css('opacity', 'unset');
        $('#id_proof_image_back-'+applicantId).css('pointer-events', '');
        $('#add_proof_image_front-'+applicantId).css('opacity', 'unset');
        $('#add_proof_image_front-'+applicantId).css('pointer-events', '');
        $('#add_proof_image_back-'+applicantId).css('opacity', 'unset');
        $('#add_proof_image_back-'+applicantId).css('pointer-events', ''); 
}

function disableEnableIdentityAndAdreessImgBox(scenario,applicantId) {
    if (scenario == 'disable') {
        $('#id_proof_image_front-'+applicantId).css('opacity', '0.3');
        $('#id_proof_image_front-'+applicantId).css('pointer-events', 'none');
        $('#id_proof_image_back-'+applicantId).css('opacity', '0.3');
        $('#id_proof_image_back-'+applicantId).css('pointer-events', 'none');
        $('#add_proof_image_front-'+applicantId).css('opacity', '0.3');
        $('#add_proof_image_front-'+applicantId).css('pointer-events', 'none');
        $('#add_proof_image_back-'+applicantId).css('opacity', '0.3');
        $('#add_proof_image_back-'+applicantId).css('pointer-events', 'none');
    }else{
        $('#id_proof_image_front-'+applicantId).css('opacity', '1');
        $('#id_proof_image_front-'+applicantId).css('pointer-events', 'all');
        $('#id_proof_image_back-'+applicantId).css('opacity', '1');
        $('#id_proof_image_back-'+applicantId).css('pointer-events', 'all');
        $('#add_proof_image_front-'+applicantId).css('opacity', '1');
        $('#add_proof_image_front-'+applicantId).css('pointer-events', 'all');
        $('#add_proof_image_back-'+applicantId).css('opacity', '1');
        $('#add_proof_image_back-'+applicantId).css('pointer-events', 'all');   
    }
}

$('.submit_ekyc_perm_add').on('click',function(){
    
    var applicantId = $(this).attr('id').split('-')[1];
    $('#add_proof_card_number-'+applicantId).attr('data-ekyc-aadharRefNo', '');
    if($("#add_proof_card_number-"+applicantId).val() == '')
    {
        $.growl({message: "Please Enter E-KYC Reference No."},{type: "warning"});         
        return false;
    }
    $(this).html('Wait...');
    var ekycObject = [];
    ekycObject.data = {};
    ekycObject.url =  '/bank/ekycdetails';
    ekycObject.data['id'] = applicantId;
    ekycObject.data['kyc_reference_no'] = $("#add_proof_card_number-"+applicantId).val();
    ekycObject.data['functionName'] = 'PermEKYCDetailsCallBack';
    crudAjaxCall(ekycObject);
    return false;
});

function PermEKYCDetailsCallBackFunction(response,object){
    if(response['status'] == "success"){
 
        $('#submit_perm_ekyc-'+object.data['id']).html('E-KYC');
		if(typeof response.data != 'undefined' && typeof response.data['ekyc_reference_no'] != 'undefined'){			
			$('#id_proof_card_number-'+object.data['id']).attr('data-ekyc-aadharRefNo', response.data['ekyc_reference_no']);
			$('#id_proof_card_number-'+object.data['id']).attr('data-ekyc', response.data['ekyc_no']);
            $('#id_proof_card_number-'+object.data['id']).attr('data-hash', response.datahash);
            $('#add_proof_card_number-'+object.data['id']).attr('data-ekyc-aadharRefNo', response.data['ekyc_reference_no']);
			$('#add_proof_card_number-'+object.data['id']).attr('data-ekyc', response.data['ekyc_no']);
            $('#add_proof_card_number-'+object.data['id']).attr('data-hash', response.datahash);

		}else{
			$('#id_proof_card_number-'+object.data['id']).attr('data-ekyc-aadharRefNo', '');
			$('#id_proof_card_number-'+object.data['id']).attr('data-ekyc','');
            $('#id_proof_card_number-'+object.data['id']).attr('data-hash','');
            $('#add_proof_card_number-'+object.data['id']).attr('data-ekyc-aadharRefNo', '');
			$('#add_proof_card_number-'+object.data['id']).attr('data-ekyc','');
            $('#add_proof_card_number-'+object.data['id']).attr('data-hash','');

			$.growl({message: 'Aadhar vault reference number not available!'},{type: "warning"});
			return false;
		}

        $('#add_proof_osv_check-'+object.data['id']).parent().addClass('display-none');
		var line3 = response.data['landmark'];
        $('#per_address_line1-'+object.data['id']).val(response.data['address_line_1']);
        $('#per_address_line2-'+object.data['id']).val(response.data['address_line_2']);
        $('#per_country-'+object.data['id']).val(response.data['country']);
        $('#per_pincode-'+object.data['id']).val(response.data['pincode']);
        $('#per_state-'+object.data['id']).val(response.data['state']);
        $('#per_city-'+object.data['id']).val(response.data['city']);
		$('#per_landmark-'+object.data['id']).val(line3.substr(0, 44));
        $('#per_pincode-'+object.data['id']).keyup();
        $('#add_proof_image_front-'+object.data['id']).prop('disabled',true);
        $('.perm_ekyc_field-'+object.data['id']).prop('disabled',true);
        
        $.growl({message: response['msg']},{type: response['status']});

        if(response.datacount <= 18)
        {               
            $(".ekyccount-"+object.data['id']).addClass('display-none');
        }
        else
        {
            $(".ekyccount-"+object.data['id']).removeClass('display-none');
        }

    }else{
       
        $('.perm_ekyc_field-'+object.data['id']).prop('disabled',false);
        $.growl({message: response['msg']},{type: "warning"});
    }
    addSelect2('per_country','Country',true);
    return false;
}

$(".dob").datepicker({
    clearBtn: true,
    format: "dd-mm-yyyy",
    endDate: "today",
    maxDate: "today",
});



function huf_copar_field(i,f){
    let opt = '';
    let coptype ='';
    console.log(_huf_relation);
    for(let e in _huf_relation) {
        opt += `<option value="${e}">${_huf_relation[e]}</option>`;
    };

    coparcenertype = {"Member":"Member","Coparcenor":"Coparcenor"};
    for(let j in coparcenertype){
        coptype += `<option value="${j}">${coparcenertype[j]}</option>`;

    }
    html = `
    <div class="details-custcol-row col-md-12">
        <div class="row m-0">
            <div class="col-3">
                <div class="details-custcol-row-top d-flex editColumnDiv">
                    <div class="detaisl-left d-flex align-content-center ">
                        <p class="lable-cus">Coparcenor Name -${i}</p>
                        <span class="display-none">
                            <i class="fa fa-check"></i>
                        </span>
                    </div>
                </div>
                <div class="details-custcol-row-bootm">
                    <div class="comments-blck">
                        <input type="text" class="form-control AddHufCoparcenarNameField huf_co_name"
                            table="non_ind_huf" name="coparcenar_name" id="coparcenar_name_field${i}-${f}"
                            value="" onkeyup="this.value = this.value.toUpperCase();">
                        <i title="save" class="fa fa-floppy-o display-none updateColumn"></i>
                    </div>
                </div>
            </div>
                <div class="col-3">
        <div class="details-custcol-row-top d-flex editColumnDiv">
            <div class="detaisl-left d-flex align-content-center ">
                <p class="lable-cus">Type -${i}</p>
                <span class="display-none">
                    <i class="fa fa-check"></i>
                </span>
            </div>
        </div>
        <div class="details-custcol-row-bootm">
            <div class="comments-blck">
                <select class="form-control coparcener_type" name="coparcener_type" table="non_ind_huf"
                    id="coparcenar_type_field${i}-${f}">
                    <option value="" disabled selected hidden></option>
                    ${coptype}
                </select>
                <i title="save" class="fa fa-floppy-o display-none updateColumn"></i>
            </div>
        </div>
    </div>
            <div class="col-3">
                <div class="details-custcol-row-top d-flex editColumnDiv">
                    <div class="detaisl-left d-flex align-content-center ">
                        <p class="lable-cus">Coparcenor Relationship -${i}</p>
                        <span class="display-none">
                            <i class="fa fa-check"></i>
                        </span>
                    </div>
                </div>
                <div class="details-custcol-row-bootm">
                    <div class="comments-blck">
                        <select class="form-control huf_relation" name="huf_relation" table="non_ind_huf" id="coparcenar_rel_field${i}-${f}">
                         <option value="" disabled selected hidden></option>
                            ${opt}
                        </select>
                        <i title="save" class="fa fa-floppy-o display-none updateColumn"></i>
                    </div>
                </div>
            </div>
            <div class="col-3">
                <div class="details-custcol-row-top d-flex editColumnDiv">
                    <div class="detaisl-left d-flex align-content-center ">
                        <p class="lable-cus">Coparcenor DOB -${i}</p>
                        <span class="display-none">
                            <i class="fa fa-check"></i>
                        </span>
                    </div>
                </div>
                <div class="details-custcol-row-bootm">
                    <div class="comments-blck">
                        <input type="text" class="form-control dob"
                            table="non_ind_huf" name="dob" id="coparcenar_dob_field${i}-${f}"
                            value="" onkeyup="this.value = this.value.toUpperCase();">
                        <i title="save" class="fa fa-floppy-o display-none updateColumn"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    `;
    return html;
}

function add_remove_huf_copar_field(i){
    let co_num_input = $(`#huf_num_of_coparcenars-${i}`);
    let co_num = co_num_input.val();
    let co_input = $(".huf_co_name");
    if(co_input.length < co_num){
        $(co_input[co_input.length - 1]).closest(".details-custcol-row").after(huf_copar_field(co_num,i));
        $(".dob").datepicker({
            clearBtn: true,
            format: "dd-mm-yyyy",
            endDate: "today",
            maxDate: "today",
        });
        only_single_member()
        $("select").select2();
    }else if(co_input.length > co_num){
        co_input[co_num].closest(".details-custcol-row").remove();
    }else{
        return;
    }
}

function add_rem_copar_fields(btn){

    let form_count = btn.attr("count");
    let co_num_input = $(`#huf_num_of_coparcenars-${form_count}`);
    co_val = co_num_input.val();
    

    let cop_select_ = $(".coparcener_type");
    let cop_member_option_ = cop_select_.find("option[value='Member']");

    if(_customerDetails[1].marital_status == 1){
        cop_member_option_.remove();
    }

    if(co_val==""){
        co_val = 0;
    } 
    if(btn.hasClass("huf_co_num_bnt")){
        if(co_val >= 10){
            $.growl({ message: "Maximum limit 10 reached." }, { type: "warning" });
            return;
        }
        co_num_input.val(++co_val);
        add_remove_huf_copar_field(form_count);
    }else if(btn.hasClass("huf_co_num_bnt_remove")){
        if(co_val <= 1){
            co_num_input.val(1);
            return;
        }
        co_num_input.val(--co_val);
        add_remove_huf_copar_field(form_count);
    }else{
        return;
    }
    
}

function identityDetailsCallBackFunction(response,object){

    if(response['status'] == "success"){   
       
        _ovd_form_check[_globalCurrentApplicant-1]['ovd_id-'+_globalCurrentApplicant] = true;   

        $('a[data-id="'+response['data']['tab']+'"]').removeClass('text-muted-lnavs').unbind("click", false);        
       
        $('[href="#' + response['data']['tab'] + '"]').tab('show');

    }
    else{

        $.growl({message: response['msg']},{type: response['status']});
    }

}

function ovdETBNTBDetailsCallBackFunction(response,object){

    if(response['status'] != "success"){        
        
        $.growl({message: response['msg']},{type: response['status']});
    }
    else{
        $('a[data-id="'+response['data']['tab']+'"]').removeClass('text-muted-lnavs').unbind("click", false);
        $('a[data-id="'+response['data']['tab']+'"]').click();
    }
}


$("body").on("click",".huf_co_num_bnt",function(){
    add_rem_copar_fields($(this));
    only_single_member();
});
$("body").on("click",".huf_co_num_bnt_remove",function(){
    add_rem_copar_fields($(this));
    only_single_member();
});
$("body").on("input",".huf_co_name",function(){
    $(this).val($(this).val().replace(/[^a-z ]/gi, '').replace(/(\..*)\./g, '$1'));
});


$("body").on("change", ".coparcener_type", function() {
    updateRelation($(this),true);
});

function only_single_member(){
    let cop_select = $(".coparcener_type");
    let cop_type_member = cop_select.find("option[value='Member']:selected"); 
    let cop_member_option = cop_select.find("option[value='Member']");

    if(_customerDetails[1].marital_status == 1){
        cop_member_option.remove();
    }

    if(cop_type_member.length > 0){
        cop_member_option.prop("disabled",true);
        cop_type_member.prop("disabled",false);
    }else{
        cop_member_option.prop("disabled",false);
    }
    cop_select.select2();
}

function updateRelation(element,change = false) {
    let id = element.attr("id");
    let id_arr = id.split("-");
    let index = id_arr[0].replace("coparcenar_type_field", "");
    var applicantId = id_arr[1];

    var relationDropdown = $('#coparcenar_rel_field' + index + '-' + applicantId);

    let cop_select_ = $(".coparcener_type");
    let cop_member_option_ = cop_select_.find("option[value='Member']");

    if(_customerDetails[1].marital_status ==1){
        cop_member_option_.remove();
    }
    if (element.val() == 'Member') {
        
        relationDropdown.find("option[value='11']").prop('disabled',false);
        relationDropdown.find("option[value='3']").prop('disabled',true);
        relationDropdown.find("option[value='5']").prop('disabled',true);
        relationDropdown.find("option[value='19']").prop('disabled',true);
        relationDropdown.find("option[value='20']").prop('disabled',true);
      
       if(change == true){
        relationDropdown.val('');
      }
    } 
    if (element.val() == 'Coparcenor') {
        relationDropdown.find("option[value='11']").prop('disabled',true);
        relationDropdown.find("option[value='3']").prop('disabled',false);
        relationDropdown.find("option[value='5']").prop('disabled',false);
        relationDropdown.find("option[value='19']").prop('disabled',false);
        relationDropdown.find("option[value='20']").prop('disabled',false);
        
        if(change == true){
            relationDropdown.val('');
        }
    }

    relationDropdown.select2(); 
    
    only_single_member();
}

window.onload = function() {
    $('.coparcener_type').each(function(){
        updateRelation($(this));
    });
    only_single_member();
};



$('.address_per_flag_huf').click(function () {
   
    var id = $(this).attr('id');
    var applicantId = $(this).attr('id').split('-')[1];
    var proofvalue = $('#' + id).prop('checked');

    if (proofvalue == true) {
        proofvalue = 1;
    } else {
        proofvalue = 0;
    }

    var obj = [];
    obj.data = {};
    obj.url = '/bank/ProofSelectedValidation';
    obj.data['applicnt_id'] = applicantId;
    obj.data['add_type'] = $(this).attr('id');
    obj.data['proof_value'] = proofvalue;
    obj.data['selected_value'] = $('#proof_of_address-1').val();
    obj.data['functionName'] = 'proofSelectedDropdownCallBack';
    crudAjaxCall(obj);
    

});

function proofSelectedDropdownCallBackFunction(response, object) {
    if (response.status == 'success') {
        var addData = response.data;
        var addType = response.requestData.add_type.split('-')[0];
        var applicantId = response.requestData.applicnt_id;
        var selectedData = '';
        var proofvalue = response.requestData.proof_value;

        if (addType == 'address_per_flag') {
            var id = '#proof_of_address-' + applicantId;
            selectedData = response.requestData.selected_value;

            if (proofvalue == 1) {
                $(id).find('option[value="40"]').remove();
                $(id).find('option[value="41"]').remove();     
                $(id).find('option[value="42"]').remove();
                $(id).find('option[value="43"]').remove();     
                $(id).find('option[value="44"]').remove();
                $(id).find('option[value="45"]').remove();
                $(id).find('option[value="46"]').remove();
                $(id).find('option[value="47"]').remove();
                $.each(addData, function (i,text) {
                    $(id).append(
                        $('<option></option>').val(text["id"]).html(text["ovd"])
                    );
                });
            }

            else if (proofvalue == 0) {

                $(id).find('option[value="1"]').remove();
                $(id).find('option[value="2"]').remove();     
                $(id).find('option[value="3"]').remove();
                $(id).find('option[value="4"]').remove();     
                $(id).find('option[value="5"]').remove();
                $(id).find('option[value="6"]').remove();

                $.each(addData, function (val, text) {
                    $(id).append(
                        $('<option></option>').val(text["id"]).html(text["ovd"])
                    );
                });
            }
            if (selectedData != '') {
                $(id).val(selectedData);
            }
        }
        return false;
    }
}
