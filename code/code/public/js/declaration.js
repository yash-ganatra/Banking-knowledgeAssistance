$(document).ready(function(){
    var declarationObj = $('#declarationsForm').validate({

        rules: {
            kit_number: {
                required: true,
            },
            customer_id: {
                required: true,
            },
            account_number: {
                required: true,
            },
        },
        messages: {
            kit_number: {
                required: "Please Select kit Number"
            },
            customer_id: {
                required: "Please Enter Customer Id"
            },
            account_number: {
                required: "Please Enter Account Number"
            },
        },
        errorPlacement: function( error, element ) {
            if($(element).attr('type') == 'text'){
                error.insertAfter( element.parent());
            }else if($(element).attr('type') == 'radio'){
                error.insertAfter( element.parent().parent() );
            }else if($(element).attr('type') == 'hidden'){
                error.insertAfter( element );
            }else{
                error.insertAfter( element.parent());
            }
        }
    });

	$('.declaration').each(function() {
		var currName = $(this).attr('name');
		if (!currName.includes('other')) {
		   $(this).rules("add",
			{
			    required: true,
				messages: {
					required: "Mandatory Declaration.",
				},
			});
		}
	});

	$('.image-input').each(function() {
		var currName = $(this).attr('name');
		if (!currName.includes('other')) {
		   $(this).rules("add",
			{
				required: function(element){
					return ($("#"+currName).find("img").length == 0);
				},
				messages: {
					required: "Image proof is mandatory for the declaration."
				},
			});
		}
	});

    $("body").on("change","#kit_number",function(){
        var schemeDataObject = [];
        schemeDataObject.data = {};
        schemeDataObject.url =  '/bank/delightKit';
        schemeDataObject.data['id'] = $(this).val();
        schemeDataObject.data['delightKit'] = $(this).find("option:selected").text();
        schemeDataObject.data['functionName'] = 'DelightKitCallBack';

        crudAjaxCall(schemeDataObject);
        return false;
    });

    $("body").on("change","#gpaplan",function(){
        var schemeDataObject = [];
        schemeDataObject.data = {};
        schemeDataObject.url =  '/bank/getgpadata';
        schemeDataObject.data['id'] = $(this).val();
        schemeDataObject.data['gpaplan'] = $(this).find("option:selected").text();
        schemeDataObject.data['functionName'] = 'GpaDataCallBack';

        crudAjaxCall(schemeDataObject);
        return false;
    });

    $("body").on("click",".nexttaservices",function(){
        if (!declarationObj.form()) { // Not Valid
            return false;
        }else{

        }
        _declaration_form_check[0]['declaration']= true;
        if ($(this).attr('id') == "nextdelight") {
            _declaration_form_check[1]['declaration']= true;

        }
        $('a[data-id="'+$(this).attr('tab')+'"]').removeClass('text-muted-lnavs').unbind("click", false);
        $('a[data-id="'+$(this).attr('tab')+'"]').click();
        $('#saveandcontinue').removeClass('display-none');
        return false;
    });

    $("body").on("click","#auto_renew_gpa",function(){
        if($("input[name='auto_renew_gpa']:checked").val() == 1){
            addSelect2('termautorenewal','Term for Auto Renewal');
        }else{
            addSelect2('termautorenewal','Term for Auto Renewal',true);
            $('#termautorenewal').val('').trigger('change');
        }
    });

    $("body").on("click",".declaration",function(){
        var name = $(this).attr("id");
        if ($("input[name="+name+"]").prop("checked") == true){
            $("#"+name+"_image_proof_div").removeClass('display-none');
            $("#dummy_image_upload").addClass('display-none');
        }else{
            $("#"+name+"_image_proof_div").addClass('display-none');
            $("#dummy_image_upload").removeClass('display-none');
        }
    });


    $("body").on("click",".applyDigiSign",function(){

        if($('#togBtn:checked').val() != 'on'){

        if($('#date_picker').val() == ""){
            $('#date_picker_err').html('Please Enter Date');
            return false;
        }
        }


        if(_accountType == 4){
            if(gpaValid == 1)
            {
                $.growl({message: "Please enter nominee details."},{type: "warning"});
                return false;
            }

            if($("input[name='gpa_required']:checked").val() == 1)
            {
                if(nominee_exists == 'no'){
                    gpaValid = 1;
                    $.growl({message: "Please Fill the Nominee Details"},{type: "warning"});
                    return false;
                }else{
                    gpaValid = 0;
                }
            }
        }

        if (!declarationObj.form()) { // Not Valid
            return false;
        }else{

        }
        for(var chk=1; chk<=(_availbleDeclarationUi-1); chk++){
            if (_availbleDeclarationUi == 1) { //not require to check for only image declarations
                break;
            }

            if(!$(this).hasClass("is_huf")){
            if (!form_check_declaration(chk)) {
                return false;
            }
        }

        }

        var applyDigiSignObject = [];
        applyDigiSignObject.data = {};
        applyDigiSignObject.data['Declarations'] = {};
        applyDigiSignObject.data['AccountDetails'] = {};
        applyDigiSignObject.data['DelightDetails'] = {};
        applyDigiSignObject.data['Declarations']['Proofs'] = {};

        if(typeof($('#date_picker').val()) != 'undefined'){
            applyDigiSignObject.data['nri_date'] = $('#date_picker').val();
        }
        applyDigiSignObject.data['dynaText'] = _dynaText;
        applyDigiSignObject.url =  '/bank/applydigisign';
        applyDigiSignObject.data['formId'] = $(this).attr("id");


        $('.declaration').each(function(){
            if($(this).attr("type") == "checkbox"){
                if ($(this).prop("checked")){
                    var otherId = $(this).attr('id').slice(0,6);
                    if (($(this).attr('id').slice(0,5) == 'other') && ($("#"+otherId+"_proof").find("img").length == 0)) {
                        
                    }else{
                        applyDigiSignObject.data['Declarations'][$(this).attr("id")] = 1;
                    }
                }else{
                    if ($(this).attr("id") == "acknowledgement_receipt-1" || $(this).attr("id") == 'delight_kit_photograph-1') {
                        applyDigiSignObject.data['Declarations'][$(this).attr("id")] = 1;
                    }else{
                        applyDigiSignObject.data['Declarations'][$(this).attr("id")] = 0;
                    }
                }
            }else if($(this).attr('type')=='radio'){
                applyDigiSignObject.data['Declarations'][$(this).attr("name")] = $('input[name='+$(this).attr('name')+']:checked').val();
            } else{
               applyDigiSignObject.data['Declarations'][$(this).attr("name")] = 1;
            }
        });
        
        if(_accountType != 3){


        $('.servicesdata').each(function(){
            if($(this).attr("type") == "checkbox"){
                if ($(this).prop("checked")){
                    applyDigiSignObject.data['AccountDetails'][$(this).attr("id")] = 1;
                }else{
                    applyDigiSignObject.data['AccountDetails'][$(this).attr("id")] = 0;
                }
            }else if($(this).attr('type')=='radio'){
                applyDigiSignObject.data['AccountDetails'][$(this).attr("name")] = $('input[name='+$(this).attr('name')+']:checked').val();
            } else{
                applyDigiSignObject.data['AccountDetails'][$(this).attr("name")] = $(this).val();
            }
        });
    }

        $('.AddDelightField').each(function(){
            /*if($(this).attr("type") == "checkbox"){
                if ($(this).prop("checked")){
                    applyDigiSignObject.data['DelightDetails'][$(this).attr("id")] = 1;
                }else{
                    applyDigiSignObject.data['AccountDetails'][$(this).attr("id")] = 0;
                }
            }else if($(this).attr('type')=='radio'){
                applyDigiSignObject.data['AccountDetails'][$(this).attr("name")] = $('input[name='+$(this).attr('name')+']:checked').val();
            } else{
                applyDigiSignObject.data['DelightDetails'][$(this).attr("name")] = $(this).val();
            }*/
            if($(this).val() != '')
            {
                applyDigiSignObject.data['DelightDetails'][$(this).attr("name")] = $(this).val();
            }
        });

        $(".uploaded_image").each(function(){
            if($(this).attr("src") != ''){
                var image = $(this).attr("src").split('/');
                if($(this).attr("name").substr(0,5) == "other"){
                    if(typeof(applyDigiSignObject.data['Declarations']['Proofs'][$(this).attr('name')]) == "undefined"){
                        applyDigiSignObject.data['Declarations']['Proofs'][$(this).attr('name')] = image[image.length-1];
                        var checkbox = $(this).attr("name").substr(0,8);
                        applyDigiSignObject.data['Declarations'][checkbox] = 1;
                    }else{
                        applyDigiSignObject.data['Declarations']['Proofs'][$(this).attr('name')] = applyDigiSignObject.data['Declarations']['Proofs'][$(this).attr('name')]+','+image[image.length-1];
                    }
                }else{
                    applyDigiSignObject.data['Declarations']['Proofs'][$(this).attr('name')] = image[image.length-1];
                }
            }
        });

        if(_accountType == '2'){
            applyDigiSignObject.data['Declarations']['Proofs']['extra_declaration_pdf-1_proof'] = $('#extra_declaration_pdf-1_proof_pdf').html().toLowerCase(); 
        }
        applyDigiSignObject.data['functionName'] = 'ApplyDigiSignCallBack';

        //console.log(applyDigiSignObject);
        // return false;

        disableSaveAndContinue(this);

        crudAjaxCall(applyDigiSignObject);
        return false;
    });



    $("body").on("click",".addOtherDeclarataion",function(){

		var total_img_to_upload = $('.upload-doc-mdr').length;
        // Ignore 2 delight  declarations 
        var delight_case = $('#acknowledgement_receipt-1_image_proof_div').length * 2;

        var no_imgs = $('.upload-doc-mdr').find('img[id^=document_preview_]').length;
        var no_pdfs = $('.upload-doc-mdr').find('.fa-file-pdf-o').length;
        var total_evids = parseInt(no_imgs)+parseInt(no_pdfs);

        if(total_evids < (total_img_to_upload - delight_case)){
            $.growl({message: "Update mandatory / existing declarations first."},{type: "warning"});
            return false;
        }

		var DeclarationDivs = $('.other-declaration-div').length;

        if (DeclarationDivs == 5 ) {
            $.growl({message: "Maximum 5 additional declaration permitted."},{type: "warning"});
            return false;
        }

		if($('img[id^=document_preview_other]').length < DeclarationDivs){
			$.growl({message: "Existing Other declaration not utilised."},{type: "warning"});
            return false;
		}

		if(DeclarationDivs==0){
			otherDeclarationsCount = 1;
		}else{
			otherDeclarationsCount = parseInt(DeclarationDivs)+1;
            
		}

		/*
        if ($('.declarationblade').find('.editColumnDiv').length != 0) {
            var lastImg = $('.declarationblade').children('.editColumnDiv').last().find('img');
            if (lastImg.length < 1) {
                return false;
            }
            var lastDeclarationId = $('.declarationblade').children('.editColumnDiv').last().find('img').attr('name');
            var lastOtherNumber = lastDeclarationId.slice(5,6);
        }else{
            lastOtherNumber = '';
            lastDeclarationId = '';
        }

        if(('other'+lastOtherNumber+'_proof') == lastDeclarationId){
            otherDeclarationsCount = parseInt(lastOtherNumber) + 1;
        }else{
            otherDeclarationsCount = 1;
        }*/

var DynamicHtml =
        '<div class="maindoc-row editColumnDiv other-declaration-div" name="other'+otherDeclarationsCount+'">'+
            '<div class="row">'+
                '<div class="col-md-12">'+
                    '<div class="detaisl-left d-flex align-content-center">'+
                        '<p class="lable-cus"><b>OTHER DECLARATION</b></p>'+
                    '</div>'+
                   ' <div class="questions-blck-row">'+
                        '<div class="radio-selection">'+
                            '<label class="radio display-none">'+
                                '<input type="checkbox" checked class="declaration" name="other'+otherDeclarationsCount+'-1" id="other'+otherDeclarationsCount+'-1"  {{$disabled}}>'+
                                '<span class="lbl padding-8">Other Declaration</span><br>'+
                                '<span class="lbl padding-8"></span>'+
                            '</label>'+
                        '</div>'+
                    '</div>'+
                '</div>'+
           '<div class="col-md-12">'+
                '<div class="upload-doc-mdr {{$display}}" id="other'+otherDeclarationsCount+'_image_proof_div">'+
                    '<div class="form-group">'+
                        '<div class="add-document d-flex align-items-center justify-content-around" id="other'+otherDeclarationsCount+'_proof">'+
                                '<div id="other'+otherDeclarationsCount+'_declaration">'+
                                            '<div class="upload-delete display-none" >'+

                                                    '<button type="button" class="delete-icon btn btn-danger btn-icon waves-effect waves-light deleteImage">'+
                                                        '<i class="fa fa-trash" aria-hidden="true"></i>'+
                                                    '</button>'+
                                            '</div>'+
                                '</div>'+
                                '<div class="add-document-btn adb-btn-inn">'+
                                '<button type="button" id="upload_other'+otherDeclarationsCount+'_proof" class="btn btn-outline-grey waves-effect upload_document" data-toggle="modal"'+
                                    'data-id="other'+otherDeclarationsCount+'_proof" data-name="other'+otherDeclarationsCount+'-1_proof" data-document="other'+otherDeclarationsCount+'-1" data-target="#upload_proof">'+
                                    '<span class="adb-icon">'+
                                        '<i class="fa fa-plus-circle"></i>'+
                                    '</span>'+
                                    'Add other'+otherDeclarationsCount+' Declaration'+
                                '</button>'+
                            '</div>'+
                        '<input type="text" style="opacity:0" name="_proof">'+
                    '</div>'+
                '</div>'+
            '</div>'+
        '</div>'+
    '</div>'+
'</div>';

      /*  var DynamicHtml =  '<div class="upload-doc-mdr declaration other-declaration-div" id="other'+otherDeclarationsCount+'_proof_div" name="other'+otherDeclarationsCount+'">'+
                                '<div class="detaisl-left d-flex align-content-center">'+
                                    '<p class="lable-cus"><b>OTHER DECLARATION</b></p>'+
                                '</div>'+
                                    '<div class="form-group">'+
                                        '<div class="add-document d-flex align-items-center justify-content-around" id="other'+otherDeclarationsCount+'_proof">'+
                                            '<div id="other'+otherDeclarationsCount+'_declaration">'+
                                                '<div class="upload-delete display-none">'+
                                                    '<button type="button" onclick="otherDelete(this)" class="other-delete delete-icon btn btn-danger btn-icon waves-effect waves-light deleteImage">'+
                                                        '<i class="fa fa-trash" aria-hidden="true"></i>'+
                                                    '</button>'+
                                                '</div>'+
                                            '</div>'+
                                            '<div class="add-document-btn adb-btn-inn ">'+
                                                '<button type="button" id="other'+otherDeclarationsCount+'_proof" class="btn btn-outline-grey waves-effect upload_document" data-toggle="modal" '+
                                                    'data-id="other'+otherDeclarationsCount+'_proof" data-name="other'+otherDeclarationsCount+'_proof" data-document="Other Proof" data-target="#upload_proof">'+
                                                    '<span class="adb-icon">'+
                                                        '<i class="fa fa-plus-circle"></i>'+
                                                    '</span>Add Other Declarations'+
                                                '</button>'+
                                            '</div>'+
                                            '<input type="text" style="opacity:0" name="other'+otherDeclarationsCount+'_proof">'+
                                        '</div>'+
                                    '</div>'+
                            '</div>';*/
        if($('.declarationblade').children().last().hasClass('editColumnDiv')){
            $('.declarationblade').children('.editColumnDiv').last().after(DynamicHtml);
        }else if($('.declarationblade').children().last().hasClass('other-declaration-div')){
            var count = parseInt(otherDeclarationsCount) - parseInt(1);
            $("#other"+count+"_proof_div").after(DynamicHtml);
        }else{
            $('.declarationblade').append(DynamicHtml);
        }

        return false;
    });
});


function DelightKitCallBackFunction(response,object)
{
    if(response['status'] == "success"){
        $("#customer_id").val(response.data['kitDetails']['customer_id']);
    }else{
        $.growl({message: response['msg']},{type: "warning"});
    }
    return false;
}
