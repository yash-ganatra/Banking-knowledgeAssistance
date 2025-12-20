$(document).ready(function(){

	var comuPre =  $('.validation_11').length;
	var permPre = $('.validation_10').length;

	if(comuPre == 7 && permPre == 7){
		$('#desc_field_11').parent().parent().before(`<div class="row"><div class="col-md-12" style="text-align:right"><input type="checkbox"
		 class="custom-control-input customChecks" name="type" id="sameasPermanant"><label style="margin-left:20px;">
		 Same as Permanent Address</label></div></div>`);
	}

	//other amend 17_07_2024
	// communication address
	var selectId =  $('#commuaddid option:selected').val();
	if(selectId == 29){
		$('#upload_amend_card-36').attr('mandatory','N');
		$('#red-36').css('display','none');
		$('#amend_image_check_-36').attr('data-mandatory','N');
	}else{
		$('#upload_amend_card-36').attr('mandatory','Y');
		$('#red-36').css('display','');
		$('#amend_image_check_-36').attr('data-mandatory','Y');
	}
	//end communication address
	
	$('#cust_acct').on('click',function(){
		var getcust_acctNo = $('#customer_id').val();

		if(getcust_acctNo == '')
		{
			$.growl({message:"Please inserted Customer ID or Account Number."},{type:"warning"});
			return false;
		}
		if(getcust_acctNo.length != 9 && getcust_acctNo.length != 14){

			$.growl({message:"Invalid number. Please recheck"},{type:"warning"});
			return false;
		}
		if(getcust_acctNo.length == 9){

			$.growl({message:"Fetching customer details."},{type:"warning"});
			
		}else{
			$.growl({message:"Fetching primary customer details."},{type:"warning"});
			
		}

		var obj = [];
		obj.data = {};
		obj.url = '/bank/fetchdataforid';
		obj.data['cust_acctNo'] = getcust_acctNo;
		obj.data['functionName'] = 'custidaccfetchDataCallBack';

		crudAjaxCall(obj);
		return false;
	});

	//continue button click for logic 
	//eky-c and continue button click for logic

 		$('#selectcustId').on('click',function(){
 			var getcust_acctNo = $('#getcust_acctNo').text();
 			var obj = [];
 			obj.data = {};
 			obj.url = '/bank/checkcustdataprocessing';
 			obj.data['functionName']  = 'fetchdatacustidCallBack';
 			obj.data['cust_acctNo'] = getcust_acctNo;
 			obj.data['getminorStatus'] = $('#getminorStatus').text();
 			obj.data['getekycStatus'] = $('#getekycStatus').text();
 			obj.data['accountStatus'] = $('#accActStatus').text();
 			crudAjaxCall(obj);
 			return false;
 		});

 		$('#getekycNo').on('click',function(){
 			var getcust_acctNo = $('#getcust_acctNo').text();
 			var getekyNo = $('#ekyc_number').val();
 				
 				if(getekyNo == ''){
 					$.growl({message:'Please insert E-KYC number !!'},{type:'warning'});
 					return false;
 				}

 			var obj = [];
 			obj.data = {};
 			obj.url = '/bank/checkcustdataprocessing';
 			obj.data['functionName']  = 'fetchdatacustidCallBack';
 			obj.data['cust_acctNo'] = getcust_acctNo;
 			obj.data['ekyc_number'] = getekyNo;
 			obj.data['getekycStatus'] = $('#getekycStatus').text();
 			obj.data['getminorStatus'] = $('#getminorStatus').text();
 			obj.data['accountStatus'] = $('#accActStatus').text();
 			obj.data['getReferenceNo'] = $('#getReferenceNo').text();
 			crudAjaxCall(obj);
 			return false;
 		});

 		
 	//endlogic 

	//image upload delete function 

	 $('.upload_document_amend').on("click",function(){

		var eviId = $(this).attr('id');
		var getevId = eviId.split('-')[1];
		
		if($(this).attr('data-doc') == 'pdf'){
			if(getevId == 21 || getevId == 22){
				$('#inputImage').attr('data-doc','pdf');
			}
		}	
		
	 	$('.pdfrowDoc').remove();
	 	$('#uploadPdfAmend').remove();
	 	$('#inputImage').val('');
        var applicantId = $(this).attr("data-id").split('-')[1];

        $(".document_name").find('h1').html('');
        $(".document_name").find('h1').html('Upload '+$(this).attr("data-document"));
        $(".document_name").attr('id',$(this).attr("data-id"));
        $(".document_name").attr('name',$(this).attr("data-name"));
        var id = $(".document_name").attr('id');
        if(id.substr(-8) == '_img'){
            $(".document_name").attr('value',$(this).attr("data-value"));
        }
        $(".image_preview").css('display','none');
        $("#img-preview-div").addClass('display-none');
        $($(this).data('target')).modal('toggle');
        $("#inputImage").click();
        return false;
    });
	 

    $('#inputImage').on('change',function() {
		
		var documentPath = $('#inputImage').val();
	 	// var extension = documentPath.split('.')[1];
	 	var extension = documentPath.substr(-3).toLowerCase();
		var documentType = $(this).attr('data-doc');

		$('#pdfDocData').remove();
		$('#inputImage').attr('data-doc','pdf|image');
	 	if(documentType == 'pdf'){

	 		$('#uploadImageAmend').css('display','none');
        	$(".image_preview").css('display','none');

			//change on nov amend pdf isseu
			$('#pdfDocButton').append(`<center><button type="button" id="pdfDocData" class="btn btn-primary savePdfDocData">
			Save document</button></center>`);
		
	 	}else{

        	$(".image_preview").css('display','block');
	 		$('#uploadImageAmend').css('display','block');
        	$('#uploadPdfAmend').remove();
			$('#uploadPdf').remove();
			$('#pdfDocData').remove();
	 	}

		if(documentType == 'pdf|image'){
			if(extension == 'pdf'){
				$('#uploadImageAmend').css('display','none');
				$(".image_preview").css('display','none');

				$('#pdfDocButton').append(`<center><button type="button" id="pdfDocData" class="btn btn-primary savePdfDocData">
				Save document</button></center>`);
				
			}else{
				$(".image_preview").css('display','block');
				$('#uploadImageAmend').css('display','block');
				$('#uploadPdfAmend').remove();
				$('#uploadPdf').remove();
				$('#pdfDocData').remove();
			}
		}
        return false;
    });

    $('.amend_image_crop').on('click',function(){

		var result = $('.preview_image').cropper('getCroppedCanvas', {
			minWidth: 256,
			minHeight: 256,
			maxWidth: 4096,
			maxHeight: 4096, 
			fillColor: '#fff',
			imageSmoothingEnabled: true,
			imageSmoothingQuality: 'high',
        });
        var base64 = result.toDataURL('image/jpeg', (40 / 100)); 
        $("#img-preview-div").removeClass('display-none');
        $(".crop_image_preview").attr('src',base64);
        $(".saveDocument").removeClass("btn-lblue").addClass("btn-primary").attr("disabled",false);
    });

    $('#uploadImageAmend').on('click',function(){

        $(".saveDocument").addClass("btn-lblue").removeClass("btn-primary").attr("disabled",true);
        var id = $(".document_name").attr('id');
        var id_card = id.split('-')[1];
        if($(".crop_image_preview").attr('src') != ''){
			 $('#amend_image_check_-'+id_card).removeAttr('disabled');
        }

		if($('#inputImage').val() != ''){
			var imagePdf =  $('#inputImage').val();	
		}else{
			var imagePdf = $(".crop_image_preview").attr('src');
		}

        var uploadImageObject = [];
        uploadImageObject.data = {};
        uploadImageObject.url =  '/bank/fileupload';
        uploadImageObject.data['image'] = imagePdf;
        uploadImageObject.data['image_type'] = $(".document_name").attr('id');
        uploadImageObject.data['name'] = $(".document_name").attr('name');
        if(id.substr(-8) == '_img'){
            uploadImageObject.data['form_id'] = $(".document_name").attr('value');
        }
        uploadImageObject.data['functionName'] = 'ImageCallBack';

        crudAjaxCall(uploadImageObject);
        return false;
    });

    //-------------pdf upload document for amended flow------------------\\
	// Upload Crf document 13_02_2023 comment code not using 
    // $('#uploadPdfAmend').on('click',function(){
    // $('body').on('click','#uploadPdfAmend',function(){

    // 	var docPath = $('#inputImage').val();
    // 	var documentName = docPath.split('\\')[2];
    // 	var extension = docPath.split('.')[1];

    // 	if(extension != 'pdf'){

    // 		$.growl({message:'Please upload pdf document !!'},{type:'warning'});
    // 		return false;
    // 	}


    // 	//--------------check max upload size 4mb------------------------\\

    // 	var documentPath = document.getElementById('inputImage');
    // 	var fileExists = documentPath.files.length;

    // 	if(fileExists > 0){

    // 		var fileLength = documentPath.files.item(0).size;
    // 		var fileSize = Math.round(fileLength/1024);

    // 		if(fileSize < 4096){

    // 		}else{
    // 			$.growl({message:'Please upload less than 4mb document!'},{type:'warning'});
    // 			return false;
    // 		}
    // 	}else{
    // 		$.growl({message:'Please upload pdf document !!'},{type:'warning'});
    // 		return false;
    // 	}

	// 	var crfNumber = $('#crf_number').text();

	// 	var pdfdata = new FormData();
	// 	jQuery.each(jQuery('#inputImage')[0].files, function(i, file) {
	// 		pdfdata.append('crffile', file);
	// 		pdfdata.append('crfdata',crfNumber);
	// 	});

	// 	var url = '/bank/pdffileupload';
	// 	var functionName = 'pdfDocumentSaveCallBack';
		
	// 	crudDocAjaxCall(pdfdata,url,functionName);
	// 	return false;
		
    // });

    //---------------------end pdf upload document----------------------\\

    $("body").on("click",'.deleteamendImage',function(){

		var getevId = $(this).attr('id');
		if(typeof(getevId) != "undefined"){
			var evdId =  getevId.split('-')[1];
			var imageType = $('#document_preview_amend_card-'+evdId).attr('src');
			var image = "";
			if(typeof(imageType) != 'undefined'){
				image = imageType.split('/');
			}else{
				var pdfType = $('#document_preview_amend_card_pdf-'+evdId).attr('href');
				image = pdfType.split('/');
			}
			var image =  atob(image[image.length-1]);
			
		}else{

			var id  = $(this).parent().parent().find('img').attr("id");
			var evdId = id.split('-')[1];	
			var checkImage = $(this).parent().parent().find('img').attr("src");

			if(typeof(checkImage) != "undefined"){
				var image = $(this).parent().parent().find('img').attr("src").split('/')[5];
			}
		}

      	var crf_number = $('#evidence_crf_number').text();

        var deleteImageObject = [];
        deleteImageObject.data = {};
        deleteImageObject.url =  '/bank/amendDeleteImage';
        deleteImageObject.data['image_div'] = $(this).parent().parent().attr("id");
        deleteImageObject.data['imageName'] = image;
        deleteImageObject.data['evidenceId'] = evdId;
        deleteImageObject.data['crfNumber'] = crf_number;
        deleteImageObject.data['functionName'] = 'AmendDeleteImageCallBack';

        crudAjaxCall(deleteImageObject);
        return false;
    });

	$('body').on('click','.pdfDelete',function(){
		var cardId  =  $(this).parent().parent().parent().parent().attr('id');
		var evId =  cardId.split('-')[1];
		var pdfName = $('#'+evId+'_pdf').text();
		var deleteImageObject = [];
        deleteImageObject.data = {};
        deleteImageObject.url =  '/bank/amendDeleteImage';
        deleteImageObject.data['image_div'] = $(this).parent().parent().attr("id");
        deleteImageObject.data['imageName'] = pdfName;
        deleteImageObject.data['evidenceId'] = evId;
        deleteImageObject.data['crfNumber'] = '';
        deleteImageObject.data['functionName'] = 'AmendDeleteImageCallBack';
		
        crudAjaxCall(deleteImageObject);
        return false;
	});

	//image end 

	$('#amendItem').on('click',function(){

		var getcust_acctNo = document.getElementById('getcust_acctNo').innerText;
		var accountNumber = document.getElementById('accountNumber').innerText;
		
		var checkStatus = rulesSelectedField();

		if(!checkStatus.status){
			$.growl({message:checkStatus.message},{type:'warning'})
			return false;
		}

		var selected = [];
		$("input:checkbox[name=type]:checked").each(function() {
		    selected.push($(this).attr('id').split('-')[1]);
		});
		
		
		var obj = [];
		obj.data = {};
		obj.url ='/bank/ameditemselected';
		obj.data['getcust_acctNo'] = getcust_acctNo;
		obj.data['accountNumber'] = accountNumber;
		obj.data['selectedItem'] = selected;
		obj.data['functionName'] = 'selectdocumentCallBack';

		crudAjaxCall(obj);
		return false;
	});

	$('.save_field').click(function(){
		var id = $(this).attr('id').substr(5);
		var validfunc = $('#input_'+id).data('func');

		if(typeof(validfunc) == 'undefined'){
			validfunc = $('#amend_toggle_'+id).data('func');
		}					 								
  
		var data1 = $('#input_'+id).val();
		var data2 = $('#amend_toggle_'+id).val();								

		var description = $('#description_'+id).text();

		if(validfunc !=''){
			var response = window[validfunc].apply(window,[id]);
			if(typeof(response) != 'undefined' && !response){
			$.growl({message:'Validation failed. Please recheck'},{type:'warning'});
			return false;
			}
		}
 
		if((data1 == "" || data2 == "") && $(this).attr('mandatory') == 'Y'){
			$.growl({message:'Please select '+description+' field'},{type:'warning'});
			return false;
		}else{
			$('#input_'+id).prop('disabled',true);
			$('#save_'+id).hide();
			$('#edit_'+id).show();

		}

		var toggleData = $('#amend_toggle_'+id).val();
		if(typeof(toggleData) != 'undefined'){
			$('#amend_toggle_'+id).prop('disabled',true);

		}
		
		// if($('.save_field:visible').length==0 || mandatoryImagesUploaded()){
		if($('.save_field:visible').length==0 ){
			// $('#saveUpload').show();
			$('#saveAmendData').removeClass('disabled');

		}

	});

	$('.edit_field').click(function(){
		var id = $(this).attr('id').substr(5);
		$('#edit_'+id).hide();
		$('#input_'+id).prop('disabled',false);
		$('#amend_toggle_'+id).prop('disabled',false);
		$('#save_'+id).show();

		// if($('.save_field:visible').length > 0 || !mandatoryImagesUploaded()){
		if($('.save_field:visible').length > 0){

			$('#saveAmendData').addClass('disabled');
		}
	});

	$('#saveAmendData').on('click',function(){

		if(!mandatoryImagesUploaded()){
			$.growl({message:"Please upload mandatory document."},{type:"warning"});
			return false;
		}

		if($('.save_field:visible').length > 0){
			$.growl({message:"Please select valid data."},{type:"warning"});
			return false;
		}
		var chekcOvdvalid = kyc();

		if(!chekcOvdvalid){
			return false;
		}

		var newDataObj = {};
		var ekycAdditiondata = {};
		var newDisplayData = {};
		var inputFieldCheck = {};
		var accountList = {};
		var comuAddData = {};

		if(typeof($('#amendComuAddProof:visible').val()) != 'undefined'){
			comuAddData['addproof_id'] = $('#commuaddid').val();
			comuAddData['addproof_no'] = $('#commuaddnumber').val();
		}

		var proof_Id = $('#proof_of_identity').val();

		ekycAdditiondata['proof_id'] = proof_Id;

		var number = $('#number_of_indentity').val();
		
		if(typeof(number) != "undefined"){
			ekycAdditiondata['id_code'] =  rsenc(number,$('meta[name="cookie"]').attr('content').split('.')[2]);
		}

		var dateVal = $('#dateProofId').val();
		var issuesdateValue = $('#issuedateProofId').val();
			ekycAdditiondata['id_date'] = dateVal;
		ekycAdditiondata['issues_id_date'] = issuesdateValue;
		$('.amendRow').each(function(){

		if(typeof($('.customChecks:visible').val()) != 'undefined'){

			$('.customChecks').each(function(){

				var checkId = $(this).attr('id');
				var getCheckId = checkId.substr(12);

				if($('#'+checkId+':checked').val() == 'on'){

					var accSeqVal = $('#act_seq'+getCheckId).text();
	       			accountList['act_seq'+getCheckId] = accSeqVal;
				}
			});
		}

			var anendNewData = $('.amendRow').find('.input_field:visible');

		    anendNewData.each(function(){
				var getUniId = $(this).attr('id').split('_')[1];
				if(getUniId == 32){
					newDataObj[$(this).attr('id')] = rsenc($(this).val(),$('meta[name="cookie"]').attr('content').split('.')[2]);
				}else{
		       	newDataObj[$(this).attr('id')] = $(this).val();
				}
		       	var id = $(this).attr('id');
		       	newDisplayData[$(this).attr('id')] = $('#'+id+' :selected').text();

				inputFieldCheck[$(this).attr('id').split('_')[1]]= 'Y';											  
		       	
		    });

			var amendNewtoggleData = $(this).find('.toggle_field');

				amendNewtoggleData.each(function(){
					var toggleId = $(this).attr('id');
					var statusToggle = '';

					if($('#'+toggleId+':checked').val() == 'on'){
						statusToggle = 'Initiated';
					}else{
						statusToggle = 'Initiated';
					}

					newDataObj[$(this).attr('id')] = statusToggle;
					newDisplayData[$(this).attr('id')] = statusToggle;

					inputFieldCheck[$(this).attr('id').split('_')[2]] = 'N';

				});											  						 
		});
		
	//--------image selected---------\\

		var imagedata = {}; 
		var ovd_check = {};	
		var checkOVD = '';
		var checkImage = '';		 
		$('.imageEvidenceData').each(function(){
			
			$('.amend_image').each(function(){
				var id = $(this).attr('id').split('-')[1];
				var imageName = $(this).attr('src').split('/')[4];
				var getCurrYear =  new Date().getFullYear();


				if(imageName != getCurrYear){

					if(imageName != ''){

						if($('#amend_image_check_-'+id).is(":checked") == false && $('#amend_image_check_-'+id).attr('data-mandatory') == 'Y'){
	            			checkOVD = false;
	            
						}else if($('#amend_image_check_-'+id).is(":checked") == true){
							ovd_check[id] = 'Y';
	            			checkOVD = true;

						}

					imagedata[id] = imageName;

					}

				}else{

					var id = $(this).attr('id').split('-')[1];
					var imageName = $(this).attr('src').split('/')[6];
					if(imageName != ''){

						if($('#amend_image_check_-'+id).is(":checked") == false && $('#amend_image_check_-'+id).attr('data-mandatory') == 'Y'){
							checkOVD = false;

						}else if($('#amend_image_check_-'+id).is(":checked") == true){
							ovd_check[id] = 'Y';
	            			checkOVD = true;

						}
		 
					imagedata[id] = imageName;
					}
				}
			});

			$('.pdfDocumentSave').each(function(){
	
				var pdfDocId = $(this).attr('id');
				var getpdfId = pdfDocId.split('_')[0];
				imagedata[getpdfId] = $('#'+pdfDocId).text();
				 
			});
		});

		
		if($('.amendImageCard').val() == ''){

			if(!mandatoryOsvCheck()){
			    $.growl({message:"Kindly confirm OSV for images uploaded"},{type: "warning"});
			    return false;
			}
		}

		$('#saveAmendData').addClass('disabled','disabled');

		var obj = [];
		obj.data = {};
		obj.url = '/bank/insertnewdata';
		obj.data['amendNewData'] = newDataObj;
		obj.data['newDisplayData'] = newDisplayData;
		obj.data['ekycAddData'] = ekycAdditiondata;
		obj.data['commuAddData'] = comuAddData;
		obj.data['imageData'] = imagedata;
		obj.data['ovdCheck'] = ovd_check;	
		//--account number for acm field --\\
		obj.data['accountSeq'] = accountList;

		obj.data['inputFieldCheck'] = inputFieldCheck;												 
		obj.data['functionName'] = 'insertedDataCallBack';
		crudAjaxCall(obj);
		return false;
		

	});

	$('#printamendForm').on('click',function(){

		var obj = [];
		obj.data = {};
		obj.url = '/bank/printrequestform';
		obj.data['crf_number'] = $('#crf_number').text();
		obj.data['printCall'] = 'Y';
		obj.data['functionName'] = 'printRequestForm';
		crudAjaxCall(obj);
		return false;
	});

	$('#uploadcrfImage').on('change',function(){
		var crf_document = $('#uploadcrfImage').val();
		// var extension = crf_document.split('.')[1];
		var extension = crf_document.substr(-3).toLowerCase();

		if(extension == 'jpg' || extension == 'png' || extension == 'pdf'){
			$.growl({message:'Successfully selected crf document.'},{type:'success'});
		}else{
		
			$('#uploadcrfImage').val('');
		}
	});

	$('#updateCrfImageFlag').on('click',function(){
		var crfImageName = $('#uploadcrfImage').val();
		var imageName = crfImageName.split('\\')[2];

		if(typeof(imageName) == 'undefined'){
			$.growl({message:'Please upload crf document !!'},{type:'warning'});
			//return false;
		}



	});
//--------check authentication--------\\

	$('.submitamenddetailNpc').on('click',function(){

		var userName =  $('#submission_user_name').val();
		var password = $("#submission_user_password").val();
		var imageOsvData = {};
		if(userName == ''){
			$.growl({message:'Blank / Invalid Credentials'},{type:'warning'});
			return false;
		}

		if(password == ''){
			$.growl({message:'Blank / Invalid Credentials'},{type:'warning'});
			return false;
		}
        password = encrypt(password,btoa($('meta[name="cookie"]').attr('content')).substr(0,6));
        password += '='+btoa($('meta[name="cookie"]').attr('content')).substr(0,6);
        password += paddingsalt($('meta[name="cookie"]').attr('content'));

		$('.submitamenddetailNpc').attr('disabled','disabled');
		$('.br_submit_loader').css('display','block')

        var approvalType = $('#approvalType').val();
    	var crf_document = '';
		var crfUploadDiv = '';  
		var image_name = '';

		var pdfDocChekc = $('#pdfclickdownload').val();
		

    	if(approvalType == 'offline'){

		//-------with crf document-------\\   	

    			crfUploadDiv = $('#crf_document_div').val();
    			if(crfUploadDiv == 'crfDocument'){

					if(pdfDocChekc == ''){
						// crf_document = $('#pdfclickdownload').attr('href');
			    		// image_name = crf_document.split('/')[1];
			    		// image_name = pdfname.split('.')[0];
						
						var image_name = $('.pdfDocumentSave').text();
			    		imageOsvData['1'] = image_name;

					}else{

						if(typeof($('.pdfDocumentSave:visible').val()) != "undefined"){
							
							image_name = $('.pdfDocumentSave').text();
						}else{
							crf_document = $('#document_preview_amend_card-1').attr('src');
							image_name = crf_document.split('/')[4];
						}
						imageOsvData['1'] = image_name;
					}
		    		var checkStatus = saveOffilneCrfDocument(userName,password,image_name);
		    		if(!checkStatus){
		    			return false;
		    		}
    			}

		//-------first time click review submit and open modal --------\\

	   		$('.subOsvImage').each(function(){
	   			var id = $(this).attr('id');
	   			var evid = id.split('-')[1];
	   			var imageSrc =  $(this).attr('src');

				if(typeof(imageSrc) != "undefined"){
					var imageName = imageSrc.split('/')[4];
					imageName = atob(imageName).split('/')[3];
				}

	   			// imageOsvData[evid] = imageName;

				if(typeof(imageSrc) == "undefined"){
					var imageName  =  $('#pdf_image-'+evid).text();
				}
				imageOsvData[evid] = imageName;


	   		});
    	}else{
			approvalType = 'auto';
		}
		
		var objcheckAuth = [];
		objcheckAuth.data = {};
		objcheckAuth.url = '/bank/savecrfdocument';
		objcheckAuth.data['functionName'] = 'checkAmendAuthFunction'; 
		objcheckAuth.data['userName'] = userName;
		objcheckAuth.data['password'] = password;
		objcheckAuth.data['imageName'] = image_name;
		objcheckAuth.data['imageosvData'] = imageOsvData;
		objcheckAuth.data['approvalType'] = approvalType;
		// objcheckAuth.data['crfImage'] = image_crfName;
		objcheckAuth.data['crf_number'] = $('#crf_number').text();
		crudAjaxCall(objcheckAuth);
        // disableSaveAndContinue(this);
		return false;

	});

	//ekyc details
	$('#amendidProofSelect').on('click',function(){
		var proof_Id = $('#proof_of_identity').val();
		var proof_number = $('#getIdNumber').val();
		var proof_date = $('#dateProofId').val();

	});

	$('#amendekyc').on('change',function(){

        var toggleekyc = $('#risk_order:checked').val();

        if(typeof(toggleekyc) == 'undefined'){

            $('.withoutEkyc').css('visibility','hidden');
            $('.withEyc').css('visibility','visible');

        }else{
            $('.withEyc').css('visibility','hidden');
            $('.withoutEkyc').css('visibility','visible');

        }
    });

    $('.submitCrf').on('click',function(){

    	var id = $(this).attr('id');

    	$('#approvalType').val();
    	$('#Username_passowrd-blck').modal('show');

    });

    $('#save_crf_form').on('click',function(){
    	var crf_document = $('#document_preview_crf_card-1').attr('src');
    	var crfPdfDoc = $('#pdfclickdownload').val();

		var pdfDocument =  $('#1_pdf').text();

		if((typeof(crf_document) == 'undefined') && (typeof(crfPdfDoc) == 'undefined') && typeof(pdfDocument) == 'undefined'){
			$.growl({message:'Please upload CRF!!'},{type:'warning'});
			return false;
		}
		if(($('#amend_image_check_-1').is(':checked') == false) && ($('#amend_image_check_-1').attr('data-mandatory') == 'Y')){

			$.growl({message:"Kindly confirm signature verified for CRF uploaded"},{type: "warning"});
			return false;
		}
    	$('#Username_passowrd-blck').modal('show');
    });


    $('.otherDocDisplay').on('click',function(){
    	$('.otherDocumentsDiv').show();
    });

   	//------------custom check for account check box case for ACM level box------------\\

	$('.customChecks').on('click',function(){

		var checkid = $(this).attr('id');
		var getId = checkid.substr(12);
		var getCheck = checkid.split('_')[1];

		
		

		if($('#'+checkid+':checked').val() == 'on'){
		 $('.checkMultiple_'+getCheck).prop("checked", true);
		 		displaydataVal();
				
			$('.amendrow').each(function(){


				var anendNewData = $(this).find('.input_field');
			    anendNewData.each(function(){
					var getid = 'input'+getId;
				    $('#'+getid).removeAttr('style');
				});
				
				var saveButton = $(this).find('.save_field');	
				saveButton.each(function(){
					var getid = 'save'+getId;
				    $('#'+getid).removeAttr('style');
				});

				var getid =  'amend_switch'+getId;
				$('#'+getid).removeAttr('style');
				
			});

		}else{
			
		 $('.checkMultiple_'+getCheck).prop("checked", false);
		 displaydataVal();
			$('.amendrow').each(function(){

				var anendNewData = $(this).find('.input_field');
			    anendNewData.each(function(){
			    	var getid = 'input'+getId;
			    	$('#'+getid).css('display','none');
				});

				var saveButton = $(this).find('.save_field');	
				saveButton.each(function(){
					var getid = 'save'+getId;
				    $('#'+getid).css('display','none');

				});
					
				var getid =  'amend_switch'+getId;
				$('#'+getid).css('display','none');
				
			});
			
		}
	});

	//--------------check for already select or not-------------------\\

		displaydataVal();

	//---------------end for this------------------------------------\\
	// $('.customChecks').each(function(){

	// 	var checkId = $(this).attr('id');
	// 	var lastId = checkId.substr(12);

	// 	if($('#'+checkId+':checked').val() == 'on'){
			
	// 		$('.amendrow').each(function(){
	// 			var inputId = 'input'+lastId;
	// 			var saveId = 'save'+lastId;
	// 			var toogleId = 'amend_switch'+lastId;

	// 	    	$('#'+inputId).removeAttr('style');
	// 		    $('#'+saveId).removeAttr('style');
	// 		    $('#'+toogleId).removeAttr('style');

	// 		});

	// 	}else{

	// 		$('.amendrow').each(function(){
	// 			var inputId = 'input'+lastId;
	// 			var saveId = 'save'+lastId;
	// 			var toogleId = 'amend_switch'+lastId;

	// 	    	$('#'+inputId).css('display','none');
	// 		    $('#'+saveId).css('display','none');
	// 		    $('#'+toogleId).css('display','none');

	// 		});
	// 	}
	// });

	$('#sameasPermanant').on('change',function(){
		var perdata = [];
		var comudata = [];
		$('.validation_10').each(function(){
			var perId = $(this).attr('id');
			perdata.push(perId);
		});
		$('.validation_11').each(function(){
			var comId = $(this).attr('id');
			comudata.push(comId);
		}); 
		var getTest = '';
		var disabledChck = [];
		if($('#sameasPermanant:checked').val() == 'on'){
			for(var i=0;perdata.length>i;i++){
				var perId = document.getElementById(perdata[i]).disabled;
				disabledChck.push(perId);
				if(perId != true){
					$('#'+comudata[i]).val('');
				}else{
					$('#'+comudata[i]).val($('#'+perdata[i]).val());
				}
			}
		}else{
			for(var i=0;perdata.length>i;i++){
				$('#'+comudata[i]).val('');
			}
			$('#amendComuAddProof').css('display','');
			$('#amend_card_proof-37').css('display','');
			$('#amend_card_proof-36').css('display','');
		}
		
		if(disabledChck[0] == true && disabledChck[1] == true && disabledChck[2] == true && disabledChck[3] == true 
			&& disabledChck[4] == true && disabledChck[5] == true && disabledChck[6] == true){
				
				$('#sameasPermanant').prop('checked',true);
				$('#amend_card_proof-37').css('display','none');
				$('#amend_card_proof-36').css('display','none');
				$('#amend_image_check_-36').css('display','none');
				$('#amendComuAddProof').css('display','none');
				$('.save_per-11').css('display','none');
				$('.edit_per-11').css('display','none');
				$('.validation_11').prop('disabled',true);
				$('#saveAmendData').removeClass('disabled');
				$.growl({message:'Permanent address select successfully as communication.'},{type:'success'});
				return false;
			}else{
				$('#sameasPermanant').prop('checked',false);
				$('#amend_card_proof-37').css('display','');
				$('#amend_card_proof-36').css('display','');
				$('#amendComuAddProof').css('display','');
				$('.validation_11').prop('disabled',false);
				$('.save_per-11').css('display','');
				$('#saveAmendData').addClass('disabled');
				$.growl({message:'Please select Permanent address.'},{type:'warning'});
				return false;
			}

	});
});


function updateCrfCallbackBackFunction(response,object){
	if(response['status'] == 'success'){ 
		$.growl({message:response['msg']},{type:response['status']});
		$.growl({message:'CRF form submitted/processed successfuly'},{type:response['status']});
		setTimeout(function(){
	   		redirectUrl('',response['url']);
		}, 3000);
	}else{
		$.growl({message:response['msg']},{type:response['status']});
		return false;
	}
}



//----------amend pending form------------\\ 
//----------save crf documnet modal check----------\\

function submitCrfForm_func(){
		var approvalType = $('#approvalType').val();
    	var crf_document = '';
    	var type_mode  = '';
    	var image_crfName = '';

    	if(approvalType == 'offline'){

    		crf_document = $('#document_preview_crf_card-1').attr('src');
    		if(typeof(crf_document) == 'undefined'){
    			// $.growl({message:'Please upload CRF!!!'},{type:'warning'});
    			// return false;
    		}
    		image_crfName = crf_document.split('/')[5];
    	
    	}

	  
    	var obj = [];
		obj.data = {};
		obj.url = '/bank/submitcrf';
		obj.data['approvalType'] = approvalType;
		obj.data['crfImage'] = image_crfName;
										  
		crudAjaxCall(obj);
		return false;
}


function savecrf_cb(response, object) {
	if(response['status'] == 'success'){ 
		$.growl({message:response['msg']},{type:response['status']});
		setTimeout(function(){
			var data = JSON.stringify(response['data']);
	   		redirectUrl(data,response['url']);
		}, 3000);
	}else{
		enableSaveAndContinue($('#submitamenddetailNpc')[0]);
        enableSaveAndContinue(this);
		$('#Username_passowrd-blck').modal('hide');
		$.growl({message:response['msg']},{type:'warning'});
		return false;
	}

}

function amendpendingForm(crf_number){

	// redirectUrl(crf_number,'/bank/getamendformSelectedCrf');
	var data = {};
	data['crfNumber'] = crf_number;
	data['breadCrumBack'] = 'Y';
	data = JSON.stringify(data);
	redirectUrl(data,'/bank/amendform');	
}	

function invokePendingCRF(crf_number){
		if(crf_number != ''){
			$.growl({message:'Processing CRF form. Please wait..'},{type:'success'});
			amendpendingForm(crf_number);
		}else{
			$.growl({message:'Form is aborted please recheck!'},{type:'warning'});
		}
	}
//----------end amend pending form logic---------------//

function custidaccfetchDataCallBackFunction(response,object){
	//redirectUrl([],'/bank/fetchdataperselectedid');
	if(response['status'] == 'success'){
		$.growl({message:response['msg']},{type:response['status']});
		var data = JSON.stringify(response.data);
		if(response.data.pagerNo == ''){
			$.growl({message:'RMN Mobile number not at finacle.'},{type:'warning'});
		}
		if(response.data.chkblankMailId == ''){
			$.growl({message:'Email_Id not at finacle.'},{type:'warning'});
		}
		setTimeout(function(){
			redirectUrl(data,'/bank/getcustomeraccountdetails');
		},2000);

		return false;
	}else{
		$.growl({message:response['msg']},{type:'warning'});
		return false;
	}
}

function selectdocumentCallBackFunction(response,object){
	if(response['status'] == 'success'){
		var data = JSON.stringify(response.data);
		$.growl({message:response['msg']},{type:response['status']});
		redirectUrl(data,'/bank/amendinput');
		return false;
	}else{
		$.growl({message:response['msg']},{type:'warning'});
		return false;
	}
}

function insertedDataCallBackFunction(response,object){
	if(response['status'] == 'success'){
		$.growl({message:response['msg']},{type:response['status']});

		redirectUrl(response.data,'/bank/amendform');
		return false;
	}else{
		$('#saveAmendData').removeClass('disabled');
		$.growl({message:response['msg']},{type:'warning'});
		return false;
	}
}

function fetchdatacustidCallBackFunction(response,object){
	if(response['status'] == 'success'){
		var data = JSON.stringify(response.data);
		$.growl({message:response['msg']},{type:response['status']});
		setTimeout(function(){
			redirectUrl(data,'/bank/fetchdataperselectedid');
		},3000)
		return false;
	}else{
		$.growl({message:response['msg']},{type:'warning'});
		return false;
	}
}


function getAmendDataApplications(url,table,tableRemainingHeight){
    var tableObject = [];
    tableObject.data = {};
    tableObject.data['table'] = table;
	tableObject.data['crfNumber'] = $("#crfNumber").val();
	tableObject.data['customerId'] = $("#customerId").val();
    tableObject.url = url;
    datatableAjaxCall(tableObject,tableRemainingHeight);
    return false;
}
// function getAmendNpcDashboard(url,table,tableRemainingHeight){
// 	var tableObject = [];
// 	tableObject.data = {};
// 	tableObject.data['table'] = table;
// 	tableObject.url = url;
// 	datatableAjaxCall(tableObject,tableRemainingHeight);
// 	return false;
// }

//pincode details
function fetcPincodeDataCallBackFunction(response,object){
	if(response['status'] == 'success'){
		$.growl({message:response['msg']},{type:response['status']});
		var data = response.data;
		var id = data[0].split('_');

		var fieldID = id[0]+'_'+id[1]+'_';
		var cntr = parseInt(id[2])+1;
		var btnid = fieldID.split('_')[1];

		$('#'+fieldID+cntr).val(data[1].citydesc).attr('disabled','disabled');
		$('#save_'+btnid+'_'+cntr).remove();
		cntr++;
		$('#'+fieldID+cntr).val(data[1].statedesc).attr('disabled','disabled');
		$('#save_'+btnid+'_'+cntr).remove();

		cntr++;
		$('#'+fieldID+cntr).val(data[1].countrydesc).attr('disabled','disabled');
		$('#save_'+btnid+'_'+cntr).remove();

		return false;
	}else{
		var data = response.data;
		$('#'+data[0]).removeAttr('disabled');
		var id = data[0].split('_');
		$('#save_'+id[1]+'_'+id[2]).css('display','');
		$('#edit_'+id[1]+'_'+id[2]).css('display','none');
		$.growl({message:response['msg']},{type:'warning'});
		return false;
	}
}

function mandatoryImagesUploaded(){
	response = true;
	$('.upload_document_amend:visible').each(function(idx,obj){
		if($(obj).attr('mandatory')=='Y'){

			response = false;
		}

	});
	return response;
}

function saveOffilneCrfDocument(userName,password,imageName){

	var obj = [];
	obj.data = {};
	obj.url = '/bank/uploadcrfapproval';
	obj.data['functionName'] = 'uploadCRFDocumentCall';
	obj.data['crfNumber'] = $('#crf_number').text();
	obj.data['approvalType'] = $('#approvalType').val();
	obj.data['userName'] = userName;
	obj.data['password'] = password;
	obj.data['crfDocument'] = imageName;
	crudAjaxCall(obj);
	return false;

}

function mandatoryOsvCheck(){
	response = true;
	$('.osv_amned_done_check').each(function(idx,obj){
		var id = $(this).attr('id');
		if(id == "amend_image_check_-36"){
			if($('#amend_image_check_-36:visible').val() != 'on' && $('#'+id+':checked').val() != 'on' && typeof($('#amend_image_check_-36:visible').val()) != 'undefined'){
				return response = false;
			}
		}
		// if(($(obj).attr('data-mandatory')=='Y' && typeof($('#'+id+':checked').val()) != 'undefined')){
		if(($(obj).attr('data-mandatory')=='Y' && $('#'+id+' :visible').prop('checked') == false)){
			response = false;
		}

	});
	return response;
}							 

function displaydataVal(){

	$('.customChecks').each(function(){

		var checkId = $(this).attr('id');
		var lastId = checkId.substr(12);

		if($('#'+checkId+':checked').val() == 'on'){
			
			$('.amendrow').each(function(){
				var inputId = 'input'+lastId;
				var saveId = 'save'+lastId;
				var toogleId = 'amend_switch'+lastId;
				// var editId = 'edit'+lastId;

		    	$('#'+inputId).removeAttr('style');
			    // $('#'+saveId).removeAttr('style');
			    $('#'+toogleId).removeAttr('style');
			    // $('#'+editId).removeAttr('style');

			});

		}else{
			
			$('.amendrow').each(function(){
				var inputId = 'input'+lastId;
				var saveId = 'save'+lastId;
				var toogleId = 'amend_switch'+lastId;
				var editId = 'edit'+lastId;

		    	$('#'+inputId).css('display','none');
			    $('#'+saveId).css('display','none');
			    $('#'+toogleId).css('display','none');
			    $('#'+editId).css('display','none');
			});
		}
	});
}
																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																	  
 $('#customCheck-15').on('click',function(){
        checkGenderSelect();
    });

        // checkGenderSelect();

    $('#customCheck-18').on('click',function(){
        if($('#customCheck-18:checked').val() == 'on'){
            $('#customCheck-10').prop('checked',true);
            $('#customCheck-10').attr('disabled','disabled');
            $('#customCheck-6').prop('checked',true);
            $('#customCheck-6').attr('disabled','disabled');
            $('#customCheck-5').prop('checked',true);
            $('#customCheck-5').attr('disabled','disabled');
            $('#customCheck-15').prop('checked',true);
            $('#customCheck-15').attr('disabled','disabled');
		
            checkGenderSelect();
        }else{
            $('#customCheck-10').prop('checked',false);
            $('#customCheck-10').removeAttr('disabled');
            $('#customCheck-6').prop('checked',false);
            $('#customCheck-6').removeAttr('disabled');
            $('#customCheck-5').prop('checked',false);
            $('#customCheck-5').removeAttr('disabled');
            $('#customCheck-15').prop('checked',false);
            $('#customCheck-15').removeAttr('disabled');
			$('#customCheck-54').prop('checked',false);
			
            checkGenderSelect();
        }
    });

    // minot turn major 

    // $('#customCheck-20').on('click',function(){
    //     if($('#customCheck-20:checked').val() == 'on'){
    //         $('#customCheck-24').prop('checked',true);
    //         $('#customCheck-24').attr('disabled','disabled');
    //         $('#customCheck-25').prop('checked',true);
    //         $('#customCheck-25').attr('disabled','disabled');
    //         //$('#customCheck-18').prop('checked',true);
    //         //$('#customCheck-18').attr('disabled','disabled');
    //     }else{
    //         $('#customCheck-24').prop('checked',false);
    //         $('#customCheck-24').removeAttr('disabled');
    //         $('#customCheck-25').prop('checked',false);
    //         $('#customCheck-25').removeAttr('disabled');
    //         //$('#customCheck-18').prop('checked',false);
    //         //$('#customCheck-18').removeAttr('disabled');
    //     }
    // });

    // change of signatoris to selected man mop

    $('#customCheck-24').on('click',function(){
        if($('#customCheck-24:checked').val() == 'on'){
            $('#customCheck-25').prop('checked',true);
            $('#customCheck-25').attr('disabled','disabled');
        }else{
            $('#customCheck-25').prop('checked',false);
            $('#customCheck-25').removeAttr('disabled');
        }
    });


    $('#customCheck-49').on('click',function(){
         if($('#customCheck-49:checked').val() == 'on'){
            $('#customCheck-25').prop('checked',true);
            $('#customCheck-25').attr('disabled','disabled');
            $('#customCheck-24').prop('checked',true);
            $('#customCheck-24').attr('disabled','disabled');
        }else{
            $('#customCheck-25').prop('checked',false);
            $('#customCheck-25').removeAttr('disabled');
            $('#customCheck-24').prop('checked',false);
            $('#customCheck-24').removeAttr('disabled');
        }
    });

	$('#customCheck-50').on('click',function(){
		if($('#customCheck-50:checked').val() == 'on'){
		   $('#customCheck-25').prop('checked',true);
		   $('#customCheck-25').attr('disabled','disabled');
		   $('#customCheck-24').prop('checked',true);
		   $('#customCheck-24').attr('disabled','disabled');
	   }else{
		   $('#customCheck-25').prop('checked',false);
		   $('#customCheck-25').removeAttr('disabled');
		   $('#customCheck-24').prop('checked',false);
		   $('#customCheck-24').removeAttr('disabled');
	   }
   });

function checkGenderSelect(){

    if($('#customCheck-15:checked').val() == 'on'){
        $('#customCheck-4').prop('checked',true);
        $('#customCheck-4').attr('disabled','disabled');
    }else{
        $('#customCheck-4').prop('checked',false);
        $('#customCheck-4').removeAttr('disabled');
    }
}																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																  

// aadhaar disabled field any other choice field 
$('.customChecks').on('click',function(){
    if($('.customChecks:checked').val() == 'on'){
        $('#customCheck-32').attr('disabled','disabled');
        $('#customCheck-32').val('');
    }else{
        $('#customCheck-32').removeAttr('disabled');
    }		
});		

$('body').on('click','.savePdfDocData',function(){

	var docFakePath = $('#inputImage').val();
    var docName = docFakePath.split('\\')[2];

	if(typeof(docName) == 'undefined'){
		$('.modal').modal('hide');
		$.growl({message:'Please choose a pdf file'},{type:'warning'});
		return false;
	}
    // var extension =  docFakePath.split('.')[1];
    var extension =  docFakePath.substr(-3).toLowerCase();
    var evId =  $('.document_name').attr('id').split('-')[1];
	// var crfNumber = '';
	var module = 'amend';
	var pdfdata = new FormData();
	jQuery.each(jQuery('#inputImage')[0].files, function(i, file) {
		pdfdata.append('pdf_file', file);
		pdfdata.append('documentId', evId);
		pdfdata.append('module',module);
	});
	if(extension != ''){

		if(extension == 'pdf'){
	
			pdfDocumentUpload(pdfdata);
		}else{
			$.growl({message:'Please choose a pdf file'},{type:'warning'});
			return false;
		}
	}
});

$('body').on('change','#commuaddid',function(){
	var selectId =  $('#commuaddid option:selected').val();
	if(selectId == 29){
		$('#upload_amend_card-36').attr('mandatory','N');
		$('#red-36').css('display','none');
		$('#amend_image_check_-36').attr('data-mandatory','N');
	}else{
		$('#upload_amend_card-36').attr('mandatory','Y');
		$('#red-36').css('display','');
		$('#amend_image_check_-36').attr('data-mandatory','Y');
	}
});
																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																  
																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																  
																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																  
																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																  
																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																  
																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																  
																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																  
																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																  
																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																  
																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																  
																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																  